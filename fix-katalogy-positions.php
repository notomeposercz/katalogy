<?php
/**
 * Pomocný skript pro opravu pozic katalogů
 * Spusťte tento soubor jednorázově pro opravu duplicitních pozic
 */

// Načtení PrestaShop prostředí
require_once(dirname(dirname(dirname(__FILE__))) . '/config/config.inc.php');
require_once(dirname(__FILE__) . '/classes/Katalog.php');

echo "<h2>Oprava pozic katalogů</h2>";

try {
    // Zobrazit aktuální stav
    echo "<h3>Aktuální stav pozic:</h3>";
    $sql = 'SELECT `id_katalog`, `title`, `position` FROM `' . _DB_PREFIX_ . 'katalogy` ORDER BY `position` ASC, `id_katalog` ASC';
    $catalogs = Db::getInstance()->executeS($sql);
    
    if ($catalogs) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Název</th><th>Pozice</th></tr>";
        foreach ($catalogs as $catalog) {
            echo "<tr>";
            echo "<td>" . $catalog['id_katalog'] . "</td>";
            echo "<td>" . htmlspecialchars($catalog['title']) . "</td>";
            echo "<td>" . $catalog['position'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Žádné katalogy nenalezeny.</p>";
    }
    
    // Opravit pozice
    echo "<h3>Opravuji pozice...</h3>";
    Katalog::fixDuplicatePositions();
    echo "<p style='color: green;'>✓ Pozice byly úspěšně opraveny!</p>";
    
    // Zobrazit nový stav
    echo "<h3>Nový stav pozic:</h3>";
    $catalogs_fixed = Db::getInstance()->executeS($sql);
    
    if ($catalogs_fixed) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Název</th><th>Pozice</th></tr>";
        foreach ($catalogs_fixed as $catalog) {
            echo "<tr>";
            echo "<td>" . $catalog['id_katalog'] . "</td>";
            echo "<td>" . htmlspecialchars($catalog['title']) . "</td>";
            echo "<td>" . $catalog['position'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<p><strong>Doporučení:</strong> Po úspěšné opravě smažte tento soubor ze serveru.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Chyba: " . $e->getMessage() . "</p>";
}
?>
