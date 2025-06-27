<?php
/**
 * DEBUG VERSION 2 - Katalogy Module Test
 */

echo "<h1>DEBUG KATALOGY v2 - " . date('Y-m-d H:i:s') . "</h1>";

// 1. Test základního načtení PrestaShop
echo "<h2>1. Test PrestaShop načtení</h2>";

// Zkusíme najít config v různých možných umístěních
$possible_configs = [
    dirname(__FILE__).'/../../config/config.inc.php',  // z modules/katalogy/
    dirname(__FILE__).'/../../../config/config.inc.php', // pokud je ještě hlouběji
    dirname(__FILE__).'/config/config.inc.php',        // původní cesta
    '/www/doc/czimg-dev1.www2.peterman.cz/www/config/config.inc.php' // absolutní cesta
];

$config_file = null;
foreach ($possible_configs as $path) {
    echo "Zkouším: $path<br>";
    if (file_exists($path)) {
        $config_file = $path;
        echo "✅ Config file nalezen: $config_file<br>";
        break;
    }
}

if ($config_file) {
    require_once($config_file);
    echo "✅ PrestaShop config načten<br>";
    echo "DB Prefix: " . _DB_PREFIX_ . "<br>";
    echo "Module Dir: " . _PS_MODULE_DIR_ . "<br>";
} else {
    echo "❌ Config file nenalezen v žádné z cest:<br>";
    foreach ($possible_configs as $path) {
        echo "- $path<br>";
    }
    echo "<br>Aktuální adresář: " . dirname(__FILE__) . "<br>";
    echo "Obsah aktuálního adresáře:<br>";
    $files = scandir(dirname(__FILE__));
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file<br>";
        }
    }
    die("❌ Nelze pokračovat bez config souboru");
}

// 2. Test existence modulu
echo "<h2>2. Test existence modulu</h2>";
$module_dir = _PS_MODULE_DIR_ . 'katalogy/';
echo "Module dir: $module_dir<br>";
if (is_dir($module_dir)) {
    echo "✅ Module adresář existuje<br>";
    
    $main_file = $module_dir . 'katalogy.php';
    if (file_exists($main_file)) {
        echo "✅ Hlavní soubor modulu existuje<br>";
    } else {
        echo "❌ Hlavní soubor modulu neexistuje<br>";
    }
    
    $class_file = $module_dir . 'classes/Katalog.php';
    if (file_exists($class_file)) {
        echo "✅ Katalog class existuje<br>";
    } else {
        echo "❌ Katalog class neexistuje<br>";
    }
} else {
    echo "❌ Module adresář neexistuje<br>";
}

// 3. Test instalace modulu
echo "<h2>3. Test instalace modulu</h2>";
if (class_exists('Module')) {
    $module = Module::getInstanceByName('katalogy');
    if ($module) {
        echo "✅ Modul instance načtena<br>";
        echo "Modul aktivní: " . ($module->active ? 'ANO' : 'NE') . "<br>";
        echo "Verze: " . $module->version . "<br>";
    } else {
        echo "❌ Modul instance se nepodařilo načíst<br>";
    }
} else {
    echo "❌ Module class neexistuje<br>";
}

// 4. Test databázové tabulky
echo "<h2>4. Test databáze</h2>";
if (class_exists('Db')) {
    $table_name = _DB_PREFIX_ . 'katalogy';
    $sql = "SHOW TABLES LIKE '$table_name'";
    $result = Db::getInstance()->executeS($sql);
    
    if ($result) {
        echo "✅ Tabulka $table_name existuje<br>";
        
        // Počet záznamů
        $count_sql = "SELECT COUNT(*) as count FROM `$table_name`";
        $count_result = Db::getInstance()->getRow($count_sql);
        echo "Počet katalogů v DB: " . $count_result['count'] . "<br>";
        
        // Aktivní katalogy
        $active_sql = "SELECT COUNT(*) as count FROM `$table_name` WHERE active = 1";
        $active_result = Db::getInstance()->getRow($active_sql);
        echo "Počet aktivních katalogů: " . $active_result['count'] . "<br>";
        
        // Seznam katalogů
        if ($active_result['count'] > 0) {
            echo "<h3>Seznam aktivních katalogů:</h3>";
            $list_sql = "SELECT id_katalog, title, active, is_new FROM `$table_name` WHERE active = 1 ORDER BY position ASC";
            $catalogs = Db::getInstance()->executeS($list_sql);
            foreach ($catalogs as $catalog) {
                echo "- ID: {$catalog['id_katalog']}, Název: {$catalog['title']}, Nový: " . ($catalog['is_new'] ? 'ANO' : 'NE') . "<br>";
            }
        }
    } else {
        echo "❌ Tabulka $table_name neexistuje<br>";
    }
} else {
    echo "❌ Db class neexistuje<br>";
}

