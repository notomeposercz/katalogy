<?php
/**
 * Smazání cache PrestaShop
 * Umístit do root adresáře PrestaShop
 */

echo "<h1>SMAZÁNÍ CACHE PRESTASHOP</h1>";

// Definice cache adresářů
$cache_dirs = [
    'cache/smarty/cache' => 'Smarty Cache',
    'cache/smarty/compile' => 'Smarty Compile', 
    'var/cache' => 'Symfony Cache',
    'cache/cachefs' => 'CacheFS',
    'img/tmp' => 'Temporary Images'
];

$total_deleted = 0;

foreach ($cache_dirs as $dir => $name) {
    $full_path = dirname(__FILE__) . '/' . $dir;
    echo "<h2>$name ($dir)</h2>";
    
    if (is_dir($full_path)) {
        $deleted = 0;
        
        // Rekurzivní mazání souborů
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($full_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                if (unlink($file->getRealPath())) {
                    $deleted++;
                }
            } elseif ($file->isDir()) {
                rmdir($file->getRealPath());
            }
        }
        
        echo "✅ Smazáno $deleted souborů<br>";
        $total_deleted += $deleted;
    } else {
        echo "⚠️ Adresář neexistuje<br>";
    }
}

// Smazání specifických cache souborů
$cache_files = [
    'cache/class_index.php' => 'Class Index',
    'config/xml/modules_list.xml' => 'Modules List'
];

foreach ($cache_files as $file => $name) {
    $full_path = dirname(__FILE__) . '/' . $file;
    echo "<h2>$name ($file)</h2>";
    
    if (file_exists($full_path)) {
        if (unlink($full_path)) {
            echo "✅ Soubor smazán<br>";
            $total_deleted++;
        } else {
            echo "❌ Nepodařilo se smazat soubor<br>";
        }
    } else {
        echo "⚠️ Soubor neexistuje<br>";
    }
}

echo "<h2>Závěr</h2>";
echo "<p><strong>Celkem smazáno: $total_deleted souborů</strong></p>";
echo "<p>Cache byla vymazána. Nyní můžete:</p>";
echo "<ul>";
echo "<li>Obnovit CMS stránku s katalogy</li>";
echo "<li>Zkontrolovat, že se shortcode [katalogy] zobrazuje správně</li>";
echo "<li>Otestovat funkčnost formulářů</li>";
echo "</ul>";

echo "<p><a href='/content/23-katalogy-reklamnich-predmetu-ke-stazeni'>➜ Přejít na CMS stránku katalogů</a></p>";
?>
