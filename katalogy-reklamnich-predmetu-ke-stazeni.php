<?php
/**
 * DEBUG VERSION - Katalogy Page
 */

echo "<h1>DEBUG - Katalogy</h1>";

// 1. Test základního načtení
echo "<h2>1. Test základního načtení</h2>";
echo "✅ PHP funguje<br>";
echo "Server: " . $_SERVER['HTTP_HOST'] . "<br>";
echo "Script: " . $_SERVER['SCRIPT_NAME'] . "<br>";

// 2. Test PrestaShop config
echo "<h2>2. Test PrestaShop config</h2>";
$config_file = dirname(__FILE__).'/config/config.inc.php';
if (file_exists($config_file)) {
    echo "✅ Config file existuje: $config_file<br>";
    require_once($config_file);
    echo "✅ Config načten<br>";
} else {
    die("❌ Config file nenalezen: $config_file");
}

// 3. Test init
echo "<h2>3. Test init</h2>";
$init_file = dirname(__FILE__).'/init.php';
if (file_exists($init_file)) {
    echo "✅ Init file existuje<br>";
    require_once($init_file);
    echo "✅ Init načten<br>";
} else {
    die("❌ Init file nenalezen: $init_file");
}

// 4. Test modulu
echo "<h2>4. Test modulu</h2>";
if (class_exists('Module')) {
    echo "✅ Module class existuje<br>";
    $is_installed = Module::isInstalled('katalogy');
    echo $is_installed ? "✅ Modul je nainstalován<br>" : "❌ Modul NENÍ nainstalován<br>";
    
    if ($is_installed) {
        $module = Module::getInstanceByName('katalogy');
        if ($module) {
            echo "✅ Module instance získána<br>";
            echo "Verze: " . $module->version . "<br>";
        } else {
            echo "❌ Nepodařilo se získat module instance<br>";
        }
    }
} else {
    echo "❌ Module class neexistuje<br>";
}

// 5. Test Katalog třídy
echo "<h2>5. Test Katalog třídy</h2>";
$katalog_file = _PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php';
echo "Hledám: $katalog_file<br>";

if (file_exists($katalog_file)) {
    echo "✅ Katalog.php existuje<br>";
    require_once($katalog_file);
    
    if (class_exists('Katalog')) {
        echo "✅ Katalog class načtena<br>";
        
        // Test database
        try {
            $catalogs = Katalog::getAllActive();
            echo "✅ Database funguje<br>";
            echo "Počet katalogů: " . count($catalogs) . "<br>";
            
            if (!empty($catalogs)) {
                echo "<h3>Nalezené katalogy:</h3>";
                foreach ($catalogs as $catalog) {
                    echo "- ID: {$catalog['id_katalog']}, Název: {$catalog['title']}<br>";
                }
            }
        } catch (Exception $e) {
            echo "❌ Chyba database: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Katalog class se nepodařilo načíst<br>";
    }
} else {
    echo "❌ Katalog.php nenalezen<br>";
    echo "Module dir: " . _PS_MODULE_DIR_ . "<br>";
}

// 6. Test template
echo "<h2>6. Test template</h2>";
$template_file = _PS_MODULE_DIR_ . 'katalogy/views/templates/front/standalone.tpl';
echo "Hledám template: $template_file<br>";
if (file_exists($template_file)) {
    echo "✅ Template existuje<br>";
} else {
    echo "❌ Template nenalezen<br>";
}

// 7. Test konfigurace
echo "<h2>7. Test konfigurace</h2>";
if (class_exists('Configuration')) {
    $email = Configuration::get('KATALOGY_EMAIL');
    echo "Email konfigurace: " . ($email ?: 'NENÍ NASTAVENO') . "<br>";
    
    $shop_name = Configuration::get('PS_SHOP_NAME');
    echo "Název shopu: " . $shop_name . "<br>";
} else {
    echo "❌ Configuration class neexistuje<br>";
}

echo "<h2>8. Informace o prostředí</h2>";
echo "PHP verze: " . PHP_VERSION . "<br>";
echo "PrestaShop verze: " . (defined('_PS_VERSION_') ? _PS_VERSION_ : 'UNKNOWN') . "<br>";
echo "Current working directory: " . getcwd() . "<br>";

// 9. Test oprávnění
echo "<h2>9. Test oprávnění adresářů</h2>";
$dirs_to_check = [
    _PS_MODULE_DIR_ . 'katalogy/',
    _PS_MODULE_DIR_ . 'katalogy/views/img/katalogy/',
    _PS_MODULE_DIR_ . 'katalogy/files/'
];

foreach ($dirs_to_check as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? '✅ Writable' : '❌ Not writable';
        echo "$dir - $writable<br>";
    } else {
        echo "$dir - ❌ Neexistuje<br>";
    }
}

?>