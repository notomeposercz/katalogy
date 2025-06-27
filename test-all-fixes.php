<?php
/**
 * Test všech oprav modulu katalogy
 * Umístit do root adresáře PrestaShop
 */

require_once(dirname(__FILE__).'/config/config.inc.php');

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test všech oprav - Katalogy</title>";
echo "<meta charset='utf-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>";
echo "<link href='/modules/katalogy/views/css/katalogy.css' rel='stylesheet'>";
echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>";
echo "</head><body>";

echo "<div class='container mt-4'>";
echo "<h1>Test všech oprav - Katalogy</h1>";

// Test 1: CSS opravy
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>1. Test CSS oprav</h3></div>";
echo "<div class='card-body'>";

echo "<h4>Úvodní text (měl by být vycentrovaný):</h4>";
echo "<div class='katalogy-intro'>";
echo "<p class='lead'>Stáhněte si naše katalogy reklamních předmětů nebo si vyžádejte fyzickou podobu. Více než 1000 produktů pro vaše podnikání.</p>";
echo "</div>";

echo "<h4>Info sekce (ikony a text by měly být vycentrované):</h4>";
echo "<div class='katalogy-info mb-4'>";
echo "<div class='row'>";
echo "<div class='col-md-4'>";
echo "<i class='material-icons' style='font-size: 48px; color: #007bff;'>file_download</i>";
echo "<h4>Stažení zdarma</h4>";
echo "<p>Všechny katalogy si můžete stáhnout zcela zdarma ve formátu PDF.</p>";
echo "</div>";
echo "<div class='col-md-4'>";
echo "<i class='material-icons' style='font-size: 48px; color: #28a745;'>local_shipping</i>";
echo "<h4>Fyzická podoba</h4>";
echo "<p>Máte zájem o tištěný katalog? Rádi vám ho zašleme poštou.</p>";
echo "</div>";
echo "<div class='col-md-4'>";
echo "<i class='material-icons' style='font-size: 48px; color: #ffc107;'>new_releases</i>";
echo "<h4>Pravidelné aktualizace</h4>";
echo "<p>Naše katalogy pravidelně aktualizujeme o nové produkty a ceny.</p>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<h4>Kontaktní sekce (text by měl mít správné odsazení):</h4>";
echo "<div class='katalogy-contact' style='background: #f8f9fa;'>";
echo "<div class='row align-items-center'>";
echo "<div class='col-md-8'>";
echo "<h3>Potřebujete poradit s výběrem?</h3>";
echo "<p>Naši odborníci vám rádi pomohou vybrat nejvhodnější reklamní předměty pro vaše potřeby. Kontaktujte nás pro individuální konzultaci.</p>";
echo "</div>";
echo "<div class='col-md-4 text-md-end'>";
echo "<a href='/kontakt' class='btn btn-primary btn-lg'>Kontaktujte nás</a>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "</div></div>";

// Test 2: Modal a formulář
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>2. Test modal a formuláře</h3></div>";
echo "<div class='card-body'>";

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
    
    echo "<p><strong>Test:</strong> Klikněte na tlačítko výše. Modal by se měl otevřít a po odeslání by se měla zobrazit zpráva bez reload stránky.</p>";
} else {
    echo "<div class='alert alert-warning'>⚠️ Žádné katalogy pro test</div>";
}

echo "</div></div>";

// Test 3: Alert systém
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>3. Test alert systému</h3></div>";
echo "<div class='card-body'>";

echo "<button class='btn btn-success' onclick='showTestAlert(\"success\", \"Test úspěšné zprávy\")'>Test úspěšné zprávy</button> ";
echo "<button class='btn btn-danger' onclick='showTestAlert(\"error\", \"Test chybové zprávy\")'>Test chybové zprávy</button>";

echo "<div id='alert-container' class='mt-3'></div>";

echo "</div></div>";

// Test 4: Email test
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>4. Test email formátu</h3></div>";
echo "<div class='card-body'>";

