<?php
declare(strict_types=1);

/**
 * Validador de oportunidades comerciales.
 * No guarda archivos ni realiza llamadas externas.
 */
function validarLeadExtraido(
    array $datos,
    bool $solicitudConfirmada = false,
    bool $permisoVerificado = false
): array {
    $errores = [];

    $nombre = trim((string) ($datos['nombre'] ?? ''));
    $negocio = trim((string) ($datos['negocio'] ?? ''));
    $email = trim((string) ($datos['email'] ?? ''));
    $necesidad = trim((string) ($datos['necesidad'] ?? ''));

    if (($datos['solicita_propuesta'] ?? false) !== true) {
        $errores[] = 'El cliente no ha solicitado una propuesta';
    }

    if ($nombre === '' || mb_strlen($nombre) > 160) {
        $errores[] = 'Nombre incompleto o no válido';
    }

    if ($negocio === '' || mb_strlen($negocio) > 200) {
        $errores[] = 'Negocio incompleto o no válido';
    }

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 254
    ) {
        $errores[] = 'Correo electrónico no válido';
    }

    if ($necesidad === '' || mb_strlen($necesidad) > 2000) {
        $errores[] = 'Necesidad comercial incompleta';
    }

    if (!$solicitudConfirmada) {
        $errores[] = 'Falta confirmar la solicitud con el cliente';
    }

    if (!$permisoVerificado) {
        $errores[] = 'Falta verificar la autorización de contacto';
    }

    return [
        'valido' => count($errores) === 0,
        'errores' => $errores,
        'datos' => [
            'nombre' => $nombre,
            'negocio' => $negocio,
            'email' => $email,
            'necesidad' => $necesidad
        ]
    ];
}
