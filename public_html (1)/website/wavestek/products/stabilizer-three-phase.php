<?php
require_once '../includes/config.php';
$product = [
  'title'      => 'Servo Voltage Stabilizer — Three Phase 3–500 KVA',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Servo Voltage Stabilizer','#'],['Three Phase 3–500 KVA','']],
  'summary'    => 'Heavy-duty three-phase servo stabilizers for factories, hospitals, data centres and large commercial establishments.',
  'desc'       => '<p>Wavestek Three Phase Servo Voltage Stabilizers (3–500 KVA) are engineered for the most demanding industrial environments. Three independent servo motor control channels ensure balanced voltage across all phases simultaneously.</p><p class="mt-3">Manufactured at our Chennai plant with rigorous quality checks. Serving government hospitals, factories, data centres, and educational institutions across India.</p>',
  'icon'       => 'fas fa-sliders-h','icon_color' => '#D32F2F','whatsapp_msg' => 'Three Phase Servo Stabilizer 3–500 KVA',
  'specs' => [
    ['Capacity','3 / 5 / 10 / 15 / 20 / 30 / 50 / 75 / 100 / 150 / 200 / 300 / 500 KVA'],
    ['Type','Servo Motor Controlled (3 Independent Channels)'],['Phase','Three Phase (3Ph/3Ph)'],
    ['Input Voltage','300V – 470V (Line-to-Line) / 170V – 270V (Phase)'],
    ['Output Voltage','415V ± 1% (L-L) / 230V ± 1% (Phase)'],
    ['Correction Speed','10–15V per second'],['Efficiency','>98%'],
    ['Bypass','Manual Bypass + Auto Bypass (optional)'],['Display','Digital Panel (3Ph Input/Output)'],
    ['Protection','Over/Under Voltage, Over Load, Phase Failure, Phase Reversal'],
    ['Cooling','Air Cooled (up to 100 KVA) / Oil Cooled (above 100 KVA)'],
    ['Enclosure','IP21 / IP54 optional'],['Operating Temp','0°C – 50°C'],
  ],
  'related' => [
    ['Single Phase Stabilizer','stabilizer-single-phase.php','fas fa-sliders-h','#1565C0'],
    ['Isolation Transformer','isolation-transformer.php','fas fa-exchange-alt','#6d4c41'],
    ['Three Phase UPS','ups-15-400kva.php','fas fa-server','#D32F2F'],
  ],
];
require_once '../includes/product-template.php';
