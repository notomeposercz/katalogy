<?php
/**
 * Override for PrestaShop Autoload to include module classes
 */

class PrestaShopAutoload extends PrestaShopAutoloadCore
{
    public function load($classname)
    {
        // Try to load module classes first
        if ($classname === 'Katalog') {
            $file = _PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php';
            if (file_exists($file)) {
                require_once($file);
                return true;
            }
        }
        
        return parent::load($classname);
    }
}