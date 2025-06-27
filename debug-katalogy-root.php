<?php
/**
 * DEBUG KATALOGY - umístit do root adresáře PrestaShop
 */

require_once(dirname(__FILE__).'/config/config.inc.php');

echo "<h1>DEBUG KATALOGY - ROOT - " . date('Y-m-d H:i:s') . "</h1>";

// 1. Základní info
echo "<h2>1. Základní informace</h2>";
echo "✅ PrestaShop config načten<br>";
echo "DB Prefix: " . _DB_PREFIX_ . "<br>";
echo "Module Dir: " . _PS_MODULE_DIR_ . "<br>";
echo "Root Dir: " . dirname(__FILE__) . "<br>";

// 2. Test modulu
echo "<h2>2. Test modulu</h2>";
$module = Module::getInstanceByName('katalogy');
if ($module) {
    echo "✅ Modul katalogy načten<br>";
    echo "Aktivní: " . ($module->active ? 'ANO' : 'NE') . "<br>";
    echo "Verze: " . $module->version . "<br>";
    echo "ID: " . $module->id . "<br>";
} else {
    echo "❌ Modul katalogy nenalezen<br>";
}

// 3. Test databáze
echo "<h2>3. Test databáze</h2>";
$table_name = _DB_PREFIX_ . 'katalogy';
$sql = "SHOW TABLES LIKE '$table_name'";
$result = Db::getInstance()->executeS($sql);

if ($result) {
    echo "✅ Tabulka $table_name existuje<br>";
    
    $count_sql = "SELECT COUNT(*) as count FROM `$table_name`";
    $count_result = Db::getInstance()->getRow($count_sql);
    echo "Celkem katalogů: " . $count_result['count'] . "<br>";
    
    $active_sql = "SELECT COUNT(*) as count FROM `$table_name` WHERE active = 1";
    $active_result = Db::getInstance()->getRow($active_sql);
    echo "Aktivních katalogů: " . $active_result['count'] . "<br>";
    
    if ($active_result['count'] > 0) {
        echo "<h3>Seznam aktivních katalogů:</h3>";
        $list_sql = "SELECT id_katalog, title, active, is_new FROM `$table_name` WHERE active = 1 ORDER BY position ASC";
        $catalogs = Db::getInstance()->executeS($list_sql);
        foreach ($catalogs as $catalog) {
            echo "- ID: {$catalog['id_katalog']}, Název: {$catalog['title']}<br>";
        }
    }
} else {
    echo "❌ Tabulka $table_name neexistuje<br>";
}

// 4. Test hooks
echo "<h2>4. Test hooks</h2>";
$hooks_to_check = ['displayKatalogyContent', 'displayKatalogySimple', 'displayCMSContent'];
foreach ($hooks_to_check as $hook_name) {
    $hook_id = Hook::getIdByName($hook_name);
    if ($hook_id) {
        echo "✅ Hook '$hook_name' existuje (ID: $hook_id)<br>";
        
        // Test registrace
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "hook_module` WHERE id_hook = $hook_id AND id_module = " . (int)$module->id;
        $hook_module = Db::getInstance()->getRow($sql);
        if ($hook_module) {
            echo "  ✅ Modul zaregistrován<br>";
        } else {
            echo "  ❌ Modul NENÍ zaregistrován<br>";
        }
    } else {
        echo "❌ Hook '$hook_name' neexistuje<br>";
    }
}

// 5. Test hook výstupu
echo "<h2>5. Test hook výstupu</h2>";
if ($module && $module->active) {
    echo "<h3>displayKatalogyContent:</h3>";
    try {
        $content = Hook::exec('displayKatalogyContent');
        if ($content) {
            echo "✅ Hook vrátil obsah (" . strlen($content) . " znaků)<br>";
            echo "<div style='border: 2px solid green; padding: 10px; max-height: 200px; overflow: auto;'>";
            echo htmlspecialchars(substr($content, 0, 500)) . (strlen($content) > 500 ? '...' : '');
            echo "</div>";
        } else {
            echo "❌ Hook nevrátil obsah<br>";
        }
    } catch (Exception $e) {
        echo "❌ Chyba: " . $e->getMessage() . "<br>";
    }
}

// 6. Konfigurace
echo "<h2>6. Konfigurace</h2>";
$email = Configuration::get('KATALOGY_EMAIL');
$cms_id = Configuration::get('KATALOGY_CMS_ID');
echo "KATALOGY_EMAIL: " . ($email ?: 'NENÍ NASTAVENO') . "<br>";
echo "KATALOGY_CMS_ID: " . ($cms_id ?: 'NENÍ NASTAVENO') . "<br>";

// 7. Soubory modulu
echo "<h2>7. Soubory modulu</h2>";
$module_files = [
    'katalogy.php' => _PS_MODULE_DIR_ . 'katalogy/katalogy.php',
    'Katalog.php' => _PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php',
    'katalogy.css' => _PS_MODULE_DIR_ . 'katalogy/views/css/katalogy.css',
    'katalogy.js' => _PS_MODULE_DIR_ . 'katalogy/views/js/katalogy.js',
    'katalogy_content.tpl' => _PS_MODULE_DIR_ . 'katalogy/views/templates/front/katalogy_content.tpl'
];

foreach ($module_files as $name => $path) {
    if (file_exists($path)) {
        echo "✅ $name existuje<br>";
    } else {
        echo "❌ $name neexistuje: $path<br>";
    }
}

echo "<h2>Závěr</h2>";
echo "Debug dokončen v " . date('Y-m-d H:i:s') . "<br>";
echo "<p><strong>Akce k provedení:</strong></p>";
if (!$module || !$module->active) {
    echo "1. Nainstalujte a aktivujte modul katalogy<br>";
}
if (empty($result)) {
    echo "2. Vytvořte databázovou tabulku<br>";
}
if ($active_result['count'] == 0) {
    echo "3. Přidejte testovací katalogy<br>";
}
echo "<p>Poté použijte v CMS: <code>{hook h='displayKatalogyContent'}</code></p>";
?>
