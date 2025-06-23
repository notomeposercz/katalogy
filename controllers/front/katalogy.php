<?php
/**
 * Frontend Controller for Katalogy Module
 */

require_once(_PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php');

class KatalogySeznamsModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
    }

    public function initContent()
    {
        parent::initContent();

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

        $this->context->smarty->assign([
            'catalogs' => $catalogs,
            'page_title' => 'Katalogy reklamních předmětů ke stažení',
            'page_description' => 'Stáhněte si naše katalogy reklamních předmětů nebo si vyžádejte fyzickou podobu.',
            'module_dir' => $this->module->getPathUri()
        ]);

        $this->setTemplate('module:katalogy/views/templates/front/katalogy.tpl');
    }

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
            $this->errors[] = $this->module->l('Prosím vyplňte všechna povinná pole správně.');
            return;
        }

        // Get catalog info
        $catalog = Katalog::getById($catalog_id);
        if (!$catalog) {
            $this->errors[] = $this->module->l('Katalog nebyl nalezen.');
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
            $this->context->language->id,
            'contact',
            $subject,
            ['message' => $email_content],
            $admin_email,
            null,
            $email,
            $name
        )) {
            $this->success[] = $this->module->l('Váš zájem byl odeslán. Brzy se vám ozveme.');
        } else {
            $this->errors[] = $this->module->l('Chyba při odesílání zprávy. Zkuste to prosím znovu.');
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        
        $breadcrumb['links'][] = [
            'title' => 'Katalogy',
            'url' => $this->context->link->getModuleLink('katalogy', 'seznam')
        ];

        return $breadcrumb;
    }

    public function getCanonicalURL()
    {
        return $this->context->link->getModuleLink('katalogy', 'seznam');
    }
}