<?php
/**
 * Override CMS Controller pro podporu shortcode katalogů
 */

class CmsController extends CmsControllerCore
{
    public function initContent()
    {
        parent::initContent();
        
        // Zpracování shortcode v CMS obsahu
        if (isset($this->cms) && $this->cms instanceof CMS) {
            $content = $this->cms->content;
            
            // Zpracování [katalogy] shortcode
            if (strpos($content, '[katalogy]') !== false) {
                $module = Module::getInstanceByName('katalogy');
                if ($module && $module->active && method_exists($module, 'renderKatalogyContent')) {
                    $katalogy_content = $module->hookDisplayKatalogyContent([]);
                    $content = str_replace('[katalogy]', $katalogy_content, $content);
                    $this->cms->content = $content;
                    
                    // Přidání CSS a JS
                    $this->addCSS('https://fonts.googleapis.com/icon?family=Material+Icons', 'all', null, false);
                    $this->addCSS(_MODULE_DIR_ . 'katalogy/views/css/katalogy.css');
                    $this->addJS(_MODULE_DIR_ . 'katalogy/views/js/katalogy.js');
                }
            }
            
            // Zpracování [katalogy-simple] shortcode
            if (strpos($content, '[katalogy-simple]') !== false) {
                $module = Module::getInstanceByName('katalogy');
                if ($module && $module->active && method_exists($module, 'hookDisplayKatalogySimple')) {
                    $katalogy_simple = $module->hookDisplayKatalogySimple([]);
                    $content = str_replace('[katalogy-simple]', $katalogy_simple, $content);
                    $this->cms->content = $content;
                    
                    // Přidání CSS a JS
                    $this->addCSS('https://fonts.googleapis.com/icon?family=Material+Icons', 'all', null, false);
                    $this->addCSS(_MODULE_DIR_ . 'katalogy/views/css/katalogy.css');
                    $this->addJS(_MODULE_DIR_ . 'katalogy/views/js/katalogy.js');
                }
            }
        }
    }
}
