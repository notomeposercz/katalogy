<?php
/**
 * Jednoduchý test shortcode - umístit do /modules/katalogy/
 */

// Načtení PrestaShop
$config_paths = [
    dirname(__FILE__).'/../../config/config.inc.php',
    dirname(__FILE__).'/../../../config/config.inc.php'
];

$config_loaded = false;
foreach ($config_paths as $config_path) {
    if (file_exists($config_path)) {
        require_once($config_path);
        $config_loaded = true;
        break;
    }
}

if (!$config_loaded) {
    die("❌ PrestaShop config nenalezen");
}

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test Shortcode - Katalogy</title>";
echo "<meta charset='utf-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body>";

echo "<div class='container mt-4'>";
echo "<h1>Test Shortcode - Katalogy</h1>";

// Test modulu
echo "<h2>1. Test modulu</h2>";
$module = Module::getInstanceByName('katalogy');
if ($module && $module->active) {
    echo "<div class='alert alert-success'>✅ Modul katalogy je aktivní</div>";
} else {
    echo "<div class='alert alert-danger'>❌ Modul katalogy není aktivní</div>";
    echo "</div></body></html>";
    exit;
}

// Test databáze
echo "<h2>2. Test databáze</h2>";
require_once(_PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php');
$catalogs = Katalog::getAllActive();
echo "<div class='alert alert-info'>Počet aktivních katalogů: " . count($catalogs) . "</div>";

// Test hook registrace
echo "<h2>3. Test hook registrace</h2>";
$hook_id = Hook::getIdByName('displayBeforeBodyClosingTag');
if ($hook_id) {
    $sql = "SELECT * FROM `" . _DB_PREFIX_ . "hook_module` WHERE id_hook = $hook_id AND id_module = " . (int)$module->id;
    $hook_module = Db::getInstance()->getRow($sql);
    if ($hook_module) {
        echo "<div class='alert alert-success'>✅ Modul je zaregistrován na displayBeforeBodyClosingTag</div>";
    } else {
        echo "<div class='alert alert-warning'>❌ Modul není zaregistrován na displayBeforeBodyClosingTag</div>";
        echo "<a href='?register_hook=1' class='btn btn-primary'>Zaregistrovat hook</a>";
    }
} else {
    echo "<div class='alert alert-danger'>❌ Hook displayBeforeBodyClosingTag neexistuje</div>";
}

// Registrace hook
if (isset($_GET['register_hook'])) {
    if ($module->registerHook('displayBeforeBodyClosingTag')) {
        echo "<div class='alert alert-success'>✅ Hook zaregistrován! Obnovte stránku.</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Nepodařilo se zaregistrovat hook</div>";
    }
}

// Test generování shortcode scriptu
echo "<h2>4. Test generování shortcode scriptu</h2>";
if (method_exists($module, 'generateShortcodeScript')) {
    echo "<div class='alert alert-success'>✅ Metoda generateShortcodeScript existuje</div>";
} else {
    echo "<div class='alert alert-danger'>❌ Metoda generateShortcodeScript neexistuje</div>";
}

// Simulace CMS obsahu
echo "<h2>5. Simulace CMS obsahu se shortcode</h2>";
echo "<div class='alert alert-info'>";
echo "<strong>Instrukce:</strong><br>";
echo "1. Upravte CMS stránku a vložte: <code>[katalogy]</code><br>";
echo "2. Uložte CMS stránku<br>";
echo "3. Obnovte CMS stránku v prohlížeči<br>";
echo "4. Shortcode by se měl automaticky nahradit obsahem katalogů";
echo "</div>";

// Test obsahu
echo "<h3>Testovací obsah:</h3>";
echo "<div style='border: 2px dashed #007bff; padding: 20px; background: #f8f9fa;' class='cms-content'>";
echo "<h4>Katalogy reklamních předmětů</h4>";
echo "<p>Úvodní text...</p>";
echo "[katalogy]";
echo "<p>Závěrečný text...</p>";
echo "</div>";

// Simulace hook výstupu
echo "<h2>6. Simulace hook výstupu</h2>";
$_GET['id_cms'] = 23; // Simulace CMS stránky
Context::getContext()->controller = new stdClass();
Context::getContext()->controller->php_self = 'cms';

$hook_output = $module->hookDisplayBeforeBodyClosingTag([]);
if ($hook_output) {
    echo "<div class='alert alert-success'>✅ Hook vrátil script (" . strlen($hook_output) . " znaků)</div>";
    echo "<details><summary>Zobrazit script</summary>";
    echo "<pre>" . htmlspecialchars($hook_output) . "</pre>";
    echo "</details>";
} else {
    echo "<div class='alert alert-warning'>❌ Hook nevrátil žádný script</div>";
}

echo "<h2>7. Návod</h2>";
echo "<div class='alert alert-primary'>";
echo "<h4>Postup pro zobrazení katalogů na CMS stránce:</h4>";
echo "<ol>";
echo "<li>Ujistěte se, že modul je aktivní a hook je zaregistrován (výše)</li>";
echo "<li>Otevřete administraci PrestaShop</li>";
echo "<li>Upravte CMS stránku 'Katalogy reklamních předmětů ke stažení'</li>";
echo "<li>V obsahu stránky nahraďte <code>{hook h='displayKatalogyContent'}</code> za <code>[katalogy]</code></li>";
echo "<li>Uložte stránku</li>";
echo "<li>Obnovte CMS stránku v prohlížeči</li>";
echo "</ol>";
echo "<p><strong>Shortcode se automaticky nahradí obsahem katalogů pomocí JavaScript.</strong></p>";
echo "</div>";

echo "</div>"; // container

// Přidání hook scriptu pro test
echo $hook_output;

echo "</body></html>";
?>
