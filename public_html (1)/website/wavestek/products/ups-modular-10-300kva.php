<?php
require_once '../includes/config.php';
$product = [
  'title'      => 'Modular UPS — 10–300 KVA',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Online UPS','#'],['Modular UPS','#'],['10–300 KVA','']],
  'summary'    => 'Scalable modular UPS architecture — add power modules as your load grows. Hot-swap modules for zero-downtime maintenance.',
  'desc'       => '<p>Wavestek Modular UPS (10–300 KVA) revolutionises power protection with its hot-swappable power module design. Start with the capacity you need today and expand seamlessly as your requirements grow — without interrupting your operations.</p><p class="mt-3">Each module can be replaced or added while the system is running. Ideal for growing data centres, telecom facilities, and critical infrastructure.</p>',
  'icon'       => 'fas fa-cubes','icon_color' => '#2e7d32','whatsapp_msg' => 'Modular UPS 10–300 KVA',
  'specs' => [
    ['System Capacity','10 – 300 KVA (expandable)'],['Module Size','10 KVA or 20 KVA per module'],
    ['Technology','True Online Double Conversion'],['Input Voltage','380V Three Phase'],
    ['Output Voltage','380V / 400V / 415V'],['Efficiency','Up to 96%'],
    ['Transfer Time','0 ms'],['Hot-Swap','Yes — modules replaceable under load'],
    ['Parallel Units','Up to N+X redundancy'],['Display','Touch LCD with module status'],
    ['Communication','SNMP, RS-232, USB, ModBus'],['Battery','SMF / Li-Ion options'],
    ['Form Factor','Rack / Tower'],['Operating Temp','0°C – 40°C'],
  ],
  'related' => [
    ['Three Phase 15–400 KVA','ups-15-400kva.php','fas fa-server','#D32F2F'],
    ['Isolation Transformer','isolation-transformer.php','fas fa-exchange-alt','#6d4c41'],
  ],
];
require_once '../includes/product-template.php';
