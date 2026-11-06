<?php
require_once '../includes/config.php';
$product = [
  'title'      => 'Servo Voltage Stabilizer — Single Phase 1–20 KVA',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Servo Voltage Stabilizer','#'],['Single Phase 1–20 KVA','']],
  'summary'    => 'Wavestek-manufactured single-phase servo voltage stabilizers for homes, offices, and light industrial loads.',
  'desc'       => '<p>Our servo voltage stabilizers are manufactured in-house at our Chennai facility, ensuring superior quality control. The servo motor mechanism provides continuous, stepless voltage correction — maintaining steady 230V output regardless of input fluctuations.</p><p class="mt-3">Ideal for computers, air conditioners, refrigerators, medical equipment and office loads.</p>',
  'icon'       => 'fas fa-sliders-h','icon_color' => '#1565C0','whatsapp_msg' => 'Single Phase Servo Stabilizer 1–20 KVA',
  'specs' => [
    ['Capacity','1 / 2 / 3 / 5 / 7.5 / 10 / 15 / 20 KVA'],['Type','Servo Motor Controlled'],
    ['Phase','Single Phase (1Ph/1Ph)'],['Input Voltage Range','140V – 280V AC'],
    ['Output Voltage','230V ± 1%'],['Correction Speed','10–15V per second'],
    ['Frequency','50 Hz ± 3 Hz'],['Efficiency','>98%'],['Waveform','Sine Wave'],
    ['Bypass','Manual Bypass Switch'],['Display','Digital Voltmeter (Input/Output)'],
    ['Protection','Over Voltage, Under Voltage, Over Load'],['Cooling','Air Cooled (Fan)'],
    ['Enclosure','MS Powder Coated'],['Operating Temp','0°C – 50°C'],
  ],
  'related' => [
    ['Three Phase Stabilizer 3–500 KVA','stabilizer-three-phase.php','fas fa-sliders-h','#D32F2F'],
    ['Online UPS 1–3 KVA','ups-1-3kva.php','fas fa-plug','#3A5F8A'],
  ],
];
require_once '../includes/product-template.php';
