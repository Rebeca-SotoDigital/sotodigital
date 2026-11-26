<?php
declare(strict_types=1);

require_once __DIR__ . '/lead-pipeline.php';

/**
 * Prepara un borrador sin sustituir una solicitud
 * que ya esté pendiente o registrada.
 */
function prepararConfirmacionLead(
    string $phone,
    array $historial
): array {
    $fase = pipelineEstado(
        $phone,
        fn(array $state) => [
            'guardar' => false,
            'resultado' => $state['fase'] ?? ''
        ]
    );

    if (in_array($fase, [
        'registrado',
        'pendiente_confirmacion'
    ], true)) {
        return [
            'estado' => 'solicitud_existente',
            'mensaje' =>
                'Ya existe una solicitud pendiente o registrada.'
        ];
    }

    $resultado = pipelinePreparar($phone, $historial);

    if ($resultado['estado'] !== 'pendiente_confirmacion') {
        return $resultado;
    }

    pipelineEstado(
        $phone,
        function (array $state) use ($resultado): array {
            $state['resumen_hash'] = hash(
                'sha256',
                $resultado['mensaje']
            );
            $state['envio_aceptado'] = false;

            return [
                'guardar' => true,
                'estado' => $state,
                'resultado' => null
            ];
        }
    );

    return $resultado;
}

/**
 * Registrar SOLO después de que la API de Meta
 * confirme HTTP 2xx para ese mensaje concreto.
 *
 * No significa que el cliente lo haya leído.
 */
function marcarResumenAceptado(
    string $phone,
    string $resumen,
    bool $aceptadoPorMeta
): bool {
    if (!$aceptadoPorMeta) {
        return false;
    }

    return pipelineEstado(
        $phone,
        function (array $state) use ($resumen): array {
            $coincide =
                ($state['fase'] ?? '') ===
                    'pendiente_confirmacion' &&
                isset($state['resumen_hash']) &&
                hash_equals(
                    $state['resumen_hash'],
                    hash('sha256', $resumen)
                );

            if (!$coincide) {
                return [
                    'guardar' => false,
                    'resultado' => false
                ];
            }

            $state['envio_aceptado'] = true;
            $state['envio_aceptado_en'] = time();

            return [
                'guardar' => true,
                'estado' => $state,
                'resultado' => true
            ];
        }
    );
}

/**
 * Genera un nuevo resumen después de una corrección.
 */
function construirResumenLead(array $datos): string
{
    return
        "Revisa los datos de tu solicitud:\n\n" .
        "Nombre: {$datos['nombre']}\n" .
        "Negocio: {$datos['negocio']}\n" .
        "Email: {$datos['email']}\n" .
        "Necesidad: {$datos['necesidad']}\n\n" .
        "Utilizaremos estos datos para atender tu " .
        "solicitud y contactar contigo por email.\n" .
        "Consulta nuestra política de privacidad " .
        "en https://soto-digital.com/.\n\n" .
        "Si todo es correcto y aceptas que utilicemos " .
        "estos datos para gestionar tu solicitud, " .
        "responde exactamente:\n\n" .
        "CONFIRMAR PROPUESTA\n\n" .
        "Para cancelar, responde CANCELAR PROPUESTA.";
}

/**
 * Procesa un mensaje del cliente relacionado
 * con una solicitud pendiente.
 */
