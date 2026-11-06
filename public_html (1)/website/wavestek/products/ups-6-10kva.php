<?php
require_once '../includes/config.php';
$product = [
  'title'      => 'Online UPS — Single Phase 6–10 KVA',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Online UPS','#'],['Single Phase','#'],['6–10 KVA','']],
  'summary'    => 'High-capacity single-phase online UPS for mid-size server rooms, medical imaging, and industrial equipment.',
  'desc'       => '<p>The Wavestek 6–10 KVA Single Phase Online UPS is engineered for mission-critical environments requiring higher loads. With true double-conversion technology, it eliminates all power problems including surges, sags, spikes, and complete outages — delivering clean power 24/7.</p><p class="mt-3">Perfect for server racks, network equipment, medical devices, and mid-scale office loads. Extended battery options available.</p>',
  'icon'       => 'fas fa-plug','icon_color' => '#3A5F8A','whatsapp_msg' => 'Online UPS Single Phase 6–10 KVA',
  'specs' => [
    ['Capacity','6 KVA / 8 KVA / 10 KVA'],['Technology','True Online Double Conversion'],['Phase','Single Phase'],
    ['Input Voltage Range','160V – 280V'],['Output Voltage','230V ± 2%'],['Efficiency','Up to 93%'],
    ['Battery Type','SMF VRLA'],['Backup Time','10–20 min at full load'],['Waveform','Pure Sine Wave'],
    ['Transfer Time','0 ms'],['Display','LCD Panel'],['Communication','RS-232 / USB / SNMP (optional)'],
    ['Overload Capacity','110% for 60s, 125% for 10s'],['Operating Temp','0°C – 40°C'],
  ],
  'related' => [
    ['UPS 1–3 KVA','ups-1-3kva.php','fas fa-plug','#3A5F8A'],
    ['Three Phase 15–400 KVA','ups-15-400kva.php','fas fa-server','#D32F2F'],
    ['SMF Batteries','battery-smf.php','fas fa-battery-full','#1565C0'],
  ],
];
require_once '../includes/product-template.php';
