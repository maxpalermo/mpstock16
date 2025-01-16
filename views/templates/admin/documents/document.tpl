{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

{include file="../forms/form-new-movement.tpl"}

<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-cogs"></i>
        <span>Elenco documenti</span>
    </div>
    <div class="panel-body">
        <table class="table table-striped table-bordered table-hover" id="table-documents">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="check-all-documents" />
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
                    <th data-field="id_mpstock_document"></th>
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
    let dataTablesMovements = [];
    let dataTable = null;

    function initDataTable() {
        dataTable = $('#table-documents').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{$admin_controller_url}",
                type: "POST",
                data: {
                    ajax: true,
                    action: "get_documents"
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
                            .append($("<button>").addClass('btn btn-link toggleInvoiceDetail').attr('data-id_invoice', row.id_mpstock_document).append('<i class="fa fa-chevron-up"></i>'))
                            .append('<input type="checkbox" name="id_mpstock_document[]" value="' + row.id_mpstock_document + '" />')
                        return node;
                    },
                },
                {
                    data: "id_mpstock_document",
                    render: function(data, type, row) {
                        return '<a href="{$base_url}admin/mpstockv2/documents/edit/' + data + '">' + data + '</a>';
                    },
                    name: "a.id_mpstock_document"
                },
                {
                    data: "number_document",
                    name: "a.number_document",
                },
                {
                    data: "date_document",
                    name: "a.date_document"
                },
                {
                    data: "mvt_reason",
                    name: "m.name",
                },
                {
                    data: "supplier",
                    name: "s.name"
                },
                {
                    data: "tot_document_ti",
                    name: "a.tot_document_ti",
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
                            bg = 'success';
                        }
                        return `<span class="pull-right text-${ bg }">${ value } EUR</span>`;
                    },
                },
                {
                    data: "employee",
                    name: "e.lastname"
                },
                {
                    data: "date_add",
                    name: "a.date_add"
                },
                {
                    data: "actions",
                    orderable: false,
                    searchable: false,
                }
            ],
            initComplete: function() {
                $('#check-all-documents').on('change', function() {
                    let checked = $(this).prop('checked');
                    $('#table-documents tbody input[type="checkbox"]').prop('checked', checked);
                });
                this.api()
                    .columns()
                    .every(function() {
                        let column = this;
                        let header = column.header();
                        let data_field = header.dataset.field;

                        if (data_field) {
                            switch (data_field) {
                                case "id_mpstock_document":
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
                $('#check-all-documents').prop('checked', false);
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
                "url": "/modules/mpstockv2/views/js/plugins/datatables/datatables-it.json"
            }
        });
    }

    async function toggleInvoiceDetail(e) {
        let button = e.target;

        if (button.tagName.toLowerCase() !== 'button') {
            button = $(button).closest('button')[0];
        }

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

            console.log(dataTablesMovements);
        }
    }

    async function addChildInvoiceDetails(data, id_invoice) {
        const params =
            JSON.stringify({
                ajax: true,
                action: 'get_invoice_details',
                id_invoice: id_invoice
            });

        let response =
            await fetch(
                "{$admin_controller_url}&action=get_invoice_details&ajax=1&id_invoice=" + id_invoice, {
                method: 'POST',
                body: params
            })

    let details = await response.json();

    return details.content;
    }

    $(function(e) {
        initDataTable();

        dataTable.on('click', '.toggleInvoiceDetail', function(e) {
            toggleInvoiceDetail(e);
        });

        $(document).on('click', ".btn-add-movement", function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const row = $(this).closest('tr').prev();
            const data = dataTable.row(row).data();

            const id_document = $(this).attr('id').replace('btn-add-movement-', '');
            const id_movement = data.id_mpstock_mvt_reason;
            const id_product = data.id_product;
            const id_product_attribute = data.id_product_attribute;
            const qty = data.qty;
            const price = data.price;

            $('#id_mpstock_document').val(id_document);
            $('#movementReason').val(id_movement).trigger("chosen:updated");
            $("#movementQuantity").val("0");
            $('#newMovementModal').modal('show');
        });

        $("#newMovementModal").on('shown.bs.modal', function(e) {
            $("#product").focus();
        });
    });
</script>