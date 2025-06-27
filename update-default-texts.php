<?php
/**
 * Script pro aktualizaci výchozích textů v modulu Katalogy
 * Spustit pouze jednou po aktualizaci modulu
 */

// Include PrestaShop configuration
require_once(dirname(__FILE__) . '/../../config/config.inc.php');

// Check if we're in PrestaShop context
if (!defined('_PS_VERSION_')) {
    die('This script can only be run from PrestaShop context');
}

echo "Aktualizace výchozích textů modulu Katalogy...\n";

// Set default texts if they don't exist
$defaultTexts = [
    'KATALOGY_INTRO_TEXT' => 'Stáhněte si naše katalogy reklamních předmětů nebo si vyžádejte fyzickou podobu. Více než 1000 produktů pro vaše podnikání.',
    'KATALOGY_BOX1_TITLE' => 'Stažení zdarma',
    'KATALOGY_BOX1_TEXT' => 'Všechny katalogy si můžete stáhnout zcela zdarma ve formátu PDF.',
    'KATALOGY_BOX2_TITLE' => 'Fyzická podoba',
    'KATALOGY_BOX2_TEXT' => 'Máte zájem o tištěný katalog? Rádi vám ho zašleme poštou.',
    'KATALOGY_BOX3_TITLE' => 'Pravidelné aktualizace',
    'KATALOGY_BOX3_TEXT' => 'Naše katalogy pravidelně aktualizujeme o nové produkty a ceny.',
    'KATALOGY_FOOTER_TITLE' => 'Potřebujete poradit s výběrem?',
    'KATALOGY_FOOTER_TEXT' => 'Naši odborníci vám rádi pomohou vybrat nejvhodnější reklamní předměty pro vaše potřeby. Kontaktujte nás pro individuální konzultaci.',
    'KATALOGY_FOOTER_BUTTON_TEXT' => 'Kontaktujte nás',
    'KATALOGY_FOOTER_BUTTON_URL' => '/kontakt',
    'KATALOGY_FOOTER_PHONE' => ''
];

foreach ($defaultTexts as $key => $value) {
    if (!Configuration::get($key)) {
        Configuration::updateValue($key, $value);
        echo "✓ Nastaveno: $key\n";
    } else {
        echo "- Již existuje: $key\n";
    }
}

echo "\nAktualizace dokončena!\n";
echo "Nyní můžete upravit texty v administraci modulu.\n";
