# Tarjetas de Soto Digital para Apple Wallet

Estado: fuentes preparadas para Rebeca y Jónatan. No hay pases firmados,
no se han publicado cambios ni añadido botones de descarga a las páginas.
`preview/` contiene fuentes JSON e imágenes, no archivos instalables.

Cada pase incluye nombre, cargo, teléfono, correo y QR de su tarjeta web.
El fondo blanco permite leer el logotipo corporativo oscuro.
Los datos proceden de las tarjetas HTML locales. Confirmarlos antes de emitir.

## Regenerar fuentes

Requiere Python 3, sin paquetes adicionales:

```sh
python3 tools/wallet/prepare.py --output /tmp/soto-wallet --pass-type-id pass.com.soto-digital.tarjetas --team-id TEAM_ID_REAL
```

`pass.com.soto-digital.tarjetas` es un identificador propuesto: hay que
registrarlo en Apple antes de usarlo. `PENDIENTE` en las muestras no es un Team ID válido.

## Pendiente para instalar

1. Elegir firma propia mediante Apple Developer o un proveedor que emita
   los pases con su certificado. Comprobar sus condiciones y costes;
   algunos proveedores también exigen una cuenta Apple Developer propia.
2. Para firma propia, registrar el Pass Type ID y emitir su certificado.
   Guardar la clave privada y los certificados fuera de la raíz pública web.
3. Regenerar las fuentes con los identificadores reales.
4. Crear manifest.json con hashes SHA-1 de los archivos del pase; firmarlo
   mediante PKCS#7 detached con el certificado del pase, su clave privada
   y el certificado intermedio WWDR correspondiente. Empaquetar los archivos
   en la raíz de un ZIP con extensión .pkpass.
5. Verificar firma y probar ambos pases en un iPhone: nombre completo,
   legibilidad del logotipo, QR y acceso a cada tarjeta digital.
6. Subir cada pase junto a su tarjeta, configurar el MIME
   `application/vnd.apple.pkpass` y añadir el distintivo oficial de Apple
   «Añadir a Apple Wallet» enlazando al archivo. No usar el atributo download.

No se ha implementado un servicio de actualización automática de los pases.
Los cambios en la tarjeta web sí son visibles al abrir el QR, que es estable.

Referencia: https://developer.apple.com/documentation/walletpasses/building-a-pass
Alta y precio regional: https://developer.apple.com/programs/enroll/
