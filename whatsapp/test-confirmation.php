<?php
declare(strict_types=1);

require __DIR__ . '/lead-confirmation.php';

$suffix = bin2hex(random_bytes(6));

putenv(
    'SOTO_PIPELINE_DIR=' . sys_get_temp_dir() .
    '/soto-confirmation-test-' . $suffix
);

putenv(
    'SOTO_LEADS_DIR=' . sys_get_temp_dir() .
    '/soto-confirmation-leads-' . $suffix
);

$phone = '34999998888';

$historial = [
    [
        'role' => 'user',
        'content' =>
            'Soy Laura, de Cafetería Nube en Santander. ' .
            'Quiero una propuesta para gestionar Instagram. ' .
            'Mi correo es laura@example.com.'
    ]
];

function mostrar(string $nombre, string $estado): void
{
    echo $nombre . ': ' . $estado . "\n";
}

try {
    $preparado = prepararConfirmacionLead(
        $phone,
        $historial
    );

    mostrar('1. Preparar', $preparado['estado']);

    if ($preparado['estado'] !== 'pendiente_confirmacion') {
        throw new RuntimeException(
            'No se pudo preparar el borrador'
        );
    }

    // Intentar confirmar antes de enviar el resumen
    $r = procesarConfirmacionLead(
        $phone,
        'CONFIRMAR PROPUESTA'
    );

    mostrar('2. Sin envío', $r['estado']);

    // Simulación de una respuesta HTTP 2xx de Meta.
    $aceptado = marcarResumenAceptado(
        $phone,
        $preparado['mensaje'],
        true
    );

    mostrar(
        '3. Meta acepta resumen',
        $aceptado ? 'aceptado' : 'rechazado'
    );

    // El cliente corrige su email
    $r = procesarConfirmacionLead(
        $phone,
        'CORREGIR EMAIL: nueva@example.com'
    );

    mostrar('4. Corregir email', $r['estado']);

    $resumenCorregido = $r['mensaje'];

    // La confirmación anterior ya no sirve
    $r = procesarConfirmacionLead(
        $phone,
        'CONFIRMAR PROPUESTA'
    );

    mostrar('5. Confirmar sin reenviar', $r['estado']);

    // Simulamos que Meta acepta el resumen corregido
    $aceptado = marcarResumenAceptado(
        $phone,
        $resumenCorregido,
        true
    );

    mostrar(
        '6. Meta acepta corrección',
        $aceptado ? 'aceptado' : 'rechazado'
    );

    // Confirmación válida del cliente
    $r = procesarConfirmacionLead(
        $phone,
        'CONFIRMAR PROPUESTA'
    );

    mostrar('7. Confirmación válida', $r['estado']);

    // No se debe registrar otra vez
    $r = procesarConfirmacionLead(
        $phone,
        'CONFIRMAR PROPUESTA'
    );

    mostrar('8. Repetida', $r['estado']);

    // No permitir sustituir el registro existente
    $r = prepararConfirmacionLead(
        $phone,
        $historial
    );

    mostrar('9. Preparar de nuevo', $r['estado']);

    // Caso independiente: cancelar un borrador simulado
    $otroPhone = '34999997777';

    pipelineEstado(
        $otroPhone,
        function (array $state): array {
            return [
                'guardar' => true,
                'estado' => [
                    'fase' => 'pendiente_confirmacion',
                    'creado_en' => time(),
                    'datos' => []
                ],
                'resultado' => null
            ];
        }
    );

    $r = procesarConfirmacionLead(
        $otroPhone,
        'CANCELAR PROPUESTA'
    );

    mostrar('10. Cancelación', $r['estado']);

    echo "\n=== PRUEBA FINALIZADA ===\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        'ERROR: ' . $e->getMessage() . "\n"
    );

    exit(1);
}
