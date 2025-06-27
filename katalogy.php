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
            $this->registerHook('displayKatalogySimple') &&
            $this->registerHook('displayCMSContent') &&
            $this->registerHook('displayRightColumn') &&
            $this->registerHook('displayLeftColumn') &&
            $this->registerHook('displayTop') &&
            $this->registerHook('displayBeforeBodyClosingTag') &&
            $this->createTables() &&
            $this->createTab() &&
            Configuration::updateValue('KATALOGY_EMAIL', Configuration::get('PS_SHOP_EMAIL')) &&
            Configuration::updateValue('KATALOGY_CMS_ID', 0) &&
            $this->createDefaultTexts() &&
            $this->fixPositions();
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->dropTables() &&
            $this->removeTab() &&
            Configuration::deleteByName('KATALOGY_EMAIL') &&
            Configuration::deleteByName('KATALOGY_CMS_ID') &&
            $this->deleteConfigurationTexts();
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

    private function createDefaultTexts()
    {
        Configuration::updateValue('KATALOGY_INTRO_TEXT', 'Stáhněte si naše katalogy reklamních předmětů nebo si vyžádejte fyzickou podobu. Více než 1000 produktů pro vaše podnikání.');
        Configuration::updateValue('KATALOGY_BOX1_TITLE', 'Stažení zdarma');
        Configuration::updateValue('KATALOGY_BOX1_TEXT', 'Všechny katalogy si můžete stáhnout zcela zdarma ve formátu PDF.');
        Configuration::updateValue('KATALOGY_BOX2_TITLE', 'Fyzická podoba');
        Configuration::updateValue('KATALOGY_BOX2_TEXT', 'Máte zájem o tištěný katalog? Rádi vám ho zašleme poštou.');
        Configuration::updateValue('KATALOGY_BOX3_TITLE', 'Pravidelné aktualizace');
        Configuration::updateValue('KATALOGY_BOX3_TEXT', 'Naše katalogy pravidelně aktualizujeme o nové produkty a ceny.');
        Configuration::updateValue('KATALOGY_FOOTER_TITLE', 'Potřebujete poradit s výběrem?');
        Configuration::updateValue('KATALOGY_FOOTER_TEXT', 'Naši odborníci vám rádi pomohou vybrat nejvhodnější reklamní předměty pro vaše potřeby. Kontaktujte nás pro individuální konzultaci.');
        Configuration::updateValue('KATALOGY_FOOTER_BUTTON_TEXT', 'Kontaktujte nás');
        Configuration::updateValue('KATALOGY_FOOTER_BUTTON_URL', '/kontakt');
        Configuration::updateValue('KATALOGY_FOOTER_PHONE', '');
        return true;
    }

    private function fixPositions()
    {
        include_once(_PS_MODULE_DIR_ . $this->name . '/classes/Katalog.php');
        Katalog::fixDuplicatePositions();
        return true;
    }

    private function deleteConfigurationTexts()
    {
        Configuration::deleteByName('KATALOGY_INTRO_TEXT');
        Configuration::deleteByName('KATALOGY_BOX1_TITLE');
        Configuration::deleteByName('KATALOGY_BOX1_TEXT');
        Configuration::deleteByName('KATALOGY_BOX2_TITLE');
        Configuration::deleteByName('KATALOGY_BOX2_TEXT');
        Configuration::deleteByName('KATALOGY_BOX3_TITLE');
        Configuration::deleteByName('KATALOGY_BOX3_TEXT');
        Configuration::deleteByName('KATALOGY_FOOTER_TITLE');
        Configuration::deleteByName('KATALOGY_FOOTER_TEXT');
        Configuration::deleteByName('KATALOGY_FOOTER_BUTTON_TEXT');
        Configuration::deleteByName('KATALOGY_FOOTER_BUTTON_URL');
        Configuration::deleteByName('KATALOGY_FOOTER_PHONE');
        return true;
    }

    public function getContent()
    {
        $output = '';
        
        if (Tools::isSubmit('submitKatalogyConfig')) {
            $email = Tools::getValue('KATALOGY_EMAIL');
            $cms_id = (int)Tools::getValue('KATALOGY_CMS_ID');
            $intro_text = Tools::getValue('KATALOGY_INTRO_TEXT');
            $box1_title = Tools::getValue('KATALOGY_BOX1_TITLE');
            $box1_text = Tools::getValue('KATALOGY_BOX1_TEXT');
            $box2_title = Tools::getValue('KATALOGY_BOX2_TITLE');
            $box2_text = Tools::getValue('KATALOGY_BOX2_TEXT');
            $box3_title = Tools::getValue('KATALOGY_BOX3_TITLE');
            $box3_text = Tools::getValue('KATALOGY_BOX3_TEXT');
            $footer_title = Tools::getValue('KATALOGY_FOOTER_TITLE');
            $footer_text = Tools::getValue('KATALOGY_FOOTER_TEXT');
            $footer_button_text = Tools::getValue('KATALOGY_FOOTER_BUTTON_TEXT');
            $footer_button_url = Tools::getValue('KATALOGY_FOOTER_BUTTON_URL');
            $footer_phone = Tools::getValue('KATALOGY_FOOTER_PHONE');

            if (Validate::isEmail($email)) {
                Configuration::updateValue('KATALOGY_EMAIL', $email);
                Configuration::updateValue('KATALOGY_CMS_ID', $cms_id);
                Configuration::updateValue('KATALOGY_INTRO_TEXT', $intro_text);
                Configuration::updateValue('KATALOGY_BOX1_TITLE', $box1_title);
                Configuration::updateValue('KATALOGY_BOX1_TEXT', $box1_text);
                Configuration::updateValue('KATALOGY_BOX2_TITLE', $box2_title);
                Configuration::updateValue('KATALOGY_BOX2_TEXT', $box2_text);
                Configuration::updateValue('KATALOGY_BOX3_TITLE', $box3_title);
                Configuration::updateValue('KATALOGY_BOX3_TEXT', $box3_text);
                Configuration::updateValue('KATALOGY_FOOTER_TITLE', $footer_title);
                Configuration::updateValue('KATALOGY_FOOTER_TEXT', $footer_text);
                Configuration::updateValue('KATALOGY_FOOTER_BUTTON_TEXT', $footer_button_text);
                Configuration::updateValue('KATALOGY_FOOTER_BUTTON_URL', $footer_button_url);
                Configuration::updateValue('KATALOGY_FOOTER_PHONE', $footer_phone);
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
        $cms_options = [['id' => 0, 'name' => $this->l('-- Automatická detekce --')]];
        foreach ($cms_pages as $page) {
            $cms_options[] = ['id' => $page['id_cms'], 'name' => $page['meta_title']];
        }

        // Informace o aktuálně detekované stránce
        $current_cms_id = (int)Configuration::get('KATALOGY_CMS_ID');
        $detected_info = '';
        if ($current_cms_id > 0) {
            $cms = new CMS($current_cms_id, $this->context->language->id);
            if (Validate::isLoadedObject($cms)) {
                $detected_info = $this->l('Aktuálně detekována: ') . $cms->meta_title . ' (ID: ' . $current_cms_id . ')';
            }
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
                        'desc' => $this->l('Vyberte CMS stránku kde se budou zobrazovat katalogy. Při automatické detekci se hledá stránka s "katalog" v názvu nebo URL.') .
                                 ($detected_info ? '<br><strong>' . $detected_info . '</strong>' : '')
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Úvodní text'),
                        'name' => 'KATALOGY_INTRO_TEXT',
                        'rows' => 3,
                        'desc' => $this->l('Text zobrazený v úvodu stránky katalogů')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Název 1. boxíku'),
                        'name' => 'KATALOGY_BOX1_TITLE',
                        'desc' => $this->l('Název prvního informačního boxíku (výchozí: Stažení zdarma)')
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Text 1. boxíku'),
                        'name' => 'KATALOGY_BOX1_TEXT',
                        'rows' => 2,
                        'desc' => $this->l('Text prvního informačního boxíku')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Název 2. boxíku'),
                        'name' => 'KATALOGY_BOX2_TITLE',
                        'desc' => $this->l('Název druhého informačního boxíku (výchozí: Fyzická podoba)')
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Text 2. boxíku'),
                        'name' => 'KATALOGY_BOX2_TEXT',
                        'rows' => 2,
                        'desc' => $this->l('Text druhého informačního boxíku')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Název 3. boxíku'),
                        'name' => 'KATALOGY_BOX3_TITLE',
                        'desc' => $this->l('Název třetího informačního boxíku (výchozí: Pravidelné aktualizace)')
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Text 3. boxíku'),
                        'name' => 'KATALOGY_BOX3_TEXT',
                        'rows' => 2,
                        'desc' => $this->l('Text třetího informačního boxíku')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Nadpis kontaktní sekce'),
                        'name' => 'KATALOGY_FOOTER_TITLE',
                        'desc' => $this->l('Nadpis v kontaktní sekci na konci stránky')
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Text kontaktní sekce'),
                        'name' => 'KATALOGY_FOOTER_TEXT',
                        'rows' => 3,
                        'desc' => $this->l('Popisný text v kontaktní sekci')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Text tlačítka'),
                        'name' => 'KATALOGY_FOOTER_BUTTON_TEXT',
                        'desc' => $this->l('Text zobrazený na tlačítku (např. "Kontaktujte nás")')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Odkaz tlačítka'),
                        'name' => 'KATALOGY_FOOTER_BUTTON_URL',
                        'desc' => $this->l('URL adresa kam má tlačítko odkazovat (např. /kontakt)')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Telefon (volitelné)'),
                        'name' => 'KATALOGY_FOOTER_PHONE',
                        'desc' => $this->l('Telefonní číslo zobrazené v kontaktní sekci (volitelné)')
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Uložit'),
                    'class' => 'btn btn-default pull-right'
                ]
            ]
        ];

        // Přidání informací o použití
        $usage_info = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Jak používat modul'),
                    'icon' => 'icon-info'
                ],
                'description' => '<div class="alert alert-info">' .
                    '<h4>' . $this->l('Způsoby zobrazení katalogů:') . '</h4>' .
                    '<p><strong>1. Automatické zobrazení:</strong> ' . $this->l('Modul automaticky detekuje CMS stránku s "katalog" v názvu a zobrazí kompletní obsah včetně úvodního textu.') . '</p>' .
                    '<p><strong>2. Ruční vložení do CMS:</strong> ' . $this->l('Vložte do obsahu CMS stránky:') . ' <code>{hook h=\'displayKatalogyContent\'}</code></p>' .
                    '<p><strong>3. Pouze katalogy:</strong> ' . $this->l('Pro zobrazení pouze katalogů bez úvodního textu:') . ' <code>{hook h=\'displayKatalogySimple\'}</code></p>' .
                    '</div>'
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
        $helper->fields_value['KATALOGY_INTRO_TEXT'] = Configuration::get('KATALOGY_INTRO_TEXT');
        $helper->fields_value['KATALOGY_BOX1_TITLE'] = Configuration::get('KATALOGY_BOX1_TITLE');
        $helper->fields_value['KATALOGY_BOX1_TEXT'] = Configuration::get('KATALOGY_BOX1_TEXT');
        $helper->fields_value['KATALOGY_BOX2_TITLE'] = Configuration::get('KATALOGY_BOX2_TITLE');
        $helper->fields_value['KATALOGY_BOX2_TEXT'] = Configuration::get('KATALOGY_BOX2_TEXT');
        $helper->fields_value['KATALOGY_BOX3_TITLE'] = Configuration::get('KATALOGY_BOX3_TITLE');
        $helper->fields_value['KATALOGY_BOX3_TEXT'] = Configuration::get('KATALOGY_BOX3_TEXT');
        $helper->fields_value['KATALOGY_FOOTER_TITLE'] = Configuration::get('KATALOGY_FOOTER_TITLE');
        $helper->fields_value['KATALOGY_FOOTER_TEXT'] = Configuration::get('KATALOGY_FOOTER_TEXT');
        $helper->fields_value['KATALOGY_FOOTER_BUTTON_TEXT'] = Configuration::get('KATALOGY_FOOTER_BUTTON_TEXT');
        $helper->fields_value['KATALOGY_FOOTER_BUTTON_URL'] = Configuration::get('KATALOGY_FOOTER_BUTTON_URL');
        $helper->fields_value['KATALOGY_FOOTER_PHONE'] = Configuration::get('KATALOGY_FOOTER_PHONE');

        return $helper->generateForm([$fields_form, $usage_info]);
    }

    public function hookDisplayHeader()
    {
        if ($this->context->controller->php_self == 'cms') {
            $current_cms_id = (int)Tools::getValue('id_cms');

            // Automatická detekce CMS stránky katalogů
            if ($this->isCatalogPage($current_cms_id)) {
                // Přidání Material Icons
                $this->context->controller->addCSS('https://fonts.googleapis.com/icon?family=Material+Icons', 'all', null, false);

                // Přidání vlastních stylů
                $this->context->controller->addCSS($this->_path . 'views/css/katalogy.css');
                $this->context->controller->addJS($this->_path . 'views/js/katalogy.js');
            }
        }
    }

    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller->php_self == 'cms') {
            $current_cms_id = (int)Tools::getValue('id_cms');

            if ($this->isCatalogPage($current_cms_id)) {
                $this->context->controller->addCSS($this->_path . 'views/css/katalogy.css');
                $this->context->controller->addJS($this->_path . 'views/js/katalogy.js');
            }
        }
    }

    public function hookDisplayKatalogyContent($params)
    {
        return $this->renderKatalogyContent();
    }

    public function hookDisplayKatalogySimple($params)
    {
        return $this->renderKatalogySimple();
    }

    public function hookDisplayCMSContent($params)
    {
        if ($this->context->controller->php_self == 'cms') {
            $current_cms_id = (int)Tools::getValue('id_cms');

            // Zkus načíst CMS obsah a zkontrolovat shortcode
            if ($current_cms_id > 0) {
                $cms = new CMS($current_cms_id, $this->context->language->id);
                if (Validate::isLoadedObject($cms)) {
                    // Pokud CMS obsahuje [katalogy] shortcode, vrať obsah
                    if (strpos($cms->content, '[katalogy]') !== false || strpos($cms->content, '[katalogy-simple]') !== false) {
                        return ''; // Nechej to na JavaScript zpracování
                    }
                }
            }

            // Fallback pro automatickou detekci
            if ($this->isCatalogPage($current_cms_id)) {
                return $this->renderKatalogyContent();
            }
        }
        return '';
    }

    public function hookDisplayRightColumn($params)
    {
        if ($this->context->controller->php_self == 'cms') {
            $current_cms_id = (int)Tools::getValue('id_cms');
            if ($this->isCatalogPage($current_cms_id)) {
                return $this->renderKatalogyContent();
            }
        }
        return '';
    }

    public function hookDisplayLeftColumn($params)
    {
        if ($this->context->controller->php_self == 'cms') {
            $current_cms_id = (int)Tools::getValue('id_cms');
            if ($this->isCatalogPage($current_cms_id)) {
                return $this->renderKatalogyContent();
            }
        }
        return '';
    }

    public function hookDisplayTop($params)
    {
        if ($this->context->controller->php_self == 'cms') {
            $current_cms_id = (int)Tools::getValue('id_cms');
            if ($this->isCatalogPage($current_cms_id)) {
                return $this->renderKatalogyContent();
            }
        }
        return '';
    }

    public function hookDisplayBeforeBodyClosingTag($params)
    {
        if ($this->context->controller->php_self == 'cms') {
            $current_cms_id = (int)Tools::getValue('id_cms');

            // Zpracování shortcode na jakékoli CMS stránce
            $script = $this->generateShortcodeScript();
            if ($script) {
                return $script;
            }

            // Fallback pro automatickou detekci katalogové stránky
            if ($this->isCatalogPage($current_cms_id)) {
                $katalogy_content = $this->renderKatalogyContent();
                $escaped_content = json_encode($katalogy_content);

                return "
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var cmsContent = document.querySelector('.cms-content, .page-cms, .rte, #content');
                    if (cmsContent && !cmsContent.querySelector('.katalogy-page')) {
                        var katalogyDiv = document.createElement('div');
                        katalogyDiv.innerHTML = $escaped_content;
                        cmsContent.appendChild(katalogyDiv);
                    }
                });
                </script>";
            }
        }
        return '';
    }

    public function hookFilterCmsContent($params)
    {
        if (isset($params['object']) && $params['object'] instanceof CMS) {
            $cms = $params['object'];
            $content = $params['content'];

            // Zpracování shortcode [katalogy]
            if (strpos($content, '[katalogy]') !== false) {
                $katalogy_content = $this->renderKatalogyContent();
                $content = str_replace('[katalogy]', $katalogy_content, $content);
            }

            // Zpracování shortcode [katalogy-simple]
            if (strpos($content, '[katalogy-simple]') !== false) {
                $katalogy_simple = $this->renderKatalogySimple();
                $content = str_replace('[katalogy-simple]', $katalogy_simple, $content);
            }

            return $content;
        }

        return $params['content'];
    }

    public function hookActionCMSPageDisplayed($params)
    {
        if (isset($params['object']) && $params['object'] instanceof CMS) {
            $cms_id = (int)$params['object']->id;

            if ($this->isCatalogPage($cms_id)) {
                // Přidání CSS a JS pro katalogy
                $this->context->controller->addCSS('https://fonts.googleapis.com/icon?family=Material+Icons', 'all', null, false);
                $this->context->controller->addCSS($this->_path . 'views/css/katalogy.css');
                $this->context->controller->addJS($this->_path . 'views/js/katalogy.js');
            }
        }
    }

    /**
     * Detekce zda je aktuální CMS stránka stránkou katalogů
     */
    private function isCatalogPage($cms_id)
    {
        // Nejdříve zkontroluj konfiguraci
        $configured_cms_id = (int)Configuration::get('KATALOGY_CMS_ID');
        if ($configured_cms_id > 0 && $cms_id == $configured_cms_id) {
            return true;
        }

        // Automatická detekce podle URL nebo názvu stránky
        if ($cms_id > 0) {
            $cms = new CMS($cms_id, $this->context->language->id);
            if (Validate::isLoadedObject($cms)) {
                // Kontrola podle link_rewrite (URL)
                if (strpos($cms->link_rewrite, 'katalogy') !== false ||
                    strpos($cms->link_rewrite, 'catalog') !== false) {
                    // Automaticky nastav konfiguraci
                    Configuration::updateValue('KATALOGY_CMS_ID', $cms_id);
                    return true;
                }

                // Kontrola podle meta_title
                if (strpos(strtolower($cms->meta_title), 'katalog') !== false) {
                    Configuration::updateValue('KATALOGY_CMS_ID', $cms_id);
                    return true;
                }
            }
        }

        return false;
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
            'error_message' => isset($_SESSION['katalogy_error']) ? $_SESSION['katalogy_error'] : '',
            'intro_text' => Configuration::get('KATALOGY_INTRO_TEXT'),
            'box1_title' => Configuration::get('KATALOGY_BOX1_TITLE'),
            'box1_text' => Configuration::get('KATALOGY_BOX1_TEXT'),
            'box2_title' => Configuration::get('KATALOGY_BOX2_TITLE'),
            'box2_text' => Configuration::get('KATALOGY_BOX2_TEXT'),
            'box3_title' => Configuration::get('KATALOGY_BOX3_TITLE'),
            'box3_text' => Configuration::get('KATALOGY_BOX3_TEXT'),
            'footer_title' => Configuration::get('KATALOGY_FOOTER_TITLE'),
            'footer_text' => Configuration::get('KATALOGY_FOOTER_TEXT'),
            'footer_button_text' => Configuration::get('KATALOGY_FOOTER_BUTTON_TEXT'),
            'footer_button_url' => Configuration::get('KATALOGY_FOOTER_BUTTON_URL'),
            'footer_phone' => Configuration::get('KATALOGY_FOOTER_PHONE')
        ]);

        // Clear messages after display
        unset($_SESSION['katalogy_success'], $_SESSION['katalogy_error']);

        return $this->display(__FILE__, 'views/templates/front/katalogy_content.tpl');
    }

    private function renderKatalogySimple()
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
            'error_message' => isset($_SESSION['katalogy_error']) ? $_SESSION['katalogy_error'] : '',
            'intro_text' => Configuration::get('KATALOGY_INTRO_TEXT'),
            'box1_title' => Configuration::get('KATALOGY_BOX1_TITLE'),
            'box1_text' => Configuration::get('KATALOGY_BOX1_TEXT'),
            'box2_title' => Configuration::get('KATALOGY_BOX2_TITLE'),
            'box2_text' => Configuration::get('KATALOGY_BOX2_TEXT'),
            'box3_title' => Configuration::get('KATALOGY_BOX3_TITLE'),
            'box3_text' => Configuration::get('KATALOGY_BOX3_TEXT'),
            'footer_title' => Configuration::get('KATALOGY_FOOTER_TITLE'),
            'footer_text' => Configuration::get('KATALOGY_FOOTER_TEXT'),
            'footer_button_text' => Configuration::get('KATALOGY_FOOTER_BUTTON_TEXT'),
            'footer_button_url' => Configuration::get('KATALOGY_FOOTER_BUTTON_URL'),
            'footer_phone' => Configuration::get('KATALOGY_FOOTER_PHONE')
        ]);

        // Clear messages after display
        unset($_SESSION['katalogy_success'], $_SESSION['katalogy_error']);

        return $this->display(__FILE__, 'views/templates/front/katalogy_simple.tpl');
    }

    /**
     * Generování JavaScript pro zpracování shortcode
     */
    private function generateShortcodeScript()
    {
        // Příprava obsahu pro shortcode
        $katalogy_full = $this->renderKatalogyContent();
        $katalogy_simple = $this->renderKatalogySimple();

        $escaped_full = json_encode($katalogy_full);
        $escaped_simple = json_encode($katalogy_simple);

        // CSS a JS cesty
        $css_path = $this->_path . 'views/css/katalogy.css';
        $js_path = $this->_path . 'views/js/katalogy.js';

        return "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Načtení CSS
            if (!document.querySelector('link[href*=\"katalogy.css\"]')) {
                var css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = '$css_path';
                document.head.appendChild(css);
            }

            // Načtení Material Icons
            if (!document.querySelector('link[href*=\"Material+Icons\"]')) {
                var icons = document.createElement('link');
                icons.rel = 'stylesheet';
                icons.href = 'https://fonts.googleapis.com/icon?family=Material+Icons';
                document.head.appendChild(icons);
            }

            // Zpracování [katalogy] shortcode
            var contentElements = document.querySelectorAll('.cms-content, .page-cms, .rte, #content, .content, .page-content');
            contentElements.forEach(function(element) {
                if (element.innerHTML.indexOf('[katalogy]') !== -1) {
                    console.log('Nalezen [katalogy] shortcode');
                    element.innerHTML = element.innerHTML.replace('[katalogy]', $escaped_full);
                }

                if (element.innerHTML.indexOf('[katalogy-simple]') !== -1) {
                    console.log('Nalezen [katalogy-simple] shortcode');
                    element.innerHTML = element.innerHTML.replace('[katalogy-simple]', $escaped_simple);
                }
            });

            // Inicializace JavaScript pro katalogy
            setTimeout(function() {
                initKatalogyInteraction();
            }, 200);
        });

        // Funkce pro inicializaci interakce s katalogy
        function initKatalogyInteraction() {
            // Handle interest buttons
            const interestButtons = document.querySelectorAll('.katalogy-interest');
            const modal = document.getElementById('interestModal');
            const catalogTitle = document.getElementById('catalogTitle');
            const catalogIdInput = document.getElementById('catalog_id');
            const form = document.getElementById('interestForm');

            if (!modal || !catalogTitle || !catalogIdInput || !form) {
                console.log('Katalogy: Modal elements not found');
                return;
            }

            interestButtons.forEach(button => {
                // Odstraň předchozí event listenery
                button.replaceWith(button.cloneNode(true));
            });

            // Znovu získej tlačítka po klonování
            const newInterestButtons = document.querySelectorAll('.katalogy-interest');

            newInterestButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const catalogId = this.getAttribute('data-catalog-id');
                    const catalogTitleText = this.getAttribute('data-catalog-title');

                    catalogTitle.textContent = 'Zájem o katalog: ' + catalogTitleText;
                    catalogIdInput.value = catalogId;

                    // Show modal - Bootstrap detection
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        // Bootstrap 5
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    } else if (typeof $ !== 'undefined' && $.fn.modal) {
                        // Bootstrap 4 with jQuery
                        $(modal).modal('show');
                    } else {
                        // Fallback for vanilla JS
                        modal.style.display = 'block';
                        modal.classList.add('show');
                        document.body.classList.add('modal-open');

                        // Create backdrop
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.id = 'katalogy-backdrop';
                        document.body.appendChild(backdrop);
                    }
                });
            });

            // Handle modal close
            const closeButtons = modal.querySelectorAll('[data-bs-dismiss=\"modal\"], [data-dismiss=\"modal\"], .close, .btn-close, .modal-close-btn');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    closeModal();
                });
            });

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            function closeModal() {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    // Bootstrap 5
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                } else if (typeof $ !== 'undefined' && $.fn.modal) {
                    // Bootstrap 4
                    $(modal).modal('hide');
                } else {
                    // Fallback
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');

                    const backdrop = document.getElementById('katalogy-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }

                // Reset form
                form.reset();
                catalogIdInput.value = '';
            }

            // Handle form submission
            form.addEventListener('submit', function(e) {
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const company = document.getElementById('company').value.trim();
                const address = document.getElementById('address').value.trim();

                if (!name || !email || !company || !address) {
                    e.preventDefault();
                    alert('Prosím vyplňte všechna povinná pole.');
                    return false;
                }

                // Basic email validation
                const emailRegex = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Prosím zadejte platnou e-mailovou adresu.');
                    return false;
                }

                // Show loading state
                const submitBtn = form.querySelector('button[type=\"submit\"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Odesílání...';
                submitBtn.disabled = true;

                // Submit form via AJAX to prevent page reload
                e.preventDefault();

                const formData = new FormData(form);
                formData.append('submitInterest', '1');

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // Close modal
                    closeModal();

                    // Show success message
                    showAlert('success', 'Váš zájem byl odeslán. Brzy se vám ozveme.');

                    // Reset form
                    form.reset();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Chyba při odesílání zprávy. Zkuste to prosím znovu.');
                })
                .finally(() => {
                    // Restore button
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            });

            // Function to show alert messages
            function showAlert(type, message) {
                // Remove existing alerts
                const existingAlerts = document.querySelectorAll('.katalogy-alert');
                existingAlerts.forEach(alert => alert.remove());

                // Create new alert
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' alert-dismissible fade show katalogy-alert';
                alertDiv.innerHTML = message + '<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>';

                // Insert at the beginning of katalogy content
                const katalogyPage = document.querySelector('.katalogy-page');
                if (katalogyPage) {
                    katalogyPage.insertBefore(alertDiv, katalogyPage.firstChild);

                    // Auto-hide after 5 seconds
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 5000);
                }
            }
        }
        </script>";
    }

    private function processInterestForm()
    {
        $name = Tools::getValue('name');
        $email = Tools::getValue('email');
        $phone = Tools::getValue('phone');
        $company = Tools::getValue('company');
        $address = Tools::getValue('address');
        $catalog_id = (int)Tools::getValue('catalog_id');
        $message = Tools::getValue('message');

        // Basic validation
        if (empty($name) || empty($email) || empty($company) || empty($address) || !Validate::isEmail($email)) {
            $_SESSION['katalogy_error'] = $this->l('Prosím vyplňte všechna povinná pole správně.');
            return;
        }

        // Prevence duplicitního odeslání
        if (!$this->preventDuplicateSubmission($catalog_id, $email)) {
            $_SESSION['katalogy_error'] = $this->l('Žádost již byla nedávno odeslána. Zkuste to prosím za chvíli.');
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

        // Create clean email content
        $email_content = $this->generateCleanEmailContent($catalog, $name, $email, $phone, $company, $address, $message);

        // Send email using PHP mail function for better control
        if ($this->sendCleanEmail($admin_email, $subject, $email_content, $email, $name)) {
            $_SESSION['katalogy_success'] = $this->l('Váš zájem byl odeslán. Brzy se vám ozveme.');
        } else {
            $_SESSION['katalogy_error'] = $this->l('Chyba při odesílání zprávy. Zkuste to prosím znovu.');
        }
    }

    /**
     * Generování čistého obsahu emailu
     */
    private function generateCleanEmailContent($catalog, $name, $email, $phone, $company, $address, $message)
    {
        $shop_name = Configuration::get('PS_SHOP_NAME');
        $shop_url = Context::getContext()->shop->getBaseURL(true);

        $content = "Nový zájem o katalog\n";
        $content .= str_repeat("=", 50) . "\n\n";

        $content .= "KATALOG:\n";
        $content .= $catalog['title'] . "\n\n";

        $content .= "KONTAKTNÍ ÚDAJE:\n";
        $content .= "Jméno: " . $name . "\n";
        $content .= "E-mail: " . $email . "\n";
        $content .= "Telefon: " . ($phone ?: 'Neuvedeno') . "\n";
        $content .= "Společnost: " . $company . "\n";
        $content .= "Adresa pro zaslání: " . $address . "\n\n";

        if ($message) {
            $content .= "ZPRÁVA:\n";
            $content .= $message . "\n\n";
        }

        $content .= str_repeat("-", 50) . "\n";
        $content .= "Odesláno ze stránek: " . $shop_name . "\n";
        $content .= "URL: " . $shop_url . "\n";
        $content .= "Datum: " . date('d.m.Y H:i:s') . "\n";

        return $content;
    }

    /**
     * Odeslání čistého emailu
     */
    private function sendCleanEmail($to, $subject, $content, $reply_to, $reply_name)
    {
        $headers = [];
        $headers[] = 'From: ' . Configuration::get('PS_SHOP_NAME') . ' <' . Configuration::get('PS_SHOP_EMAIL') . '>';
        $headers[] = 'Reply-To: ' . $reply_name . ' <' . $reply_to . '>';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: 8bit';

        return mail($to, $subject, $content, implode("\r\n", $headers));
    }

    /**
     * Prevence opakovaného odeslání formuláře
     */
    private function preventDuplicateSubmission($catalog_id, $email)
    {
        $session_key = 'katalogy_last_submission_' . $catalog_id . '_' . md5($email);
        $current_time = time();

        if (isset($_SESSION[$session_key])) {
            $last_submission = $_SESSION[$session_key];
            // Prevence opakování do 5 minut
            if (($current_time - $last_submission) < 300) {
                return false;
            }
        }

        $_SESSION[$session_key] = $current_time;
        return true;
    }
}