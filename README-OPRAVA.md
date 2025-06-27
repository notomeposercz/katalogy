# Oprava modulu Katalogy - Návod

## Problém
Modul katalogy se nezobrazuje na frontend stránce, i když je nainstalovaný a obsahuje data v databázi.

## Řešení

### 1. Nahrajte aktualizované soubory na server
Nahrajte všechny soubory z lokálního projektu na server přes FTP.

### 2. Spusťte diagnostické skripty

#### A) Základní diagnostika (DOPORUČENO)
Nahrajte soubor `debug-katalogy-root.php` do **root adresáře** PrestaShop (vedle index.php) a otevřete:
`https://czimg-dev1.www2.peterman.cz/debug-katalogy-root.php`

#### B) Alternativní diagnostika
Pokud máte soubory v modules/katalogy/, otevřete:
`https://czimg-dev1.www2.peterman.cz/modules/katalogy/debug-katalogy-2.php`

#### C) Rychlý test s UI
`https://czimg-dev1.www2.peterman.cz/modules/katalogy/quick-test.php`

#### D) Test CMS hooks (pro problém s nezobrazováním)
`https://czimg-dev1.www2.peterman.cz/debug-cms-hooks.php`

#### E) Test shortcode funkčnosti
`https://czimg-dev1.www2.peterman.cz/test-shortcode.php`

Tyto skripty zkontrolují:
- Existenci modulu a jeho aktivaci
- Databázové tabulky a data
- Registraci hooks
- Konfiguraci
- Funkčnost shortcode

### 3. Oprava hooks (pokud je potřeba)
Pokud diagnostika ukáže problémy s hooks, spusťte:
`https://czimg-dev1.www2.peterman.cz/modules/katalogy/fix-hooks.php`

Tento skript:
- Zkontroluje a vytvoří chybějící hooks
- Zaregistruje modul na všechny potřebné hooks
- Opraví konfiguraci

### 4. Přidání testovacích dat (pokud je databáze prázdná)
Pokud v databázi nejsou žádné katalogy, spusťte:
`https://czimg-dev1.www2.peterman.cz/modules/katalogy/add-test-catalogs.php`

Tento skript přidá 4 testovací katalogy.

### 5. Test hooks
Pro přímé testování hooks spusťte:
`https://czimg-dev1.www2.peterman.cz/modules/katalogy/test-hook.php`

## Použití v CMS stránce

### ⭐ Varianta 1: Shortcode (DOPORUČENO)
Do obsahu CMS stránky vložte:
```
[katalogy]
```
Pro kompletní obsah s úvodním textem, nebo:
```
[katalogy-simple]
```
Pro pouze seznam katalogů.

**Po vložení shortcode:**
1. Uložte CMS stránku
2. Spusťte: `https://czimg-dev1.www2.peterman.cz/clear-cache.php`
3. Obnovte CMS stránku

### Varianta 2: Hook (alternativa)
Do CMS stránky vložte:
```
{hook h='displayKatalogyContent'}
```
nebo
```
{hook h='displayKatalogySimple'}
```

## Automatická detekce CMS stránky

Modul nyní automaticky detekuje CMS stránku podle:
- URL obsahující "katalog" nebo "catalog"
- Názvu stránky obsahujícího "katalog"

Pokud je stránka detekována, automaticky se načtou CSS a JS styly.

## Správa katalogů

Katalogy spravujte v administraci PrestaShop:
`Katalog > Katalogy`

Můžete:
- Přidávat nové katalogy
- Nahrávat obrázky a soubory
- Nastavovat pozici a aktivitu
- Označovat jako "nové"

## Řešení problémů

### Hook se nezobrazuje
1. Spusťte `fix-hooks.php`
2. Zkontrolujte, že modul je aktivní
3. Ověřte syntax v CMS stránce

### Chybí styly
1. Zkontrolujte, že CSS soubor existuje: `/modules/katalogy/views/css/katalogy.css`
2. Ověřte automatickou detekci CMS stránky
3. Manuálně nastavte KATALOGY_CMS_ID v konfiguraci modulu

### Formulář nefunguje
1. Zkontrolujte nastavení emailu v konfiguraci modulu
2. Ověřte, že JS soubor se načítá: `/modules/katalogy/views/js/katalogy.js`

## Kontakt
Pro další problémy použijte diagnostické skripty a pošlete výsledky.
