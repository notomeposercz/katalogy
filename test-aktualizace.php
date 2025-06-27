<?php
/**
 * Test script pro ověření aktualizace modulu Katalogy
 */

// Include PrestaShop configuration
require_once(dirname(__FILE__) . '/../../config/config.inc.php');

if (!defined('_PS_VERSION_')) {
    die('This script can only be run from PrestaShop context');
}

echo "<h1>Test aktualizace modulu Katalogy</h1>\n";

// Test 1: Check if module is installed
echo "<h2>1. Test instalace modulu</h2>\n";
$module = Module::getInstanceByName('katalogy');
if ($module && $module->id) {
    echo "✅ Modul je nainstalován (ID: {$module->id})<br>\n";
} else {
    echo "❌ Modul není nainstalován<br>\n";
    exit;
}

// Test 2: Check configuration values
echo "<h2>2. Test konfiguračních hodnot</h2>\n";
$configKeys = [
    'KATALOGY_EMAIL',
    'KATALOGY_CMS_ID',
    'KATALOGY_INTRO_TEXT',
    'KATALOGY_BOX1_TITLE',
    'KATALOGY_BOX1_TEXT',
    'KATALOGY_BOX2_TITLE',
    'KATALOGY_BOX2_TEXT',
    'KATALOGY_BOX3_TITLE',
    'KATALOGY_BOX3_TEXT'
];

foreach ($configKeys as $key) {
    $value = Configuration::get($key);
    if ($value) {
        echo "✅ $key: " . substr($value, 0, 50) . "...<br>\n";
    } else {
        echo "❌ $key: není nastaveno<br>\n";
    }
}

// Test 3: Check database table
echo "<h2>3. Test databázové tabulky</h2>\n";
$sql = 'SHOW TABLES LIKE "' . _DB_PREFIX_ . 'katalogy"';
$result = Db::getInstance()->executeS($sql);
if ($result) {
    echo "✅ Tabulka katalogy existuje<br>\n";
    
    // Check table structure
    $sql = 'DESCRIBE ' . _DB_PREFIX_ . 'katalogy';
    $columns = Db::getInstance()->executeS($sql);
    echo "Sloupce tabulky:<br>\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})<br>\n";
    }
} else {
    echo "❌ Tabulka katalogy neexistuje<br>\n";
}

// Test 4: Check files
echo "<h2>4. Test souborů</h2>\n";
$files = [
    'katalogy.php',
    'classes/Katalog.php',
    'controllers/admin/AdminKatalogyController.php',
    'views/templates/front/katalogy_content.tpl',
    'views/css/katalogy.css'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file<br>\n";
    } else {
        echo "❌ $file - soubor neexistuje<br>\n";
    }
}

// Test 5: Check admin tab
echo "<h2>5. Test admin záložky</h2>\n";
$tab_id = Tab::getIdFromClassName('AdminKatalogy');
if ($tab_id) {
    echo "✅ Admin záložka existuje (ID: $tab_id)<br>\n";
} else {
    echo "❌ Admin záložka neexistuje<br>\n";
}

echo "<h2>Test dokončen</h2>\n";
echo "<p>Pokud jsou všechny testy zelené (✅), aktualizace proběhla úspěšně.</p>\n";
