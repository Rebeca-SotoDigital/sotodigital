<?php
declare(strict_types=1);

/**
 * Prueba integral del agente Soto Digital.
 *
 * Claude: API real.
 * WhatsApp: transporte simulado.
 * Leads: directorio temporal aislado.
 *
 * NO envía WhatsApps ni correos reales.
 */

require_once __DIR__ . '/conversation-controller.php';
require_once __DIR__ . '/whatsapp-bridge.php';

// Seguridad: desactivar expresamente los envíos reales.
putenv('SOTO_WHATSAPP_LIVE_SEND=0');

// Directorios exclusivos de esta ejecución.
$suffix = bin2hex(random_bytes(8));
$base = sys_get_temp_dir() . '/soto-integracion-' . $suffix;

putenv('SOTO_CONVERSATIONS_DIR=' . $base . '/conversations');
putenv('SOTO_PIPELINE_DIR=' . $base . '/pipeline');
putenv('SOTO_LEADS_DIR=' . $base . '/leads');

// Teléfono ficticio.
$phone = '34999998888';

// Simulador de Meta: NO realiza conexiones de red.
$metaCorrecto = static function (
    string $to,
    string $texto
): array {
    return [
        'http_status' => 200,
        'body' => json_encode([
            'messaging_product' => 'whatsapp',
            'messages' => [
                ['id' => 'wamid.SOTO_INTEGRACION_SIMULADA']
            ]
        ], JSON_THROW_ON_ERROR)
    ];
};

$metaError = static function (
    string $to,
    string $texto
): array {
    return [
        'http_status' => 403,
        'body' => json_encode([
            'error' => [
                'message' => 'Error simulado de Meta'
            ]
        ], JSON_THROW_ON_ERROR)
    ];
};

