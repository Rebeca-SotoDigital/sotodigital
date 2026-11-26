<?php
declare(strict_types=1);

require __DIR__ . '/conversation-controller.php';

// Datos aislados para esta prueba.
$suffix = bin2hex(random_bytes(6));
$base = sys_get_temp_dir() . '/soto-controller-' . $suffix;

putenv('SOTO_CONVERSATIONS_DIR=' . $base . '/conversations');
putenv('SOTO_PIPELINE_DIR=' . $base . '/pipeline');
putenv('SOTO_LEADS_DIR=' . $base . '/leads');

$phone = '34999998888';

function probar(
    string $titulo,
    string $phone,
    string $mensaje
): array {
    echo "\n=== {$titulo} ===\n";
    echo "CLIENTE: {$mensaje}\n";

    $resultado = responderAlCliente($phone, $mensaje);

    echo 'ESTADO: ' . $resultado['estado'] . "\n";
    echo "SOTO DIGITAL: {$resultado['respuesta']}\n";

    return $resultado;
}

function comprobar(
    bool $condicion,
    string $descripcion
): void {
    if (!$condicion) {
        throw new RuntimeException(
            'Prueba fallida: ' . $descripcion
        );
    }
}

try {

    // Historial comercial ficticio.
    guardarTurno(
        $phone,
        'user',
        'Hola, soy Laura, de Cafetería Nube en Santander.'
    );

    guardarTurno(
        $phone,
        'assistant',
        '¿Qué necesitas mejorar en tu negocio?'
    );

    guardarTurno(
        $phone,
        'user',
        'Quiero gestionar mejor Instagram para ' .
        'conseguir clientes y recibir una propuesta. ' .
        'Mi correo es laura@example.com.'
    );

    // 1. Preparar la propuesta.
    $r = probar(
        '1. PREPARAR',
        $phone,
        'SOLICITAR PROPUESTA'
    );

    comprobar(
        $r['estado'] === 'pendiente_confirmacion',
        'Preparación de propuesta'
    );

    $resumenOriginal = $r['respuesta'];

    // 2. Confirmar sin envío aceptado.
    $r = probar(
        '2. SIN ENVÍO',
        $phone,
        'CONFIRMAR PROPUESTA'
    );

    comprobar(
        $r['estado'] === 'envio_no_confirmado',
        'Bloquear confirmación prematura'
    );

    // 3. Simular que Meta acepta el resumen original.
    $aceptado = marcarResumenAceptado(
        $phone,
        $resumenOriginal,
        true
    );

    comprobar($aceptado, 'Aceptación del primer resumen');

    // 4. Corregir email.
    $r = probar(
        '4. CORRECCIÓN',
        $phone,
        'CORREGIR EMAIL: nuevo@example.com'
    );

    comprobar(
        $r['estado'] === 'corregido',
        'Corrección del email'
    );

    $resumenCorregido = $r['respuesta'];

    // 5. Debe rechazar confirmar sin reenviar.
    $r = probar(
        '5. CONFIRMACIÓN BLOQUEADA',
        $phone,
        'CONFIRMAR PROPUESTA'
    );

    comprobar(
        $r['estado'] === 'envio_no_confirmado',
        'Invalidar resumen anterior'
    );

    // 6. Simular aceptación del resumen corregido.
    $aceptado = marcarResumenAceptado(
        $phone,
        $resumenCorregido,
        true
    );

    comprobar($aceptado, 'Aceptación del resumen corregido');

    // 7. Confirmar la propuesta.
    $r = probar(
        '7. CONFIRMACIÓN FINAL',
        $phone,
        'CONFIRMAR PROPUESTA'
    );

    comprobar(
        $r['estado'] === 'registrado',
        'Registro del lead'
    );

    // 8. Repetir la confirmación.
    $r = probar(
        '8. DUPLICADO',
        $phone,
        'CONFIRMAR PROPUESTA'
    );

    comprobar(
        $r['estado'] === 'registrado',
        'Evitar duplicados'
    );

    $archivos = glob($base . '/leads/*.json');

    comprobar(
        is_array($archivos) && count($archivos) === 1,
        'Debe existir un único lead'
    );

    $lead = json_decode(
        file_get_contents($archivos[0]),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    comprobar(
        ($lead['email'] ?? '') === 'nuevo@example.com',
        'Debe conservar el correo corregido'
    );

    echo "\n=== TODAS LAS PRUEBAS SUPERADAS ===\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        "\nERROR: " . $e->getMessage() . "\n"
    );
    exit(1);
}
