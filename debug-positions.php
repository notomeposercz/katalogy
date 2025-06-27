<?php
/**
 * Debug AJAX pozic - pro zachycení AJAX požadavků
 * Umístit do /modules/katalogy/
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config/config.inc.php');

// Nastavení pro zachycení chyb
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

echo "<h1>DEBUG AJAX POZIC KATALOGŮ</h1>";

// Kontrola error logu
echo "<h2>1. Error Log</h2>";
$error_log_file = dirname(__FILE__) . '/../../logs/error.log';
if (file_exists($error_log_file)) {
    $log_content = file_get_contents($error_log_file);
    $lines = explode("\n", $log_content);
    $relevant_lines = array_filter($lines, function($line) {
        return strpos($line, 'AJAX') !== false || strpos($line, 'Position') !== false;
    });
    
    if ($relevant_lines) {
        echo "<h3>Posledních 10 AJAX záznamů:</h3>";
        echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow: auto;'>";
        echo implode("\n", array_slice($relevant_lines, -10));
        echo "</pre>";
    } else {
        echo "<p>Žádné AJAX záznamy v error logu.</p>";
    }
} else {
    echo "<p>Error log nenalezen: $error_log_file</p>";
}

// Test AJAX endpointu
echo "<h2>2. Test AJAX endpointu</h2>";
echo "<div id='ajax-test-results'></div>";

// JavaScript pro test drag & drop
echo "<script>
function testAjaxPositions() {
    const testData = {
        'ajax': '1',
        'action': 'updatePositions',
        'katalogy': {
            '0': 'katalogy_1_14',
            '1': 'katalogy_2_21', 
            '2': 'katalogy_3_16'
        }
    };
    
    const formData = new FormData();
    Object.keys(testData).forEach(key => {
        if (typeof testData[key] === 'object') {
            Object.keys(testData[key]).forEach(subKey => {
                formData.append(key + '[' + subKey + ']', testData[key][subKey]);
            });
        } else {
            formData.append(key, testData[key]);
        }
    });
    
    document.getElementById('ajax-test-results').innerHTML = '<p>Testování AJAX...</p>';
    
    fetch('/admin569bziqe/index.php?controller=AdminKatalogy&token=fff8b47487c1e464a96d3c38c7d29e6b', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('ajax-test-results').innerHTML = 
            '<h3>AJAX Odpověď:</h3><pre style=\"background: #e8f5e8; padding: 10px;\">' + 
            data.substring(0, 500) + '</pre>';
    })
    .catch(error => {
        document.getElementById('ajax-test-results').innerHTML = 
            '<h3>AJAX Chyba:</h3><pre style=\"background: #f5e8e8; padding: 10px;\">' + 
            error.toString() + '</pre>';
    });
}
</script>";

echo "<button onclick='testAjaxPositions()' style='background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer;'>Test AJAX Pozic</button>";

// Aktuální stav pozic
echo "<h2>3. Aktuální pozice</h2>";
$sql = 'SELECT `id_katalog`, `title`, `position` FROM `' . _DB_PREFIX_ . 'katalogy` ORDER BY `position` ASC';
$catalogs = Db::getInstance()->executeS($sql);

if ($catalogs) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Název</th><th>Pozice v DB</th><th>Očekávaná pozice v admin</th></tr>";
    foreach ($catalogs as $catalog) {
        $expected_admin_position = (int)$catalog['position'];
        echo "<tr>";
        echo "<td>" . $catalog['id_katalog'] . "</td>";
        echo "<td>" . htmlspecialchars($catalog['title']) . "</td>";
        echo "<td><strong>" . $catalog['position'] . "</strong></td>";
        echo "<td>" . $expected_admin_position . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Kontrola admin URL
echo "<h2>4. Admin URL kontrola</h2>";
echo "<p>Aktuální admin URL: " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";

// Zkus najít správný admin token
$admin_url = '';
try {
    $context = Context::getContext();
    if (isset($context->link)) {
        $admin_url = $context->link->getAdminLink('AdminKatalogy');
        echo "<p>Správný admin link: <a href='$admin_url' target='_blank'>$admin_url</a></p>";
    }
} catch (Exception $e) {
    echo "<p>Chyba při získávání admin linku: " . $e->getMessage() . "</p>";
}

// Návrh řešení
echo "<h2>5. Možná řešení</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
echo "<h3>Problém: Pozice se v admin zobrazují +1</h3>";
echo "<p><strong>Možné příčiny:</strong></p>";
echo "<ul>";
echo "<li>PrestaShop počítá pozice od 0, ale my ukládáme od 1</li>";
echo "<li>AJAX požadavek se neposílá na správný endpoint</li>";
echo "<li>JavaScript drag & drop neposílá správný formát dat</li>";
echo "</ul>";
echo "<p><strong>Řešení:</strong></p>";
echo "<ol>";
echo "<li>Nahraďte metodu <code>ajaxProcessUpdatePositions()</code> v AdminKatalogyController.php</li>";
echo "<li>Přidejte metodu <code>ajaxProcessMove()</code></li>";
echo "<li>Zkontrolujte error log po drag & drop operaci</li>";
echo "</ol>";
echo "</div>";

// Instrukce
echo "<h2>6. Postup testování</h2>";
echo "<ol>";
echo "<li>Klikněte na 'Test AJAX Pozic' výše</li>";
echo "<li>Zkuste drag & drop v administraci</li>";
echo "<li>Obnovte tuto stránku a zkontrolujte změny</li>";
echo "<li>Zkontrolujte error log pro AJAX zprávy</li>";
echo "</ol>";
?>