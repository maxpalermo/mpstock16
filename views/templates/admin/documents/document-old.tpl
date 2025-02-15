{*
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

{include file="../forms/form-new-document.tpl"}
{include file="../forms/form-edit-movement.tpl"}

<div class="panel">
    <div class="panel-heading">
        <i class="material-icons">description</i>
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
</div>

{include file="../scripts/script-document-movements.tpl"}

<script type="text/javascript">
    let dataTablesMovements = [];
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

    function newDocument() {
        $("#modal-new-document").modal('show');
    }

    function initDataTable() {
        dataTable = $('#table-documents').DataTable({
            order: [
                [1, "desc"]
            ],
            language: {
                "url": "/modules/mpstockv2/views/js/plugins/datatables/lang/it_IT.json"
            },
            processing: true,
            serverSide: true,
            ajax: {
                url: "{$admin_controller_url}",
                type: "POST",
                data: {
                    ajax: 1,
                    action: "getDocuments"
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
                            .append($("<button>").addClass('btn btn-link toggleInvoiceDetail').attr('data-id_invoice', row.id_mpstock_document).append('<i class="material-icons">add_circle</i>'))
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
            $(button).find('i').text("add_circle");
            row.child.hide();
        } else {
            // Open this row
            $(button).find('i').text("remove");
            const content = await addChildInvoiceDetails(row.data(), id_invoice);
            row.child(content).show();
        }
    }

    async function addChildInvoiceDetails(data, id_invoice) {
        const formData = new FormData();

        formData.append('ajax', 1);
        formData.append('action', 'GetInvoiceDetails');
        formData.append('id_invoice', id_invoice);

        let response = await fetch(
            "{$admin_controller_url}",
            {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );

        let details = await response.json();

        const tableDetails = createTableMovement(details.id_document, details.id_mvt_reason, details.movements);

        return tableDetails;
    }

    function btnActionAdd(id_document, id_mvt_reason) {
        const modalAddMovement = document.getElementById("modal-edit-movement");

        $(modalAddMovement).find("#edit-id_mpstock_document").val(id_document);
        $(modalAddMovement).find("#edit-id_mpstock_mvt_reason").val(id_mvt_reason).trigger("chosen:updated");
        $(modalAddMovement).find("#edit-ean13").val("").trigger("chosen:updated");
        $(modalAddMovement).find("#edit-product_name").val("").trigger("chosen:updated");
        $(modalAddMovement).find("#edit-id_product").val("").trigger("chosen:updated");
        $(modalAddMovement).find("#edit-id_product_attribute").val("").trigger("chosen:updated");
        $(modalAddMovement).find("#edit-quantity").val("").trigger("chosen:updated");
        $(modalAddMovement).find("#edit-id_supplier").val("").trigger("chosen:updated");
        $(modalAddMovement).find("#edit-id_employee").val("").trigger("chosen:updated");
        $(modalAddMovement).modal('show');
    }

    function btnActionDelete(id) {
        Swal.fire({
            title: 'Attenzione!',
            text: "Sei sicuro di voler eliminare il documento selezionato?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'S ',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(
                    "{$admin_controller_url}",
                    {
                        ajax: 1,
                        action: 'deleteDocument',
                        id: id
                    },
                    function(data) {
                        if (data.success) {
                            Swal.fire(
                                'Eliminato!',
                                'Il documento ' + id + ' stato eliminato.',
                                'success'
                            );
                            dataTable.ajax.reload();
                        }
                    },
                    'json'
                );
            }
        });

        return false;
    }

    function btnActionEdit(id) {
        const modalEditMovement = document.getElementById("modal-edit-movement");

        $.ajax({
            url: "{$admin_controller_url}",
            type: "POST",
            data: {
                ajax: true,
                action: "getMovement",
                id: id
            },
            success: function(response) {
                fillFormMovement(response);
            },
        });

        return false;
    }

    async function fillFormMovement(response) {
        $(modalEditMovement).find("#edit-id_mpstock_movement").val(response.id_mpstock_movement);
        $(modalEditMovement).find("#edit-id_mpstock_mvt_reason").val(response.id_mpstock_mvt_reason).trigger("chosen:updated");
        $(modalEditMovement).find("#edit-ean13").val(response.ean13).trigger("chosen:updated");
        $(modalEditMovement).find("#edit-product_name").val(response.product_name);
        $(modalEditMovement).find("#edit-id_product").val(response.id_product);

        await getCombinations(response.id_product, response.id_product_attribute);

        $(modalEditMovement).find("#edit-quantity-actual").val(response.stock_quantity_before);
        $(modalEditMovement).find("#edit-quantity").val(response.stock_movement * response.sign);
        $(modalEditMovement).find("#edit-sign").val(response.sign);
        $(modalEditMovement).find("#edit-quantity-total").val(response.stock_quantity_after);

        $(modalEditMovement).find("#edit-id_supplier").val(response.id_supplier).trigger("chosen:updated");
        $(modalEditMovement).find("#edit-id_employee").val(response.employee_name);

        $(modalEditMovement).find("#id_document").val(response.id_mpstock_document);
        $(modalEditMovement).find("#number_document").val(response.number_document);
        $(modalEditMovement).find("#date_document").val(response.date_document);
        $(modalEditMovement).find("#id_mpstock_mvt_reason").val(response.id_mpstock_mvt_reason).trigger("chosen:updated");

        $(modalEditMovement).modal("show");
    }

    async function getCombinations(id_product, id_product_attribute = 0) {
        const combinations = await fetch(
            "{$admin_controller_url}",
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    ajax: true,
                    action: 'getProductCombinations',
                    id: id_product
                })
            }
        );

        const data = await combinations.json();

        $("#edit-product_attribute_name").empty();
        data.forEach(function(combination) {
            $("#edit-product_attribute_name").append("<option value='" + combination.value + "'>" + combination.label + "</option>");
        })

        if (id_product_attribute > 0) {
            $("#edit-product_attribute_name option[value='" + id_product_attribute + "']").prop('selected', true);
        }

        $("#edit-product_attribute_name").trigger("chosen:updated");
    }

    document.addEventListener('DOMContentLoaded', function() {
        initDataTable();

        dataTable.on('click', '.toggleInvoiceDetail', function(e) {
            toggleInvoiceDetail(e);
        });

        $(document).on('click', '.btn-action', function(e) {
            e.preventDefault();
            let action = $(this).data('action');
            let id_document = $(this).data('id_document');
            let id_mvt_reason = $(this).data('id_mvt_reason');
            let method = "btnAction" + action.charAt(0).toUpperCase() + action.slice(1);
            console.log(`firing method: ${ method }(${ id_document }, ${ id_mvt_reason })`);

            window[method](id_document, id_mvt_reason);

            return false;
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