<style>
    .ui-autocomplete {
        z-index: 9999 !important;
        max-height: 200px;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>

<div class="modal fade" id="modal-movement-detail" tabindex="-1" role="dialog" aria-labelledby="modal-movement-detail-label" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="min-width: 700px; max-width: 1200px;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modal-movement-detail-label">Modifica Movimento</h4>
            </div>
            <div class="modal-body">
                <form id="movement-detail-form">
                    <div class="d-flex justify-content-between align-items-top">
                        <div class="image-preview justify-content-center">
                            <img id="detail-image-preview" src="https://placehold.co/160x240" alt="Anteprima immagine" class="img-thumbnail" style="min-width: 160px; max-width: 160px; min-height: 240px; max-height: 240px; object-fit: contain;">
                        </div>
                        <div class="form-values">
                            <div class="form-group">
                                <label for="detail-id_mpstock_movement">ID Movimento</label>
                                <input type="hidden" id="detail-id_product" name="id_product" />
                                <input type="hidden" name="id_mpstock_document" id="detail-id_mpstock_document" value="">
                                <input type="text" class="form-control text-center" id="detail-id_mpstock_movement" name="id_mpstock_movement" readonly>
                            </div>
                            <div class="form-group">
                                <label for="detail-id_mpstock_mvt_reason">Tipo di movimento</label>
                                <select class="form-control chosen" id="detail-id_mpstock_mvt_reason" name="id_mpstock_mvt_reason" required>
                                    {foreach from=$mvtReasons item=mvtReason}
                                        <option value="{$mvtReason.id_mpstock_mvt_reason}" data-sign="{$mvtReason.sign}">{$mvtReason.name}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="detail-ean13">EAN13</label>
                                <input type="text" class="form-control" id="detail-ean13" name="ean13" placeholder="Cerca per EAN13" required>
                            </div>
                            <div class="form-group">
                                <label for="detail-reference">Riferimento</label>
                                <input type="text" class="form-control autocomplete" id="detail-reference" name="reference" placeholder="Cerca per riferimento" required>
                            </div>
                            <div class="form-group">
                                <label for="detail-product_name">Prodotto</label>
                                <input type="text" class="form-control autocomplete" id="detail-product_name" name="product_name" placeholder="Cerca per nome" required>
                            </div>
                            <div class="form-group">
                                <label for="detail-id_product_attribute">Variante prodotto</label>
                                <select
                                        class="form-control"
                                        id="detail-id_product_attribute"
                                        name="id_product_attribute"
                                        placeholder="Cerca per combinazione"
                                        required>
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="detail-quantity-actual">Quantità attuale</label>
                                        <input type="number" class="form-control" id="detail-quantity-actual" name="quantity_actual" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="detail-quantity">Movimento</label>
                                        <input type="number" class="form-control" id="detail-quantity" name="quantity" required min="1" step="1" pattern="{literal}[0-9]+(\.[0-9]{1,2})?{/literal}" title="Inserisci un numero maggiore di zero">
                                        <input type="hidden" id="detail-sign" name="sign" value="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="detail-quantity-total">Quantità totale</label>
                                        <input type="number" class="form-control" id="detail-quantity-total" name="quantity_total" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="detail-id_supplier">Fornitore</label>
                                <select class="form-control chosen" id="detail-id_supplier" name="id_supplier" required>
                                    <option value=""></option>
                                    {foreach from=$suppliers item=supplier}
                                        <option value="{$supplier.id_supplier}">{$supplier.name}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="detail-id_employee">Dipendente</label>
                                <input type="text" class="form-control" id="detail-id_employee" name="id_employee" readonly>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
                <button type="button" class="btn btn-primary" id="submit-movement-detail">Salva modifiche</button>
            </div>
        </div>
    </div>
</div>