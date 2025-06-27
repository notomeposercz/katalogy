<?php
/**
 * Rychlá oprava pozic katalogů - spustit jednou
 * Umístit do /modules/katalogy/ a spustit
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config/config.inc.php');

echo "<h2>🔧 Oprava pozic katalogů</h2>";

try {
    // Zobrazit aktuální stav
    echo "<h3>📊 Stav před opravou:</h3>";
    $sql = 'SELECT `id_katalog`, `title`, `position` FROM `' . _DB_PREFIX_ . 'katalogy` ORDER BY `position` ASC, `id_katalog` ASC';
    $catalogs = Db::getInstance()->executeS($sql);
    
    if ($catalogs) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Název</th><th>Pozice</th></tr>";
        foreach ($catalogs as $catalog) {
            echo "<tr>";
            echo "<td>" . $catalog['id_katalog'] . "</td>";
            echo "<td>" . htmlspecialchars($catalog['title']) . "</td>";
            echo "<td><strong>" . $catalog['position'] . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Zkontroluj duplicity
        $duplicates = [];
        $positions_used = [];
        foreach ($catalogs as $catalog) {
            if (in_array($catalog['position'], $positions_used)) {
                $duplicates[] = $catalog['position'];
            } else {
                $positions_used[] = $catalog['position'];
            }
        }
        
        if ($duplicates) {
            echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px 0;'>";
            echo "⚠️ <strong>Nalezeny duplicitní pozice:</strong> " . implode(', ', array_unique($duplicates));
            echo "</div>";
        } else {
            echo "<div style='background: #e8f5e8; border: 1px solid #4caf50; padding: 10px; margin: 10px 0;'>";
            echo "✅ <strong>Žádné duplicitní pozice</strong>";
            echo "</div>";
        }
    } else {
        echo "<p>❌ Žádné katalogy nenalezeny.</p>";
        exit;
    }
    
    // Proveď opravu
    echo "<h3>🔄 Provádím opravu pozic...</h3>";
    
    // Začni transakci
    Db::getInstance()->execute('START TRANSACTION');
    
    $position = 1;
    $success_count = 0;
    
    foreach ($catalogs as $catalog) {
        $update_sql = 'UPDATE `' . _DB_PREFIX_ . 'katalogy` 
                      SET `position` = ' . (int)$position . ' 
                      WHERE `id_katalog` = ' . (int)$catalog['id_katalog'];
        
        if (Db::getInstance()->execute($update_sql)) {
            echo "✅ Katalog ID " . $catalog['id_katalog'] . " → pozice " . $position . "<br>";
            $success_count++;
        } else {
            echo "❌ Chyba u katalogu ID " . $catalog['id_katalog'] . "<br>";
        }
        
        $position++;
    }
    
    // Potvrď transakci
    Db::getInstance()->execute('COMMIT');
    
    echo "<div style='background: #e8f5e8; border: 1px solid #4caf50; padding: 10px; margin: 10px 0;'>";
    echo "✅ <strong>Úspěšně opraveno " . $success_count . " pozic!</strong>";
    echo "</div>";
    
    // Zobrazit nový stav
    echo "<h3>📊 Stav po opravě:</h3>";
    $catalogs_fixed = Db::getInstance()->executeS($sql);
    
    if ($catalogs_fixed) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background: #e8f5e8;'><th>ID</th><th>Název</th><th>Nová pozice</th></tr>";
        foreach ($catalogs_fixed as $catalog) {
            echo "<tr>";
            echo "<td>" . $catalog['id_katalog'] . "</td>";
            echo "<td>" . htmlspecialchars($catalog['title']) . "</td>";
            echo "<td><strong>" . $catalog['position'] . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0;'>";
    echo "<h4>📋 Další kroky:</h4>";
    echo "<ol>";
    echo "<li><strong>Nahrajte opravený AdminKatalogyController.php</strong> do <code>/modules/katalogy/controllers/admin/</code></li>";
    echo "<li><strong>Nahrajte admin-drag-drop.js</strong> do <code>/modules/katalogy/views/js/</code></li>";
    echo "<li><strong>Otestujte drag & drop</strong> v administraci</li>";
    echo "<li><strong>Smažte tento soubor</strong> ze serveru po úspěšném testu</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #e1f5fe; border: 1px solid #03a9f4; padding: 15px; margin: 20px 0;'>";
    echo "<h4>🔗 Užitečné odkazy:</h4>";
    echo "<ul>";
    echo "<li><a href='test-drag-drop.php'>Test drag & drop diagnostika</a></li>";
    echo "<li><a href='debug-positions.php'>Debug pozic</a></li>";
    echo "<li>Admin katalogy: /admin/index.php?controller=AdminKatalogy</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    // Vrať transakci zpět při chybě
    Db::getInstance()->execute('ROLLBACK');
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px 0;'>";
    echo "❌ <strong>Chyba:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<p><small>🕒 Dokončeno: " . date('Y-m-d H:i:s') . "</small></p>";
?>