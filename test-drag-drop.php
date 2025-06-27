<?php
/**
 * Test a diagnostika drag & drop funkcí
 * Umístit do /modules/katalogy/
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config/config.inc.php');
require_once('classes/Katalog.php');

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test Drag & Drop - Katalogy</title>";
echo "<meta charset='utf-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body>";

echo "<div class='container mt-4'>";
echo "<h1>Test Drag & Drop - Katalogy</h1>";

// Test 1: Aktuální pozice
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>1. Aktuální pozice v databázi</h3></div>";
echo "<div class='card-body'>";

$sql = 'SELECT `id_katalog`, `title`, `position` FROM `' . _DB_PREFIX_ . 'katalogy` ORDER BY `position` ASC';
$catalogs = Db::getInstance()->executeS($sql);

if ($catalogs) {
    echo "<table class='table table-striped'>";
    echo "<tr><th>ID</th><th>Název</th><th>Pozice v DB</th><th>Pozice v admin (očekávána)</th></tr>";
    foreach ($catalogs as $catalog) {
        $admin_position = (int)$catalog['position'];
        echo "<tr>";
        echo "<td>" . $catalog['id_katalog'] . "</td>";
        echo "<td>" . htmlspecialchars($catalog['title']) . "</td>";
        echo "<td><strong>" . $catalog['position'] . "</strong></td>";
        echo "<td>" . $admin_position . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='alert alert-warning'>Žádné katalogy v databázi</div>";
}

echo "</div></div>";

// Test 2: Simulace AJAX požadavku
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>2. Test AJAX komunikace</h3></div>";
echo "<div class='card-body'>";

echo "<p>Klikněte na tlačítko pro test AJAX požadavku:</p>";
echo "<button id='testAjax' class='btn btn-primary'>Test AJAX pozic</button>";
echo "<div id='ajaxResult' class='mt-3'></div>";

echo "</div></div>";

// Test 3: Admin URL
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>3. Admin URL informace</h3></div>";
echo "<div class='card-body'>";

try {
    $context = Context::getContext();
    $link = $context->link;
    
    if ($link) {
        $admin_url = $link->getAdminLink('AdminKatalogy');
        echo "<p><strong>Správný admin URL:</strong><br>";
        echo "<code>$admin_url</code></p>";
        
        // Extrahuj token
        $parsed_url = parse_url($admin_url);
        parse_str($parsed_url['query'], $query_params);
        $token = isset($query_params['token']) ? $query_params['token'] : '';
        
        echo "<p><strong>Token:</strong> <code>$token</code></p>";
        
        $ajax_url = $admin_url . '&ajax=1&action=updatePositions';
        echo "<p><strong>AJAX URL:</strong><br>";
        echo "<code>$ajax_url</code></p>";
    } else {
        echo "<div class='alert alert-danger'>Nepodařilo se získat admin link</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Chyba: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

// Test 4: Error log
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>4. Posledních 10 řádků z error logu</h3></div>";
echo "<div class='card-body'>";

$error_log_paths = [
    dirname(__FILE__) . '/../../../var/logs/',
    dirname(__FILE__) . '/../../../logs/',
    '/var/log/apache2/',
    '/var/log/nginx/'
];

$log_found = false;
foreach ($error_log_paths as $log_dir) {
    if (is_dir($log_dir)) {
        $log_files = glob($log_dir . '*.log');
        foreach ($log_files as $log_file) {
            if (is_readable($log_file)) {
                $log_content = file_get_contents($log_file);
                $lines = explode("\n", $log_content);
                $recent_lines = array_slice($lines, -10);
                
                echo "<h5>$log_file</h5>";
                echo "<pre style='background: #f8f9fa; padding: 10px; max-height: 200px; overflow: auto;'>";
                echo htmlspecialchars(implode("\n", $recent_lines));
                echo "</pre>";
                $log_found = true;
                break 2;
            }
        }
    }
}

if (!$log_found) {
    echo "<div class='alert alert-info'>Error log nenalezen v očekávaných umístěních</div>";
}

echo "</div></div>";

// Test 5: Instrukce
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>5. Návod k řešení</h3></div>";
echo "<div class='card-body'>";

echo "<div class='alert alert-primary'>";
echo "<h4>Postup opravy drag & drop:</h4>";
echo "<ol>";
echo "<li><strong>Nahrajte opravený AdminKatalogyController.php</strong> do <code>/modules/katalogy/controllers/admin/</code></li>";
echo "<li><strong>Zkontrolujte admin URL</strong> výše a ujistěte se, že token je správný</li>";
echo "<li><strong>Otestujte AJAX</strong> pomocí tlačítka výše</li>";
echo "<li><strong>Zkuste drag & drop v administraci</strong> a sledujte error log</li>";
echo "<li><strong>Po úspěšném testu</strong> smažte tento soubor ze serveru</li>";
echo "</ol>";
echo "</div>";

echo "<div class='alert alert-warning'>";
echo "<h4>Možné příčiny problému:</h4>";
echo "<ul>";
echo "<li>Nesprávný AJAX endpoint URL</li>";
echo "<li>Špatný token pro admin sekci</li>";
echo "<li>JavaScript chyby v PrestaShop admin</li>";
echo "<li>Nesprávný formát dat posílaných z drag & drop</li>";
echo "</ul>";
echo "</div>";

echo "</div></div>";

echo "</div>"; // container

// JavaScript pro test
echo "<script>";
echo "document.getElementById('testAjax').addEventListener('click', function() {";
echo "    var resultDiv = document.getElementById('ajaxResult');";
echo "    resultDiv.innerHTML = '<div class=\"alert alert-info\">Testování AJAX...</div>';";
echo "    ";
echo "    var testData = new FormData();";
echo "    testData.append('ajax', '1');";
echo "    testData.append('action', 'updatePositions');";
echo "    testData.append('katalogy[0]', 'katalogy_1_" . ($catalogs[0]['id_katalog'] ?? '1') . "');";
echo "    testData.append('katalogy[1]', 'katalogy_2_" . ($catalogs[1]['id_katalog'] ?? '2') . "');";
echo "    ";
echo "    var adminUrl = '" . (isset($ajax_url) ? $ajax_url : '/admin/index.php?controller=AdminKatalogy&ajax=1&action=updatePositions') . "';";
echo "    ";
echo "    fetch(adminUrl, {";
echo "        method: 'POST',";
echo "        body: testData";
echo "    })";
echo "    .then(response => response.text())";
echo "    .then(data => {";
echo "        resultDiv.innerHTML = '<div class=\"alert alert-success\">AJAX odpověď:</div><pre>' + data + '</pre>';";
echo "    })";
echo "    .catch(error => {";
echo "        resultDiv.innerHTML = '<div class=\"alert alert-danger\">AJAX chyba: ' + error + '</div>';";
echo "    });";
echo "});";
echo "</script>";

echo "</body></html>";
?>