<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-cogs"></i>
        <span>Elenco moviomenti</span>
    </div>
    <div class="panel-body">
        <table class="table table-striped table-bordered table-hover" id="table-movements-{$id_document}">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="check-all-movements-{$id_document}" />
                    </th>
                    <th>Id</th>
                    <th>Numero</th>
                    <th>Data</th>
                    <th>Movimento</th>
                    <th>Fornitore</th>
                    <th>Totale</th>
                    <th>Operatore</th>
                    <th>Data inserimento</th>
                    <th>Azioni</th>
                </tr>
                <tr>
                    <th data-field=""></th>
                    <th data-field="id_mpstock_movement"></th>
                    <th data-field="number_document"></th>
                    <th data-field="date_document"></th>
                    <th data-field="mvt_reason"></th>
                    <th data-field="supplier"></th>
                    <th data-field="tot_document_ti"></th>
                    <th data-field="employee"></th>
                    <th data-field="date_add"></th>
                    <th data-field=""></th>
                </tr>
            </thead>
            <tbody>
                <!-- DATI CARICATI VIA AJAX -->
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <tr>
            <th colspan="10" class="text-center">
                <button type="button" class="btn btn-primary" id="btn-add-document">
                    <i class="icon icon-plus"></i>
                    <span>Aggiungi documento</span>
                </button>
            </th>
        </tr>
    </div>
</div>

<script type="text/javascript">
    let dataTable = null;

    function initDataTable_{$id_document}() {
    dataTable = $('#table-movements-{$id_document}').DataTable({
    processing: true,
        serverSide: true,
        ajax: {
            url: "{$admin_controller_url}",
            type: "POST",
            data: {
                ajax: true,
                action: "get_movements"
            }
        },
        columns: [{
                data: "checkbox",
                orderable: false,
                searchable: false,
                name: "checkbox",
                render: function(data, type, row) {
                    let node = document.createElement('div');
                    $(node)
                        .addClass('d-flex justify-content-between')
                        .append('<input type="checkbox" name="id_mpstock_movement[]" value="' + row.id_mpstock_movement + '" />')
                    return node;
                },
            },
            {
                data: "id_mpstock_movement",
                render: function(data, type, row) {
                    return '<a href="{$base_url}admin/mpstock/movements/edit/' + data + '">' + data + '</a>';
                },
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
            },
            {
                data: "stock_movement",
                name: "a.stock_movement",
            },
            {
                data: "stock_quantity_after",
                name: "a.stock_quantity_after",
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
            $('#check-all-movements').on('change', function() {
                let checked = $(this).prop('checked');
                $('#table-movements tbody input[type="checkbox"]').prop('checked', checked);
            });
            this.api()
                .columns()
                .every(function() {
                    let column = this;
                    console.log(column.index());
                    let header = column.header();
                    let data_field = header.dataset.field;

                    if (data_field) {
                        switch (data_field) {
                            case "id_mpstock_movement":
                            case "number_document":
                            case "mvt_reason":
                            case "supplier":
                            case "tot_document_ti":
                            case "employee":
                                let input = document.createElement('input');
                                $(input).addClass('form-control');
                                header.replaceChildren(input);

                                // Event listener for user input
                                input.addEventListener('focus', (e) => {
                                    e.stopPropagation();
                                    e.stopImmediatePropagation();
                                    $(this).select();
                                });

                                input.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    e.stopImmediatePropagation();
                                });

                                input.addEventListener('keyup', (e) => {
                                    e.stopPropagation();
                                    e.stopImmediatePropagation();

                                    if (column.search() !== this.value) {
                                        column.search(input.value).draw();
                                    }
                                });
                                break;
                            case "date_document":
                            case "date_add":
                                let datepicker = document.createElement('input');
                                $(datepicker).attr('type', 'date').addClass('form-control');
                                header.replaceChildren(datepicker);

                                // Event listener for user input
                                datepicker.addEventListener('focus', (e) => {
                                    e.stopPropagation();
                                    e.stopImmediatePropagation();
                                    $(this).select();
                                });

                                datepicker.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    e.stopImmediatePropagation();
                                });

                                datepicker.addEventListener('change', () => {
                                    if (column.search() !== datepicker.value) {
                                        column.search(datepicker.value).draw();
                                    }
                                });
                                break;
                            default:
                                return '';
                        }
                    }
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
            "url": "/modules/mpstock/views/js/plugins/datatables/datatables-it.json"
        }
    });
    }

    async function toggleInvoiceDetail(e) {
        let button = e.target;

        if (button.tagName.toLowerCase() !== 'button') {
            button = $(button).closest('button')[0];
        }

        console.log("BUTTON", button);

        let tr = button.closest('tr');
        let id_invoice = button.dataset.id_invoice;
        let row = dataTable.row(tr);

        if (row.child.isShown()) {
            // This row is already open - close it
            $(button).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            row.child.hide();
        } else {
            // Open this row
            $(button).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            const content = await addChildInvoiceDetails(row.data(), id_invoice);
            row.child(content).show();
        }
    }

    async function addChildInvoiceDetails(data, id_invoice) {
        console.log(data);

        const params = JSON.stringify({
            ajax: true,
            action: 'get_invoice_details',
            id_invoice: id_invoice
        });

        let response = await fetch(
            "{$admin_controller_url}&action=get_invoice_details&ajax=1&id_invoice=" + id_invoice,
            {
                method: 'POST',
                body: params
            })

        let details = await response.json();

        return details.content;
    }

    $(function(e) {
        console.log("DomContentLoaded");
        initDataTable();

        dataTable.on('click', '.toggleInvoiceDetail', function(e) {
            toggleInvoiceDetail(e);
        });
    });
</script>