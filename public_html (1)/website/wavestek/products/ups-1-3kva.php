<?php
require_once '../includes/config.php';

$product = [
  'title'      => 'Online UPS — Single Phase 1–3 KVA',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Online UPS','#'],['Single Phase','#'],['1–3 KVA','']],
  'summary'    => 'Wavestek single-phase online UPS systems from 1 KVA to 3 KVA — designed for computers, servers, medical equipment and small office loads.',
  'desc'       => '<p>Our 1–3 KVA Single Phase Online UPS delivers true double-conversion protection, ensuring your critical equipment receives clean, stable power at all times. Manufactured in our Chennai facility, these units feature high-efficiency operation, wide input voltage range, and built-in surge protection.</p><p class="mt-3">Ideal for small servers, workstations, POS systems, medical instruments, and home office setups. Backed by Wavestek\'s 24/7 service support across India.</p>',
  'icon'       => 'fas fa-plug',
  'icon_color' => '#3A5F8A',
  'whatsapp_msg' => 'Online UPS Single Phase 1–3 KVA',
  'specs'      => [
    ['Capacity',               '1 KVA / 2 KVA / 3 KVA'],
    ['Technology',             'True Online Double Conversion'],
    ['Phase',                  'Single Phase (1Ph/1Ph)'],
    ['Input Voltage Range',    '160V – 280V AC'],
    ['Input Frequency',        '50 Hz ± 5 Hz'],
    ['Output Voltage',         '230V AC ± 2%'],
    ['Output Frequency',       '50 Hz ± 0.5 Hz'],
    ['Power Factor (Output)',  '0.8 Lag'],
    ['Efficiency',             'Up to 92%'],
    ['Battery Type',           'SMF VRLA (Maintenance Free)'],
    ['Battery Voltage',        '12V × 4 (48V)'],
    ['Backup Time',            '15–30 min at full load (extendable)'],
    ['Recharge Time',          '4–6 hours to 90%'],
    ['Transfer Time',          '0 ms (True Online)'],
    ['Waveform',               'Pure Sine Wave'],
    ['Display',                'LED / LCD Panel'],
    ['Protection',             'Over Voltage, Under Voltage, Short Circuit, Overload, Battery Low'],
    ['Communication',          'RS-232 / USB Port'],
    ['Operating Temp',         '0°C to 40°C'],
    ['Humidity',               '0–95% (Non-condensing)'],
  ],
  'related' => [
    ['Online UPS 6–10 KVA',        'ups-6-10kva.php',           'fas fa-plug',         '#3A5F8A'],
    ['Three Phase UPS 15–400 KVA', 'ups-15-400kva.php',         'fas fa-server',       '#D32F2F'],
    ['Modular UPS 10–300 KVA',     'ups-modular-10-300kva.php', 'fas fa-cubes',        '#2e7d32'],
    ['Isolation Transformer',       'isolation-transformer.php', 'fas fa-exchange-alt', '#6d4c41'],
  ],
];

require_once '../includes/product-template.php';
