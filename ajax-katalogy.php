<?php
/**
 * AJAX endpoint pro načítání katalogů
 * Umístit do /modules/katalogy/
 */

// Načtení PrestaShop
$config_paths = [
    dirname(__FILE__).'/../../config/config.inc.php',
    dirname(__FILE__).'/../../../config/config.inc.php'
];

$config_loaded = false;
foreach ($config_paths as $config_path) {
    if (file_exists($config_path)) {
        require_once($config_path);
        $config_loaded = true;
        break;
    }
}

if (!$config_loaded) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'PrestaShop config nenalezen']);
    exit;
}

// Načtení modulu
require_once(_PS_MODULE_DIR_ . 'katalogy/classes/Katalog.php');

header('Content-Type: application/json');

try {
    $action = Tools::getValue('action');
    $type = Tools::getValue('type', 'full');
    
    if ($action !== 'get_katalogy') {
        throw new Exception('Neplatná akce');
    }
    
    // Zkontroluj, že modul je aktivní
    $module = Module::getInstanceByName('katalogy');
    if (!$module || !$module->active) {
        throw new Exception('Modul katalogy není aktivní');
    }
    
    // Handle interest form submission
    if (Tools::isSubmit('submitInterest')) {
        processInterestForm();
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
        'module_dir' => $module->getPathUri(),
        'success_message' => isset($_SESSION['katalogy_success']) ? $_SESSION['katalogy_success'] : '',
        'error_message' => isset($_SESSION['katalogy_error']) ? $_SESSION['katalogy_error'] : ''
    ]);

    // Clear messages after display
    unset($_SESSION['katalogy_success'], $_SESSION['katalogy_error']);

    // Vybrat správný template
    $template = ($type === 'simple') ? 'katalogy_simple.tpl' : 'katalogy_content.tpl';
    $content = $module->display(_PS_MODULE_DIR_ . 'katalogy/katalogy.php', 'views/templates/front/' . $template);
    
    echo json_encode([
        'success' => true,
        'content' => $content,
        'type' => $type
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function processInterestForm()
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
?>
