<?php
/**
 * Katalogy Module for PrestaShop 8.2.0 - CMS Integration
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Katalogy extends Module
{
    public function __construct()
    {
        $this->name = 'katalogy';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'Miroslav Urbánek';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Katalogy');
        $this->description = $this->l('Modul pro správu katalogů ke stažení');
        $this->confirmUninstall = $this->l('Opravdu chcete odinstalovat modul Katalogy?');
    }

    public function install()
    {
        include_once(_PS_MODULE_DIR_ . $this->name . '/classes/Katalog.php');
        
        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('displayKatalogyContent') &&
            $this->registerHook('displayCMSContent') &&
            $this->createTables() &&
            $this->createTab() &&
            Configuration::updateValue('KATALOGY_EMAIL', Configuration::get('PS_SHOP_EMAIL')) &&
            Configuration::updateValue('KATALOGY_CMS_ID', 0);
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->dropTables() &&
            $this->removeTab() &&
            Configuration::deleteByName('KATALOGY_EMAIL') &&
            Configuration::deleteByName('KATALOGY_CMS_ID');
    }

    private function createTables()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'katalogy` (
            `id_katalog` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` text,
            `image` varchar(255),
            `file_url` varchar(500),
            `file_path` varchar(500),
            `is_new` tinyint(1) DEFAULT 0,
            `position` int(11) DEFAULT 0,
            `active` tinyint(1) DEFAULT 1,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_katalog`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    private function dropTables()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'katalogy`';
        return Db::getInstance()->execute($sql);
    }

    private function createTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminKatalogy';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Katalogy';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminCatalog');
        $tab->module = $this->name;
        return $tab->add();
    }

    private function removeTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminKatalogy');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    public function getContent()
    {
        $output = '';
        
        if (Tools::isSubmit('submitKatalogyConfig')) {
            $email = Tools::getValue('KATALOGY_EMAIL');
            $cms_id = (int)Tools::getValue('KATALOGY_CMS_ID');
            
            if (Validate::isEmail($email)) {
                Configuration::updateValue('KATALOGY_EMAIL', $email);
                Configuration::updateValue('KATALOGY_CMS_ID', $cms_id);
                $output .= $this->displayConfirmation($this->l('Nastavení bylo uloženo.'));
            } else {
                $output .= $this->displayError($this->l('Neplatná e-mailová adresa.'));
            }
        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        // Získání seznamu CMS stránek
        $cms_pages = CMS::getCMSPages($this->context->language->id);
        $cms_options = [['id' => 0, 'name' => $this->l('-- Vyberte CMS stránku --')]];
        foreach ($cms_pages as $page) {
            $cms_options[] = ['id' => $page['id_cms'], 'name' => $page['meta_title']];
        }

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Nastavení modulu Katalogy'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('E-mail pro formulář'),
                        'name' => 'KATALOGY_EMAIL',
                        'required' => true,
                        'desc' => $this->l('E-mail na který budou chodit zprávy z formuláře "Zájem o katalog"')
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('CMS stránka pro katalogy'),
                        'name' => 'KATALOGY_CMS_ID',
                        'options' => [
                            'query' => $cms_options,
                            'id' => 'id',
                            'name' => 'name'
                        ],
                        'desc' => $this->l('Vyberte CMS stránku kde se budou zobrazovat katalogy')
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Uložit'),
                    'class' => 'btn btn-default pull-right'
                ]
            ]
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submitKatalogyConfig';

        $helper->fields_value['KATALOGY_EMAIL'] = Configuration::get('KATALOGY_EMAIL');
        $helper->fields_value['KATALOGY_CMS_ID'] = Configuration::get('KATALOGY_CMS_ID');

        return $helper->generateForm([$fields_form]);
    }

    public function hookDisplayHeader()
{
    $cms_id = (int)Configuration::get('KATALOGY_CMS_ID');
    
    // Debug info - můžete smazat po opravě
    if ($this->context->controller->php_self == 'cms') {
        $current_cms_id = (int)Tools::getValue('id_cms');
        // error_log("Katalogy Debug: Current CMS ID: $current_cms_id, Config CMS ID: $cms_id");
    }
    
    if ($this->context->controller->php_self == 'cms' && 
        (int)Tools::getValue('id_cms') == $cms_id && $cms_id > 0) {
        
        // Přidání Material Icons
        $this->context->controller->addCSS('https://fonts.googleapis.com/icon?family=Material+Icons', 'all', null, false);
        
        // Přidání vlastních stylů
        $this->context->controller->addCSS($this->_path . 'views/css/katalogy.css');
        $this->context->controller->addJS($this->_path . 'views/js/katalogy.js');
    }
}

    public function hookActionFrontControllerSetMedia()
    {
        $cms_id = (int)Configuration::get('KATALOGY_CMS_ID');
        
        if ($this->context->controller->php_self == 'cms' && 
            (int)Tools::getValue('id_cms') == $cms_id && $cms_id > 0) {
            $this->context->controller->addCSS($this->_path . 'views/css/katalogy.css');
            $this->context->controller->addJS($this->_path . 'views/js/katalogy.js');
        }
    }

    public function hookDisplayKatalogyContent($params)
    {
        return $this->renderKatalogyContent();
    }

    public function hookDisplayCMSContent($params)
    {
        $cms_id = (int)Configuration::get('KATALOGY_CMS_ID');
        
        if ((int)Tools::getValue('id_cms') == $cms_id && $cms_id > 0) {
            return $this->renderKatalogyContent();
        }
        return '';
    }

    private function renderKatalogyContent()
    {
        // Handle interest form submission
        if (Tools::isSubmit('submitInterest')) {
            $this->processInterestForm();
        }

        // Include Katalog class
        require_once(_PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php');

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
            'module_dir' => $this->getPathUri(),
            'success_message' => isset($_SESSION['katalogy_success']) ? $_SESSION['katalogy_success'] : '',
            'error_message' => isset($_SESSION['katalogy_error']) ? $_SESSION['katalogy_error'] : ''
        ]);

        // Clear messages after display
        unset($_SESSION['katalogy_success'], $_SESSION['katalogy_error']);

        return $this->display(__FILE__, 'views/templates/front/katalogy_content.tpl');
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
            $_SESSION['katalogy_error'] = $this->l('Prosím vyplňte všechna povinná pole správně.');
            return;
        }

        // Include Katalog class
        require_once(_PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php');

        // Get catalog info
        $catalog = Katalog::getById($catalog_id);
        if (!$catalog) {
            $_SESSION['katalogy_error'] = $this->l('Katalog nebyl nalezen.');
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
            $_SESSION['katalogy_success'] = $this->l('Váš zájem byl odeslán. Brzy se vám ozveme.');
        } else {
            $_SESSION['katalogy_error'] = $this->l('Chyba při odesílání zprávy. Zkuste to prosím znovu.');
        }
    }
}