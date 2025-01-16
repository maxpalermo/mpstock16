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
<style>
    .ui-autocomplete {
        max-height: 200px;
        overflow-y: auto;
        overflow-x: hidden;
        z-index: 10000 !important;
    }

    .ui-menu-item {
        padding: 5px;
    }

    .ui-menu-item:hover {
        background-color: #f0f0f0;
    }

    .ui-front {
        z-index: 100;
    }

    .ui-autocomplete-loading {
        background: white url('/modules/mpstockv2/views/img/ajax-loader.gif') right center no-repeat;
    }

    .ui-autocomplete-no-results {
        padding: 5px;
    }
</style>
<!-- Modal -->
<div class="modal fade" id="newMovementModal" tabindex="-1" role="dialog" aria-labelledby="newMovementModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="newMovementForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="newMovementModalLabel">Nuovo Movimento di Magazzino</h4>
                </div>
                <input type="hidden" id="id_mpstock_document" name="id_mpstock_document" value="0">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product">Prodotto</label>
                        <input type="text" class="form-control" id="product" name="product" placeholder="Cerca prodotto">
                    </div>
                    <div class="form-group">
                        <label for="productAttribute">Attributo del Prodotto</label>
                        <select class="form-control" id="productAttribute" name="productAttribute">
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="movementReason">Motivo del Movimento</label>
                        <select class="form-control chosen" id="movementReason" name="movementReason" required>
                            {foreach from=$mvtReasons item=reason}
                                <option value="{$reason.id_mpstock_mvt_reason}" data-sign="{$reason.sign}">{$reason.name}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div class="form-group">
                            <label for="currentStock">Stock Attuale</label>
                            <input type="text" class="form-control text-right" id="currentStock" name="currentStock" readonly>
                        </div>
                        <div class="form-group">
                            <label for="movementQuantity">Quantità</label>
                            <input type="number" class="form-control text-right" id="movementQuantity" name="movementQuantity" required>
                        </div>
                        <div class="form-group">
                            <label for="currentStock">Stock finale</label>
                            <input type="text" class="form-control text-right" id="finalStock" name="finalStock" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-primary">Salva modifiche</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function calcQuantity() {
        const sign = $("#movementReason").find("option:selected").data("sign") == 1 ? -1 : 1;
        const currentStock = parseFloat($("#currentStock").val());
        const movementQuantity = parseFloat($("#movementQuantity").val());
        const finalStock = currentStock + (sign * movementQuantity);
        $("#finalStock").val(finalStock);
    }

    function loadProductAttributes(productId) {
        $.ajax({
            url: "{$admin_controller_url}",
            dataType: "json",
            data: {
                ajax: 1,
                action: "getProductAttributes",
                productId: productId
            },
            success: function(data) {
                const productAttribute = document.getElementById("productAttribute");
                $(productAttribute).empty();
                $.each(data, function(index, item) {
                    $(productAttribute).append($("<option>").val(item.value).text(item.label));
                });
                $(productAttribute).trigger("change");
            }
        });
    }

    function loadCurrentStock(productId, productAttributeId) {
        $.ajax({
            url: "{$admin_controller_url}",
            dataType: "json",
            data: {
                ajax: 1,
                action: "getCurrentStock",
                productId: productId,
                productAttributeId: productAttributeId
            },
            success: function(data) {
                $("#currentStock").val(data.currentStock);
            }
        });
    }

    $(document).ready(function() {
        $("input").on("focus", function() {
            $(this).select();
        });

        $("#movementQuantity").on("input", function() {
            calcQuantity();
        });

        $("#movementReason").change(function() {
            calcQuantity();
        });

        $("#product").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{$admin_controller_url}",
                    dataType: "json",
                    data: {
                        ajax: 1,
                        action: "searchTermProduct",
                        term: request.term
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            select: function(event, ui) {
                const productId = ui.item.value;
                const productName = ui.item.label;

                $("#product").val(productName).data("id", productId);
                loadProductAttributes(productId);
                return false;
            }
        });

        $("#productAttribute").change(function() {
            const productId = $("#product").data("id");
            const productAttributeId = $("#productAttribute").find("option:selected").val();

            loadCurrentStock(productId, productAttributeId);
        });

        $("#newMovementForm").submit(function(event) {
            event.preventDefault();

            if (confirm("Confermi il salvataggio del movimento?") === false) {
                return false;
            }

            const documentId = $("#id_mpstock_document").val();

            const formData = {
                ajax: 1,
                action: "saveMovement",
                documentId: documentId,
                productId: $("#product").data("id"),
                productAttributeId: $("#productAttribute").find("option:selected").val(),
                movementReason: $("#movementReason").find("option:selected").val(),
                movementQuantity: $("#movementQuantity").val(),
                movementQuantityAfter: $("#finalStock").val()
            };

            $.ajax({
                url: "{$admin_controller_url}",
                method: "POST",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $("#newMovementModal").modal('hide');
                    } else {
                        alert("Errore nel salvataggio del movimento: " + response.message);
                    }
                }
            });
        });
    });
</script>