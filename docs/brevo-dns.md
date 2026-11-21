# DNS de Brevo para soto-digital.com

Alta de dominio y remitente completada el 21/11/2026. Configuración completada: Brevo confirma dominio verificado/autenticado y remitente activo. Los valores siguientes son los devueltos por la cuenta real de Brevo; no son ejemplos.

## Registros que debe tener la zona DNS

| Tipo | Nombre / host | Valor / destino |
| --- | --- | --- |
| CNAME | `brevo1._domainkey` | `b1.soto-digital-com.dkim.brevo.com` |
| CNAME | `brevo2._domainkey` | `b2.soto-digital-com.dkim.brevo.com` |
| TXT | `@` | `brevo-code:0c0b13015ec153ffc9f4531feed0064d` |
| TXT | `_dmarc` | `v=DMARC1; p=none; rua=mailto:rua@dmarc.brevo.com` |

## Aplicación

1. Entrar al proveedor que gestiona los DNS del dominio. Los NS consultados son dns97.servidoresdns.net y dns98.servidoresdns.net.
2. Añadir los registros anteriores con TTL predeterminado del proveedor. Si el panel exige nombre completo, añadir .soto-digital.com al host; si añade el dominio automáticamente, usar el host corto de la tabla. @ es el dominio raíz.
3. No modificar MX, SPF ni otros selectores DKIM existentes: estos registros se añaden para Brevo. No duplicar un registro que ya exista con el mismo nombre y tipo.
4. La consulta pública de _dmarc devolvió CNAME a www.soto-digital.com, sin TXT DMARC válido. Revisar en el panel si es un registro explícito o herencia de un comodín. Un TXT no puede coexistir con un CNAME explícito en el mismo nombre; resolver ese caso en el panel y conservar el resto de la zona. Si ya existe un DMARC válido, revisar su política antes de sustituirlo; no añadir un segundo DMARC.
5. Guardar, esperar a la propagación y comprobar públicamente los registros. Después ejecutar la validación de dominio en Brevo.
6. Revisar el correo de ads@soto-digital.com y completar la verificación solicitada por Brevo. No se ha leído ese buzón ni usado ningún código.
7. Repetir `php bin/campaign-leads.php brevo-check` con la configuración privada. El envío real de prueba se hará por separado.

El usuario añadió los registros en el panel DNS. La verificación posterior confirma los cuatro registros válidos en Brevo y DMARC correcto en dns97 y dns98. La caché pública de Google aún devolvía el CNAME antiguo para DMARC, por lo que se contrastó con los servidores autoritativos. Esta sesión no ha modificado registros DNS. No se ha enviado ningún lead ni correo de prueba. El alta de remitente sí inicia el correo de verificación de Brevo solicitado por el usuario.
