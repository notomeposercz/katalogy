<?php
/**
 * Debug CMS hooks - zjistit které hooks se spouštějí na CMS stránce
 * Umístit do root adresáře PrestaShop
 */

require_once(dirname(__FILE__).'/config/config.inc.php');

echo "<h1>DEBUG CMS HOOKS - " . date('Y-m-d H:i:s') . "</h1>";

// Simulace CMS stránky ID 23
$cms_id = 23;

echo "<h2>Informace o CMS stránce ID: $cms_id</h2>";

// Načtení CMS stránky
$cms = new CMS($cms_id, Context::getContext()->language->id);
if (Validate::isLoadedObject($cms)) {
    echo "✅ CMS stránka načtena<br>";
    echo "Název: " . $cms->meta_title . "<br>";
    echo "Link rewrite: " . $cms->link_rewrite . "<br>";
    echo "Aktivní: " . ($cms->active ? 'ANO' : 'NE') . "<br>";
    echo "Obsah: " . substr(strip_tags($cms->content), 0, 200) . "...<br>";
} else {
    echo "❌ CMS stránka nenalezena<br>";
}

// Test všech možných hooks na CMS stránce
echo "<h2>Test hooks na CMS stránce</h2>";

$hooks_to_test = [
    'displayCMSContent',
    'displayKatalogyContent', 
    'displayKatalogySimple',
    'displayHeader',
    'displayTop',
    'displayLeftColumn',
    'displayRightColumn',
    'displayFooter',
    'actionFrontControllerSetMedia'
];

foreach ($hooks_to_test as $hook_name) {
    echo "<h3>Hook: $hook_name</h3>";
    
    $hook_id = Hook::getIdByName($hook_name);
    if ($hook_id) {
        echo "✅ Hook existuje (ID: $hook_id)<br>";
        
        // Zkontroluj moduly zaregistrované na tento hook
        $sql = "SELECT m.name, m.active FROM `" . _DB_PREFIX_ . "hook_module` hm 
                LEFT JOIN `" . _DB_PREFIX_ . "module` m ON hm.id_module = m.id_module
                WHERE hm.id_hook = $hook_id AND m.active = 1
                ORDER BY hm.position";
        $modules = Db::getInstance()->executeS($sql);
        
        if ($modules) {
            echo "Zaregistrované moduly:<br>";
            foreach ($modules as $module) {
                echo "- " . $module['name'] . "<br>";
            }
        } else {
            echo "❌ Žádné aktivní moduly na tomto hook<br>";
        }
        
        // Test výstupu hook
        try {
            $output = Hook::exec($hook_name, ['cms' => $cms]);
            if ($output) {
                echo "✅ Hook vrátil obsah (" . strlen($output) . " znaků)<br>";
                if ($hook_name == 'displayKatalogyContent' || $hook_name == 'displayKatalogySimple') {
                    echo "<div style='border: 2px solid green; padding: 10px; margin: 10px 0; max-height: 300px; overflow: auto;'>";
                    echo htmlspecialchars(substr($output, 0, 1000));
                    echo "</div>";
                }
            } else {
                echo "❌ Hook nevrátil obsah<br>";
            }
        } catch (Exception $e) {
            echo "❌ Chyba při volání hook: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Hook neexistuje<br>";
    }
    echo "<hr>";
}

// Test modulu katalogy specificky
echo "<h2>Test modulu katalogy</h2>";
$module = Module::getInstanceByName('katalogy');
if ($module && $module->active) {
    echo "✅ Modul katalogy je aktivní<br>";
    
    // Simulace context pro CMS stránku
    $old_controller = Context::getContext()->controller;
    
    // Vytvoř mock CMS controller
    Context::getContext()->controller = new stdClass();
    Context::getContext()->controller->php_self = 'cms';
    
    // Nastav $_GET pro simulaci
    $_GET['id_cms'] = $cms_id;
    
    echo "Simulace CMS stránky s ID: $cms_id<br>";
    
    // Test isCatalogPage metody (pokud existuje)
    if (method_exists($module, 'isCatalogPage')) {
        $is_catalog = $module->isCatalogPage($cms_id);
        echo "isCatalogPage($cms_id): " . ($is_catalog ? 'ANO' : 'NE') . "<br>";
    }
    
    // Test hook metod
    if (method_exists($module, 'hookDisplayCMSContent')) {
        echo "Test hookDisplayCMSContent:<br>";
        try {
            $content = $module->hookDisplayCMSContent(['cms' => $cms]);
            if ($content) {
                echo "✅ hookDisplayCMSContent vrátila obsah (" . strlen($content) . " znaků)<br>";
                echo "<div style='border: 2px solid blue; padding: 10px; margin: 10px 0; max-height: 300px; overflow: auto;'>";
                echo htmlspecialchars(substr($content, 0, 1000));
                echo "</div>";
            } else {
                echo "❌ hookDisplayCMSContent nevrátila obsah<br>";
            }
        } catch (Exception $e) {
            echo "❌ Chyba v hookDisplayCMSContent: " . $e->getMessage() . "<br>";
        }
    }
    
    // Obnov původní controller
    Context::getContext()->controller = $old_controller;
    unset($_GET['id_cms']);
} else {
    echo "❌ Modul katalogy není aktivní<br>";
}

// Test konfigurace
echo "<h2>Konfigurace modulu</h2>";
$email = Configuration::get('KATALOGY_EMAIL');
$cms_config_id = Configuration::get('KATALOGY_CMS_ID');
echo "KATALOGY_EMAIL: " . ($email ?: 'NENÍ NASTAVENO') . "<br>";
echo "KATALOGY_CMS_ID: " . ($cms_config_id ?: 'NENÍ NASTAVENO') . "<br>";

if ($cms_config_id != $cms_id) {
    echo "⚠️ Konfigurace CMS ID ($cms_config_id) se neshoduje s testovanou stránkou ($cms_id)<br>";
    echo "<a href='?set_cms_id=1'>Nastavit KATALOGY_CMS_ID na $cms_id</a><br>";
}

if (isset($_GET['set_cms_id']) && $_GET['set_cms_id'] == '1') {
    Configuration::updateValue('KATALOGY_CMS_ID', $cms_id);
    echo "✅ KATALOGY_CMS_ID nastaveno na $cms_id<br>";
}

echo "<h2>Závěr</h2>";
echo "Debug dokončen. Zkontrolujte výše uvedené informace pro identifikaci problému.<br>";
?>
