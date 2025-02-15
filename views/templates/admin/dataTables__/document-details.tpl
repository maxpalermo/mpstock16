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
                <button type="button" class="btn btn-primary" id="btn-add-movement-{$id_document}">
                    <i class="icon icon-plus"></i>
                    <span>Aggiungi movimento</span>
                </button>
            </th>
        </tr>
    </div>
</div>

<script type="text/javascript">
    let dataTable = null;

    function setBgColor(data, type, row) {
        let bg = 'info';
        if (Number(data) < 0) {
            bg = 'danger';
        } else if (Number(data) == 0) {
            bg = 'warning';
        } else {
            bg = 'info';
        }
        return `<span class="pull-right text-${ bg }">${ data }</span>`;
    }

    function initDataTable_{$id_document}() {
    dataTable =
        $('#table-movements-{$id_document}')
        .DataTable({
            columns: [{
                    data: "checkbox",
                    orderable: false,
                    searchable: false,
                    name: "checkbox",
                },
                {
                    data: "id_mpstock_movement",
                    name: "a.id_mpstock_movement"
                },
                {
                    data: "product_name",
                    name: "pl.name",
                },
                {
                    data: "product_combination",
                    name: "",
                    searchable: false,
                    orderable: false,
                },
                {
                    data: "reference",
                    name: "a.reference",
                },
                {
                    data: "ean13",
                    name: "a.ean13"
                },
                {
                    data: "price_te",
                    name: "a.price_te",
                    render: function(data, type, row) {
                        let bg = 'info';
                        let value = Number(data).toLocaleString("it-IT", {
                            maximumFractionDigits: 2,
                            minimumFractionDigits: 2
                        });

                        if (Number(data) < 0) {
                            bg = 'danger';
                        } else if (Number(data) == 0) {
                            bg = 'warning';
                        } else {
                            bg = 'info';
                        }
                        return `<span class="pull-right text-${ bg }">${ value } EUR</span>`;
                    },
                },
                {
                    data: "stock_quantity_before",
                    name: "a.stock_quantity_before",
                    render: function(data, type, row) {
                        return setBgColor(data, type, row);
                    },
                },
                {
                    data: "stock_movement",
                    name: "a.stock_movement",
                    render: function(data, type, row) {
                        return setBgColor(data, type, row);
                    },
                },
                {
                    data: "stock_quantity_after",
                    name: "a.stock_quantity_after",
                    render: function(data, type, row) {
                        return setBgColor(data, type, row);
                    },
                },
                {
                    data: "date_add",
                    name: "a.date_add"
                },
                {
                    data: "employee",
                    name: "e.lastname"
                },
                {
                    data: "actions",
                    orderable: false,
                    searchable: false,
                }
            ],
            initComplete: function() {
                $('#check-all-movements-{$id_document}')
                .on('change', function() {
                    let checked = $(this).prop('checked');
                    $('#table-movements-{$id_document} tbody input[type="checkbox"]').prop('checked', checked);
                });
            },
            callback: function() {
                $('#check-all-movements-{$id_document}').prop('checked', false);
            },
            rowCallback: function(row, data) {
                if (data.status == 0) {
                    $(row).addClass('danger');
                }
            },
            order: [
                [1, "asc"]
            ],
            language: {
                "url": "/modules/mpstockv2/views/js/plugins/datatables/lang/it_IT.json"
            }
        });
    }

    $(function(e) {
        console.log("DomContentLoaded");
        initDataTable_{$id_document}();

        dataTable.on('click', '.toggleInvoiceDetail', function(e) {
            toggleInvoiceDetail(e);
        });
    });
</script>