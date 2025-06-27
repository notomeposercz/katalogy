<?php
/**
 * Najít hooks, které skutečně fungují na CMS stránce
 * Umístit do root adresáře PrestaShop
 */

require_once(dirname(__FILE__).'/config/config.inc.php');

echo "<h1>HLEDÁNÍ FUNKČNÍCH HOOKS NA CMS STRÁNCE</h1>";

// Vytvoř testovací modul pro detekci hooks
$test_module_content = '<?php
class TestHookDetector extends Module
{
    public function __construct()
    {
        $this->name = "testhookdetector";
        $this->version = "1.0.0";
        parent::__construct();
    }
    
    public function install()
    {
        return parent::install() && $this->registerHook("displayCMSContent");
    }
    
    public function hookDisplayCMSContent($params)
    {
        return "TEST HOOK WORKS!";
    }
}';

// Zkontroluj, které hooks se spouštějí na CMS stránce
echo "<h2>Test hooks na CMS stránce ID 23</h2>";

// Simulace CMS stránky
$cms_id = 23;
$cms = new CMS($cms_id, Context::getContext()->language->id);

if (!Validate::isLoadedObject($cms)) {
    die("❌ CMS stránka nenalezena");
}

echo "✅ CMS stránka načtena: " . $cms->meta_title . "<br><br>";

// Seznam všech možných hooks na CMS stránce
$possible_hooks = [
    'displayCMSContent',
    'displayCMSDisputeInformation', 
    'displayCMSPrintButton',
    'displayRightColumn',
    'displayLeftColumn',
    'displayTop',
    'displayHeader',
    'displayFooter',
    'displayBeforeBodyClosingTag',
    'displayAfterBodyOpeningTag',
    'actionFrontControllerSetMedia',
    'actionCMSPageDisplayed'
];

echo "<h2>Test všech možných hooks:</h2>";

foreach ($possible_hooks as $hook_name) {
    echo "<h3>$hook_name</h3>";
    
    $hook_id = Hook::getIdByName($hook_name);
    if ($hook_id) {
        echo "✅ Hook existuje (ID: $hook_id)<br>";
        
        // Zkontroluj moduly na tomto hook
        $sql = "SELECT m.name FROM `" . _DB_PREFIX_ . "hook_module` hm 
                LEFT JOIN `" . _DB_PREFIX_ . "module` m ON hm.id_module = m.id_module
                WHERE hm.id_hook = $hook_id AND m.active = 1";
        $modules = Db::getInstance()->executeS($sql);
        
        if ($modules) {
            echo "Moduly: ";
            foreach ($modules as $module) {
                echo $module['name'] . " ";
            }
            echo "<br>";
        }
        
        // Test výstupu
        try {
            $output = Hook::exec($hook_name, ['cms' => $cms]);
            if ($output && trim($output)) {
                echo "✅ <strong>HOOK FUNGUJE!</strong> Vrátil: " . strlen($output) . " znaků<br>";
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 5px 0;'>";
                echo "Výstup: " . htmlspecialchars(substr($output, 0, 200)) . (strlen($output) > 200 ? '...' : '');
                echo "</div>";
            } else {
                echo "❌ Hook nevrátil obsah<br>";
            }
        } catch (Exception $e) {
            echo "❌ Chyba: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Hook neexistuje<br>";
    }
    echo "<hr>";
}

// Alternativní řešení - registrace na jiný hook
echo "<h2>Doporučení</h2>";
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px;'>";
echo "<h3>Možná řešení:</h3>";
echo "<p><strong>1. Použít hook displayRightColumn nebo displayLeftColumn</strong><br>";
echo "Tyto hooks se obvykle spouštějí na CMS stránkách.</p>";

echo "<p><strong>2. Použít vlastní shortcode systém</strong><br>";
echo "Vytvořit vlastní zpracování [katalogy] v obsahu CMS.</p>";

echo "<p><strong>3. Použít hook displayTop</strong><br>";
echo "Zobrazit katalogy v horní části stránky.</p>";
echo "</div>";

// Test modulu katalogy na jiných hooks
echo "<h2>Test modulu katalogy na alternativních hooks</h2>";
$module = Module::getInstanceByName('katalogy');
if ($module && $module->active) {
    $alternative_hooks = ['displayRightColumn', 'displayLeftColumn', 'displayTop'];
    
    foreach ($alternative_hooks as $hook_name) {
        echo "<h3>Test $hook_name</h3>";
        
        // Zaregistruj modul na tento hook
        if (!$module->isRegisteredInHook($hook_name)) {
            if ($module->registerHook($hook_name)) {
                echo "✅ Modul zaregistrován na $hook_name<br>";
            } else {
                echo "❌ Nepodařilo se zaregistrovat na $hook_name<br>";
                continue;
            }
        } else {
            echo "✅ Modul už je zaregistrován na $hook_name<br>";
        }
        
        // Test výstupu
        $method_name = 'hook' . $hook_name;
        if (method_exists($module, $method_name)) {
            echo "✅ Metoda $method_name existuje<br>";
        } else {
            echo "❌ Metoda $method_name neexistuje - je potřeba přidat do modulu<br>";
        }
    }
}

echo "<h2>Závěr</h2>";
echo "<p>Zkontrolujte výše uvedené výsledky a použijte hook, který skutečně funguje.</p>";
?>
