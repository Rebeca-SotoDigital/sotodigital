<?php
declare(strict_types=1);

require __DIR__ . '/lead-store.php';

// Datos completamente ficticios para pruebas.
$clienteId = '34999998888';

$datos = [
    'nombre' => 'Laura',
    'negocio' => 'Cafetería Ejemplo',
    'email' => 'laura@example.com',
    'necesidad' =>
        'Quiere mejorar Instagram y conseguir más clientes.',
    'confirmado' => true,
    'consentimiento' => true
];

try {
    $lead = guardarLead($clienteId, $datos);

    echo "\n=== LEAD REGISTRADO ===\n\n";
    echo 'Negocio: ' . $lead['negocio'] . "\n";
    echo 'Email: ' . $lead['email'] . "\n";
    echo 'Necesidad: ' . $lead['necesidad'] . "\n";
    echo 'Estado: ' . $lead['estado'] . "\n";
    echo "\n=== PRUEBA FINALIZADA ===\n";

} catch (Throwable $e) {
    fwrite(
        STDERR,
        'ERROR: ' . $e->getMessage() . "\n"
    );

    exit(1);
}
