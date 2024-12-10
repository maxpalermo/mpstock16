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

<!-- Modal for importing order products -->
<div id="newRecordModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="newRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newRecordModalLabel">Nuovo Record</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="newRecordForm">
                    <div class="form-group">
                        <label for="documentSelect">Seleziona Documento</label>
                        <input type="text" id="documentSelect" class="form-control autocomplete autocomplete-document" placeholder="Cerca documento...">
                    </div>
                    <div class="form-group">
                        <label for="productSelect">Seleziona Prodotto</label>
                        <input type="text" id="productSelect" class="form-control" placeholder="Cerca prodotto...">
                    </div>
                    <table class="table table-bordered" id="productTable">
                        <thead>
                            <tr>
                                <th><input type="text" id="searchId" class="form-control" placeholder="ID"></th>
                                <th><input type="text" id="searchProductId" class="form-control" placeholder="ID Prodotto"></th>
                                <th><input type="text" id="searchCombinationId" class="form-control" placeholder="ID Combinazione"></th>
                                <th><input type="text" id="searchName" class="form-control" placeholder="Nome"></th>
                                <th><input type="text" id="searchReference" class="form-control" placeholder="Riferimento"></th>
                                <th><input type="text" id="searchEan13" class="form-control" placeholder="EAN13"></th>
                                <th>Quantità</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows will be populated via AJAX -->
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                <button type="button" class="btn btn-primary" id="saveNewRecord">Salva</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Fetch documents and populate the document select
        $.ajax({
            url: '{ajax_controller}',
            method: 'GET',
            success: function(data) {
                $('#documentSelect').html(data);
            }
        });
        $(".autocomplete-document").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '{ajax_controller}',
                    data: { term: request.term },
                    success: function(data) {
                        response(data);
                    }
                });
            }
        });
        // Autocomplete for product selection
        $('#productSelect').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '/path/to/fetch/products',
                    data: { term: request.term },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            select: function(event, ui) {
                // Add selected product to the table
                var newRow = `<tr>
<td>${ui.item.id}</td>
<td>${ui.item.id_prodotto}</td>
<td>${ui.item.id_combinazione}</td>
<td>${ui.item.nome}</td>
<td>${ui.item.riferimento}</td>
<td>${ui.item.ean13}</td>
                <td><input type="number" class="form-control" value="1"></td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success btn-save"><i class="fa fa-save"></i></button>
                        <button type="button" class="btn btn-danger btn-delete"><i class="fa fa-trash"></i></button>
                    </div>
                </td>
            </tr>`;
                $('#productTable tbody').append(newRow);
            }
        });

        // Fetch products associated with the selected document and populate the table
        $('#documentSelect').change(function() {
            var documentId = $(this).val();
            $.ajax({
                url: '/path/to/fetch/products/by/document',
                data: { documentId: documentId },
                success: function(data) {
                    $('#productTable tbody').html(data);
                }
            });
        });

        // Save new record
        $('#saveNewRecord').click(function() {
            var formData = $('#newRecordForm').serialize();
            $.ajax({
                url: '/path/to/save/new/record',
                method: 'POST',
                data: formData,
                success: function(response) {
                    // Handle success
                }
            });
        });

        // Handle save and delete actions in the table
        $('#productTable').on('click', '.btn-save', function() {
            var row = $(this).closest('tr');
            var rowData = {
                id: row.find('td:eq(0)').text(),
                id_prodotto: row.find('td:eq(1)').text(),
                id_combinazione: row.find('td:eq(2)').text(),
                nome: row.find('td:eq(3)').text(),
                riferimento: row.find('td:eq(4)').text(),
                ean13: row.find('td:eq(5)').text(),
                quantita: row.find('td:eq(6) input').val()
            };
            $.ajax({
                url: '/path/to/save/product',
                method: 'POST',
                data: rowData,
                success: function(response) {
                    // Handle success
                }
            });
        });

        $('#productTable').on('click', '.btn-delete', function() {
            $(this).closest('tr').remove();
        });
    });
</script>