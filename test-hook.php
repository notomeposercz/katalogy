<?php
/**
 * Test Hook - pro testování hook funkcí
 */

// Načtení PrestaShop
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

echo "<h1>TEST HOOK - " . date('Y-m-d H:i:s') . "</h1>";

// Test Context
echo "<h2>Context Test</h2>";
if (class_exists('Context')) {
    $context = Context::getContext();
    echo "✅ Context načten<br>";
    echo "Controller: " . (isset($context->controller) ? get_class($context->controller) : 'NENÍ') . "<br>";
    echo "Language ID: " . (isset($context->language) ? $context->language->id : 'NENÍ') . "<br>";
} else {
    echo "❌ Context class neexistuje<br>";
}

// Test Hook class
echo "<h2>Hook Test</h2>";
if (class_exists('Hook')) {
    echo "✅ Hook class existuje<br>";
    
    // Test displayKatalogyContent hook
    echo "<h3>Test displayKatalogyContent hook</h3>";
    try {
        $hook_result = Hook::exec('displayKatalogyContent');
        if ($hook_result) {
            echo "✅ Hook displayKatalogyContent vrátil obsah:<br>";
            echo "<div style='border: 2px solid green; padding: 10px; margin: 10px 0;'>";
            echo $hook_result;
            echo "</div>";
        } else {
            echo "❌ Hook displayKatalogyContent nevrátil žádný obsah<br>";
        }
    } catch (Exception $e) {
        echo "❌ Chyba při volání hook: " . $e->getMessage() . "<br>";
    }
    
    // Test displayKatalogySimple hook
    echo "<h3>Test displayKatalogySimple hook</h3>";
    try {
        $hook_result = Hook::exec('displayKatalogySimple');
        if ($hook_result) {
            echo "✅ Hook displayKatalogySimple vrátil obsah:<br>";
            echo "<div style='border: 2px solid blue; padding: 10px; margin: 10px 0;'>";
            echo $hook_result;
            echo "</div>";
        } else {
            echo "❌ Hook displayKatalogySimple nevrátil žádný obsah<br>";
        }
    } catch (Exception $e) {
        echo "❌ Chyba při volání hook: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Hook class neexistuje<br>";
}

// Test modulu přímo
echo "<h2>Test modulu přímo</h2>";
if (class_exists('Module')) {
    $module = Module::getInstanceByName('katalogy');
    if ($module && $module->active) {
        echo "✅ Modul katalogy je aktivní<br>";
        
        // Test renderKatalogyContent metody
        if (method_exists($module, 'hookDisplayKatalogyContent')) {
            echo "✅ Metoda hookDisplayKatalogyContent existuje<br>";
            try {
                $content = $module->hookDisplayKatalogyContent([]);
                if ($content) {
                    echo "✅ hookDisplayKatalogyContent vrátila obsah:<br>";
                    echo "<div style='border: 2px solid orange; padding: 10px; margin: 10px 0;'>";
                    echo $content;
                    echo "</div>";
                } else {
                    echo "❌ hookDisplayKatalogyContent nevrátila obsah<br>";
                }
            } catch (Exception $e) {
                echo "❌ Chyba při volání hookDisplayKatalogyContent: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "❌ Metoda hookDisplayKatalogyContent neexistuje<br>";
        }
    } else {
        echo "❌ Modul katalogy není aktivní nebo neexistuje<br>";
    }
}

echo "<hr>";
echo "<p>Test dokončen v " . date('Y-m-d H:i:s') . "</p>";
?>
