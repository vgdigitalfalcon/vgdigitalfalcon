<?php
require_once '../includes/config.php';
$product = [
  'title'      => 'SMF Batteries (Sealed Maintenance Free)',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Batteries','#'],['SMF','']],
  'summary'    => 'Sealed Maintenance Free (SMF) VRLA batteries for UPS, inverters, and telecom applications. No water top-up required.',
  'desc'       => '<p>Sealed Maintenance Free (SMF) VRLA batteries for UPS, inverters, and telecom applications. No water top-up required.</p>',
  'icon'       => 'fas fa-battery-full',
  'icon_color' => '#2e7d32',
  'whatsapp_msg' => 'SMF Batteries (Sealed Maintenance Free)',
  'specs' => [['Type','SMF VRLA (Valve Regulated Lead Acid)'],['Voltage','12V'],['Capacity','7Ah – 200Ah'],['Technology','AGM / GEL'],['Design Life','3–5 years (float)'],['Self Discharge','<3% per month'],['Temp Range','-15°C to 50°C'],['Terminals','Flag / Bolt'],['Certifications','CE, UL, ISO']],
  'related' => [['Tubular Batteries','battery-tubular.php','fas fa-battery-three-quarters','#6d4c41'],['Online UPS 1–3 KVA','ups-1-3kva.php','fas fa-plug','#3A5F8A'],['Microtek Inverters','inverter-microtek.php','fas fa-bolt','#F57C00']],
];
require_once '../includes/product-template.php';
