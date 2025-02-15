<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-cogs"></i>
        <span>Elenco movimenti</span>
    </div>
    <div class="panel-body">
        <table class="table table-striped table-bordered table-hover" id="table-movements-{$id_document}">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="check-all-movements-{$id_document}" />
                    </th>
                    <th>Id</th>
                    <th>Prodotto</th>
                    <th>Combinazione</th>
                    <th>Riferimento</th>
                    <th>Ean13</th>
                    <th>Prezzo</th>
                    <th>Stock iniziale</th>
                    <th>Movimento</th>
                    <th>Stock finale</th>
                    <th>Data inserimento</th>
                    <th>Operatore</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$movements item=movement}
                    <tr>
                        <td>
                            <input type="checkbox" name="id_mpstock_movement[]" value="{$movement.id_mpstock_movement}" />
                        </td>
                        <td>
                            <a href="{$admin_controller_url}">{$movement.id_mpstock_movement}</a>
                        </td>
                        <td>{$movement.product_name}</td>
                        <td>{$movement.product_combination}</td>
                        <td>{$movement.reference}</td>
                        <td>{$movement.ean13}</td>
                        <td>{$movement.price_te}</td>
                        <td>{$movement.stock_quantity_before}</td>
                        <td>{$movement.stock_movement}</td>
                        <td>{$movement.stock_quantity_after}</td>
                        <td>{$movement.date_add}</td>
                        <td>{$movement.employee}</td>
                        <td>
                            <a href="{$admin_controller_url}admin/mpstockv2/movements/edit/{$movement.id_mpstock_movement}" class="btn btn-primary btn-sm">
                                <i class="icon icon-edit"></i>
                            </a>
                            <a href="{$admin_controller_url}admin/mpstockv2/movements/delete/{$movement.id_mpstock_movement}" class="btn btn-danger btn-sm">
                                <i class="icon icon-trash"></i>
                            </a>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <tr>
            <th colspan="10" class="text-center">
                <button type="button" class="btn btn-primary btn-add-movement" id="btn-add-movement-{$id_document}">
                    <i class="icon icon-plus"></i>
                    <span>Aggiungi movimento</span>
                </button>
            </th>
        </tr>
    </div>
</div>