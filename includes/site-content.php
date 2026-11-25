<?php
const SOTO_PROPOSAL_CTA = 'Pide tu propuesta gratuita';
const SOTO_PROPOSAL_SHORT_CTA = 'Pide tu propuesta';

const SOTO_WHATSAPP_NUMBER = '34693420125';
const SOTO_WHATSAPP_MESSAGE = 'Hola, me gustaría información sobre vuestros servicios.';

function soto_is_staging(): bool {
    $environment = getenv('SITE_ENV');
    return $environment === false || $environment === ''
        ? soto_base_path() !== ''
        : $environment === 'staging';
}

function soto_show_pending(): bool {
    $setting = getenv('SHOW_PENDING_PLACEHOLDERS');
    return $setting === false || $setting === ''
        ? soto_is_staging()
        : filter_var($setting, FILTER_VALIDATE_BOOLEAN);
}

const SOTO_SERVICES = [
    'servicio-web' => ['Páginas web', '/diseno-web.php', ' que abren puertas', 'Tu negocio, bien presentado y siempre disponible. Diseño y desarrollo web con dominio, hosting y mantenimiento.'],
    'servicio-publicidad' => ['Campañas', '/publicidad-digital.php', ' con dirección', 'Anuncios en Google, Instagram y Meta pensados para conseguir consultas, no solo clics.'],
    'servicio-visibilidad' => ['Redes sociales', '/redes-sociales.php', ' con algo que decir', 'Estrategia, fotografía, textos y gestión de tus perfiles para que tengas una presencia con sentido.'],
    'servicio-marca' => ['Branding', '/branding.php', ' y una imagen muy tuya', 'Logotipo, identidad visual y diseño gráfico que se reconocen en todos los sitios donde apareces.'],
    'servicio-email-marketing' => ['Email marketing', '/email-marketing.php', ' para mantener el contacto', 'Newsletters y campañas de correo para avisar a tus clientes de novedades y no perderlos de vista.'],
    'servicio-marketing-contenidos' => ['Marketing de contenidos', '/marketing-contenidos.php', ' que aporta valor', 'Artículos, textos y piezas que explican lo que haces y te posicionan como referente.'],
    'servicio-seo-sem' => ['SEO y SEM', '/seo-sem.php', ' para que te encuentren', 'Mejoramos tu posición en Google y lanzamos anuncios de búsqueda para que aparezcas cuando te buscan.'],
    'pantalla' => ['Pantalla LED', '/#pantalla'],
];
