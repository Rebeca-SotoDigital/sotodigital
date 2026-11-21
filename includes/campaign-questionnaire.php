<?php
// Agreed rules. Historical leads retain their saved score and rule version.
return [
 'sector' => ['label' => '¿En qué sector trabaja tu negocio?', 'options' => [
  'servicios' => 'Servicios profesionales', 'comercio' => 'Comercio y tienda online', 'restauracion' => 'Hostelería y restauración', 'industria' => 'Industria y distribución', 'automocion' => 'Automoción', 'otro' => 'Otro sector']],
 'objective' => ['label' => '¿Qué te gustaría conseguir?', 'options' => [
  'consultas' => 'Recibir llamadas o formularios', 'ventas' => 'Conseguir ventas online', 'visibilidad' => 'Dar a conocer mi negocio', 'orientacion' => 'Necesito ayuda para definirlo']],
 'budget' => ['label' => '¿Qué inversión mensual tienes en mente?', 'hint' => 'Una orientación para preparar el plan. No es una tarifa ni un compromiso de inversión.', 'options' => [
  'menos-300' => 'Menos de 300 €', '300-1000' => 'De 300 a menos de 1.000 €', '1000-3000' => 'Entre 1.000 y 3.000 €', 'mas-3000' => 'Más de 3.000 €', 'por-definir' => 'Necesito orientación']],
 'urgency' => ['label' => '¿Cuándo quieres empezar?', 'options' => [
  'ahora' => 'Lo antes posible', 'este-mes' => 'Esta semana o este mes', '1-3-meses' => 'En los próximos 1–3 meses', 'explorando' => 'Estoy explorando opciones']],
 'scoring' => [
  'version' => 'v1-acordada-2026-11-21',
  'budget' => ['menos-300' => 1, '300-1000' => 2, '1000-3000' => 3, 'mas-3000' => 3, 'por-definir' => 0],
  'urgency' => ['ahora' => 3, 'este-mes' => 3, '1-3-meses' => 1, 'explorando' => 0],
  'objective' => ['consultas' => 2, 'ventas' => 2, 'visibilidad' => 2, 'orientacion' => 0],
 ],
];
