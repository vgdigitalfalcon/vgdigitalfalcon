<?php
require_once '../includes/config.php';
$product = [
  'title'      => 'Online UPS — Three Phase 15–400 KVA',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Online UPS','#'],['Three Phase','#'],['15–400 KVA','']],
  'summary'    => 'Industrial-grade three-phase online UPS systems for data centres, factories, hospitals and large infrastructure.',
  'desc'       => '<p>Wavestek Three Phase Online UPS systems (15–400 KVA) are built for large-scale critical power protection. DSP-controlled double-conversion topology ensures zero transfer time and perfect output power quality regardless of input conditions.</p><p class="mt-3">Installed at Regional Meteorological Centre Chennai, SRM Technologies (60 KVA Eaton), and multiple government hospitals. Custom configurations available.</p>',
  'icon'       => 'fas fa-server','icon_color' => '#D32F2F','whatsapp_msg' => 'Three Phase Online UPS 15–400 KVA',
  'specs' => [
    ['Capacity','15 / 20 / 30 / 40 / 60 / 80 / 100 / 160 / 200 / 250 / 300 / 400 KVA'],
    ['Technology','DSP-Based True Double Conversion'],['Input Phase','3Ph/3Ph or 3Ph/1Ph'],
    ['Input Voltage','380V ± 20% (Three Phase)'],['Input Power Factor','>0.99 (with PFC)'],
    ['Output Voltage','380/400/415V AC'],['Output Frequency','50Hz ± 0.1Hz'],
    ['Efficiency','Up to 96% AC-AC'],['Transfer Time','0 ms'],
    ['Overload','110% – 10 min / 150% – 10s'],['Battery Type','SMF VRLA or Lithium-Ion (optional)'],
    ['Display','7" Touch LCD'],['Communication','RS-232, USB, SNMP, Dry Contact'],
    ['Parallel Redundancy','Up to 4 units (N+X)'],['Operating Temp','0°C – 40°C'],
  ],
  'related' => [
    ['Modular UPS 10–300 KVA','ups-modular-10-300kva.php','fas fa-cubes','#2e7d32'],
    ['Isolation Transformer','isolation-transformer.php','fas fa-exchange-alt','#6d4c41'],
    ['Three Phase Stabilizer','stabilizer-three-phase.php','fas fa-sliders-h','#1565C0'],
  ],
];
require_once '../includes/product-template.php';
