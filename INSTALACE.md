# Instalace modulu Katalogy pro PrestaShop 8.2.0

## Struktura souborů

Vytvořte následující strukturu souborů v adresáři `modules/katalogy/`:

```
katalogy/
├── katalogy.php                           (hlavní soubor modulu)
├── config.xml                             (konfigurace modulu)
├── index.php                              (bezpečnostní soubor)
├── classes/
│   └── Katalog.php                        (model třídy)
├── controllers/
│   ├── admin/
│   │   └── AdminKatalogyController.php    (admin kontroler)
│   └── front/
│       └── seznam.php                     (frontend kontroler)
├── views/
│   ├── templates/
│   │   └── front/
│   │       └── katalogy.tpl               (frontend template)
│   ├── css/
│   │   └── katalogy.css                   (styly)
│   ├── js/
│   │   └── katalogy.js                    (JavaScript)
│   └── img/
│       └── katalogy/                      (adresář pro obrázky)
├── files/                                 (adresář pro soubory katalogů)
└── override/
    └── classes/
        └── PrestaShopAutoload.php         (autoloader override)
```

## Postup instalace

1. **Nahrajte soubory**: Zkopírujte všechny soubory do příslušných adresářů:
   - Hlavní modul: `/modules/katalogy/`
   - Override: `/override/classes/PrestaShopAutoload.php`

2. **Nastavte oprávnění**: Ujistěte se, že následující adresáře mají oprávnění 755 nebo 777:
   - `/modules/katalogy/views/img/katalogy/`
   - `/modules/katalogy/files/`

3. **Smazání cache**: Smažte cache PrestaShop:
   - `/var/cache/`
   - `/cache/`

4. **Instalace modulu**:
   - Přihlaste se do administrace PrestaShop
   - Jděte do **Moduly → Správce modulů**
   - Najděte modul "Katalogy" a klikněte na **Instalovat**

5. **Konfigurace**:
   - Po instalaci jděte do **Moduly → Správce modulů**
   - Najděte modul "Katalogy" a klikněte na **Konfigurovat**
   - Nastavte e-mailovou adresu pro příjem formulářů

## Použití

### Administrace

1. **Přidání katalogu**:
   - Jděte do **Katalog → Katalogy**
   - Klikněte na **Přidat nový**
   - Vyplňte údaje katalogu
   - Nahrajte obrázek a soubor nebo zadejte URL

2. **Správa pořadí**:
   - V seznamu katalogů můžete měnit pořadí drag & drop
   - Nové katalogy se automaticky řadí jako první

### Frontend

- **URL**: `yourshop.com/katalogy-reklamnich-predmetu-ke-stazeni`
- Uživatelé mohou stahovat katalogy nebo projevit zájem o fyzickou podobu
- Formulář zájmu se odesílá na e-mail nastavený v konfiguraci

## Řešení problémů

### Chyba "Class not found"
1. Zkontrolujte, že override soubor `PrestaShopAutoload.php` je správně umístěn
2. Smažte cache PrestaShop
3. Zkontrolujte oprávnění souborů

### URL nefunguje
1. Zkontrolujte, že jsou aktivní friendly URLs
2. Smažte cache
3. Regenerujte .htaccess v **Předvolby → SEO a URL**

## Možná rozšíření

- Vícejazyčnost katalogů
- Kategorie katalogů
- Statistiky stažení
- Export seznamu zájemců
- Integrace s newsletterem

## Požadavky

- PrestaShop 8.0.0 nebo novější
- PHP 7.4 nebo novější
- MySQL 5.7 nebo novější

## Podpora

V případě problémů zkontrolujte:
- Oprávnění souborů a adresářů
- PHP error log
- PrestaShop log v `var/logs/`
- Override je správně umístěn