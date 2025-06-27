<?php
/**
 * Fix Hooks - oprava registrace hooks pro modul katalogy
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

echo "<h1>FIX HOOKS - Katalogy Module</h1>";

// Načtení modulu
$module = Module::getInstanceByName('katalogy');
if (!$module) {
    die("❌ Modul katalogy nenalezen");
}

echo "✅ Modul katalogy načten<br>";
echo "Aktivní: " . ($module->active ? 'ANO' : 'NE') . "<br>";

// Seznam hooks k registraci
$hooks_to_register = [
    'displayHeader',
    'actionFrontControllerSetMedia', 
    'displayKatalogyContent',
    'displayKatalogySimple',
    'displayCMSContent'
];

echo "<h2>Registrace hooks</h2>";

foreach ($hooks_to_register as $hook_name) {
    echo "<h3>Hook: $hook_name</h3>";
    
    // Zkontroluj, jestli hook existuje
    $hook_id = Hook::getIdByName($hook_name);
    if (!$hook_id) {
        echo "❌ Hook '$hook_name' neexistuje, vytvářím...<br>";
        $hook = new Hook();
        $hook->name = $hook_name;
        $hook->title = $hook_name;
        $hook->description = 'Hook for ' . $hook_name;
        if ($hook->add()) {
            echo "✅ Hook '$hook_name' vytvořen<br>";
            $hook_id = $hook->id;
        } else {
            echo "❌ Nepodařilo se vytvořit hook '$hook_name'<br>";
            continue;
        }
    } else {
        echo "✅ Hook '$hook_name' existuje (ID: $hook_id)<br>";
    }
    
    // Zkontroluj registraci modulu na hook
    $sql = "SELECT * FROM `" . _DB_PREFIX_ . "hook_module` 
            WHERE id_hook = $hook_id 
            AND id_module = " . (int)$module->id;
    $existing = Db::getInstance()->getRow($sql);
    
    if ($existing) {
        echo "✅ Modul už je zaregistrován na hook '$hook_name'<br>";
    } else {
        echo "❌ Modul není zaregistrován na hook '$hook_name', registruji...<br>";
        
        if ($module->registerHook($hook_name)) {
            echo "✅ Modul úspěšně zaregistrován na hook '$hook_name'<br>";
        } else {
            echo "❌ Nepodařilo se zaregistrovat modul na hook '$hook_name'<br>";
        }
    }
}

// Test konfigurace
echo "<h2>Konfigurace</h2>";
$email = Configuration::get('KATALOGY_EMAIL');
$cms_id = Configuration::get('KATALOGY_CMS_ID');

echo "KATALOGY_EMAIL: " . ($email ?: 'NENÍ NASTAVENO') . "<br>";
echo "KATALOGY_CMS_ID: " . ($cms_id ?: 'NENÍ NASTAVENO') . "<br>";

if (!$email) {
    $default_email = Configuration::get('PS_SHOP_EMAIL');
    if ($default_email) {
        Configuration::updateValue('KATALOGY_EMAIL', $default_email);
        echo "✅ Nastaven výchozí email: $default_email<br>";
    }
}

// Test databáze
echo "<h2>Test databáze</h2>";
$table_name = _DB_PREFIX_ . 'katalogy';
$sql = "SHOW TABLES LIKE '$table_name'";
$result = Db::getInstance()->executeS($sql);

if ($result) {
    echo "✅ Tabulka $table_name existuje<br>";
    
    $count_sql = "SELECT COUNT(*) as count FROM `$table_name` WHERE active = 1";
    $count_result = Db::getInstance()->getRow($count_sql);
    echo "Počet aktivních katalogů: " . $count_result['count'] . "<br>";
} else {
    echo "❌ Tabulka $table_name neexistuje<br>";
    echo "Vytvářím tabulku...<br>";
    
    $create_sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'katalogy` (
        `id_katalog` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `description` text,
        `image` varchar(255),
        `file_url` varchar(500),
        `file_path` varchar(500),
        `is_new` tinyint(1) DEFAULT 0,
        `position` int(11) DEFAULT 0,
        `active` tinyint(1) DEFAULT 1,
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY (`id_katalog`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
    
    if (Db::getInstance()->execute($create_sql)) {
        echo "✅ Tabulka vytvořena<br>";
    } else {
        echo "❌ Nepodařilo se vytvořit tabulku<br>";
    }
}

echo "<h2>Závěr</h2>";
echo "✅ Fix hooks dokončen<br>";
echo "<p>Nyní zkuste znovu použít hook v CMS stránce:</p>";
echo "<code>{hook h='displayKatalogyContent'}</code><br>";
echo "<p>Nebo pro pouze katalogy:</p>";
echo "<code>{hook h='displayKatalogySimple'}</code><br>";
?>
