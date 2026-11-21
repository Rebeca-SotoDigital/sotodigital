<?php
// Provisional rules: confirm with Soto Digital. Score reflects answers, not predicted revenue.
return [
 'sector' => ['label' => '¿En qué sector trabaja tu negocio?', 'options' => [
  'servicios' => 'Servicios profesionales', 'comercio' => 'Comercio y tienda online', 'restauracion' => 'Hostelería y restauración', 'industria' => 'Industria y distribución', 'automocion' => 'Automoción', 'otro' => 'Otro sector']],
 'objective' => ['label' => '¿Qué te gustaría conseguir?', 'options' => [
  'consultas' => 'Recibir más consultas', 'ventas' => 'Conseguir más ventas', 'visibilidad' => 'Dar a conocer mi negocio', 'orientacion' => 'Necesito ayuda para definirlo']],
 'budget' => ['label' => '¿Qué inversión mensual tienes en mente?', 'hint' => 'Una orientación para preparar el plan. No es una tarifa ni un compromiso de inversión.', 'options' => [
  'menos-300' => 'Menos de 300 €', '300-600' => 'Entre 300 y 600 €', '600-1500' => 'Entre 600 y 1.500 €', 'mas-1500' => 'Más de 1.500 €', 'por-definir' => 'Necesito orientación']],
 'urgency' => ['label' => '¿Cuándo quieres empezar?', 'options' => [
  'ahora' => 'Lo antes posible', 'este-mes' => 'Durante el próximo mes', '1-3-meses' => 'En los próximos 1–3 meses', 'explorando' => 'Estoy explorando opciones']],
 'scoring' => [
  'version' => 'provisional-2026-11-21',
  'budget' => ['menos-300' => 0, '300-600' => 2, '600-1500' => 3, 'mas-1500' => 4, 'por-definir' => 0],
  'urgency' => ['ahora' => 3, 'este-mes' => 2, '1-3-meses' => 1, 'explorando' => 0],
  'objective' => ['consultas' => 3, 'ventas' => 3, 'visibilidad' => 1, 'orientacion' => 0],
 ],
];
