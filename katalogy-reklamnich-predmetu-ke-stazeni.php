<?php
/**
 * Standalone Katalogy Page
 * Place this file in the root directory of PrestaShop
 */

// Include PrestaShop config
require_once(dirname(__FILE__).'/config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');

// Include Katalog class
require_once(_PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php');

// Initialize context
$context = Context::getContext();

// Handle interest form submission
if (Tools::isSubmit('submitInterest')) {
    $name = Tools::getValue('name');
    $email = Tools::getValue('email');
    $phone = Tools::getValue('phone');
    $company = Tools::getValue('company');
    $catalog_id = (int)Tools::getValue('catalog_id');
    $message = Tools::getValue('message');

    // Basic validation
    if (!empty($name) && !empty($email) && Validate::isEmail($email)) {
        // Get catalog info
        $catalog = Katalog::getById($catalog_id);
        if ($catalog) {
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
                $context->language->id,
                'contact',
                $subject,
                ['message' => $email_content],
                $admin_email,
                null,
                $email,
                $name
            )) {
                $success_message = 'Váš zájem byl odeslán. Brzy se vám ozveme.';
            } else {
                $error_message = 'Chyba při odesílání zprávy. Zkuste to prosím znovu.';
            }
        }
    } else {
        $error_message = 'Prosím vyplňte všechna povinná pole správně.';
    }
}

// Get all active catalogs
$catalogs = Katalog::getAllActive();

// Process catalogs data
foreach ($catalogs as &$catalog) {
    $katalog_obj = new Katalog($catalog['id_katalog']);
    $catalog['download_url'] = $katalog_obj->getDownloadUrl();
    $catalog['image_url'] = $katalog_obj->getImageUrl();
    $catalog['has_download'] = $katalog_obj->hasDownload();
}

// Assign variables to Smarty
$context->smarty->assign([
    'catalogs' => $catalogs,
    'page_title' => 'Katalogy reklamních předmětů ke stažení',
    'page_description' => 'Stáhněte si naše katalogy reklamních předmětů nebo si vyžádejte fyzickou podobu.',
    'module_dir' => _MODULE_DIR_ . 'katalogy/',
    'success_message' => isset($success_message) ? $success_message : '',
    'error_message' => isset($error_message) ? $error_message : '',
    'base_dir' => _PS_BASE_URL_,
    'css_dir' => _THEME_CSS_DIR_,
    'js_dir' => _THEME_JS_DIR_,
    'img_dir' => _THEME_IMG_DIR_
]);

// Set page meta
$context->smarty->assign([
    'meta_title' => 'Katalogy reklamních předmětů ke stažení',
    'meta_description' => 'Stáhněte si naše katalogy reklamních předmětů nebo si vyžádejte fyzickou podobu.',
    'meta_keywords' => 'katalogy, reklamní předměty, stažení'
]);

// Get shop information
$context->smarty->assign([
    'shop' => [
        'name' => Configuration::get('PS_SHOP_NAME'),
        'logo' => _PS_IMG_ . Configuration::get('PS_LOGO')
    ]
]);

// Display the page
$context->smarty->display(_PS_MODULE_DIR_ . 'katalogy/views/templates/front/standalone.tpl');
?>