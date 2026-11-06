<?php
require_once '../includes/config.php';
$product = [
  'title'      => 'Solar Water Heater',
  'breadcrumb' => [['Home','../index.php'],['Products','#'],['Solar Products','#'],['Solar Water Heater','']],
  'summary'    => 'Evacuated tube and flat plate solar water heaters for homes, hotels, hospitals and industrial applications.',
  'desc'       => '<p>Evacuated tube and flat plate solar water heaters for homes, hotels, hospitals and industrial applications.</p>',
  'icon'       => 'fas fa-sun',
  'icon_color' => '#D32F2F',
  'whatsapp_msg' => 'Solar Water Heater',
  'specs' => [['Type','Evacuated Tube Collector (ETC) / Flat Plate Collector (FPC)'],['Capacity','100 LPD – 5000 LPD'],['Operating Temp','60°C – 80°C'],['Pressure','Up to 6 bar (pressurised)'],['Tank Insulation','PUF (Polyurethane Foam)'],['Tank Material','SS 304 / SS 316'],['Collector','Borosilicate Glass Tubes / Copper Header'],['Annual Saving','Up to 1500 units / year (100 LPD)'],['Warranty','5 years (tank), 3 years (collector)'],['Certifications','BIS, MNRE approved']],
  'related' => [['Solar Inverter','solar-inverter.php','fas fa-solar-panel','#F57C00'],['SMF Batteries','battery-smf.php','fas fa-battery-full','#2e7d32']],
];
require_once '../includes/product-template.php';
