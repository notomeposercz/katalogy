/**
 * Frontend JavaScript pro zpracování katalogy shortcode
 * Umístit do /modules/katalogy/views/js/
 */

document.addEventListener('DOMContentLoaded', function() {
    // Najdi všechny [katalogy] shortcode v obsahu stránky
    var contentElements = document.querySelectorAll('.cms-content, .page-cms, .rte, #content, .content');
    
    contentElements.forEach(function(element) {
        var content = element.innerHTML;
        
        // Zpracování [katalogy] shortcode
        if (content.indexOf('[katalogy]') !== -1) {
            console.log('Nalezen [katalogy] shortcode');
            loadKatalogy(element, '[katalogy]', 'full');
        }
        
        // Zpracování [katalogy-simple] shortcode
        if (content.indexOf('[katalogy-simple]') !== -1) {
            console.log('Nalezen [katalogy-simple] shortcode');
            loadKatalogy(element, '[katalogy-simple]', 'simple');
        }
    });
});

function loadKatalogy(element, shortcode, type) {
    // Vytvoř placeholder
    var placeholder = document.createElement('div');
    placeholder.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="material-icons" style="font-size: 48px; color: #ccc;">hourglass_empty</i><br>Načítání katalogů...</div>';
    
    // Nahraď shortcode placeholder
    element.innerHTML = element.innerHTML.replace(shortcode, placeholder.outerHTML);
    
    // AJAX požadavek na načtení katalogů
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/modules/katalogy/ajax-katalogy.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Nahraď placeholder skutečným obsahem
                        element.innerHTML = element.innerHTML.replace(placeholder.outerHTML, response.content);
                        
                        // Načti CSS a JS
                        loadKatalogyAssets();
                        
                        // Inicializuj JavaScript pro katalogy
                        initKatalogyJS();
                    } else {
                        element.innerHTML = element.innerHTML.replace(placeholder.outerHTML, 
                            '<div class="alert alert-warning">Chyba při načítání katalogů: ' + response.message + '</div>');
                    }
                } catch (e) {
                    console.error('Chyba při parsování odpovědi:', e);
                    element.innerHTML = element.innerHTML.replace(placeholder.outerHTML, 
                        '<div class="alert alert-danger">Chyba při načítání katalogů.</div>');
                }
            } else {
                element.innerHTML = element.innerHTML.replace(placeholder.outerHTML, 
                    '<div class="alert alert-danger">Chyba při komunikaci se serverem.</div>');
            }
        }
    };
    
    xhr.send('action=get_katalogy&type=' + type);
}

function loadKatalogyAssets() {
    // Načti CSS
    if (!document.querySelector('link[href*="katalogy.css"]')) {
        var css = document.createElement('link');
        css.rel = 'stylesheet';
        css.href = '/modules/katalogy/views/css/katalogy.css';
        document.head.appendChild(css);
    }
    
    // Načti Material Icons
    if (!document.querySelector('link[href*="Material+Icons"]')) {
        var icons = document.createElement('link');
        icons.rel = 'stylesheet';
        icons.href = 'https://fonts.googleapis.com/icon?family=Material+Icons';
        document.head.appendChild(icons);
    }
}

function initKatalogyJS() {
    // Inicializace JavaScript pro katalogy (modaly, formuláře, atd.)
    // Tento kód by měl být stejný jako v katalogy.js
    
    // Handle interest buttons
    const interestButtons = document.querySelectorAll('.katalogy-interest');
    const modal = document.getElementById('interestModal');
    
    if (modal && interestButtons.length > 0) {
        interestButtons.forEach(button => {
            button.addEventListener('click', function() {
                const catalogId = this.getAttribute('data-catalog-id');
                const catalogTitle = this.getAttribute('data-catalog-title');
                
                const catalogTitleElement = document.getElementById('catalogTitle');
                const catalogIdInput = document.getElementById('catalog_id');
                
                if (catalogTitleElement && catalogIdInput) {
                    catalogTitleElement.textContent = 'Zájem o katalog: ' + catalogTitle;
                    catalogIdInput.value = catalogId;
                    
                    // Show modal
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    } else if (typeof $ !== 'undefined' && $.fn.modal) {
                        $(modal).modal('show');
                    }
                }
            });
        });
    }
}
