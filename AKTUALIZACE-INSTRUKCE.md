# Aktualizace modulu Katalogy - Instrukce

## Provedené změny

### 1. Editovatelné texty v konfiguraci
- ✅ Přidána možnost editace úvodního textu stránky katalogů
- ✅ Přidána možnost editace názvů a textů u 3 informačních boxíků:
  - Stažení zdarma
  - Fyzická podoba  
  - Pravidelné aktualizace

### 2. Vylepšený formulář "Zájem o katalog"
- ✅ Pole "Společnost" je nyní povinné
- ✅ Přidáno nové povinné pole "Adresa pro zaslání"
- ✅ Texty nad textarea jsou zarovnány doprava
- ✅ Opravena pozice tlačítka pro zavření (křížek) - nyní je v pravém horním rohu

### 3. Přestylovaná patička kontaktní sekce
- ✅ Kompletně nový design s gradientem a stíny
- ✅ Tlačítko "Kontaktujte nás" zarovnáno doprava
- ✅ Responzivní design pro mobilní zařízení
- ✅ Vylepšené vizuální efekty (hover stavy, animace)

### 4. Opravené drag and drop řazení v administraci
- ✅ Opravena funkcionalita přetahování katalogů v administraci
- ✅ Správné ukládání pozic do databáze
- ✅ Vylepšené AJAX zpracování

### 5. Dodatečné opravy (druhá iterace)
- ✅ Opraveno zobrazení křížku pro zavření modalu (nyní viditelná ikona místo bílého čtverce)
- ✅ Opraveno zarovnání textů ve formuláři (všechny texty jsou nyní vlevo)
- ✅ Přidána editovatelná patička kontaktní sekce:
  - Editovatelný nadpis kontaktní sekce
  - Editovatelný text kontaktní sekce
  - Editovatelný text tlačítka
  - Editovatelný odkaz tlačítka
  - Volitelné zobrazení telefonu

## Instalace aktualizace

### Krok 1: Nahrání souborů
Nahrajte všechny aktualizované soubory na server přes FTP do složky `/modules/katalogy/`

### Krok 2: Aktualizace výchozích textů (pouze při první instalaci)
Pokud modul už máte nainstalovaný, spusťte jednou tento skript:
```
php update-default-texts.php
```

### Krok 3: Vyčištění cache
Vymažte cache PrestaShop v administraci:
- Pokročilé parametry → Výkon → Vymazat cache

### Krok 4: Konfigurace textů
1. Přejděte do administrace → Moduly → Katalogy → Konfigurovat
2. Upravte texty podle vašich potřeb:
   - Úvodní text
   - Názvy a texty informačních boxíků
   - Nadpis a text kontaktní sekce
   - Text a odkaz tlačítka
   - Telefon (volitelné)
3. Uložte změny

## Testování

### Frontend
- Zkontrolujte zobrazení katalogů na webu
- Otestujte formulář "Zájem o katalog" s novými povinnými poli
- Ověřte responzivní design na mobilních zařízeních

### Backend
- Otestujte drag and drop řazení katalogů v administraci
- Zkontrolujte ukládání pozic
- Ověřte editaci textů v konfiguraci

## Poznámky

- Všechny změny jsou zpětně kompatibilní
- Existující katalogy zůstávají beze změny
- Nové texty lze kdykoliv upravit v administraci
- Formulář nyní vyžaduje vyplnění společnosti a adresy

## Podpora

V případě problémů kontaktujte vývojáře s popisem chyby a kroky k reprodukci.
