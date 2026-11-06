<?php
require_once '../includes/config.php';
$product = [
  'title'      => 'Tubular Batteries',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Batteries','#'],['Tubular','']],
  'summary'    => 'Long-life tubular batteries for inverters and solar applications. Superior performance in frequent power-cut areas.',
  'desc'       => '<p>Long-life tubular batteries for inverters and solar applications. Superior performance in frequent power-cut areas.</p>',
  'icon'       => 'fas fa-battery-three-quarters',
  'icon_color' => '#6d4c41',
  'whatsapp_msg' => 'Tubular Batteries',
  'specs' => [['Type','Tubular (Flooded Lead Acid)'],['Voltage','12V'],['Capacity','100Ah – 220Ah'],['Design Life','5–8 years'],['Technology','Tubular Positive Plate'],['Electrolyte','Liquid (requires top-up)'],['Brands','Exide, Amaron, Luminous'],['Warranty','3–5 years'],['Application','Inverters, Solar, UPS']],
  'related' => [['SMF Batteries','battery-smf.php','fas fa-battery-full','#2e7d32'],['Luminous Inverters','inverter-luminous.php','fas fa-bolt','#1565C0'],['Solar Inverter','solar-inverter.php','fas fa-solar-panel','#F57C00']],
];
require_once '../includes/product-template.php';
