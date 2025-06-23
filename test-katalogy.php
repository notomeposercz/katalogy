<?php
echo "TEST - soubor funguje!<br>";
echo "Server: " . $_SERVER['HTTP_HOST'] . "<br>";
echo "Script path: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current dir: " . getcwd() . "<br>";

// Test existence config souboru
$config = dirname(__FILE__) . '/config/config.inc.php';
echo "Config existuje: " . (file_exists($config) ? 'ANO' : 'NE') . "<br>";
echo "Config path: $config<br>";

// Výpis souborů v adresáři
echo "<h2>Soubory v root adresáři:</h2>";
$files = scandir('.');
foreach($files as $file) {
    if($file != '.' && $file != '..' && is_file($file)) {
        echo "- $file<br>";
    }
}
?>