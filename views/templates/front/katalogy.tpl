{extends file='page.tpl'}

{block name='page_title'}
    <h1>{$page_title}</h1>
{/block}

{block name='page_content'}
    <div class="katalogy-page">
        {if isset($page_description) && $page_description}
            <div class="page-description">
                <p>{$page_description}</p>
            </div>
        {/if}

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

    {* Interest Modal *}
    <div id="interestModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Zájem o katalog</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="interestForm" method="post">
                    <div class="modal-body">
                        <div class="form-group">
                            <strong id="catalogTitle"></strong>
                        </div>
                        
                        <div class="form-group">
                            <label for="name">Jméno a příjmení *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-mail *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Telefon</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        
                        <div class="form-group">
                            <label for="company">Společnost</label>
                            <input type="text" class="form-control" id="company" name="company">
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Zpráva</label>
                            <textarea class="form-control" id="message" name="message" rows="3" placeholder="Volitelná zpráva..."></textarea>
                        </div>
                        
                        <input type="hidden" id="catalog_id" name="catalog_id" value="">
                        <input type="hidden" name="submitInterest" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn btn-primary">Odeslat žádost</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/block}