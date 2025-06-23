# Instalace modulu Katalogy pro PrestaShop 8.2.0

## Popis řešení

Modul vytváří **samostatnou PHP stránku** v kořenovém adresáři s přesnou URL `katalogy-reklamnich-predmetu-ke-stazeni.php`. Toto řešení nevyžaduje žádné úpravy systémových souborů PrestaShop.

## Struktura souborů

Vytvořte následující strukturu souborů:

```
/ (kořenový adresář PrestaShop)
└── katalogy-reklamnich-predmetu-ke-stazeni.php  (hlavní stránka)

modules/katalogy/
├── katalogy.php                           (hlavní soubor modulu)
├── config.xml                             (konfigurace modulu)
├── index.php                              (bezpečnostní soubor)
├── classes/
│   └── Katalog.php                        (model třídy)
├── controllers/
│   └── admin/
│       └── AdminKatalogyController.php    (admin kontroler)
├── views/
│   ├── templates/
│   │   └── front/
│   │       ├── katalogy.tpl               (původní template)
│   │       ├── katalogy_content.tpl       (template pro CMS)
│   │       └── standalone.tpl             (template pro standalone)
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

1. **Nahrajte soubory modulu**: Zkopírujte soubory modulu do `/modules/katalogy/`

2. **Nahrajte hlavní stránku**: Zkopírujte soubor `katalogy-reklamnich-predmetu-ke-stazeni.php` do **kořenového adresáře** PrestaShop (vedle index.php)

3. **Nahrajte override**: Zkopírujte `/override/classes/PrestaShopAutoload.php`

4. **Nastavte oprávnění**: Ujistěte se, že následující adresáře mají oprávnění 755 nebo 777:
   - `/modules/katalogy/views/img/katalogy/`
   - `/modules/katalogy/files/`

5. **Smazání cache**: Smažte cache PrestaShop:
   - `/var/cache/`
   - `/cache/`

6. **Instalace modulu**:
   - Přihlaste se do administrace PrestaShop
   - Jděte do **Moduly → Správce modulů**
   - Najděte modul "Katalogy" a klikněte na **Instalovat**

7. **Konfigurace**:
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

- **URL**: `yourshop.com/katalogy-reklamnich-predmetu-ke-stazeni.php`
- Samostatná stránka s kompletním designem
- Uživatelé mohou stahovat katalogy nebo projevit zájem o fyzickou podobu
- Formulář zájmu se odesílá na e-mail nastavený v konfiguraci

## Jak to funguje

1. **Samostatný PHP soubor** načte PrestaShop konfiguraci
2. **Použije PrestaShop třídy** pro databázi a e-mail
3. **Zobrazí katalogy** pomocí Smarty template
4. **Zpracuje formuláře** na stejné stránce
5. **Kompletní design** s Bootstrap 5

## Výhody tohoto řešení

- ✅ **Nezávislé na PrestaShop routing**
- ✅ **Žádné úpravy systémových souborů**
- ✅ **Přesná URL** jak požadujete
- ✅ **Moderní Bootstrap 5 design**
- ✅ **Responzivní zobrazení**
- ✅ **Funkční formuláře a e-maily**

## Řešení problémů

### Stránka se nezobrazuje
1. Zkontrolujte, že soubor `katalogy-reklamnich-predmetu-ke-stazeni.php` je v kořenovém adresáři
2. Zkontrolujte oprávnění souboru (644)
3. Zkontrolujte, že je modul nainstalovaný

### Chyba "Class not found"
1. Zkontrolujte, že override soubor `PrestaShopAutoload.php` je správně umístěn
2. Smažte cache PrestaShop
3. Zkontrolujte oprávnění souborů

### Formulář nefunguje
1. Zkontrolujte nastavení e-mailu v konfiguraci modulu
2. Zkontrolujte PHP error log
3. Zkontrolujte PrestaShop e-mail nastavení

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
- Existenci hlavního souboru v kořenovém adresáři