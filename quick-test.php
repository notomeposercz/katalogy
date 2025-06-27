<?php
/**
 * Rychlý test modulu katalogy
 */

// Načtení PrestaShop config
$possible_configs = [
    dirname(__FILE__).'/../../config/config.inc.php',
    dirname(__FILE__).'/../../../config/config.inc.php',
    dirname(__FILE__).'/config/config.inc.php'
];

$config_loaded = false;
foreach ($possible_configs as $config_path) {
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
echo "<title>Quick Test - Katalogy Module</title>";
echo "<meta charset='utf-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>";
if (file_exists(_PS_MODULE_DIR_ . 'katalogy/views/css/katalogy.css')) {
    echo "<link href='/modules/katalogy/views/css/katalogy.css' rel='stylesheet'>";
}
echo "</head><body>";

echo "<div class='container mt-4'>";
echo "<h1>Quick Test - Katalogy Module</h1>";

// Test 1: Základní info
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>1. Základní informace</h3></div>";
echo "<div class='card-body'>";

$module = Module::getInstanceByName('katalogy');
if ($module && $module->active) {
    echo "<div class='alert alert-success'>✅ Modul katalogy je aktivní (verze: " . $module->version . ")</div>";
} else {
    echo "<div class='alert alert-danger'>❌ Modul katalogy není aktivní nebo neexistuje</div>";
}

echo "</div></div>";

// Test 2: Databáze
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>2. Databáze</h3></div>";
echo "<div class='card-body'>";

$table_name = _DB_PREFIX_ . 'katalogy';
$sql = "SHOW TABLES LIKE '$table_name'";
$result = Db::getInstance()->executeS($sql);

if ($result) {
    echo "<div class='alert alert-success'>✅ Tabulka $table_name existuje</div>";
    
    $count_sql = "SELECT COUNT(*) as count FROM `$table_name` WHERE active = 1";
    $count_result = Db::getInstance()->getRow($count_sql);
    echo "<p><strong>Počet aktivních katalogů:</strong> " . $count_result['count'] . "</p>";
} else {
    echo "<div class='alert alert-danger'>❌ Tabulka $table_name neexistuje</div>";
}

echo "</div></div>";

// Test 3: Hook test
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>3. Test Hook</h3></div>";
echo "<div class='card-body'>";

if (class_exists('Hook')) {
    echo "<h4>displayKatalogyContent:</h4>";
    try {
        $hook_result = Hook::exec('displayKatalogyContent');
        if ($hook_result) {
            echo "<div class='alert alert-success'>✅ Hook vrátil obsah</div>";
            echo "<div style='border: 2px solid #28a745; padding: 15px; border-radius: 5px;'>";
            echo $hook_result;
            echo "</div>";
        } else {
            echo "<div class='alert alert-warning'>⚠️ Hook nevrátil žádný obsah</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>❌ Chyba při volání hook: " . $e->getMessage() . "</div>";
    }
    
    echo "<hr>";
    echo "<h4>displayKatalogySimple:</h4>";
    try {
        $hook_result = Hook::exec('displayKatalogySimple');
        if ($hook_result) {
            echo "<div class='alert alert-success'>✅ Hook vrátil obsah</div>";
            echo "<div style='border: 2px solid #007bff; padding: 15px; border-radius: 5px;'>";
            echo $hook_result;
            echo "</div>";
        } else {
            echo "<div class='alert alert-warning'>⚠️ Hook nevrátil žádný obsah</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>❌ Chyba při volání hook: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>❌ Hook class neexistuje</div>";
}

echo "</div></div>";

// Test 4: Konfigurace
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>4. Konfigurace</h3></div>";
echo "<div class='card-body'>";

$email = Configuration::get('KATALOGY_EMAIL');
$cms_id = Configuration::get('KATALOGY_CMS_ID');

echo "<p><strong>KATALOGY_EMAIL:</strong> " . ($email ?: '<span class="text-danger">NENÍ NASTAVENO</span>') . "</p>";
echo "<p><strong>KATALOGY_CMS_ID:</strong> " . ($cms_id ?: '<span class="text-danger">NENÍ NASTAVENO</span>') . "</p>";

echo "</div></div>";

// Test 5: Návod
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>5. Návod k použití</h3></div>";
echo "<div class='card-body'>";

echo "<h4>Pro zobrazení katalogů v CMS stránce použijte:</h4>";
echo "<div class='alert alert-info'>";
echo "<p><strong>Kompletní obsah (s úvodním textem):</strong></p>";
echo "<code>{hook h='displayKatalogyContent'}</code>";
echo "</div>";

echo "<div class='alert alert-info'>";
echo "<p><strong>Pouze katalogy (bez úvodního textu):</strong></p>";
echo "<code>{hook h='displayKatalogySimple'}</code>";
echo "</div>";

echo "<h4>Užitečné odkazy:</h4>";
echo "<ul>";
echo "<li><a href='/modules/katalogy/debug-katalogy-2.php' target='_blank'>Detailní debug</a></li>";
echo "<li><a href='/modules/katalogy/fix-hooks.php' target='_blank'>Oprava hooks</a></li>";
echo "<li><a href='/modules/katalogy/add-test-catalogs.php' target='_blank'>Přidání testovacích katalogů</a></li>";
echo "<li><a href='/modules/katalogy/test-hook.php' target='_blank'>Test hooks</a></li>";
echo "</ul>";

echo "</div></div>";

echo "</div>"; // container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>";
if (file_exists(_PS_MODULE_DIR_ . 'katalogy/views/js/katalogy.js')) {
    echo "<script src='/modules/katalogy/views/js/katalogy.js'></script>";
}
echo "</body></html>";
?>