$module = Module::getInstanceByName('katalogy');
if ($module && method_exists($module, 'generateCleanEmailContent') && count($catalogs) > 0) {
    $test_catalog = $catalogs[0];
    $test_email = $module->generateCleanEmailContent(
        $test_catalog,
        'Jan Novák',
        'jan.novak@example.com',
        '+420 123 456 789',
        'Test s.r.o.',
        'Testovací zpráva pro katalog.'
    );
    
    echo "<h4>Náhled čistého email formátu:</h4>";
    echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #ddd;'>";
    echo htmlspecialchars($test_email);
    echo "</pre>";
} else {
    echo "<div class='alert alert-warning'>⚠️ Nelze testovat email - modul nebo metoda nedostupná</div>";
}

echo "</div></div>";

// Modal HTML
echo "<div class='modal fade' id='interestModal' tabindex='-1' role='dialog'>";
echo "<div class='modal-dialog' role='document'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title' id='interestModalLabel'>Zájem o katalog</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";
echo "<form id='interestForm' method='post'>";
echo "<div class='modal-body'>";
echo "<div class='form-group mb-3'>";
echo "<strong id='catalogTitle'>Test katalog</strong>";
echo "</div>";
echo "<div class='form-group mb-3'>";
echo "<label for='name' class='form-label'>Jméno a příjmení *</label>";
echo "<input type='text' class='form-control' id='name' name='name' required>";
echo "</div>";
echo "<div class='form-group mb-3'>";
echo "<label for='email' class='form-label'>E-mail *</label>";
echo "<input type='email' class='form-control' id='email' name='email' required>";
echo "</div>";
echo "<div class='form-group mb-3'>";
echo "<label for='phone' class='form-label'>Telefon</label>";
echo "<input type='tel' class='form-control' id='phone' name='phone'>";
echo "</div>";
echo "<div class='form-group mb-3'>";
echo "<label for='company' class='form-label'>Společnost</label>";
echo "<input type='text' class='form-control' id='company' name='company'>";
echo "</div>";
echo "<div class='form-group mb-3'>";
echo "<label for='message' class='form-label'>Zpráva</label>";
echo "<textarea class='form-control' id='message' name='message' rows='3'></textarea>";
echo "</div>";
echo "<input type='hidden' id='catalog_id' name='catalog_id' value=''>";
echo "</div>";
echo "<div class='modal-footer'>";
echo "<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Zrušit</button>";
echo "<button type='submit' class='btn btn-primary'>Odeslat žádost</button>";
echo "</div>";
echo "</form>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "</div>"; // container

// JavaScript
echo "<script>";
echo "function showTestAlert(type, message) {";
echo "    const container = document.getElementById('alert-container');";
echo "    const alertDiv = document.createElement('div');";
echo "    alertDiv.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' alert-dismissible fade show';";
echo "    alertDiv.innerHTML = message + '<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>';";
echo "    container.innerHTML = '';";
echo "    container.appendChild(alertDiv);";
echo "}";

// Simulace katalogy JavaScript
echo "document.addEventListener('DOMContentLoaded', function() {";
echo "    const interestButtons = document.querySelectorAll('.katalogy-interest');";
echo "    const modal = document.getElementById('interestModal');";
echo "    const catalogTitle = document.getElementById('catalogTitle');";
echo "    const catalogIdInput = document.getElementById('catalog_id');";
echo "    const form = document.getElementById('interestForm');";
echo "    ";
echo "    interestButtons.forEach(button => {";
echo "        button.addEventListener('click', function() {";
echo "            const catalogId = this.getAttribute('data-catalog-id');";
echo "            const catalogTitleText = this.getAttribute('data-catalog-title');";
echo "            catalogTitle.textContent = 'Zájem o katalog: ' + catalogTitleText;";
echo "            catalogIdInput.value = catalogId;";
echo "            const bsModal = new bootstrap.Modal(modal);";
echo "            bsModal.show();";
echo "        });";
echo "    });";
echo "    ";
echo "    form.addEventListener('submit', function(e) {";
echo "        e.preventDefault();";
echo "        const bsModal = bootstrap.Modal.getInstance(modal);";
echo "        bsModal.hide();";
echo "        showTestAlert('success', 'Test: Formulář by byl odeslán (bez skutečného odeslání)');";
echo "        form.reset();";
echo "    });";
echo "});";
echo "</script>";

echo "</body></html>";
?>
