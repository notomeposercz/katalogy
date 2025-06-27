<?php
/**
 * Debug shortcode na konkrétní CMS stránce
 * Umístit do root adresáře PrestaShop
 */

require_once(dirname(__FILE__).'/config/config.inc.php');

echo "<h1>DEBUG SHORTCODE NA CMS STRÁNCE</h1>";

$cms_id = 23; // ID CMS stránky s katalogy

// Test CMS stránky
echo "<h2>1. Test CMS stránky ID: $cms_id</h2>";
$cms = new CMS($cms_id, Context::getContext()->language->id);
if (Validate::isLoadedObject($cms)) {
    echo "✅ CMS stránka načtena<br>";
    echo "Název: " . $cms->meta_title . "<br>";
    echo "Aktivní: " . ($cms->active ? 'ANO' : 'NE') . "<br>";
    
    // Zkontroluj obsah
    echo "<h3>Obsah CMS stránky:</h3>";
    echo "Obsahuje [katalogy]: " . (strpos($cms->content, '[katalogy]') !== false ? 'ANO' : 'NE') . "<br>";
    echo "Obsahuje {hook}: " . (strpos($cms->content, '{hook') !== false ? 'ANO' : 'NE') . "<br>";
    
    echo "<h4>Část obsahu (prvních 500 znaků):</h4>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
    echo htmlspecialchars(substr($cms->content, 0, 500));
    echo "</div>";
} else {
    echo "❌ CMS stránka nenalezena<br>";
}

// Test modulu
echo "<h2>2. Test modulu katalogy</h2>";
$module = Module::getInstanceByName('katalogy');
if ($module && $module->active) {
    echo "✅ Modul katalogy je aktivní<br>";
    echo "Verze: " . $module->version . "<br>";
} else {
    echo "❌ Modul katalogy není aktivní<br>";
}

// Test hook displayBeforeBodyClosingTag
echo "<h2>3. Test hook displayBeforeBodyClosingTag</h2>";
$hook_id = Hook::getIdByName('displayBeforeBodyClosingTag');
if ($hook_id) {
    echo "✅ Hook existuje (ID: $hook_id)<br>";
    
    // Zkontroluj registraci modulu
    $sql = "SELECT * FROM `" . _DB_PREFIX_ . "hook_module` WHERE id_hook = $hook_id AND id_module = " . (int)$module->id;
    $hook_module = Db::getInstance()->getRow($sql);
    if ($hook_module) {
        echo "✅ Modul je zaregistrován na hook<br>";
    } else {
        echo "❌ Modul NENÍ zaregistrován na hook<br>";
        echo "<a href='?register=1'>Zaregistrovat hook</a><br>";
    }
} else {
    echo "❌ Hook neexistuje<br>";
}

// Registrace hook
if (isset($_GET['register'])) {
    if ($module->registerHook('displayBeforeBodyClosingTag')) {
        echo "✅ Hook zaregistrován!<br>";
    } else {
        echo "❌ Nepodařilo se zaregistrovat hook<br>";
    }
}

// Test výstupu hook
echo "<h2>4. Test výstupu hook</h2>";
// Simulace CMS prostředí
$_GET['id_cms'] = $cms_id;
Context::getContext()->controller = new stdClass();
Context::getContext()->controller->php_self = 'cms';

if (method_exists($module, 'hookDisplayBeforeBodyClosingTag')) {
    echo "✅ Metoda hookDisplayBeforeBodyClosingTag existuje<br>";
    
    try {
        $hook_output = $module->hookDisplayBeforeBodyClosingTag([]);
        if ($hook_output && trim($hook_output)) {
            echo "✅ Hook vrátil script (" . strlen($hook_output) . " znaků)<br>";
            echo "<details><summary>Zobrazit script (prvních 1000 znaků)</summary>";
            echo "<pre>" . htmlspecialchars(substr($hook_output, 0, 1000)) . "</pre>";
            echo "</details>";
        } else {
            echo "❌ Hook nevrátil žádný script<br>";
        }
    } catch (Exception $e) {
        echo "❌ Chyba při volání hook: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Metoda hookDisplayBeforeBodyClosingTag neexistuje<br>";
}

// Test katalogů v databázi
echo "<h2>5. Test katalogů v databázi</h2>";
require_once(_PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php');
try {
    $catalogs = Katalog::getAllActive();
    echo "✅ Počet aktivních katalogů: " . count($catalogs) . "<br>";
    
    if (count($catalogs) > 0) {
        echo "<h4>Seznam katalogů:</h4>";
        foreach ($catalogs as $catalog) {
            echo "- " . $catalog['title'] . " (ID: " . $catalog['id_katalog'] . ")<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Chyba při načítání katalogů: " . $e->getMessage() . "<br>";
}

// Návod
echo "<h2>6. Návod k opravě</h2>";
echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3;'>";
echo "<h3>Postup:</h3>";
echo "<ol>";
echo "<li><strong>Ujistěte se, že hook je zaregistrován</strong> (výše)</li>";
echo "<li><strong>Upravte CMS stránku:</strong>";
echo "<ul>";
echo "<li>Přihlaste se do administrace PrestaShop</li>";
echo "<li>Jděte na Design > Stránky > Upravit stránku 'Katalogy reklamních předmětů ke stažení'</li>";
echo "<li>V obsahu nahraďte <code>{hook h='displayKatalogyContent'}</code> za <code>[katalogy]</code></li>";
echo "<li>Uložte stránku</li>";
echo "</ul></li>";
echo "<li><strong>Otestujte:</strong> Obnovte CMS stránku: <a href='/content/23-katalogy-reklamnich-predmetu-ke-stazeni' target='_blank'>Katalogy stránka</a></li>";
echo "</ol>";

echo "<h3>Jak to funguje:</h3>";
echo "<p>JavaScript na konci stránky najde <code>[katalogy]</code> v obsahu a nahradí ho skutečnými katalogy.</p>";
echo "</div>";

// Test přímého nahrazení
echo "<h2>7. Test přímého nahrazení shortcode</h2>";
if (isset($cms) && Validate::isLoadedObject($cms)) {
    $test_content = $cms->content;
    
    if (strpos($test_content, '[katalogy]') !== false) {
        echo "✅ Nalezen [katalogy] shortcode v obsahu<br>";
        
        // Simulace nahrazení
        $katalogy_content = $module->hookDisplayKatalogyContent([]);
        if ($katalogy_content) {
            $replaced_content = str_replace('[katalogy]', $katalogy_content, $test_content);
            echo "✅ Shortcode by byl nahrazen obsahem (" . strlen($katalogy_content) . " znaků)<br>";
        } else {
            echo "❌ Modul nevrátil obsah pro nahrazení<br>";
        }
    } else {
        echo "❌ Shortcode [katalogy] nenalezen v obsahu CMS<br>";
        echo "<p><strong>Akce:</strong> Upravte CMS stránku a přidejte [katalogy] do obsahu</p>";
    }
}

echo "<hr>";
echo "<p><strong>Datum testu:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
