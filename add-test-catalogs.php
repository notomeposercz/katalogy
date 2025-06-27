<?php
/**
 * Přidání testovacích katalogů
 */

// Načtení PrestaShop config
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
require_once(_PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php');

echo "<h1>PŘIDÁNÍ TESTOVACÍCH KATALOGŮ</h1>";

// Zkontroluj, jestli tabulka existuje
$table_name = _DB_PREFIX_ . 'katalogy';
$sql = "SHOW TABLES LIKE '$table_name'";
$result = Db::getInstance()->executeS($sql);

if (!$result) {
    die("❌ Tabulka $table_name neexistuje. Spusťte nejdříve fix-hooks.php");
}

// Testovací katalogy
$test_catalogs = [
    [
        'title' => 'Katalog reklamních předmětů 2024',
        'description' => 'Kompletní katalog reklamních předmětů pro rok 2024. Obsahuje více než 1000 produktů včetně novinek.',
        'file_url' => 'https://example.com/katalog-2024.pdf',
        'is_new' => 1,
        'position' => 1,
        'active' => 1
    ],
    [
        'title' => 'Textilní reklamní předměty',
        'description' => 'Specializovaný katalog textilních reklamních předmětů - trička, mikiny, čepice a další.',
        'file_url' => 'https://example.com/textil-katalog.pdf',
        'is_new' => 0,
        'position' => 2,
        'active' => 1
    ],
    [
        'title' => 'Kancelářské potřeby s potiskem',
        'description' => 'Katalog kancelářských potřeb vhodných pro potisk firemního loga - pera, bloky, diáře.',
        'file_url' => 'https://example.com/kancelar-katalog.pdf',
        'is_new' => 0,
        'position' => 3,
        'active' => 1
    ],
    [
        'title' => 'Technické gadgety',
        'description' => 'Moderní technické reklamní předměty - powerbanky, USB flash disky, bezdrátové nabíječky.',
        'file_url' => 'https://example.com/tech-katalog.pdf',
        'is_new' => 1,
        'position' => 4,
        'active' => 1
    ]
];

echo "<h2>Přidávání katalogů</h2>";

foreach ($test_catalogs as $index => $catalog_data) {
    echo "<h3>Katalog " . ($index + 1) . ": " . $catalog_data['title'] . "</h3>";
    
    // Zkontroluj, jestli katalog už neexistuje
    $check_sql = "SELECT id_katalog FROM `$table_name` WHERE title = '" . pSQL($catalog_data['title']) . "'";
    $existing = Db::getInstance()->getRow($check_sql);
    
    if ($existing) {
        echo "⚠️ Katalog už existuje (ID: " . $existing['id_katalog'] . ")<br>";
        continue;
    }
    
    // Vytvoř nový katalog
    $katalog = new Katalog();
    $katalog->title = $catalog_data['title'];
    $katalog->description = $catalog_data['description'];
    $katalog->file_url = $catalog_data['file_url'];
    $katalog->is_new = $catalog_data['is_new'];
    $katalog->position = $catalog_data['position'];
    $katalog->active = $catalog_data['active'];
    $katalog->date_add = date('Y-m-d H:i:s');
    $katalog->date_upd = date('Y-m-d H:i:s');
    
    if ($katalog->save()) {
        echo "✅ Katalog úspěšně přidán (ID: " . $katalog->id . ")<br>";
    } else {
        echo "❌ Nepodařilo se přidat katalog<br>";
    }
}

// Zobraz aktuální stav
echo "<h2>Aktuální stav databáze</h2>";
$count_sql = "SELECT COUNT(*) as count FROM `$table_name`";
$count_result = Db::getInstance()->getRow($count_sql);
echo "Celkem katalogů: " . $count_result['count'] . "<br>";

$active_sql = "SELECT COUNT(*) as count FROM `$table_name` WHERE active = 1";
$active_result = Db::getInstance()->getRow($active_sql);
echo "Aktivních katalogů: " . $active_result['count'] . "<br>";

if ($active_result['count'] > 0) {
    echo "<h3>Seznam aktivních katalogů:</h3>";
    $list_sql = "SELECT id_katalog, title, is_new, position FROM `$table_name` WHERE active = 1 ORDER BY position ASC";
    $catalogs = Db::getInstance()->executeS($list_sql);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Název</th><th>Nový</th><th>Pozice</th></tr>";
    foreach ($catalogs as $catalog) {
        echo "<tr>";
        echo "<td>" . $catalog['id_katalog'] . "</td>";
        echo "<td>" . $catalog['title'] . "</td>";
        echo "<td>" . ($catalog['is_new'] ? 'ANO' : 'NE') . "</td>";
        echo "<td>" . $catalog['position'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>Závěr</h2>";
echo "✅ Testovací katalogy přidány<br>";
echo "<p>Nyní můžete testovat zobrazení na CMS stránce pomocí:</p>";
echo "<code>{hook h='displayKatalogyContent'}</code><br>";
?>
