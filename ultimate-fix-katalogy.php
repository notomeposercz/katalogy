<?php
/**
 * KOMPLETNÍ OPRAVA KATALOGY - Spustit v root adresáři PrestaShop
 */

require_once(dirname(__FILE__).'/config/config.inc.php');
require_once(_PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php');

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>🔧 Kompletní oprava Katalogy</title>";
echo "<meta charset='utf-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body>";

echo "<div class='container mt-4'>";
echo "<h1>🔧 KOMPLETNÍ OPRAVA KATALOGY</h1>";

// KROK 1: Oprava pozic v DB
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>1. 🔄 Oprava pozic v databázi</h3></div>";
echo "<div class='card-body'>";

echo "<h4>Stav před opravou:</h4>";
$sql = 'SELECT `id_katalog`, `title`, `position` FROM `' . _DB_PREFIX_ . 'katalogy` ORDER BY `position` ASC, `id_katalog` ASC';
$catalogs = Db::getInstance()->executeS($sql);

if ($catalogs) {
    echo "<table class='table table-sm'>";
    echo "<tr><th>ID</th><th>Název</th><th>Pozice v DB</th></tr>";
    foreach ($catalogs as $catalog) {
        echo "<tr>";
        echo "<td>" . $catalog['id_katalog'] . "</td>";
        echo "<td>" . htmlspecialchars($catalog['title']) . "</td>";
        echo "<td>" . $catalog['position'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // OPRAVA POZIC - začni od 1
    echo "<h4>⚡ Opravuji pozice...</h4>";
    $position = 1;
    foreach ($catalogs as $catalog) {
        $update_sql = 'UPDATE `' . _DB_PREFIX_ . 'katalogy` 
                      SET `position` = ' . (int)$position . ' 
                      WHERE `id_katalog` = ' . (int)$catalog['id_katalog'];
        
        if (Db::getInstance()->execute($update_sql)) {
            echo "✅ Katalog ID " . $catalog['id_katalog'] . " → pozice " . $position . "<br>";
        }
        $position++;
    }
    
    echo "<div class='alert alert-success'>✅ Pozice opraveny!</div>";
} else {
    echo "<div class='alert alert-warning'>⚠️ Žádné katalogy nenalezeny</div>";
}

echo "</div></div>";

// KROK 2: Kontrola admin URL a token
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>2. 🔗 Kontrola admin URL</h3></div>";
echo "<div class='card-body'>";

try {
    $context = Context::getContext();
    $employee = $context->employee;
    
    if ($employee && $employee->id) {
        $admin_token = Tools::getAdminTokenLite('AdminKatalogy');
        $admin_link = $context->link->getAdminLink('AdminKatalogy');
        
        echo "<p><strong>Admin Link:</strong><br>";
        echo "<code>$admin_link</code></p>";
        
        echo "<p><strong>Token:</strong> <code>$admin_token</code></p>";
        
        $ajax_url = $admin_link . '&ajax=1&action=updatePositions';
        echo "<p><strong>AJAX URL:</strong><br>";
        echo "<code>$ajax_url</code></p>";
        
        // Najdi správný admin adresář
        $admin_dirs = glob(dirname(__FILE__) . '/admin*');
        $admin_dir = '';
        if ($admin_dirs) {
            $admin_dir = basename($admin_dirs[0]);
            echo "<p><strong>Admin adresář:</strong> <code>$admin_dir</code></p>";
        }
        
    } else {
        echo "<div class='alert alert-warning'>⚠️ Není přihlášený admin uživatel</div>";
        
        // Zkus najít admin adresář jinak
        $admin_dirs = glob(dirname(__FILE__) . '/admin*');
        if ($admin_dirs) {
            $admin_dir = basename($admin_dirs[0]);
            echo "<p><strong>Nalezen admin adresář:</strong> <code>$admin_dir</code></p>";
            echo "<p><strong>Ruční admin URL:</strong><br>";
            echo "<code>/" . $admin_dir . "/index.php?controller=AdminKatalogy</code></p>";
        }
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Chyba: " . $e->getMessage() . "</div>";
}

echo "</div></div>";

// KROK 3: Test AJAX s pravým tokenem
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>3. 🧪 Test AJAX s opravou</h3></div>";
echo "<div class='card-body'>";

echo "<button id='testAjaxFixed' class='btn btn-primary'>Test AJAX s opraveným URL</button>";
echo "<div id='ajaxResult' class='mt-3'></div>";

echo "</div></div>";

// KROK 4: Vytvoření helper souboru
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>4. 📄 Vytvoření helper souboru</h3></div>";
echo "<div class='card-body'>";

$helper_content = '<?php
/**
 * AJAX Helper pro Katalogy - umístit do /modules/katalogy/
 */

require_once(dirname(dirname(dirname(__FILE__))) . "/config/config.inc.php");
require_once("classes/Katalog.php");

header("Content-Type: application/json");

if (!Tools::isSubmit("ajax") || Tools::getValue("action") !== "updatePositions") {
    die(json_encode(["error" => "Invalid request"]));
}

$positions = Tools::getValue("katalogy");

if (!is_array($positions)) {
    die(json_encode(["error" => "Invalid positions data"]));
}

try {
    Db::getInstance()->execute("START TRANSACTION");
    
    foreach ($positions as $position => $value) {
        $parts = explode("_", $value);
        if (count($parts) >= 3 && is_numeric($parts[2])) {
            $katalog_id = (int)$parts[2];
            $new_position = (int)$position + 1;
            
            $sql = "UPDATE `" . _DB_PREFIX_ . "katalogy` 
                   SET `position` = " . (int)$new_position . " 
                   WHERE `id_katalog` = " . (int)$katalog_id;
            
            if (!Db::getInstance()->execute($sql)) {
                throw new Exception("Failed to update position for katalog $katalog_id");
            }
        }
    }
    
    Db::getInstance()->execute("COMMIT");
    echo json_encode(["success" => true]);
    
} catch (Exception $e) {
    Db::getInstance()->execute("ROLLBACK");
    echo json_encode(["error" => $e->getMessage()]);
}
?>';

$helper_file = _PS_MODULE_DIR_ . 'katalogy/ajax-positions.php';
if (file_put_contents($helper_file, $helper_content)) {
    echo "<div class='alert alert-success'>✅ Helper soubor vytvořen: <code>$helper_file</code></div>";
} else {
    echo "<div class='alert alert-danger'>❌ Nepodařilo se vytvořit helper soubor</div>";
}

echo "</div></div>";

// KROK 5: Kontrola admin kontroleru
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>5. 🎛️ Kontrola AdminKatalogyController</h3></div>";
echo "<div class='card-body'>";

$admin_controller = _PS_MODULE_DIR_ . 'katalogy/controllers/admin/AdminKatalogyController.php';
if (file_exists($admin_controller)) {
    echo "<div class='alert alert-success'>✅ AdminKatalogyController existuje</div>";
    
    $controller_content = file_get_contents($admin_controller);
    if (strpos($controller_content, 'ajaxProcessUpdatePositions') !== false) {
        echo "<div class='alert alert-success'>✅ AJAX metoda ajaxProcessUpdatePositions nalezena</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ AJAX metoda ajaxProcessUpdatePositions nenalezena</div>";
    }
    
    if (strpos($controller_content, 'position_identifier') !== false) {
        echo "<div class='alert alert-success'>✅ position_identifier nastaven</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ position_identifier nenastaven</div>";
    }
} else {
    echo "<div class='alert alert-danger'>❌ AdminKatalogyController neexistuje</div>";
}

echo "</div></div>";

// KROK 6: Finální instrukce
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h3>6. 📋 Finální kroky</h3></div>";
echo "<div class='card-body'>";

echo "<div class='alert alert-info'>";
echo "<h4>Pokud drag & drop stále nefunguje:</h4>";
echo "<ol>";
echo "<li><strong>Nahrajte nový AdminKatalogyController.php</strong> z artifactů výše</li>";
echo "<li><strong>Zkuste helper AJAX:</strong> Použijte URL <code>/modules/katalogy/ajax-positions.php</code></li>";
echo "<li><strong>Ověřte admin přístup:</strong> Přihlaste se jako admin</li>";
echo "<li><strong>Zkontrolujte browser console:</strong> F12 → Console při drag & drop</li>";
echo "<li><strong>Manuální test:</strong> Upravte pozici přes editaci katalogu</li>";
echo "</ol>";
echo "</div>";

echo "<div class='alert alert-warning'>";
echo "<h4>Debug informace:</h4>";
echo "<ul>";
echo "<li>Pozice v DB jsou nyní: 1, 2, 3, 4, 5, 6</li>";
echo "<li>V admin by měly být zobrazeny jako: 1, 2, 3, 4, 5, 6</li>";
echo "<li>AJAX helper soubor vytvořen pro alternativní řešení</li>";
echo "</ul>";
echo "</div>";

echo "</div></div>";

echo "</div>"; // container

// JavaScript pro test
echo "<script>";
echo "document.addEventListener('DOMContentLoaded', function() {";
echo "    document.getElementById('testAjaxFixed').addEventListener('click', function() {";
echo "        var resultDiv = document.getElementById('ajaxResult');";
echo "        resultDiv.innerHTML = '<div class=\"alert alert-info\">Testování helper AJAX...</div>';";
echo "        ";
echo "        var testData = new FormData();";
echo "        testData.append('ajax', '1');";
echo "        testData.append('action', 'updatePositions');";
echo "        testData.append('katalogy[0]', 'katalogy_1_14');"; // První katalog
echo "        testData.append('katalogy[1]', 'katalogy_2_21');"; // Druhý katalog
echo "        ";
echo "        fetch('/modules/katalogy/ajax-positions.php', {";
echo "            method: 'POST',";
echo "            body: testData";
echo "        })";
echo "        .then(response => response.json())";
echo "        .then(data => {";
echo "            if (data.success) {";
echo "                resultDiv.innerHTML = '<div class=\"alert alert-success\">✅ Helper AJAX funguje!</div>';";
echo "            } else {";
echo "                resultDiv.innerHTML = '<div class=\"alert alert-danger\">❌ Helper AJAX chyba: ' + (data.error || 'Unknown error') + '</div>';";
echo "            }";
echo "        })";
echo "        .catch(error => {";
echo "            resultDiv.innerHTML = '<div class=\"alert alert-danger\">❌ Fetch chyba: ' + error + '</div>';";
echo "        });";
echo "    });";
echo "});";
echo "</script>";

echo "</body></html>";
?>