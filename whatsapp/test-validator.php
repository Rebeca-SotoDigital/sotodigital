<?php
declare(strict_types=1);

require __DIR__ . '/lead-validator.php';

$datos = [
    'nombre' => 'Laura',
    'negocio' => 'Cafetería Nube',
    'email' => 'laura@example.com',
    'necesidad' => 'Gestión de Instagram',
    'solicita_propuesta' => true
];

$casos = [
    'Solicitud confirmada' => [$datos, true, true],
    'Sin confirmación' => [$datos, false, true],
    'Sin autorización verificada' => [$datos, true, false],
    'Consulta general' => [
        array_merge($datos, [
            'solicita_propuesta' => false
        ]),
        false,
        false
    ]
];

foreach ($casos as $nombre => $caso) {
    [$entrada, $confirmada, $permiso] = $caso;

    $resultado = validarLeadExtraido(
        $entrada,
        $confirmada,
        $permiso
    );

    echo "\n=== $nombre ===\n";
    echo $resultado['valido']
        ? "APTO PARA REGISTRO\n"
        : "NO REGISTRAR\n";

    foreach ($resultado['errores'] as $error) {
        echo "- $error\n";
    }
}