function procesarConfirmacionLead(
    string $phone,
    string $textoCliente
): array {
    $texto = trim($textoCliente);

    if ($texto === '') {
        return [
            'estado' => 'sin_accion',
            'mensaje' => ''
        ];
    }

    return pipelineEstado(
        $phone,
        function (array $state) use ($phone, $texto): array {

            $fase = $state['fase'] ?? '';

            // Cancelación explícita
            if ($texto === 'CANCELAR PROPUESTA') {
                if ($fase !== 'pendiente_confirmacion') {
                    return [
                        'guardar' => false,
                        'resultado' => [
                            'estado' => 'sin_borrador',
                            'mensaje' =>
                                'No hay una solicitud pendiente.'
                        ]
                    ];
                }

                return [
                    'guardar' => true,
                    'estado' => [
                        'fase' => 'cancelado',
                        'cancelado_en' => time()
                    ],
                    'resultado' => [
                        'estado' => 'cancelado',
                        'mensaje' =>
                            'Solicitud cancelada. No se ha ' .
                            'registrado ningún nuevo lead.'
                    ]
                ];
            }

            if ($fase === 'registrado') {
                return [
                    'guardar' => false,
                    'resultado' => [
                        'estado' => 'registrado',
                        'mensaje' =>
                            'La solicitud ya está registrada.'
                    ]
                ];
            }

            if ($fase !== 'pendiente_confirmacion') {
                return [
                    'guardar' => false,
                    'resultado' => [
                        'estado' => 'sin_borrador',
                        'mensaje' =>
                            'No hay una solicitud pendiente.'
                    ]
                ];
            }

            $creado = (int) ($state['creado_en'] ?? 0);

            if (
                $creado <= 0 ||
                time() - $creado > SOTO_LEAD_TTL
            ) {
                return [
                    'guardar' => true,
                    'estado' => ['fase' => 'caducado'],
                    'resultado' => [
                        'estado' => 'caducado',
                        'mensaje' =>
                            'La solicitud ha caducado.'
                    ]
                ];
            }

            // Confirmación exacta
            if ($texto === 'CONFIRMAR PROPUESTA') {

                if (
                    ($state['envio_aceptado'] ?? false)
                    !== true
                ) {
                    return [
                        'guardar' => false,
                        'resultado' => [
                            'estado' => 'envio_no_confirmado',
                            'mensaje' =>
                                'Todavía no podemos confirmar ' .
                                'el envío del resumen.'
                        ]
                    ];
                }

                $validacion = validarLeadExtraido(
                    $state['datos'] ?? [],
                    true,
                    true
                );

                if (!$validacion['valido']) {
                    return [
                        'guardar' => false,
                        'resultado' => [
                            'estado' => 'no_valido',
                            'errores' =>
                                $validacion['errores']
                        ]
                    ];
                }

                $lead = guardarLead(
                    $phone,
                    array_merge(
                        $validacion['datos'],
                        [
                            'confirmado' => true,
                            'consentimiento' => true
                        ]
                    )
                );

                return [
                    'guardar' => true,
                    'estado' => [
                        'fase' => 'registrado',
                        'lead_id' => $lead['id'],
                        'registrado_en' => time()
                    ],
                    'resultado' => [
                        'estado' => 'registrado',
                        'mensaje' =>
                            '¡Gracias! Tu solicitud está ' .
                            'registrada. Soto Digital ' .
                            'revisará tus necesidades ' .
                            'para preparar la propuesta.'
                    ]
                ];
            }

            // Correcciones con un formato controlado:
            // CORREGIR EMAIL: nuevo@example.com
            if (preg_match(
                '/^CORREGIR (NOMBRE|NEGOCIO|EMAIL|NECESIDAD):\s*(.+)$/ui',
                $texto,
                $matches
            )) {
                $campo = mb_strtolower($matches[1]);
                $nuevoValor = trim($matches[2]);

                $limites = [
                    'nombre' => 160,
                    'negocio' => 200,
                    'email' => 254,
                    'necesidad' => 2000
                ];

                if (
                    $nuevoValor === '' ||
                    mb_strlen($nuevoValor) > $limites[$campo]
                ) {
                    return [
                        'guardar' => false,
                        'resultado' => [
                            'estado' => 'correccion_invalida',
                            'mensaje' =>
                                'El dato está vacío o es demasiado largo.'
                        ]
                    ];
                }

                if (
                    $campo === 'email' &&
                    !filter_var(
                        $nuevoValor,
                        FILTER_VALIDATE_EMAIL
                    )
                ) {
                    return [
                        'guardar' => false,
                        'resultado' => [
                            'estado' => 'correccion_invalida',
                            'mensaje' =>
                                'El correo electrónico no es válido.'
                        ]
                    ];
                }

                $datos = $state['datos'];
                $datos[$campo] = $nuevoValor;

                $resumen = construirResumenLead($datos);

                $state['datos'] = $datos;
                $state['resumen_hash'] = hash(
                    'sha256',
                    $resumen
                );

                // Una corrección invalida el envío anterior.
                $state['envio_aceptado'] = false;
                unset($state['envio_aceptado_en']);

                return [
                    'guardar' => true,
                    'estado' => $state,
                    'resultado' => [
                        'estado' => 'corregido',
                        'mensaje' => $resumen
                    ]
                ];
            }

            return [
                'guardar' => false,
                'resultado' => [
                    'estado' => 'sin_accion',
                    'mensaje' =>
                        'Puedes corregir tus datos, ' .
                        'confirmar o cancelar la solicitud.'
                ]
            ];
        }
    );
}