// 5. Test konfigurace
echo "<h2>5. Test konfigurace</h2>";
if (class_exists('Configuration')) {
    $email = Configuration::get('KATALOGY_EMAIL');
    $cms_id = Configuration::get('KATALOGY_CMS_ID');
    
    echo "KATALOGY_EMAIL: " . ($email ?: 'NENÍ NASTAVENO') . "<br>";
    echo "KATALOGY_CMS_ID: " . ($cms_id ?: 'NENÍ NASTAVENO') . "<br>";
    
    if ($cms_id > 0) {
        if (class_exists('CMS')) {
            $cms = new CMS($cms_id);
            if (Validate::isLoadedObject($cms)) {
                echo "CMS stránka: " . $cms->meta_title . "<br>";
                echo "CMS link_rewrite: " . $cms->link_rewrite . "<br>";
            } else {
                echo "❌ CMS stránka s ID $cms_id neexistuje<br>";
            }
        }
    }
} else {
    echo "❌ Configuration class neexistuje<br>";
}

// 6. Test hook registrace
echo "<h2>6. Test hook registrace</h2>";
if (class_exists('Hook')) {
    $hooks_to_check = ['displayKatalogyContent', 'displayCMSContent', 'displayHeader'];
    foreach ($hooks_to_check as $hook_name) {
        $hook_id = Hook::getIdByName($hook_name);
        if ($hook_id) {
            echo "✅ Hook '$hook_name' existuje (ID: $hook_id)<br>";
            
            // Zkontroluj, jestli je modul zaregistrován na tento hook
            $sql = "SELECT * FROM `" . _DB_PREFIX_ . "hook_module` WHERE id_hook = $hook_id AND id_module = (SELECT id_module FROM `" . _DB_PREFIX_ . "module` WHERE name = 'katalogy')";
            $hook_module = Db::getInstance()->getRow($sql);
            if ($hook_module) {
                echo "  ✅ Modul je zaregistrován na hook '$hook_name'<br>";
            } else {
                echo "  ❌ Modul NENÍ zaregistrován na hook '$hook_name'<br>";
            }
        } else {
            echo "❌ Hook '$hook_name' neexistuje<br>";
        }
    }
}

// 7. Test Katalog class
echo "<h2>7. Test Katalog class</h2>";
$katalog_file = _PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php';
if (file_exists($katalog_file)) {
    echo "✅ Katalog.php existuje<br>";
    require_once($katalog_file);
    
    if (class_exists('Katalog')) {
        echo "✅ Katalog class načtena<br>";
        
        try {
            $catalogs = Katalog::getAllActive();
            echo "✅ Katalog::getAllActive() funguje<br>";
            echo "Počet katalogů z class: " . count($catalogs) . "<br>";
            
            if (!empty($catalogs)) {
                echo "<h3>Katalogy z Katalog class:</h3>";
                foreach ($catalogs as $catalog) {
                    echo "- ID: {$catalog['id_katalog']}, Název: {$catalog['title']}<br>";
                    
                    // Test objektu
                    $katalog_obj = new Katalog($catalog['id_katalog']);
                    echo "  Download URL: " . ($katalog_obj->getDownloadUrl() ?: 'ŽÁDNÁ') . "<br>";
                    echo "  Image URL: " . ($katalog_obj->getImageUrl() ?: 'ŽÁDNÁ') . "<br>";
                    echo "  Has download: " . ($katalog_obj->hasDownload() ? 'ANO' : 'NE') . "<br>";
                }
            }
        } catch (Exception $e) {
            echo "❌ Chyba při volání Katalog::getAllActive(): " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Katalog class se nepodařilo načíst<br>";
    }
} else {
    echo "❌ Katalog.php nenalezen<br>";
}

// 8. Test template souborů
echo "<h2>8. Test template souborů</h2>";
$templates = [
    'katalogy_content.tpl' => _PS_MODULE_DIR_ . 'katalogy/views/templates/front/katalogy_content.tpl',
    'katalogy_simple.tpl' => _PS_MODULE_DIR_ . 'katalogy/views/templates/front/katalogy_simple.tpl'
];

foreach ($templates as $name => $path) {
    if (file_exists($path)) {
        echo "✅ Template '$name' existuje<br>";
    } else {
        echo "❌ Template '$name' neexistuje: $path<br>";
    }
}

// 9. Test CSS a JS
echo "<h2>9. Test CSS a JS</h2>";
$assets = [
    'CSS' => _PS_MODULE_DIR_ . 'katalogy/views/css/katalogy.css',
    'JS' => _PS_MODULE_DIR_ . 'katalogy/views/js/katalogy.js'
];

foreach ($assets as $type => $path) {
    if (file_exists($path)) {
        echo "✅ $type soubor existuje<br>";
    } else {
        echo "❌ $type soubor neexistuje: $path<br>";
    }
}

echo "<h2>10. Závěr</h2>";
echo "<p>Debug dokončen v " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Pro zobrazení katalogů použijte:</strong></p>";
echo "<p>Kompletní obsah: <code>{hook h='displayKatalogyContent'}</code></p>";
echo "<p>Pouze katalogy: <code>{hook h='displayKatalogySimple'}</code></p>";
?>
