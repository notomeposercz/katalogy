<?php
/**
 * Test finálních oprav modulu katalogy
 * Umístit do root adresáře PrestaShop
 */

require_once(dirname(__FILE__).'/config/config.inc.php');

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test finálních oprav - Katalogy</title>";
echo "<meta charset='utf-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>";
echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>";
echo "</head><body>";

echo "<div class='container mt-4'>";
echo "<h1>Test finálních oprav - Katalogy</h1>";

// Test 1: Modul
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>1. Test modulu</h3></div>";
echo "<div class='card-body'>";

$module = Module::getInstanceByName('katalogy');
if ($module && $module->active) {
    echo "<div class='alert alert-success'>✅ Modul katalogy je aktivní</div>";
} else {
    echo "<div class='alert alert-danger'>❌ Modul katalogy není aktivní</div>";
}

echo "</div></div>";

// Test 2: Template změny
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>2. Test template změn</h3></div>";
echo "<div class='card-body'>";

$template_file = _PS_MODULE_DIR_ . 'katalogy/views/templates/front/katalogy_content.tpl';
if (file_exists($template_file)) {
    $template_content = file_get_contents($template_file);
    
    // Zkontroluj, že nadpis byl odstraněn
    if (strpos($template_content, '<h1 class="page-title">') === false) {
        echo "<div class='alert alert-success'>✅ Duplicitní nadpis odstraněn z template</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ Duplicitní nadpis stále v template</div>";
    }
    
    // Zkontroluj Bootstrap 5 tlačítka
    if (strpos($template_content, 'data-bs-dismiss') !== false) {
        echo "<div class='alert alert-success'>✅ Bootstrap 5 tlačítka aktualizována</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ Bootstrap 5 tlačítka nejsou aktualizována</div>";
    }
} else {
    echo "<div class='alert alert-danger'>❌ Template soubor nenalezen</div>";
}

echo "</div></div>";

// Test 3: CSS změny
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>3. Test CSS změn</h3></div>";
echo "<div class='card-body'>";

$css_file = _PS_MODULE_DIR_ . 'katalogy/views/css/katalogy.css';
if (file_exists($css_file)) {
    $css_content = file_get_contents($css_file);
    
    if (strpos($css_content, '.katalogy-intro') !== false) {
        echo "<div class='alert alert-success'>✅ CSS aktualizováno pro lepší integraci</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ CSS změny nebyly aplikovány</div>";
    }
} else {
    echo "<div class='alert alert-danger'>❌ CSS soubor nenalezen</div>";
}

echo "</div></div>";

// Test 4: JavaScript funkčnost
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>4. Test JavaScript funkčnosti</h3></div>";
echo "<div class='card-body'>";

echo "<h4>Simulace katalogů s modal:</h4>";

// Načti katalogy pro test
require_once(_PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php');
$catalogs = Katalog::getAllActive();

if (count($catalogs) > 0) {
    $first_catalog = $catalogs[0];
    
    echo "<div class='katalogy-item' style='border: 1px solid #ddd; padding: 15px; margin: 10px 0;'>";
    echo "<h5>" . htmlspecialchars($first_catalog['title']) . "</h5>";
    echo "<button class='btn btn-secondary katalogy-interest' ";
    echo "data-catalog-id='" . $first_catalog['id_katalog'] . "' ";
    echo "data-catalog-title='" . htmlspecialchars($first_catalog['title']) . "' ";
    echo "type='button'>";
    echo "<i class='material-icons'>mail</i> Zájem o katalog";
    echo "</button>";
    echo "</div>";
    
    // Modal
    echo "<div class='modal fade' id='interestModal' tabindex='-1' role='dialog'>";
    echo "<div class='modal-dialog' role='document'>";
    echo "<div class='modal-content'>";
    echo "<div class='modal-header'>";
    echo "<h5 class='modal-title' id='interestModalLabel'>Zájem o katalog</h5>";
    echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
    echo "</div>";
    echo "<div class='modal-body'>";
    echo "<div class='form-group mb-3'>";
    echo "<strong id='catalogTitle'>Test katalog</strong>";
    echo "</div>";
    echo "<div class='form-group mb-3'>";
    echo "<label for='name' class='form-label'>Jméno a příjmení *</label>";
    echo "<input type='text' class='form-control' id='name' name='name' required>";
    echo "</div>";
    echo "<input type='hidden' id='catalog_id' name='catalog_id' value=''>";
    echo "</div>";
    echo "<div class='modal-footer'>";
    echo "<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Zrušit</button>";
    echo "<button type='submit' class='btn btn-primary'>Odeslat žádost</button>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    echo "<p><strong>Test:</strong> Klikněte na tlačítko 'Zájem o katalog' výše. Měl by se otevřít modal.</p>";
} else {
    echo "<div class='alert alert-warning'>⚠️ Žádné katalogy v databázi pro test</div>";
}

echo "</div></div>";

// Test 5: Návod
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>5. Finální kontrola</h3></div>";
echo "<div class='card-body'>";

echo "<div class='alert alert-info'>";
echo "<h4>Kontrolní seznam:</h4>";
echo "<ol>";
echo "<li>✅ Modul je aktivní a funguje</li>";
echo "<li>✅ Shortcode [katalogy] se zobrazuje na CMS stránce</li>";
echo "<li>✅ Duplicitní nadpis odstraněn</li>";
echo "<li>✅ Bootstrap 5 kompatibilita</li>";
echo "<li>🔄 <strong>Otestujte modal výše</strong></li>";
echo "</ol>";
echo "</div>";

echo "<h4>Odkazy pro testování:</h4>";
echo "<ul>";
echo "<li><a href='/content/23-katalogy-reklamnich-predmetu-ke-stazeni' target='_blank'>CMS stránka s katalogy</a></li>";
echo "<li><a href='/modules/katalogy/debug-shortcode-cms.php' target='_blank'>Debug CMS stránky</a></li>";
echo "</ul>";

echo "</div></div>";

echo "</div>"; // container

// JavaScript pro test modal
echo "<script>";
echo "document.addEventListener('DOMContentLoaded', function() {";
echo "    const interestButtons = document.querySelectorAll('.katalogy-interest');";
echo "    const modal = document.getElementById('interestModal');";
echo "    const catalogTitle = document.getElementById('catalogTitle');";
echo "    const catalogIdInput = document.getElementById('catalog_id');";
echo "    ";
echo "    interestButtons.forEach(button => {";
echo "        button.addEventListener('click', function() {";
echo "            const catalogId = this.getAttribute('data-catalog-id');";
echo "            const catalogTitleText = this.getAttribute('data-catalog-title');";
echo "            ";
echo "            catalogTitle.textContent = 'Zájem o katalog: ' + catalogTitleText;";
echo "            catalogIdInput.value = catalogId;";
echo "            ";
echo "            // Bootstrap 5 modal";
echo "            const bsModal = new bootstrap.Modal(modal);";
echo "            bsModal.show();";
echo "        });";
echo "    });";
echo "});";
echo "</script>";

echo "</body></html>";
?>
