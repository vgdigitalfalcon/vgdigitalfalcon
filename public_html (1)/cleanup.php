<?php
// The same list of cities used for generation
$cities = [
    "Madurai", "Chennai", "Trichy", "Coimbatore", "Erode", "Namakkal", "Tirunelveli", 
    "Nagercoil", "Kanniyakumari", "Salem", "Tiruppur", "Vellore", "Thoothukudi", 
    "Cuddalore", "Dharmapuri", "Dindigul", "Karur", "Krishnagiri", "Viluppuram", 
    "Sivakasi", "Thanjavur", "Theni", "Nagapattinam", "Tiruvannamalai", "Kanchipuram", 
    "Tiruvallur", "Ranipet", "Sivagangai", "Virudhunagar", "Ooty", "Perundurai", 
    "Coonoor", "Kumbakonam", "Ramanathapuram", "Kovilpatti", "Sattur", "Rajapalayam", 
    "Pondicherry", "Marthandam", "Hosur", "Tiruchengode", "Pollachi", "Gobichettipalayam", 
    "Mettupalayam", "Udumalpet", "Avinashi", "Palladam", "Kangeyam", "Dharapuram", 
    "Bhavani", "Sathyamangalam", "Anthiyur", "Komarapalayam", "Rasipuram", "Attur", 
    "Mettur", "Edappadi", "Sankari", "Yercaud", "Arakkonam", "Gudiyatham", "Arcot", 
    "Ambur", "Vaniyambadi", "Walajapet", "Arani", "Cheyyar", "Tindivanam", "Gingee", 
    "Chidambaram", "Neyveli", "Panruti", "Vriddhachalam", "Mayiladuthurai", "Sirkazhi", 
    "Mannargudi", "Pattukkottai", "Papanasam", "Orathanadu", "Perambalur", "Ariyalur", 
    "Jayankondam", "Pudukkottai", "Aranthangi", "Karaikudi", "Devakottai", "Paramakudi", 
    "Rameswaram", "Mudukulathur", "Usilampatti", "Melur", "Bodinayakanur", "Cumbum", 
    "Palani", "Oddanchatram", "Nilakottai", "Aruppukkottai", "Tenkasi", "Sankarankovil", 
    "Ambasamudram", "Chengalpattu", "Tambaram", "Avadi", "Poonamallee", "Red Hills", 
    "Sholinganallur", "Kelambakkam", "Siruseri", "Mahabalipuram", "Uthiramerur", 
    "Kalpakkam", "Sriperumbudur", "Manali", "Minjur", "Gummidipoondi", "Ponneri", 
    "Periyakulam", "Shencottai", "Veerakeralamputhur", "Batlagundu", "Andipatti", 
    "Vadipatti", "Alangulam", "Kayalpattinam", "Tiruchendur", "Ettayapuram", 
    "Vilathikulam", "Srivilliputhur", "Watrap", "Ilayangudi", "Keelakarai", "Mandapam", 
    "Thiruthuraipoondi", "Vedaranyam", "Velankanni", "Poompuhar", "Muthupet", "Lalgudi", 
    "Manapparai", "Musiri", "Thuraiyur", "Kulithalai", "Harur", "Pennagaram", 
    "Denkanikottai", "Bargur", "Uthangarai", "Valparai", "Kotagiri", "Gudalur"
];

echo "<h2>Starting Cleanup...</h2><ul>";

// Loop through the cities and delete the folders and their contents
foreach ($cities as $city) {
    $city_slug = strtolower(str_replace(' ', '-', trim($city)));
    
    // Check if the directory exists
    if (is_dir($city_slug)) {
        // Get all files inside the directory
        $files = array_diff(scandir($city_slug), array('.', '..'));
        
        // Delete each file
        foreach ($files as $file) {
            unlink("$city_slug/$file");
        }
        
        // Delete the empty directory
        rmdir($city_slug);
        echo "<li>Deleted folder and contents: <strong>/" . $city_slug . "/</strong></li>";
    }
}

// Delete the generated sitemap
if (file_exists('sitemap-programmatic.xml')) {
    unlink('sitemap-programmatic.xml');
    echo "<li>Deleted file: <strong>sitemap-programmatic.xml</strong></li>";
}

echo "</ul><h3>✅ Cleanup Complete! All generated pages have been removed.</h3>";
?>