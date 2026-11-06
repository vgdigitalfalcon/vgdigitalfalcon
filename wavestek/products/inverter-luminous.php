<?php
require_once '../includes/config.php';
$product = [
  'title'      => 'Inverters — Luminous',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Inverters','#'],['Luminous','']],
  'summary'    => "Luminous inverters — India's leading brand with advanced sine wave technology and smart battery care.",
  'desc'       => "<p>Luminous inverters are renowned across India for reliability and intelligent battery management. Available in pure sine wave and modified sine wave variants, with smart adaptive charging for extended battery life.</p><p class='mt-3'>Ideal for homes, offices and small commercial establishments. Backed by Wavestek installation and AMC support.</p>",
  'icon'       => 'fas fa-bolt',
  'icon_color' => '#1565C0',
  'whatsapp_msg' => 'Luminous Inverters',
  'specs' => [
    ['Capacity',       '600VA / 800VA / 1 KVA / 1.5 KVA / 2 KVA / 3 KVA / 5 KVA'],
    ['Technology',     'Pure Sine Wave / Modified Sine Wave'],
    ['Input Voltage',  '100V - 290V AC'],
    ['Output Voltage', '230V +/- 2%'],
    ['Battery Type',   '12V / 24V / 48V configurations'],
    ['Charging Mode',  'Smart Adaptive 3-Stage Charging'],
    ['Transfer Time',  'Less than 10 ms'],
    ['Display',        'LED / Digital Panel'],
    ['Protection',     'Over Load, Over Voltage, Short Circuit, Deep Discharge'],
    ['Efficiency',     'Up to 90%'],
    ['Warranty',       '2 Years'],
    ['Certifications', 'BIS, CE'],
    ['Operating Temp', '0 to 45 degrees C'],
  ],
  'related' => [
    ['Microtek Inverters', 'inverter-microtek.php', 'fas fa-bolt',              '#F57C00'],
    ['SMF Batteries',      'battery-smf.php',       'fas fa-battery-full',      '#2e7d32'],
    ['Tubular Batteries',  'battery-tubular.php',   'fas fa-battery-three-quarters', '#6d4c41'],
  ],
];
require_once '../includes/product-template.php';
