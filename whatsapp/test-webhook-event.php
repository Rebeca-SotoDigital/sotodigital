<?php
declare(strict_types=1);

require __DIR__ . '/webhook-event-processor.php';

// Directorio temporal independiente.
$suffix = bin2hex(random_bytes(6));

$dir = sys_get_temp_dir() .
    '/soto-webhook-test-' . $suffix;

putenv('SOTO_QUEUE_DIR=' . $dir);

function verificarWebhook(
    bool $condicion,
    string $descripcion
): void {
    if (!$condicion) {
        throw new RuntimeException(
            'PRUEBA FALLIDA: ' . $descripcion
        );
    }

    echo "[OK] " . $descripcion . "\n";
}

function eventoMeta(array $messages): array
{
    return [
        'object' => 'whatsapp_business_account',
        'entry' => [
            [
                'changes' => [
                    [
                        'field' => 'messages',
                        'value' => [
                            'messaging_product' => 'whatsapp',
                            'messages' => $messages
                        ]
                    ]
                ]
            ]
        ]
    ];
}

try {
    echo "\n=== PRUEBA EVENTOS DE META ===\n\n";

    $mensaje = [
        'from' => '34999998888',
        'id' => 'wamid.SOTO_PRUEBA_001',
        'timestamp' => '1795630000',
        'type' => 'text',
        'text' => [
            'body' => 'Hola, quiero mejorar mi Instagram'
        ]
    ];

    // 1. Primer mensaje.
    $r = procesarEventoWebhook(
        eventoMeta([$mensaje])
    );

    verificarWebhook(
        $r['encolados'] === 1,
        'Mensaje nuevo encolado'
    );

    // 2. Meta reenvía exactamente el mismo evento.
    $r = procesarEventoWebhook(
        eventoMeta([$mensaje])
    );

    verificarWebhook(
        $r['duplicados'] === 1,
        'Reenvío duplicado detectado'
    );

    // 3. Otro mensaje del mismo cliente.
    $mensaje2 = $mensaje;
    $mensaje2['id'] = 'wamid.SOTO_PRUEBA_002';
    $mensaje2['text']['body'] =
        'Me interesa una propuesta';

    $r = procesarEventoWebhook(
        eventoMeta([$mensaje2])
    );

    verificarWebhook(
        $r['encolados'] === 1,
        'Segundo mensaje independiente encolado'
    );

    // 4. Mensaje que no es de texto.
    $imagen = [
        'from' => '34999998888',
        'id' => 'wamid.SOTO_IMAGEN_001',
        'type' => 'image'
    ];

    $r = procesarEventoWebhook(
        eventoMeta([$imagen])
    );

    verificarWebhook(
        $r['ignorados'] === 1,
        'Mensaje de imagen ignorado'
    );

    // 5. Evento de estado de entrega.
    $statusEvent = [
        'object' => 'whatsapp_business_account',
        'entry' => [
            [
                'changes' => [
                    [
                        'field' => 'messages',
                        'value' => [
                            'statuses' => [
                                [
                                    'id' => 'wamid.SOTO_PRUEBA_001',
                                    'status' => 'delivered'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    $r = procesarEventoWebhook($statusEvent);

    verificarWebhook(
        $r['encolados'] === 0 &&
        $r['duplicados'] === 0,
        'Notificación de entrega no encolada'
    );

    // 6. Verificar archivos almacenados.
    $files = glob($dir . '/*.json');

    verificarWebhook(
        is_array($files) && count($files) === 2,
        'Existen exactamente dos mensajes en la cola'
    );

    // 7. Recuperar un registro y comprobar su estado.
    $registro = json_decode(
        file_get_contents($files[0]),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    verificarWebhook(
        ($registro['estado'] ?? '') === 'pendiente',
        'El mensaje queda pendiente de procesamiento'
    );

    echo "\n=== TODAS LAS PRUEBAS SUPERADAS ===\n";
    echo "Mensajes reales enviados: 0\n";
    echo "Llamadas a Claude: 0\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        'ERROR: ' . $e->getMessage() . "\n"
    );
    exit(1);
}
