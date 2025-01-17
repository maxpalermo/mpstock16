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

<style>
    .text-bold {
        font-weight: bold;
    }

    .font-medium {
        font-size: 1.2em;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #ddd !important;
    }

    .table-bordered tr:hover {
        cursor: pointer;
        background-color: #f5f5f5;
    }
</style>

<div class="panel">
    <div class="panel-heading">
        <span class="title font-medium">Giacenze di Magazzino</span>
    </div>
    <div class="panel-body">
        <table id="productTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>ID Prodotto</th>
                    <th>Nome Prodotto</th>
                    <th>Riferimento</th>
                    <th>EAN13</th>
                    <th>Quantità</th>
                    <th>Prezzo</th>
                    <th>Attivo</th>
                </tr>
                <tr>
                    <th data-field="a.id_product"></th>
                    <th data-field="p.name"></th>
                    <th data-field="p.reference"></th>
                    <th data-field="p.ean13"></th>
                    <th data-field="a.quantity"></th>
                    <th data-field="p.price"></th>
                    <th data-field="p.active"></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    const adminControllerUrl = '{$link->getAdminLink('AdminMpStockAvailability')}';
    let dataTable = null;
</script>

{literal}
    <script>
        function renderQuantity(data, type, row) {
            let className = '';
            if (data == 0) {
                className = 'default';
            } else if (data > 0) {
                className = 'success';
            } else {
                className = 'danger';
            }

            return `<span class="badge badge-${className} text-bold">${data}</span>`;
        }

        function addPlusSign(data, type, row) {
            return `<div class="combination-row" style="display: flex; justify-content: space-between; padding: 0.50rem;">` +
                `<span class="font-medium pointer icon icon-table icon-toggle-rows text-success mr-1" data-product-id="${row.id_product}" title="Mostra combinazioni"></span>` +
                `<span class="product-row">${data}</span>` +
                `</div>`;
        }

        function renderPrice(data, type, row) {
            let formattedPrice = Number(data).toLocaleString("it-IT", {
                style: "currency",
                currency: "EUR",
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            return `<span class="font-medium">${formattedPrice}</span>`;
        }

        function renderActive(data, type, row) {
            if (data == 1) {
                return `<span class="font-medium text-success"><i class="fa fa-check"></i></span>`;
            } else {
                return `<span class="font-medium text-danger"><i class="fa fa-times"></i></span>`;
            }
        }

        $(document).ready(function() {
            dataTable = $('#productTable').DataTable({
                order: [
                    [1, "asc"]
                ],
                language: {
                    "url": "/modules/mpstockv2/views/js/plugins/datatables/datatables-it.json"
                },
                paging: true,
                searching: true,
                serverSide: true,
                processing: true,
                ajax: {
                    url: adminControllerUrl,
                    type: 'POST',
                    data: {
                        ajax: true,
                        action: 'getTable'
                    }
                },
                columns: [{
                        data: 'id_product',
                        name: 'id_product',
                        title: 'ID',
                        width: "96px",
                        className: "dt-right",
                        render: addPlusSign
                    },
                    {
                        data: 'product_name',
                        name: 'product_name',
                        title: 'Nome Prodotto',
                        render: $.fn.dataTable.render.text()
                    },
                    {
                        data: 'product_reference',
                        name: 'product_reference',
                        title: 'Riferimento',
                        render: $.fn.dataTable.render.text()
                    },
                    {
                        data: 'product_ean13',
                        name: 'product_ean13',
                        title: 'EAN13',
                        className: "dt-center",
                        width: "13em",
                        render: $.fn.dataTable.render.text()
                    },
                    {
                        data: 'quantity',
                        name: 'a.quantity',
                        title: 'Quantità',
                        render: renderQuantity
                    },
                    {
                        data: 'price',
                        name: 'p.price',
                        title: 'Prezzo',
                        className: "dt-right",
                        width: "96px",
                        render: renderPrice
                    },
                    {
                        data: 'active',
                        name: 'p.active',
                        title: 'Attivo',
                        className: "dt-center",
                        width: "64px",
                        render: renderActive
                    }
                ],
                initComplete: function() {
                    this.api()
                        .columns()
                        .every(function() {
                            let column = this;
                            let header = column.header();
                            let data_field = header.dataset.field;

                            if (data_field == 'p.active') {
                                let select = document.createElement('select');
                                select.className = 'form-control';
                                select.style.width = '50px';
                                select.innerHTML = `
                                    <option value="">--</option>
                                    <option value="1">Si</option>
                                    <option value="0">No</option>
                                `;
                                select.addEventListener('change', (e) => {
                                    let value = e.target.value;
                                    column.search(value).draw();
                                });
                                header.replaceChildren(select);
                            } else {
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
                            }
                        });
                },
            });

            $(document).on("click", ".icon-toggle-rows", function() {
                const id_product = Number($(this).data('product-id'));
                const tr = $(this).closest('tr');
                const row = dataTable.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    $(this).removeClass('icon-list text-info').addClass('icon-table text-success');
                } else {
                    const data = {
                        ajax: true,
                        action: 'getCombinationsTable',
                        id_product: id_product
                    };
                    $.post(adminControllerUrl, data, function(response) {
                        row.child(response.table).show();
                        tr.addClass('shown');
                        $(this).removeClass('icon-table text-success').addClass('icon-list text-info');
                    });
                }
            });
        });
    </script>
{/literal}