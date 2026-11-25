<?php
declare(strict_types=1);

require __DIR__ . '/lead-store.php';

$base = sys_get_temp_dir()
    . '/soto-lead-atomic-'
    . bin2hex(random_bytes(6));

putenv('SOTO_LEADS_DIR=' . $base);

function comprobarLeadAtomic(
    bool $condicion,
    string $descripcion
): void {
    if (!$condicion) {
        throw new RuntimeException(
            'PRUEBA FALLIDA: ' . $descripcion
        );
    }

    echo "[OK] {$descripcion}\n";
}


function esperarFalloLead(callable $operation): bool
{
    try {
        $operation();
        return false;
    } catch (RuntimeException | JsonException $e) {
        return true;
    }
}


try {
    echo "\n=== PRUEBAS LEAD ATÓMICO ===\n\n";

    $phone = '34999998888';

    $datos = [
        'nombre' => 'Cliente Prueba',
        'negocio' => 'Cafetería Ejemplo',
        'email' => 'cliente@example.com',
        'necesidad' => 'Necesito una web',
        'confirmado' => true,
        'consentimiento' => true
    ];

    // 1. Primera confirmación.
    $lead = guardarLead($phone, $datos);

    comprobarLeadAtomic(
        $lead['estado'] === 'pendiente',
        'Lead registrado correctamente'
    );

    $archivo = leadDirectory() . '/' .
        hash('sha256', $phone) . '.json';

    $original = file_get_contents($archivo);

    // 2. La misma confirmación no genera otro lead.
    $repetido = guardarLead($phone, $datos);

    comprobarLeadAtomic(
        $repetido['id'] === $lead['id'],
        'La confirmación repetida conserva el ID'
    );

    comprobarLeadAtomic(
        file_get_contents($archivo) === $original,
        'No sobrescribe el lead existente'
    );

    // 3. No permite registrar datos diferentes.
    $otrosDatos = $datos;
    $otrosDatos['email'] = 'otro@example.com';

    comprobarLeadAtomic(
        esperarFalloLead(
            fn() => guardarLead($phone, $otrosDatos)
        ),
        'Rechaza datos distintos del mismo cliente'
    );

    // 4. Falta de consentimiento.
    $sinPermiso = $datos;
    $sinPermiso['consentimiento'] = false;

    $rechazado = false;

    try {
        guardarLead('34999997777', $sinPermiso);
    } catch (InvalidArgumentException $e) {
        $rechazado = true;
    }

    comprobarLeadAtomic(
        $rechazado,
        'Exige consentimiento explícito'
    );

    // 5. Detectar archivo corrupto.
    $otroPhone = '34999996666';

    $corrupto = leadDirectory() . '/' .
        hash('sha256', $otroPhone) . '.json';

    file_put_contents($corrupto, '{"estado":');

    comprobarLeadAtomic(
        esperarFalloLead(
            fn() => guardarLead($otroPhone, $datos)
        ),
        'Detecta un registro corrupto'
    );

    comprobarLeadAtomic(
        file_get_contents($corrupto) === '{"estado":',
        'No sobrescribe el registro dañado'
    );

    // 6. El estado comercial puede evolucionar.
    $actualizado = json_decode(
        file_get_contents($archivo),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $actualizado['estado'] = 'contactado';

    file_put_contents(
        $archivo,
        json_encode($actualizado, JSON_THROW_ON_ERROR)
    );

    $recuperado = guardarLead($phone, $datos);

    comprobarLeadAtomic(
        $recuperado['estado'] === 'contactado',
        'No duplica leads ya contactados'
    );

    // 7. Comprobar limpieza de temporales.
    $temporales = glob(
        $base . '/.lead-*'
    );

    comprobarLeadAtomic(
        is_array($temporales) &&
        count($temporales) === 0,
        'No quedan archivos temporales'
    );

    echo "\n=== TODAS LAS PRUEBAS SUPERADAS ===\n";
    echo "Leads reales creados: 0\n";
    echo "WhatsApps reales enviados: 0\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        'ERROR: ' . $e->getMessage() . "\n"
    );
    exit(1);
}
