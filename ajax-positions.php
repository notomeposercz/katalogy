<?php
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
?>