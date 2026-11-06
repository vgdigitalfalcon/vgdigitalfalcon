<?php
require_once '../includes/config.php';
$product = [
  'title'      => 'Solar Inverter',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Solar Products','#'],['Solar Inverter','']],
  'summary'    => 'Grid-tie and off-grid solar inverters for residential and commercial solar installations. Maximise solar energy utilisation.',
  'desc'       => '<p>Grid-tie and off-grid solar inverters for residential and commercial solar installations. Maximise solar energy utilisation.</p>',
  'icon'       => 'fas fa-solar-panel',
  'icon_color' => '#F57C00',
  'whatsapp_msg' => 'Solar Inverter',
  'specs' => [['Type','Grid-Tie / Off-Grid / Hybrid'],['Capacity','1 kW – 100 kW'],['Input (DC)','24V – 600V (MPPT)'],['Output (AC)','230V / 415V'],['MPPT Efficiency','>99%'],['System Efficiency','>97%'],['Protection','Over Voltage, Short Circuit, Islanding'],['Display','LCD + Wi-Fi App Monitoring'],['Warranty','5 years'],['Certifications','BIS, IEC']],
  'related' => [['Solar Water Heater','solar-water-heater.php','fas fa-sun','#D32F2F'],['Tubular Batteries','battery-tubular.php','fas fa-battery-three-quarters','#6d4c41'],['Online UPS 1–3 KVA','ups-1-3kva.php','fas fa-plug','#3A5F8A']],
];
require_once '../includes/product-template.php';
