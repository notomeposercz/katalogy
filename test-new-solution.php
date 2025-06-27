<?php
/**
 * Test nového řešení s AJAX shortcode
 * Umístit do root adresáře PrestaShop
 */

// Načtení PrestaShop config
$config_paths = [
    dirname(__FILE__).'/config/config.inc.php',
    dirname(__FILE__).'/../../config/config.inc.php'
];

$config_loaded = false;
foreach ($config_paths as $config_path) {
    if (file_exists($config_path)) {
        require_once($config_path);
        $config_loaded = true;
        break;
    }
}

if (!$config_loaded) {
    die("❌ PrestaShop config nenalezen");
}

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test nového řešení - Katalogy</title>";
echo "<meta charset='utf-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>";
echo "</head><body>";

echo "<div class='container mt-4'>";
echo "<h1>Test nového řešení - AJAX Shortcode</h1>";

// Test 1: AJAX endpoint
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>1. Test AJAX endpoint</h3></div>";
echo "<div class='card-body'>";

$ajax_file = dirname(__FILE__) . '/modules/katalogy/ajax-katalogy.php';
if (file_exists($ajax_file)) {
    echo "<div class='alert alert-success'>✅ AJAX soubor existuje</div>";
    echo "<button class='btn btn-primary' onclick='testAjax()'>Test AJAX požadavku</button>";
    echo "<div id='ajax-result' class='mt-3'></div>";
} else {
    echo "<div class='alert alert-danger'>❌ AJAX soubor neexistuje: $ajax_file</div>";
}

echo "</div></div>";

// Test 2: Frontend JavaScript
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>2. Test Frontend JavaScript</h3></div>";
echo "<div class='card-body'>";

$js_file = dirname(__FILE__) . '/modules/katalogy/katalogy-frontend.js';
if (file_exists($js_file)) {
    echo "<div class='alert alert-success'>✅ Frontend JS existuje</div>";
    echo "<script src='/modules/katalogy/katalogy-frontend.js'></script>";
} else {
    echo "<div class='alert alert-danger'>❌ Frontend JS neexistuje: $js_file</div>";
}

echo "</div></div>";

// Test 3: Simulace CMS obsahu se shortcode
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>3. Simulace CMS obsahu se shortcode</h3></div>";
echo "<div class='card-body'>";

echo "<h4>Testovací obsah s [katalogy] shortcode:</h4>";
echo "<div class='cms-content' style='border: 2px dashed #007bff; padding: 20px; background: #f8f9fa;'>";
echo "<h2>Katalogy reklamních předmětů ke stažení</h2>";
echo "<p>Úvodní text před katalogy...</p>";
echo "[katalogy]";
echo "<p>Závěrečný text po katalozích...</p>";
echo "</div>";

echo "<hr>";

echo "<h4>Testovací obsah s [katalogy-simple] shortcode:</h4>";
echo "<div class='cms-content' style='border: 2px dashed #28a745; padding: 20px; background: #f8f9fa;'>";
echo "<h2>Pouze katalogy</h2>";
echo "[katalogy-simple]";
echo "</div>";

echo "</div></div>";

// Test 4: Návod
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>4. Návod k použití</h3></div>";
echo "<div class='card-body'>";

echo "<div class='alert alert-info'>";
echo "<h4>Postup implementace:</h4>";
echo "<ol>";
echo "<li><strong>Nahrajte soubory:</strong>";
echo "<ul>";
echo "<li><code>/modules/katalogy/ajax-katalogy.php</code></li>";
echo "<li><code>/modules/katalogy/katalogy-frontend.js</code></li>";
echo "<li><code>/modules/katalogy/katalogy-shortcode.php</code></li>";
echo "</ul></li>";
echo "<li><strong>Upravte CMS stránku:</strong> Nahraďte hook za <code>[katalogy]</code></li>";
echo "<li><strong>Přidejte JavaScript:</strong> Do template nebo CMS stránky vložte:";
echo "<br><code>&lt;script src='/modules/katalogy/katalogy-frontend.js'&gt;&lt;/script&gt;</code></li>";
echo "<li><strong>Otestujte:</strong> Obnovte CMS stránku</li>";
echo "</ol>";
echo "</div>";

echo "</div></div>";

echo "</div>"; // container

// JavaScript pro testování
echo "<script>";
echo "function testAjax() {";
echo "  var result = document.getElementById('ajax-result');";
echo "  result.innerHTML = '<div class=\"alert alert-info\">Testování AJAX...</div>';";
echo "  ";
echo "  fetch('/modules/katalogy/ajax-katalogy.php', {";
echo "    method: 'POST',";
echo "    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },";
echo "    body: 'action=get_katalogy&type=full'";
echo "  })";
echo "  .then(response => response.json())";
echo "  .then(data => {";
echo "    if (data.success) {";
echo "      result.innerHTML = '<div class=\"alert alert-success\">✅ AJAX funguje! Vráceno ' + data.content.length + ' znaků</div>';";
echo "    } else {";
echo "      result.innerHTML = '<div class=\"alert alert-danger\">❌ AJAX chyba: ' + data.message + '</div>';";
echo "    }";
echo "  })";
echo "  .catch(error => {";
echo "    result.innerHTML = '<div class=\"alert alert-danger\">❌ Chyba: ' + error.message + '</div>';";
echo "  });";
echo "}";
echo "</script>";

echo "</body></html>";
?>