function verificar(
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

function verificarEstado(
    array $resultado,
    string $esperado,
    string $descripcion
): void {
    verificar(
        ($resultado['estado'] ?? '') === $esperado,
        $descripcion . ' (esperado: ' . $esperado .
        ', obtenido: ' .
        ($resultado['estado'] ?? 'sin estado') . ')'
    );
}

try {
    echo "\n=== INTEGRACION SOTO DIGITAL ===\n\n";

    // --------------------------------------------
    // 1. Conversación inicial con Claude
    // --------------------------------------------
    echo "--- 1. CONVERSACION CON CLAUDE ---\n";

    $r = responderAlCliente(
        $phone,
        'Hola, tengo una cafetería y quiero atraer más clientes.'
    );

    verificarEstado(
        $r,
        'respuesta_claude',
        'Claude genera una respuesta comercial'
    );

    verificar(
        trim($r['respuesta']) !== '',
        'La respuesta no está vacía'
    );

    $historial = cargarConversacion($phone);

    verificar(
        count($historial) === 2,
        'Se han guardado los dos turnos'
    );

    // Simulamos que Meta acepta la respuesta normal.
    $envio = ejecutarWhatsAppBridge(
        $phone,
        $r,
        $metaCorrecto
    );

    verificarEstado(
        $envio,
        'aceptado',
        'El puente acepta una respuesta normal'
    );

    // --------------------------------------------
    // 2. Datos comerciales del cliente
    // --------------------------------------------
    echo "\n--- 2. DATOS COMERCIALES ---\n";

    // La cliente ficticia facilita sus datos.
    guardarTurno(
        $phone,
        'user',
        'Soy Laura y mi negocio es Cafetería Nube, ' .
        'en Santander. Quiero que gestionéis Instagram ' .
        'para conseguir más clientes. ' .
        'Mi correo es laura@example.com. ' .
        'Quiero recibir una propuesta personalizada.'
    );

    guardarTurno(
        $phone,
        'assistant',
        'Perfecto, vamos a revisar los datos ' .
        'para preparar tu propuesta por correo.'
    );

    $historial = cargarConversacion($phone);

    verificar(
        count($historial) === 4,
        'La conversación conserva el contexto'
    );

    // --------------------------------------------
    // 3. Preparar propuesta con Claude
    // --------------------------------------------
    echo "\n--- 3. PREPARACION DE PROPUESTA ---\n";

    $r = responderAlCliente(
        $phone,
        'SOLICITAR PROPUESTA'
    );

    verificarEstado(
        $r,
        'pendiente_confirmacion',
        'Claude extrae los datos y PHP prepara el borrador'
    );

    verificar(
        $r['requiere_marcar_envio'] === true,
        'El resumen requiere registrar el envío'
    );

    $resumenOriginal = $r['respuesta'];

    verificar(
        str_contains($resumenOriginal, 'laura@example.com'),
        'El resumen contiene el email original'
    );

    // --------------------------------------------
    // 4. Meta falla: no aceptar la confirmación
    // --------------------------------------------
    echo "\n--- 4. FALLO SIMULADO DE META ---\n";

    $envio = ejecutarWhatsAppBridge(
        $phone,
        $r,
        $metaError
    );

    verificarEstado(
        $envio,
        'envio_fallido',
        'Un rechazo de Meta no se considera éxito'
    );

    $rConfirmacion = responderAlCliente(
        $phone,
        'CONFIRMAR PROPUESTA'
    );

    verificarEstado(
        $rConfirmacion,
        'envio_no_confirmado',
        'No registra un lead tras fallar el envío'
    );

    // --------------------------------------------
    // 5. Meta acepta el resumen original
    // --------------------------------------------
    echo "\n--- 5. ENVIO CORRECTO ---\n";

    $envio = ejecutarWhatsAppBridge(
        $phone,
        [
            'respuesta' => $resumenOriginal,
            'requiere_marcar_envio' => true
        ],
        $metaCorrecto
    );

    verificarEstado(
        $envio,
        'aceptado',
        'Meta acepta el resumen simulado'
    );

    verificar(
        ($envio['resumen_marcado'] ?? false) === true,
        'Se vincula el resumen con el borrador'
    );

    // --------------------------------------------
    // 6. Corregir el correo
    // --------------------------------------------
    echo "\n--- 6. CORRECCION DE DATOS ---\n";

    $r = responderAlCliente(
        $phone,
        'CORREGIR EMAIL: nuevo@example.com'
    );

    verificarEstado(
        $r,
        'corregido',
        'El cliente puede corregir el email'
    );

    verificar(
        str_contains($r['respuesta'], 'nuevo@example.com'),
        'El resumen incorpora el nuevo correo'
    );

    $resumenCorregido = $r['respuesta'];

    // El resumen nuevo todavía no ha sido aceptado.
    $rConfirmacion = responderAlCliente(
        $phone,
        'CONFIRMAR PROPUESTA'
    );

    verificarEstado(
        $rConfirmacion,
        'envio_no_confirmado',
        'La corrección invalida el envío anterior'
    );

    // --------------------------------------------
    // 7. Enviar resumen corregido
    // --------------------------------------------
    echo "\n--- 7. RESUMEN CORREGIDO ---\n";

    $envio = ejecutarWhatsAppBridge(
        $phone,
        [
            'respuesta' => $resumenCorregido,
            'requiere_marcar_envio' => true
        ],
        $metaCorrecto
    );

    verificarEstado(
        $envio,
        'aceptado',
        'Meta acepta el resumen corregido'
    );

    verificar(
        ($envio['resumen_marcado'] ?? false) === true,
        'El borrador queda vinculado al resumen nuevo'
    );

    // --------------------------------------------
    // 8. Confirmación comercial
    // --------------------------------------------
    echo "\n--- 8. CONFIRMACION FINAL ---\n";

    $r = responderAlCliente(
        $phone,
        'CONFIRMAR PROPUESTA'
    );

    verificarEstado(
        $r,
        'registrado',
        'Se registra el lead tras la confirmación'
    );

    // --------------------------------------------
    // 9. Comprobar almacenamiento
    // --------------------------------------------
    echo "\n--- 9. VERIFICACION DEL LEAD ---\n";

    $archivos = glob($base . '/leads/*.json');

    verificar(
        is_array($archivos) && count($archivos) === 1,
        'Existe exactamente un archivo de lead'
    );

    $lead = json_decode(
        file_get_contents($archivos[0]),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    verificar(
        ($lead['nombre'] ?? '') === 'Laura',
        'Nombre del cliente correcto'
    );

    verificar(
        ($lead['negocio'] ?? '') === 'Cafetería Nube',
        'Negocio correcto'
    );

    verificar(
        ($lead['email'] ?? '') === 'nuevo@example.com',
        'Se conserva el correo corregido'
    );

    verificar(
        ($lead['estado'] ?? '') === 'pendiente',
        'Lead pendiente de gestión comercial'
    );

    // --------------------------------------------
    // 10. Evitar confirmaciones duplicadas
    // --------------------------------------------
    echo "\n--- 10. DUPLICADOS ---\n";

    $r = responderAlCliente(
        $phone,
        'CONFIRMAR PROPUESTA'
    );

    verificarEstado(
        $r,
        'registrado',
        'Una confirmación repetida no crea otro lead'
    );

    verificar(
        count(glob($base . '/leads/*.json')) === 1,
        'El almacenamiento mantiene un único registro'
    );

    echo "\n";
    echo "====================================\n";
    echo " TODAS LAS PRUEBAS SUPERADAS\n";
    echo "====================================\n";
    echo "WhatsApp real: NO utilizado\n";
    echo "Correos reales: NO enviados\n";
    echo "Datos: FICTICIOS\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        "\nERROR: " . $e->getMessage() . "\n"
    );
    exit(1);
}
