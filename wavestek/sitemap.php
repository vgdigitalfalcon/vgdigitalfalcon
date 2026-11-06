<?php
header('Content-Type: application/xml; charset=utf-8');
$base = 'https://www.wavestekpowersolutions.in';
$today = date('Y-m-d');

$urls = [
  ['/', '1.0', 'weekly'],
  ['/about', '0.8', 'monthly'],
  ['/service', '0.8', 'monthly'],
  ['/contact', '0.7', 'monthly'],
  ['/products/ups-1-3kva', '0.9', 'monthly'],
  ['/products/ups-6-10kva', '0.9', 'monthly'],
  ['/products/ups-15-400kva', '0.9', 'monthly'],
  ['/products/ups-modular-10-300kva', '0.9', 'monthly'],
  ['/products/isolation-transformer', '0.8', 'monthly'],
  ['/products/stabilizer-single-phase', '0.9', 'monthly'],
  ['/products/stabilizer-three-phase', '0.9', 'monthly'],
  ['/products/inverter-microtek', '0.8', 'monthly'],
  ['/products/inverter-luminous', '0.8', 'monthly'],
  ['/products/battery-smf', '0.8', 'monthly'],
  ['/products/battery-tubular', '0.8', 'monthly'],
  ['/products/solar-inverter', '0.8', 'monthly'],
  ['/products/solar-water-heater', '0.8', 'monthly'],
];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
  echo "  <url>\n";
  echo "    <loc>" . $base . $u[0] . "</loc>\n";
  echo "    <lastmod>" . $today . "</lastmod>\n";
  echo "    <changefreq>" . $u[2] . "</changefreq>\n";
  echo "    <priority>" . $u[1] . "</priority>\n";
  echo "  </url>\n";
}
echo '</urlset>';
?>
