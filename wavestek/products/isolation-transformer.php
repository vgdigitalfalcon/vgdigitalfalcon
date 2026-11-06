<?php
require_once '../includes/config.php';
$product = [
  'title'      => 'Isolation Transformer',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Online UPS','#'],['Isolation Transformer','']],
  'summary'    => 'Industrial-grade isolation transformers for noise filtering, galvanic isolation and equipment protection.',
  'desc'       => '<p>Wavestek Isolation Transformers provide galvanic isolation between power supply and load, eliminating common-mode noise, ground loops and electrical interference. Essential for medical equipment, sensitive electronics and industrial machinery.</p>',
  'icon'       => 'fas fa-exchange-alt','icon_color' => '#6d4c41','whatsapp_msg' => 'Isolation Transformer',
  'specs' => [
    ['KVA Range','1 KVA – 500 KVA'],['Type','Single Phase / Three Phase'],['Frequency','50 Hz'],
    ['Primary Voltage','415V / 230V (configurable)'],['Secondary Voltage','415V / 230V (configurable)'],
    ['Insulation Class','Class H (180°C)'],['Efficiency','>98%'],['Regulation','±2%'],
    ['Cooling','Air Cooled / Oil Cooled (above 100 KVA)'],['Enclosure','IP21 / IP54 optional'],
    ['Mounting','Floor / Wall'],['Operating Temp','-10°C to 50°C'],
  ],
  'related' => [
    ['Three Phase UPS','ups-15-400kva.php','fas fa-server','#D32F2F'],
    ['Three Phase Stabilizer','stabilizer-three-phase.php','fas fa-sliders-h','#1565C0'],
  ],
];
require_once '../includes/product-template.php';
