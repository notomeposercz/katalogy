<?php
/**
 * Katalogy Module for PrestaShop 8.2.0
 * Manages downloadable catalogs with admin interface
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
        $this->version = '1.0.0';
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
            $this->registerHook('moduleRoutes') &&
            $this->createTables() &&
            $this->createTab() &&
            $this->addCustomRoute() &&
            Configuration::updateValue('KATALOGY_EMAIL', Configuration::get('PS_SHOP_EMAIL'));
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->dropTables() &&
            $this->removeTab() &&
            $this->removeCustomRoute() &&
            Configuration::deleteByName('KATALOGY_EMAIL');
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
            if (Validate::isEmail($email)) {
                Configuration::updateValue('KATALOGY_EMAIL', $email);
                $output .= $this->displayConfirmation($this->l('Nastavení bylo uloženo.'));
            } else {
                $output .= $this->displayError($this->l('Neplatná e-mailová adresa.'));
            }
        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
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

        return $helper->generateForm([$fields_form]);
    }

    public function hookDisplayHeader()
    {
        if ($this->context->controller->php_self == 'seznam') {
            $this->context->controller->addCSS($this->_path . 'views/css/katalogy.css');
            $this->context->controller->addJS($this->_path . 'views/js/katalogy.js');
        }
    }

    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller->php_self == 'seznam') {
            $this->context->controller->addCSS($this->_path . 'views/css/katalogy.css');
            $this->context->controller->addJS($this->_path . 'views/js/katalogy.js');
        }
    }

    public function hookModuleRoutes()
    {
        return [
            'module-katalogy-seznam' => [
                'controller' => 'seznam',
                'rule' => 'katalogy-reklamnich-predmetu-ke-stazeni',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => 'katalogy',
                    'controller' => 'seznam'
                ]
            ]
        ];
    }

    private function addCustomRoute()
    {
        // Custom route will be handled by hookModuleRoutes
        return true;
    }

    private function removeCustomRoute()
    {
        // Route cleanup if needed
        return true;
    }

    public function autoload($classname)
    {
        if ($classname === 'Katalog') {
            require_once(_PS_MODULE_DIR_ . $this->name . '/classes/Katalog.php');
        }
    }
}