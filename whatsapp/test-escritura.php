<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$archivo = __DIR__ . '/prueba-escritura.txt';

$resultado = @file_put_contents(

    $archivo,

    "Prueba de escritura Soto Digital\n",

    LOCK_EX

);

if ($resultado === false) {

    echo "ERROR: PHP no puede escribir en la carpeta.\n";

    echo "Carpeta: " . __DIR__ . "\n";

} else {

    echo "OK: Archivo creado correctamente.\n";
}