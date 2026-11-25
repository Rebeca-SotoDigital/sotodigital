<?php
// Only documented deliverables. No invented acquisition source, sales or growth metrics.
return [
 'service_details' => [
  'google-ads' => [
   'heading' => 'Qué incluye la gestión de Google Ads',
   'intro' => 'Definimos el alcance contigo antes de empezar: preparación de campañas, seguimiento y mejoras según los datos disponibles.',
   'items' => [
    ['title'=>'Búsquedas y público', 'text'=>'Investigamos palabras clave y términos a excluir. Ajustamos las zonas y los horarios a tu actividad y a las personas a las que quieres llegar.'],
    ['title'=>'Anuncios y página de destino', 'text'=>'Preparamos mensajes alineados con cada servicio y revisamos el recorrido hasta el formulario. Si necesitas crear o mejorar una landing, lo detallamos en la propuesta.'],
    ['title'=>'Presupuesto y medición', 'text'=>'Acordamos la inversión y qué solicitudes vamos a medir. Revisamos el consentimiento y las conversiones para distinguir las consultas de las simples visitas.'],
    ['title'=>'Seguimiento y decisiones', 'text'=>'Revisamos búsquedas, anuncios y coste por consulta cuando haya datos suficientes. Compartimos contigo qué está ocurriendo y qué ajustes proponemos.'],
   ],
   'process' => [
    ['label'=>'PUNTO DE PARTIDA', 'title'=>'Entendemos tu punto de partida.', 'text'=>'La propuesta gratuita parte de los datos que envías: servicio, objetivo, inversión y momento para empezar. Si ya tienes campañas, acordamos qué información o acceso necesitamos para revisarlas.'],
    ['label'=>'ESTRATEGIA', 'title'=>'Priorizamos dónde invertir.', 'text'=>'Definimos las búsquedas, zonas, anuncios y página de destino adecuados para tu negocio. La propuesta separa nuestros honorarios del presupuesto que se invierte en Google.'],
    ['label'=>'PROYECCIÓN Y PROPUESTA', 'title'=>'Decidimos con supuestos claros.', 'text'=>'Cuando disponemos de datos suficientes, planteamos escenarios orientativos de inversión y consultas, indicando sus supuestos. Revisamos contigo el alcance y cómo mediremos el avance antes de activar campañas.'],
   ],
   'faqs' => [
    ['question'=>'¿Qué incluye la propuesta gratuita?', 'answer'=>'Revisamos los datos que nos aportas para valorar tu punto de partida y tus necesidades. Si dejas móvil, podemos llamarte; también podemos enviarte una propuesta por email. No obliga a contratar ni implica entregar un informe independiente.'],
    ['question'=>'¿Podéis revisar campañas que ya están funcionando?', 'answer'=>'Sí. Indícalo en el cuestionario y valoraremos qué información necesitamos revisar. Los accesos se acuerdan después; no envíes contraseñas ni claves en el formulario.'],
    ['question'=>'¿Necesito tener una web o una landing?', 'answer'=>'Para una campaña que dirija a tu web hace falta una página de destino adecuada. Puedes pedir la propuesta sin tenerla: valoraremos si necesitas una landing y presupuestaremos su creación o mejora según el alcance.'],
    ['question'=>'¿Cuánto cuesta y cuánto tengo que invertir en Google?', 'answer'=>'Preparamos una propuesta según tus objetivos y el alcance. Nuestros honorarios y la inversión publicitaria son conceptos separados. El rango del cuestionario es orientativo y no constituye una tarifa ni un presupuesto cerrado.'],
    ['question'=>'¿Cuándo responderéis y cuándo empezaría la campaña?', 'answer'=>'La primera respuesta llega en un máximo de {{response_time}}. La fecha de lanzamiento se acuerda después de revisar la propuesta, preparar la página de destino y comprobar la medición; no se activa una campaña por enviar el formulario.'],
    ['question'=>'¿Garantizáis ventas o un coste por consulta?', 'answer'=>'No. Los resultados dependen de la demanda, la competencia, la oferta y la página de destino, entre otros factores. Las proyecciones son escenarios con supuestos, no compromisos de ventas ni costes garantizados.'],
   ],
  ],
 ],
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
 'videos' => [],
 // Approved authentic quotes only; see docs/testimonios-clientes.md.
 'testimonials' => [],
];
