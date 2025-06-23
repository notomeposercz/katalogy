<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <title>{$meta_title}</title>
    <meta name="description" content="{$meta_description}">
    <meta name="keywords" content="{$meta_keywords}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{$module_dir}views/css/katalogy.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .header {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .container {
            max-width: 1200px;
        }
        .page-title {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 20px;
            text-align: center;
        }
        .page-description {
            text-align: center;
            color: #666;
            font-size: 1.2em;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="page-title">{$page_title}</h1>
                    <p class="page-description">{$page_description}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        {if $success_message}
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {$success_message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        {/if}

        {if $error_message}
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {$error_message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        {/if}

        <div class="katalogy-page">
            {if $catalogs}
                <div class="katalogy-grid">
                    {foreach from=$catalogs item=catalog}
                        <div class="katalogy-item {if $catalog.is_new}katalogy-new{/if}">
                            {if $catalog.is_new}
                                <div class="new-badge">Nový</div>
                            {/if}
                            
                            <div class="katalogy-image">
                                {if $catalog.image_url}
                                    <img src="{$catalog.image_url}" alt="{$catalog.title|escape:'html':'UTF-8'}" />
                                {else}
                                    <div class="no-image">
                                        <i class="material-icons">folder</i>
                                    </div>
                                {/if}
                            </div>

                            <div class="katalogy-content">
                                <h3 class="katalogy-title">{$catalog.title|escape:'html':'UTF-8'}</h3>
                                
                                {if $catalog.description}
                                    <p class="katalogy-description">{$catalog.description|escape:'html':'UTF-8'}</p>
                                {/if}

                                <div class="katalogy-actions">
                                    {if $catalog.has_download}
                                        <a href="{$catalog.download_url}" 
                                           class="btn btn-primary katalogy-download" 
                                           target="_blank">
                                            <i class="material-icons">file_download</i>
                                            Stáhnout katalog
                                        </a>
                                    {/if}
                                    
                                    <button class="btn btn-secondary katalogy-interest" 
                                            data-catalog-id="{$catalog.id_katalog}"
                                            data-catalog-title="{$catalog.title|escape:'html':'UTF-8'}">
                                        <i class="material-icons">mail</i>
                                        Zájem o katalog
                                    </button>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            {else}
                <div class="alert alert-info">
                    <p>Momentálně nejsou k dispozici žádné katalogy.</p>
                </div>
            {/if}
        </div>
    </div>

    <!-- Interest Modal -->
    <div class="modal fade" id="interestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Zájem o katalog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <strong id="catalogTitle"></strong>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Jméno a příjmení *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Telefon</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        
                        <div class="mb-3">
                            <label for="company" class="form-label">Společnost</label>
                            <input type="text" class="form-control" id="company" name="company">
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Zpráva</label>
                            <textarea class="form-control" id="message" name="message" rows="3" placeholder="Volitelná zpráva..."></textarea>
                        </div>
                        
                        <input type="hidden" id="catalog_id" name="catalog_id" value="">
                        <input type="hidden" name="submitInterest" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn btn-primary">Odeslat žádost</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle interest buttons
            const interestButtons = document.querySelectorAll('.katalogy-interest');
            const modal = new bootstrap.Modal(document.getElementById('interestModal'));
            const catalogTitle = document.getElementById('catalogTitle');
            const catalogIdInput = document.getElementById('catalog_id');

            interestButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const catalogId = this.getAttribute('data-catalog-id');
                    const catalogTitleText = this.getAttribute('data-catalog-title');
                    
                    catalogTitle.textContent = 'Zájem o katalog: ' + catalogTitleText;
                    catalogIdInput.value = catalogId;
                    
                    modal.show();
                });
            });
        });
    </script>
</body>
</html>