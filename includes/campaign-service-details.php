<?php
// Scope is agreed in the proposal; no invented prices, metrics or client results.
return (static function () {
$definitions = [
 'estrategia-digital'=>[
  [['Diagnóstico','Revisamos tu presencia digital, tus objetivos y los recursos disponibles.'],['Prioridades','Seleccionamos canales y acciones según tu momento y presupuesto.'],['Plan y seguimiento','Definimos qué hacer primero y cómo valorar el avance.']],
  [['¿Tengo que contratar varios servicios?','No. Podemos empezar por una necesidad concreta y proponer otros canales solo cuando tengan sentido.'],['¿Qué necesitáis para preparar la propuesta?','Tu actividad, objetivo, inversión orientativa y cuándo quieres empezar. Si tienes web o campañas, cuéntanos qué está funcionando y qué quieres mejorar.']]],
 'diseno-web'=>[
  [['Estructura y contenidos','Ordenamos servicios, mensajes y llamadas al contacto para que tu oferta se entienda.'],['Diseño y desarrollo','Preparamos una web adaptada a móvil y escritorio, con la identidad de tu negocio.'],['Publicación y alcance','Detallamos páginas, formularios, dominio, hosting y mantenimiento en la propuesta.']],
  [['¿Podéis renovar mi web actual?','Sí. Revisamos su estructura y tecnología para valorar qué se puede aprovechar y qué conviene rehacer.'],['¿Quién aporta los textos y las imágenes?','Acordamos qué materiales aportas y qué redacción, diseño o fotografía hay que preparar. Ese alcance se refleja en la propuesta.']]],
 'redes-sociales'=>[
  [['Estrategia editorial','Definimos público, mensajes y canales adecuados para tu actividad.'],['Contenido y calendario','Planificamos textos, imágenes y vídeos; acordamos formatos y frecuencia antes de empezar.'],['Gestión y revisión','Concretamos publicación, atención a mensajes y seguimiento en la propuesta.']],
  [['¿Cuántas publicaciones incluye?','La frecuencia y los formatos se acuerdan según tus objetivos y el contenido disponible; no hay un número fijo anunciado.'],['¿La publicidad está incluida?','La gestión de contenido y las campañas de pago son alcances distintos. Si recomendamos anuncios, detallamos honorarios e inversión publicitaria por separado.']]],
 'branding'=>[
  [['Posicionamiento de marca','Trabajamos qué te diferencia y cómo quieres que te reconozcan.'],['Identidad visual','Definimos logotipo, colores y tipografías según el alcance acordado.'],['Aplicaciones y entrega','Concretamos materiales, formatos y pautas de uso antes de diseñarlos.']],
  [['¿Necesito cambiar mi logotipo?','No necesariamente. Podemos revisar la identidad actual y valorar si conviene ajustarla o construir una nueva.'],['¿Qué archivos y materiales recibiré?','La propuesta recoge los entregables y formatos de uso, así como las aplicaciones y revisiones incluidas.']]],
 'meta-ads'=>[
  [['Objetivo y audiencias','Definimos a quién dirigir los anuncios y qué acción queremos medir.'],['Mensajes y creatividades','Acordamos formatos y piezas para Instagram y Facebook alineados con tu oferta.'],['Medición y ajustes','Revisamos consultas y rendimiento según los datos disponibles, separando honorarios e inversión.']],
  [['¿Necesito cuentas de Instagram o Facebook?','Revisamos los perfiles y activos publicitarios de los que dispones y acordamos la preparación necesaria. No compartas contraseñas en el formulario.'],['¿Incluye la gestión diaria de redes?','No se presupone. Diferenciamos campañas de pago y gestión de contenido orgánico, aunque pueden formar parte de una estrategia conjunta.']]],
 'email-marketing'=>[
  [['Audiencia y planificación','Definimos destinatarios, objetivos y un calendario adecuado para tus comunicaciones.'],['Contenido y diseño','Preparamos mensajes y plantillas coherentes con tu marca.'],['Configuración y seguimiento','Acordamos plataforma, segmentación y métricas disponibles; sus licencias se detallan aparte cuando corresponda.']],
  [['¿Necesito tener una lista de contactos?','Podemos trabajar con tu base existente si dispones de una base jurídica adecuada para esos envíos. No compramos listas ni añadimos contactos sin revisar su procedencia.'],['¿Incluye automatizaciones?','Podemos plantearlas según tus necesidades. Los flujos, contenidos, plataforma y alcance se detallan en la propuesta.']]],
 'marketing-contenidos'=>[
  [['Temas y enfoque','Elegimos contenidos que respondan a las dudas de tu público y expliquen tu experiencia.'],['Redacción y formatos','Acordamos artículos, textos de servicios u otras piezas, su extensión y frecuencia.'],['Revisión y coherencia','Contrastamos la información con tu equipo y mantenemos una voz consistente.']],
  [['¿Tenéis experiencia en mi sector?','Cuéntanos tu actividad para valorar el encaje. Necesitaremos vuestra colaboración para contrastar información técnica y afirmaciones sobre el negocio.'],['¿Publicáis también el contenido?','Podemos incluir publicación y adaptación a distintos canales; se concreta antes de empezar. El contenido por sí solo no garantiza posiciones ni ventas.']]],
 'seo'=>[
  [['Revisión técnica','Analizamos estructura, rastreo y problemas que puedan dificultar el acceso al contenido.'],['Búsquedas y contenidos','Priorizamos términos relevantes y mejoras en páginas y textos.'],['Seguimiento progresivo','Definimos indicadores y revisamos la evolución orgánica con datos disponibles.']],
  [['¿Cuándo se notan los resultados del SEO?','Es un trabajo progresivo. Los plazos dependen del punto de partida, la competencia y las mejoras; no garantizamos una fecha ni una posición concreta.'],['¿SEO incluye pagar anuncios a Google?','No. SEO trabaja la visibilidad orgánica; la inversión en anuncios corresponde a campañas SEM y se plantea por separado.']]],
 'sem'=>[
  [['Intención de búsqueda','Seleccionamos servicios, palabras clave, zonas y exclusiones para orientar la inversión.'],['Anuncios y destino','Conectamos los mensajes del anuncio con una página y una acción de contacto claras.'],['Presupuesto y control','Acordamos inversión, conversiones y revisión del coste de las consultas cuando haya datos.']],
  [['¿En qué se diferencia del SEO?','SEM utiliza publicidad de pago en buscadores; SEO trabaja el posicionamiento orgánico de forma progresiva. Pueden complementarse según el objetivo.'],['¿Los honorarios incluyen el gasto publicitario?','Son conceptos separados. La propuesta diferencia el trabajo de gestión y el presupuesto destinado a la plataforma publicitaria.']]],
 'pantalla-led'=>[
  [['Disponibilidad','Confirmamos fechas, ubicación y condiciones antes de contratar una emisión.'],['Creatividad','Acordamos diseño del anuncio, formato y materiales que hay que aportar.'],['Plan de emisión','Detallamos duración, calendario y condiciones en la propuesta, sin inventar cifras de audiencia.']],
  [['¿La pantalla está disponible para contratar?','La disponibilidad y las fechas deben confirmarse con el equipo antes de reservar. Envíanos tu interés para que podamos informarte.'],['¿Podéis preparar el anuncio?','Sí. Valoramos los materiales disponibles y detallamos el diseño y la adaptación necesarios en la propuesta.']]],
];
$services=(static function(){return require __DIR__.'/campaign-services.php';})();$details=[];
foreach($definitions as $slug=>[$scope,$questions]){
 $items=[];foreach($scope as [$title,$text])$items[]=['title'=>$title,'text'=>$text];
 $faqs=[['question'=>'¿La propuesta es gratuita?','answer'=>'Sí. Revisamos los datos que nos aportas y damos una primera respuesta en un máximo de {{response_time}}. No obliga a contratar ni implica entregar un informe independiente.']];
 foreach($questions as [$question,$answer])$faqs[]=['question'=>$question,'answer'=>$answer];
 $faqs[]=['question'=>'¿Cuánto cuesta el servicio?','answer'=>'Preparamos un presupuesto según objetivos, entregables y alcance. El rango del cuestionario es orientativo, no una tarifa ni un presupuesto cerrado.'];
 $details[$slug]=['heading'=>$slug==='estrategia-digital'?'Qué trabajamos en tu estrategia':'Qué trabajamos en '.mb_strtolower($services[$slug][0]),'intro'=>'Acordamos contigo las acciones y los entregables antes de empezar.','items'=>$items,'faqs'=>$faqs];
}
return $details;
})();
