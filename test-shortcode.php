<?php
/**
 * Test shortcode funkčnosti
 * Umístit do root adresáře PrestaShop
 */

require_once(dirname(__FILE__).'/config/config.inc.php');

echo "<h1>TEST SHORTCODE KATALOGY</h1>";

// Test 1: Základní funkčnost modulu
echo "<h2>1. Test modulu</h2>";
$module = Module::getInstanceByName('katalogy');
if ($module && $module->active) {
    echo "✅ Modul katalogy je aktivní<br>";
} else {
    die("❌ Modul katalogy není aktivní");
}

// Test 2: Test CMS stránky
echo "<h2>2. Test CMS stránky ID 23</h2>";
$cms_id = 23;
$cms = new CMS($cms_id, Context::getContext()->language->id);
if (Validate::isLoadedObject($cms)) {
    echo "✅ CMS stránka načtena<br>";
    echo "Název: " . $cms->meta_title . "<br>";
    echo "Obsah obsahuje [katalogy]: " . (strpos($cms->content, '[katalogy]') !== false ? 'ANO' : 'NE') . "<br>";
    echo "Obsah obsahuje {hook}: " . (strpos($cms->content, '{hook') !== false ? 'ANO' : 'NE') . "<br>";
    
    // Zobraz část obsahu
    echo "<h3>Část obsahu CMS:</h3>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; max-height: 200px; overflow: auto;'>";
    echo htmlspecialchars(substr($cms->content, 0, 500));
    echo "</div>";
} else {
    echo "❌ CMS stránka nenalezena<br>";
}

// Test 3: Simulace zpracování shortcode
echo "<h2>3. Test zpracování shortcode</h2>";

// Test obsahu s [katalogy]
$test_content = "Úvodní text\n\n[katalogy]\n\nZávěrečný text";
echo "<h3>Testovací obsah:</h3>";
echo "<pre>" . htmlspecialchars($test_content) . "</pre>";

// Zpracování shortcode
if (strpos($test_content, '[katalogy]') !== false) {
    echo "✅ Shortcode [katalogy] nalezen<br>";
    
    // Získání obsahu z modulu
    $katalogy_content = $module->hookDisplayKatalogyContent([]);
    if ($katalogy_content) {
        echo "✅ Modul vrátil obsah (" . strlen($katalogy_content) . " znaků)<br>";
        
        // Nahrazení shortcode
        $processed_content = str_replace('[katalogy]', $katalogy_content, $test_content);
        echo "<h3>Zpracovaný obsah:</h3>";
        echo "<div style='border: 2px solid green; padding: 10px; max-height: 400px; overflow: auto;'>";
        echo $processed_content;
        echo "</div>";
    } else {
        echo "❌ Modul nevrátil obsah<br>";
    }
} else {
    echo "❌ Shortcode [katalogy] nenalezen<br>";
}

// Test 4: Test override souboru
echo "<h2>4. Test override souboru</h2>";
$override_file = dirname(__FILE__) . '/override/controllers/front/CmsController.php';
if (file_exists($override_file)) {
    echo "✅ Override soubor existuje<br>";
    echo "Cesta: $override_file<br>";
    
    // Zkontroluj, jestli je override aktivní
    $cache_file = dirname(__FILE__) . '/cache/class_index.php';
    if (file_exists($cache_file)) {
        $cache_content = file_get_contents($cache_file);
        if (strpos($cache_content, 'override/controllers/front/CmsController.php') !== false) {
            echo "✅ Override je zaregistrován v cache<br>";
        } else {
            echo "❌ Override není zaregistrován v cache<br>";
            echo "<p><strong>Akce:</strong> Smažte cache složku nebo přegenerujte cache</p>";
        }
    } else {
        echo "⚠️ Cache soubor neexistuje<br>";
    }
} else {
    echo "❌ Override soubor neexistuje<br>";
    echo "Vytvořte soubor: $override_file<br>";
}

// Test 5: Návod k použití
echo "<h2>5. Návod k použití</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007cba;'>";
echo "<h3>Způsoby zobrazení katalogů v CMS:</h3>";
echo "<p><strong>1. Shortcode (doporučeno):</strong></p>";
echo "<code>[katalogy]</code> - kompletní obsah s úvodním textem<br>";
echo "<code>[katalogy-simple]</code> - pouze katalogy<br><br>";

echo "<p><strong>2. Hook (alternativa):</strong></p>";
echo "<code>{hook h='displayKatalogyContent'}</code> - kompletní obsah<br>";
echo "<code>{hook h='displayKatalogySimple'}</code> - pouze katalogy<br><br>";

echo "<p><strong>3. Po změně obsahu CMS:</strong></p>";
echo "- Smažte cache PrestaShop<br>";
echo "- Obnovte stránku<br>";
echo "</div>";

// Test 6: Akce
echo "<h2>6. Akce</h2>";
if (isset($_GET['clear_cache'])) {
    echo "Mazání cache...<br>";
    
    // Smaž cache složky
    $cache_dirs = [
        dirname(__FILE__) . '/cache/smarty/cache',
        dirname(__FILE__) . '/cache/smarty/compile',
        dirname(__FILE__) . '/var/cache'
    ];
    
    foreach ($cache_dirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            echo "✅ Cache smazána: $dir<br>";
        }
    }
    
    echo "<p><strong>Cache smazána. Obnovte CMS stránku.</strong></p>";
} else {
    echo "<a href='?clear_cache=1' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>SMAZAT CACHE</a><br><br>";
}

echo "<h2>Závěr</h2>";
echo "<p>Pro zobrazení katalogů v CMS stránce:</p>";
echo "<ol>";
echo "<li>Upravte obsah CMS stránky a vložte: <code>[katalogy]</code></li>";
echo "<li>Uložte změny</li>";
echo "<li>Smažte cache (tlačítko výše)</li>";
echo "<li>Obnovte CMS stránku</li>";
echo "</ol>";
?>
