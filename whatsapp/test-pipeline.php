<?php
declare(strict_types=1);

require __DIR__ . '/lead-pipeline.php';

// Directorios temporales exclusivos para esta prueba.
$suffix = bin2hex(random_bytes(6));

putenv(
    'SOTO_PIPELINE_DIR=' .
    sys_get_temp_dir() . '/soto-pipeline-test-' . $suffix
);

putenv(
    'SOTO_LEADS_DIR=' .
    sys_get_temp_dir() . '/soto-leads-test-' . $suffix
);

// Identificadores ficticios.
$phone = '34999998888';
$otroPhone = '34999997777';

$historial = [
    [
        'role' => 'user',
        'content' =>
            'Hola, soy Laura, de Cafetería Nube en Santander.'
    ],
    [
        'role' => 'assistant',
        'content' =>
            '¿En qué podemos ayudarte?'
    ],
    [
        'role' => 'user',
        'content' =>
            'Quiero que gestionéis nuestro Instagram ' .
            'para conseguir más clientes. ' .
            'Mi correo es laura@example.com.'
    ],
    [
        'role' => 'assistant',
        'content' =>
            '¿Te gustaría recibir una propuesta por email?'
    ],
    [
        'role' => 'user',
        'content' =>
            'Sí, quiero recibir una propuesta personalizada.'
    ]
];

function mostrarEstado(string $titulo, array $resultado): void
{
    echo "\n=== {$titulo} ===\n";
    echo 'Estado: ' . $resultado['estado'] . "\n";
    echo $resultado['mensaje'] ?? '';
    echo "\n";
}

try {
    // Simulación: Claude extrae datos y crea borrador.
    $preparado = pipelinePreparar($phone, $historial);
    mostrarEstado('1. PREPARAR', $preparado);

    if ($preparado['estado'] !== 'pendiente_confirmacion') {
        exit("La prueba no puede continuar.\n");
    }

    // No debe aceptar otro texto.
    mostrarEstado(
        '2. CONFIRMACIÓN INCORRECTA',
        pipelineConfirmar($phone, 'Sí, vale')
    );

    // No debe aceptar confirmación de otro cliente.
    mostrarEstado(
        '3. OTRO CLIENTE',
        pipelineConfirmar(
            $otroPhone,
            SOTO_LEAD_CONFIRMACION
        )
    );

    // Simulamos que el resumen se ha presentado
    // y el cliente ha respondido correctamente.
    mostrarEstado(
        '4. CONFIRMACIÓN VÁLIDA',
        pipelineConfirmar(
            $phone,
            SOTO_LEAD_CONFIRMACION
        )
    );

    // Repetir no debe generar un nuevo registro.
    mostrarEstado(
        '5. CONFIRMACIÓN REPETIDA',
        pipelineConfirmar(
            $phone,
            SOTO_LEAD_CONFIRMACION
        )
    );

    echo "\n=== PRUEBA FINALIZADA ===\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        'ERROR: ' . $e->getMessage() . "\n"
    );
    exit(1);
}
