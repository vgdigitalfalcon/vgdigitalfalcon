<?php
require_once '../includes/config.php';
$product = [
  'title'      => 'Inverters — Microtek',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Inverters','#'],['Microtek','']],
  'summary'    => 'Microtek inverters — trusted brand for homes and offices. Pure sine wave output with intelligent battery management.',
  'desc'       => '<p>Microtek inverters — trusted brand for homes and offices. Pure sine wave output with intelligent battery management.</p>',
  'icon'       => 'fas fa-bolt',
  'icon_color' => '#F57C00',
  'whatsapp_msg' => 'Inverters — Microtek',
  'specs' => [['Capacity','600VA – 5 KVA'],['Technology','Pure Sine Wave'],['Input Voltage','100V–290V'],['Output','230V ± 2%'],['Battery','12V–96V configurations'],['Efficiency','Up to 90%'],['Display','LED/LCD'],['Charging Current','10A–20A']],
  'related' => [['Luminous Inverters','inverter-luminous.php','fas fa-bolt','#1565C0'],['SMF Batteries','battery-smf.php','fas fa-battery-full','#2e7d32'],['Tubular Batteries','battery-tubular.php','fas fa-battery-three-quarters','#6d4c41']],
];
require_once '../includes/product-template.php';
