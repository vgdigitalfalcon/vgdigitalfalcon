<?php

include("config/db.php");

header("Content-Type: text/xml");

echo '<?xml version="1.0" encoding="UTF-8"?>';

echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

$result = $conn->query("SELECT slug FROM seo_pages");

while($row = $result->fetch_assoc()){

echo "<url>";
echo "<loc>https://vgdigitalfalcon.com/service/".$row['slug']."</loc>";
echo "</url>";

}

echo "</urlset>";