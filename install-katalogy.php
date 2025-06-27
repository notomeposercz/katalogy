<?php
/**
 * Instalace/reinstalace modulu katalogy
 * Umístit do root adresáře PrestaShop
 */

require_once(dirname(__FILE__).'/config/config.inc.php');

echo "<h1>INSTALACE MODULU KATALOGY</h1>";

// 1. Zkontroluj existenci modulu
echo "<h2>1. Kontrola modulu</h2>";
$module_dir = _PS_MODULE_DIR_ . 'katalogy/';
if (!is_dir($module_dir)) {
    die("❌ Adresář modulu neexistuje: $module_dir<br>Nahrajte soubory modulu přes FTP.");
}
echo "✅ Adresář modulu existuje<br>";

$main_file = $module_dir . 'katalogy.php';
if (!file_exists($main_file)) {
    die("❌ Hlavní soubor modulu neexistuje: $main_file");
}
echo "✅ Hlavní soubor modulu existuje<br>";

// 2. Načti modul
echo "<h2>2. Načítání modulu</h2>";
require_once($main_file);

if (!class_exists('Katalogy')) {
    die("❌ Třída Katalogy se nepodařila načíst");
}
echo "✅ Třída Katalogy načtena<br>";

// 3. Zkontroluj instalaci
echo "<h2>3. Kontrola instalace</h2>";
$module = Module::getInstanceByName('katalogy');
if ($module) {
    echo "✅ Modul instance existuje<br>";
    echo "Aktivní: " . ($module->active ? 'ANO' : 'NE') . "<br>";
    echo "Nainstalovaný: " . (Module::isInstalled('katalogy') ? 'ANO' : 'NE') . "<br>";
} else {
    echo "❌ Modul instance neexistuje<br>";
}

// 4. Reinstalace pokud je potřeba
echo "<h2>4. Reinstalace</h2>";
if (isset($_GET['reinstall']) && $_GET['reinstall'] == '1') {
    echo "Spouštím reinstalaci...<br>";
    
    // Odinstalace
    if ($module && Module::isInstalled('katalogy')) {
        echo "Odinstalovávám modul...<br>";
        if ($module->uninstall()) {
            echo "✅ Modul odinstalován<br>";
        } else {
            echo "❌ Chyba při odinstalaci<br>";
        }
    }
    
    // Nová instance
    $module = new Katalogy();
    
    // Instalace
    echo "Instaluji modul...<br>";
    if ($module->install()) {
        echo "✅ Modul nainstalován<br>";
    } else {
        echo "❌ Chyba při instalaci<br>";
        echo "Chyby: " . implode('<br>', $module->_errors) . "<br>";
    }
    
    // Aktivace
    if (!$module->active) {
        echo "Aktivuji modul...<br>";
        if ($module->enable()) {
            echo "✅ Modul aktivován<br>";
        } else {
            echo "❌ Chyba při aktivaci<br>";
        }
    }
} else {
    if (!$module || !$module->active || !Module::isInstalled('katalogy')) {
        echo "<p><strong>Modul vyžaduje reinstalaci.</strong></p>";
        echo "<a href='?reinstall=1' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>REINSTALOVAT MODUL</a>";
    } else {
        echo "✅ Modul je správně nainstalován a aktivní<br>";
    }
}

// 5. Test po instalaci
if (isset($_GET['reinstall']) || ($module && $module->active)) {
    echo "<h2>5. Test po instalaci</h2>";
    
    // Test databáze
    $table_name = _DB_PREFIX_ . 'katalogy';
    $sql = "SHOW TABLES LIKE '$table_name'";
    $result = Db::getInstance()->executeS($sql);
    
    if ($result) {
        echo "✅ Databázová tabulka existuje<br>";
    } else {
        echo "❌ Databázová tabulka neexistuje<br>";
    }
    
    // Test hooks
    $hooks = ['displayKatalogyContent', 'displayCMSContent', 'displayHeader'];
    foreach ($hooks as $hook_name) {
        $hook_id = Hook::getIdByName($hook_name);
        if ($hook_id) {
            $sql = "SELECT * FROM `" . _DB_PREFIX_ . "hook_module` WHERE id_hook = $hook_id AND id_module = " . (int)$module->id;
            $hook_module = Db::getInstance()->getRow($sql);
            if ($hook_module) {
                echo "✅ Hook '$hook_name' zaregistrován<br>";
            } else {
                echo "❌ Hook '$hook_name' není zaregistrován<br>";
            }
        } else {
            echo "❌ Hook '$hook_name' neexistuje<br>";
        }
    }
    
    // Test konfigurace
    $email = Configuration::get('KATALOGY_EMAIL');
    if ($email) {
        echo "✅ Email konfigurace nastavena: $email<br>";
    } else {
        echo "❌ Email konfigurace není nastavena<br>";
    }
}

echo "<h2>Závěr</h2>";
echo "<p>Po úspěšné instalaci:</p>";
echo "<ol>";
echo "<li>Přidejte testovací katalogy: <a href='modules/katalogy/add-test-catalogs.php'>add-test-catalogs.php</a></li>";
echo "<li>Vložte do CMS stránky: <code>{hook h='displayKatalogyContent'}</code></li>";
echo "<li>Otestujte: <a href='modules/katalogy/quick-test.php'>quick-test.php</a></li>";
echo "</ol>";
?>
