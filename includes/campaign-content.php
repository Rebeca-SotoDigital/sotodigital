<?php
// Only documented deliverables. No invented acquisition source, sales or growth metrics.
return [
 'service_details' => array_merge(require __DIR__.'/campaign-service-details.php', [
  'google-ads' => [
   'heading' => 'Qué incluye la gestión de Google Ads',
   'intro' => 'Definimos el alcance contigo antes de empezar: preparación de campañas, seguimiento y mejoras según los datos disponibles.',
   'items' => [
    ['title'=>'Búsquedas y público', 'text'=>'Investigamos palabras clave y términos a excluir. Ajustamos las zonas y los horarios a tu actividad y a las personas a las que quieres llegar.'],
    ['title'=>'Anuncios y página de destino', 'text'=>'Preparamos mensajes alineados con cada servicio y revisamos el recorrido hasta el formulario. Si necesitas crear o mejorar una landing, lo detallamos en la propuesta.'],
    ['title'=>'Presupuesto y medición', 'text'=>'Acordamos la inversión y qué solicitudes vamos a medir. Revisamos el consentimiento y las conversiones para distinguir las consultas de las simples visitas.'],
    ['title'=>'Seguimiento y decisiones', 'text'=>'Revisamos búsquedas, anuncios y coste por consulta cuando haya datos suficientes. Compartimos contigo qué está ocurriendo y qué ajustes proponemos.'],
   ],
   'proposal' => [
    'heading'=>'Una propuesta que te ayude a decidir.',
    'intro'=>'Tras revisar tu solicitud, concretamos contigo qué necesita tu negocio. Recibirás una propuesta con acciones y presupuesto, antes de comprometerte.',
    'items'=>[
     ['title'=>'Por dónde empezar', 'text'=>'Una valoración inicial de tu oferta, tu web y tu objetivo, a partir de la información que nos facilites. Indicamos qué conviene preparar antes de anunciarte.'],
     ['title'=>'Qué vamos a hacer', 'text'=>'Campañas recomendadas, mensajes, página de destino y tareas prioritarias. Detallamos qué incluye la gestión y qué trabajos se presupuestan aparte.'],
     ['title'=>'Cuánto y cómo revisarlo', 'text'=>'Honorarios e inversión en Google separados, condiciones de puesta en marcha e indicadores para valorar el avance. Si hay datos suficientes, añadimos escenarios orientativos con sus supuestos.'],
    ],
   ],
   'formats' => [
    'heading'=>'Cada negocio necesita una combinación distinta.',
    'intro'=>'Valoramos estas opciones según tu oferta, los materiales disponibles y el presupuesto. La propuesta concreta qué formatos trabajaremos; no hace falta utilizarlos todos.',
    'items'=>[
     ['title'=>'Búsqueda: cuando ya te están buscando', 'text'=>'Para presentar tus servicios a personas que buscan una solución. Por ejemplo, una empresa que quiere captar solicitudes de presupuesto para un servicio concreto.'],
     ['title'=>'Shopping: cuando vendes productos online', 'text'=>'Lo valoramos para tiendas online con catálogo, precios y compra en la web. Antes revisamos los requisitos y la preparación del catálogo; ese trabajo se concreta en la propuesta.'],
     ['title'=>'YouTube y Display: para dar a conocer y recordar', 'text'=>'Los valoramos cuando conviene explicar una oferta con vídeo o imagen, o volver a conectar con público que ya mostró interés. Revisamos materiales, permisos y medición antes de plantearlos.'],
    ],
   ],
   'approach' => [
    'heading'=>'Hablarás con quienes trabajan en tu proyecto.',
    'intro'=>'Somos Rebeca y Jónatan. Queremos que entiendas qué se está haciendo, para qué y cuál es el siguiente paso.',
    'items'=>[
     ['title'=>'Una conversación directa', 'text'=>'Desde la primera revisión conoceremos tu negocio contigo. Las dudas y las decisiones tendrán interlocutores con nombre propio.'],
     ['title'=>'Del anuncio al contacto', 'text'=>'Coordinamos mensaje, página de destino y recorrido de la consulta. Si la web o el contenido necesitan mejoras, te explicamos cuáles y su alcance antes de hacerlas.'],
     ['title'=>'Prioridades que puedas entender', 'text'=>'Te explicamos dónde proponemos invertir y por qué. Combinamos otros canales cuando tengan sentido para tu objetivo y tus recursos.'],
    ],
   ],
   'reporting' => [
    'heading'=>'Datos que terminan en decisiones.',
    'intro'=>'Acordamos la frecuencia de revisión antes de empezar. Miramos qué ocurre después del clic y contrastamos contigo la calidad de las consultas recibidas.',
    'items'=>[
     ['title'=>'Qué consultas llegan', 'text'=>'Revisamos las solicitudes que se puedan atribuir a la campaña y su coste. Con tu información, distinguimos las consultas que encajan de las que no responden a lo que ofreces.'],
     ['title'=>'Dónde se pierde el interés', 'text'=>'Revisamos términos de búsqueda, anuncios y recorrido de la landing para localizar puntos de mejora, según los datos y permisos disponibles.'],
     ['title'=>'Qué cambiaremos después', 'text'=>'Por ejemplo: si llegan consultas de un servicio que no prestas, revisamos búsquedas y mensajes. Si hay clics pero no solicitudes, revisamos la oferta y la página. Son ejemplos de decisiones, no resultados de clientes.'],
    ],
   ],
   'process' => [
    ['label'=>'PUNTO DE PARTIDA', 'title'=>'Entendemos tu punto de partida.', 'text'=>'La propuesta gratuita parte de los datos que envías: servicio, objetivo, inversión y momento para empezar. Si ya tienes campañas, acordamos qué información o acceso necesitamos para revisarlas.'],
    ['label'=>'ESTRATEGIA', 'title'=>'Priorizamos dónde invertir.', 'text'=>'Definimos las búsquedas, zonas, anuncios y página de destino adecuados para tu negocio. La propuesta separa nuestros honorarios del presupuesto que se invierte en Google.'],
    ['label'=>'PROYECCIÓN Y PROPUESTA', 'title'=>'Decidimos con supuestos claros.', 'text'=>'Cuando disponemos de datos suficientes, planteamos escenarios orientativos de inversión y consultas, indicando sus supuestos. Revisamos contigo el alcance y cómo mediremos el avance antes de activar campañas.'],
   ],
   'faqs' => [
    ['question'=>'¿Qué incluye la propuesta gratuita?', 'answer'=>'Una valoración inicial de los datos que nos aportas, acciones recomendadas, alcance y presupuesto con honorarios e inversión publicitaria separados. Si necesitamos aclarar algo, te llamamos si has dejado móvil o contactamos por email. No obliga a contratar ni incluye un informe de auditoría independiente.'],
    ['question'=>'¿Podéis revisar campañas que ya están funcionando?', 'answer'=>'Sí. Indícalo en el cuestionario y valoraremos qué información necesitamos revisar. Los accesos se acuerdan después; no envíes contraseñas ni claves en el formulario.'],
    ['question'=>'¿Necesito tener una web o una landing?', 'answer'=>'Para una campaña que dirija a tu web hace falta una página de destino adecuada. Puedes pedir la propuesta sin tenerla: valoraremos si necesitas una landing y presupuestaremos su creación o mejora según el alcance.'],
    ['question'=>'¿Cuánto cuesta y cuánto tengo que invertir en Google?', 'answer'=>'Preparamos una propuesta según tus objetivos y el alcance. Nuestros honorarios y la inversión publicitaria son conceptos separados. El rango del cuestionario es orientativo y no constituye una tarifa ni un presupuesto cerrado.'],
    ['question'=>'¿Cuándo responderéis y cuándo empezaría la campaña?', 'answer'=>'La primera respuesta llega en un máximo de {{response_time}}. La fecha de lanzamiento se acuerda después de revisar la propuesta, preparar la página de destino y comprobar la medición; no se activa una campaña por enviar el formulario.'],
    ['question'=>'¿Garantizáis ventas o un coste por consulta?', 'answer'=>'No. Los resultados dependen de la demanda, la competencia, la oferta y la página de destino, entre otros factores. Las proyecciones son escenarios con supuestos, no compromisos de ventas ni costes garantizados.'],
   ],
  ],
 ]),
 'cases' => [
  ['sector' => 'Restauración', 'situation' => 'Una oferta que el público necesita consultar antes de visitar el local.', 'work' => 'Conectamos web y redes sociales.', 'result' => 'Carta, ubicación, horario e información práctica reunidos en la web, con acceso al perfil de Instagram.', 'services' => ['estrategia-digital', 'diseno-web', 'redes-sociales', 'marketing-contenidos']],
  ['sector' => 'Distribución técnica', 'situation' => 'Soluciones profesionales que necesitan una presentación clara en internet.', 'work' => 'Desarrollamos una web de presentación corporativa.', 'result' => 'Un espacio propio para explicar la actividad, presentar soluciones y acceder a las vías de contacto.', 'services' => ['estrategia-digital', 'diseno-web', 'branding', 'marketing-contenidos']],
 ],
 // Add only approved success cases here: ['name' => '...', 'evidence' => '...'].
 'featured_clients' => [
  ['name' => 'Cantabria Kebab', 'evidence' => 'Web y gestión de redes sociales para restauración.', 'approved'=>false, 'project'=>'proyecto-cantabria-kebab.php'],
  ['name' => 'TecniRefinish', 'evidence' => 'Web corporativa y presentación de soluciones profesionales.', 'approved'=>false, 'project'=>'proyecto-tecnirefinish.php'],
 ],
 // Per service: local MP4 path relative to site root; no external embeds before consent.
 // Names and education supplied by the founders; photos still pending.
 'team' => [
  ['name'=>'Rebeca','description'=>'CEO y cofundadora.','personal'=>'Creativa por naturaleza, inconformista por elección e incansable cuando se trata de convertir una idea en realidad.','approved'=>true,'photo'=>'img/equipo/rebeca.webp'],
  ['name'=>'Jónatan','description'=>'CCO y cofundador.','personal'=>'Los retos no me frenan, me impulsan. Creo en el esfuerzo, la constancia y el compromiso de llevar cada proyecto hasta el final.','approved'=>true,'photo'=>'img/equipo/jonatan.webp'],
 ],
 'videos' => ['google-ads' => 'img/videos/google-ads.mp4'],
 // Approved authentic quotes only; see docs/testimonios-clientes.md.
 'testimonials' => [],
];
