{* Kompletní stránka katalogů s úvodním obsahem *}

{if $success_message}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {$success_message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

{if $error_message}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {$error_message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<div class="katalogy-cms-page">
    <div class="row">
        <div class="col-12">
            <div class="katalogy-intro text-center mb-4">
                <p class="lead">{$intro_text|escape:'html':'UTF-8'}</p>
            </div>

            <div class="katalogy-info mb-4">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <i class="material-icons" style="font-size: 48px; color: #007bff;">file_download</i>
                        <h4>{$box1_title|escape:'html':'UTF-8'}</h4>
                        <p>{$box1_text|escape:'html':'UTF-8'}</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="material-icons" style="font-size: 48px; color: #28a745;">local_shipping</i>
                        <h4>{$box2_title|escape:'html':'UTF-8'}</h4>
                        <p>{$box2_text|escape:'html':'UTF-8'}</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="material-icons" style="font-size: 48px; color: #ffc107;">new_releases</i>
                        <h4>{$box3_title|escape:'html':'UTF-8'}</h4>
                        <p>{$box3_text|escape:'html':'UTF-8'}</p>
                    </div>
                </div>
            </div>

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
                                        <img src="{$catalog.image_url}" alt="{$catalog.title|escape:'html':'UTF-8'}" loading="lazy" />
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
                                               target="_blank"
                                               rel="noopener noreferrer">
                                                <i class="material-icons">file_download</i>
                                                Stáhnout katalog
                                            </a>
                                        {/if}

                                        <button class="btn btn-secondary katalogy-interest"
                                                data-catalog-id="{$catalog.id_katalog}"
                                                data-catalog-title="{$catalog.title|escape:'html':'UTF-8'}"
                                                type="button">
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
                        <p><i class="material-icons">info</i> Momentálně nejsou k dispozici žádné katalogy.</p>
                    </div>
                {/if}
            </div>

            <div class="katalogy-contact mt-5">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-8 col-lg-9">
                            <div class="contact-content">
                                <h3>{$footer_title|escape:'html':'UTF-8'}</h3>
                                <p>{$footer_text|escape:'html':'UTF-8'}</p>
                                {if $footer_phone}
                                    <p class="contact-phone">
                                        <i class="material-icons">phone</i>
                                        <strong>{$footer_phone|escape:'html':'UTF-8'}</strong>
                                    </p>
                                {/if}
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <div class="contact-action">
                                <a href="{$footer_button_url|escape:'html':'UTF-8'}" class="btn btn-contact">
                                    <i class="material-icons">phone</i>
                                    {$footer_button_text|escape:'html':'UTF-8'}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{* Interest Modal *}
<div class="modal fade" id="interestModal" tabindex="-1" role="dialog" aria-labelledby="interestModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="interestModalLabel">Zájem o katalog</h5>
                <button type="button" class="modal-close-btn" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <form id="interestForm" method="post" action="{$smarty.server.REQUEST_URI}">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <strong id="catalogTitle"></strong>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="name" class="form-label">Jméno a příjmení *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">E-mail *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="phone" class="form-label">Telefon</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="company" class="form-label">Společnost *</label>
                        <input type="text" class="form-control" id="company" name="company" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="address" class="form-label">Adresa pro zaslání *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="Zadejte úplnou adresu pro zaslání katalogu..." required></textarea>
                    </div>

                    <div class="form-group mb-3">
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