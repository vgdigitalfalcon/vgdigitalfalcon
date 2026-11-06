<?php
define('SITE_NAME', 'Wavestek Power Solutions');
define('SITE_URL', '');
define('PHONE_PRIMARY', '+91 9884152004');
define('PHONE_SECONDARY', '+91 93424 86992');
define('PHONE_OFFICE', '+91 044 4863 4854');
define('EMAIL_ADMIN', 'admin@wavestekpowersolutions.in');
define('EMAIL_GENERAL', 'wavestekups@gmail.com');
define('WHATSAPP_NO', '919884152004');
define('ADDRESS_SALES', '3/23, Old Bangaru Colony 1st Street, West K.K.Nagar, Chennai - 600078');
define('ADDRESS_FACTORY', 'Door No 5B, Perumal Koil Street, Kolapakkam, Chennai - 600128');

$nav_menu = [
    ['label' => 'Home',    'href' => 'index.php'],
    ['label' => 'About',   'href' => 'about.php'],
    ['label' => 'Products','href' => '#', 'dropdown' => [
        ['label' => 'Online UPS', 'href' => '#', 'dropdown' => [
            ['label' => 'Single Phase', 'href' => '#', 'dropdown' => [
                ['label' => '1–3 KVA',  'href' => 'products/ups-1-3kva.php'],
                ['label' => '6–10 KVA', 'href' => 'products/ups-6-10kva.php'],
            ]],
            ['label' => 'Three Phase', 'href' => '#', 'dropdown' => [
                ['label' => '15–400 KVA', 'href' => 'products/ups-15-400kva.php'],
            ]],
            ['label' => 'Modular UPS', 'href' => '#', 'dropdown' => [
                ['label' => '10–300 KVA', 'href' => 'products/ups-modular-10-300kva.php'],
            ]],
            ['label' => 'Isolation Transformer', 'href' => 'products/isolation-transformer.php'],
        ]],
        ['label' => 'Servo Voltage Stabilizer', 'href' => '#', 'dropdown' => [
            ['label' => 'Single Phase (1–20 KVA)',  'href' => 'products/stabilizer-single-phase.php'],
            ['label' => 'Three Phase (3–500 KVA)',  'href' => 'products/stabilizer-three-phase.php'],
        ]],
        ['label' => 'Inverters', 'href' => '#', 'dropdown' => [
            ['label' => 'Microtek',  'href' => 'products/inverter-microtek.php'],
            ['label' => 'Luminous',  'href' => 'products/inverter-luminous.php'],
        ]],
        ['label' => 'Batteries', 'href' => '#', 'dropdown' => [
            ['label' => 'SMF',     'href' => 'products/battery-smf.php'],
            ['label' => 'Tubular', 'href' => 'products/battery-tubular.php'],
        ]],
        ['label' => 'Solar Products', 'href' => '#', 'dropdown' => [
            ['label' => 'Solar Inverter',      'href' => 'products/solar-inverter.php'],
            ['label' => 'Solar Water Heater',  'href' => 'products/solar-water-heater.php'],
        ]],
    ]],
    ['label' => 'Service',  'href' => 'service.php'],
    ['label' => 'Contact',  'href' => 'contact.php'],
];

function render_dropdown($items, $depth = 0) {
    $cls = $depth === 0 ? 'dropdown-menu' : 'dropdown-menu dropdown-submenu-menu';
    echo '<ul class="' . $cls . '">';
    foreach ($items as $item) {
        $has_sub = !empty($item['dropdown']);
        if ($has_sub) {
            echo '<li class="dropdown-item-has-children">';
            echo '<a href="' . htmlspecialchars($item['href']) . '" class="dropdown-item">' . htmlspecialchars($item['label']) . ' <i class="fas fa-chevron-right ms-auto small"></i></a>';
            render_dropdown($item['dropdown'], $depth + 1);
            echo '</li>';
        } else {
            echo '<li><a href="' . htmlspecialchars(SITE_URL . '/' . ltrim($item['href'], '/')) . '" class="dropdown-item">' . htmlspecialchars($item['label']) . '</a></li>';
        }
    }
    echo '</ul>';
}
?>
