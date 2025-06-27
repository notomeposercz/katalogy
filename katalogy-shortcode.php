<?php
/**
 * Katalogy Shortcode Handler
 * Umístit do /modules/katalogy/
 * 
 * Tento soubor zpracovává shortcode [katalogy] v CMS obsahu
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php');

class KatalogyShortcode
{
    private $module;
    
    public function __construct()
    {
        $this->module = Module::getInstanceByName('katalogy');
    }
    
    /**
     * Zpracování shortcode v CMS obsahu
     */
    public function processContent($content, $cms_id = null)
    {
        if (!$this->module || !$this->module->active) {
            return $content;
        }
        
        // Zpracování [katalogy]
        if (strpos($content, '[katalogy]') !== false) {
            $katalogy_content = $this->renderKatalogy();
            $content = str_replace('[katalogy]', $katalogy_content, $content);
        }
        
        // Zpracování [katalogy-simple]
        if (strpos($content, '[katalogy-simple]') !== false) {
            $katalogy_simple = $this->renderKatalogySimple();
            $content = str_replace('[katalogy-simple]', $katalogy_simple, $content);
        }
        
        return $content;
    }
    
    /**
     * Renderování kompletních katalogů
     */
    private function renderKatalogy()
    {
        // Handle interest form submission
        if (Tools::isSubmit('submitInterest')) {
            $this->processInterestForm();
        }

        // Get all active catalogs
        $catalogs = Katalog::getAllActive();

        // Process catalogs data for template
        foreach ($catalogs as &$catalog) {
            $katalog_obj = new Katalog($catalog['id_katalog']);
            $catalog['download_url'] = $katalog_obj->getDownloadUrl();
            $catalog['image_url'] = $katalog_obj->getImageUrl();
            $catalog['has_download'] = $katalog_obj->hasDownload();
        }

        $context = Context::getContext();
        $context->smarty->assign([
            'catalogs' => $catalogs,
            'module_dir' => $this->module->getPathUri(),
            'success_message' => isset($_SESSION['katalogy_success']) ? $_SESSION['katalogy_success'] : '',
            'error_message' => isset($_SESSION['katalogy_error']) ? $_SESSION['katalogy_error'] : ''
        ]);

        // Clear messages after display
        unset($_SESSION['katalogy_success'], $_SESSION['katalogy_error']);

        return $this->module->display(_PS_MODULE_DIR_ . 'katalogy/katalogy.php', 'views/templates/front/katalogy_content.tpl');
    }
    
    /**
     * Renderování jednoduchých katalogů
     */
    private function renderKatalogySimple()
    {
        // Handle interest form submission
        if (Tools::isSubmit('submitInterest')) {
            $this->processInterestForm();
        }

        // Get all active catalogs
        $catalogs = Katalog::getAllActive();

        // Process catalogs data for template
        foreach ($catalogs as &$catalog) {
            $katalog_obj = new Katalog($catalog['id_katalog']);
            $catalog['download_url'] = $katalog_obj->getDownloadUrl();
            $catalog['image_url'] = $katalog_obj->getImageUrl();
            $catalog['has_download'] = $katalog_obj->hasDownload();
        }

        $context = Context::getContext();
        $context->smarty->assign([
            'catalogs' => $catalogs,
            'module_dir' => $this->module->getPathUri(),
            'success_message' => isset($_SESSION['katalogy_success']) ? $_SESSION['katalogy_success'] : '',
            'error_message' => isset($_SESSION['katalogy_error']) ? $_SESSION['katalogy_error'] : ''
        ]);

        // Clear messages after display
        unset($_SESSION['katalogy_success'], $_SESSION['katalogy_error']);

        return $this->module->display(_PS_MODULE_DIR_ . 'katalogy/katalogy.php', 'views/templates/front/katalogy_simple.tpl');
    }
    
    /**
     * Zpracování formuláře zájmu
     */
    private function processInterestForm()
    {
        $name = Tools::getValue('name');
        $email = Tools::getValue('email');
        $phone = Tools::getValue('phone');
        $company = Tools::getValue('company');
        $catalog_id = (int)Tools::getValue('catalog_id');
        $message = Tools::getValue('message');

        // Basic validation
        if (empty($name) || empty($email) || !Validate::isEmail($email)) {
            $_SESSION['katalogy_error'] = 'Prosím vyplňte všechna povinná pole správně.';
            return;
        }

        // Get catalog info
        $catalog = Katalog::getById($catalog_id);
        if (!$catalog) {
            $_SESSION['katalogy_error'] = 'Katalog nebyl nalezen.';
            return;
        }

        // Prepare email
        $admin_email = Configuration::get('KATALOGY_EMAIL');
        $subject = 'Zájem o katalog: ' . $catalog['title'];
        
        $email_content = "
Nový zájem o katalog ze stránek:

Katalog: {$catalog['title']}

Kontaktní údaje:
Jméno: $name
E-mail: $email
Telefon: $phone
Společnost: $company

Zpráva:
$message

---
Odesláno ze stránek " . Configuration::get('PS_SHOP_NAME');

        // Send email
        if (Mail::Send(
            Context::getContext()->language->id,
            'contact',
            $subject,
            ['message' => $email_content],
            $admin_email,
            null,
            $email,
            $name
        )) {
            $_SESSION['katalogy_success'] = 'Váš zájem byl odeslán. Brzy se vám ozveme.';
        } else {
            $_SESSION['katalogy_error'] = 'Chyba při odesílání zprávy. Zkuste to prosím znovu.';
        }
    }
    
    /**
     * Přidání CSS a JS pro katalogy
     */
    public function addAssets()
    {
        $context = Context::getContext();
        if (isset($context->controller)) {
            $context->controller->addCSS('https://fonts.googleapis.com/icon?family=Material+Icons', 'all', null, false);
            $context->controller->addCSS(_MODULE_DIR_ . 'katalogy/views/css/katalogy.css');
            $context->controller->addJS(_MODULE_DIR_ . 'katalogy/views/js/katalogy.js');
        }
    }
}
?>
