<style>
    .ui-autocomplete {
        z-index: 9999 !important;
        max-height: 200px;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>

<div class="modal fade" id="modal-edit-movement" tabindex="-1" role="dialog" aria-labelledby="modal-edit-movement-label" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modal-edit-movement-label">Modifica Movimento</h4>
            </div>
            <div class="modal-body">
                <form id="edit-movement-form">
                    <div class="form-group">
                        <label for="edit-id_mpstock_movement">ID Movimento</label>
                        <input type="hidden" id="edit-id_product" name="id_product" />
                        <input type="hidden" name="id_mpstock_document" id="edit-id_mpstock_document" value="">
                        <input type="text" class="form-control text-center" id="edit-id_mpstock_movement" name="id_mpstock_movement" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit-id_mpstock_mvt_reason">Tipo di movimento</label>
                        <select class="form-control chosen" id="edit-id_mpstock_mvt_reason" name="id_mpstock_mvt_reason" required>
                            {foreach from=$mvtReasons item=mvtReason}
                                <option value="{$mvtReason.id_mpstock_mvt_reason}" data-sign="{$mvtReason.sign}">{$mvtReason.name}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-ean13">EAN13</label>
                        <input type="text" class="form-control" id="edit-ean13" name="ean13" placeholder="Cerca per EAN13" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-reference">Riferimento</label>
                        <input type="text" class="form-control autocomplete" id="edit-reference" name="reference" placeholder="Cerca per riferimento" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-product_name">Prodotto</label>
                        <input type="text" class="form-control autocomplete" id="edit-product_name" name="product_name" placeholder="Cerca per nome" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-id_product_attribute">Variante prodotto</label>
                        <select
                                class="form-control"
                                id="edit-id_product_attribute"
                                name="id_product_attribute"
                                placeholder="Cerca per combinazione"
                                required>
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="edit-quantity-actual">Quantità attuale</label>
                                <input type="number" class="form-control" id="edit-quantity-actual" name="quantity_actual" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="edit-quantity">Quantità da movimentare</label>
                                <input type="number" class="form-control" id="edit-quantity" name="quantity" required>
                                <input type="hidden" id="edit-sign" name="sign" value="0">
                            </div>
                            <div class="col-md-4">
                                <label for="edit-quantity-total">Quantità totale</label>
                                <input type="number" class="form-control" id="edit-quantity-total" name="quantity_total" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-id_supplier">Fornitore</label>
                        <select class="form-control chosen" id="edit-id_supplier" name="id_supplier" required>
                            <option value=""></option>
                            {foreach from=$suppliers item=supplier}
                                <option value="{$supplier.id_supplier}">{$supplier.name}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-id_employee">Dipendente</label>
                        <input type="text" class="form-control" id="edit-id_employee" name="id_employee" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
                <button type="button" class="btn btn-primary" id="submit-edit-movement">Salva modifiche</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    let modalEditMovement = null;

    function initModalEditMovement() {
        modalEditMovement = $('#modal-edit-movement');
    }

    function showEditMovementModal(id) {
        modalEditMovement.find("#edit-id_mpstock_movement").val(id);
        modalEditMovement.find("#edit-ean13").val("");
        modalEditMovement.find("#edit-product_name").val("");
        modalEditMovement.find("#edit-id_product_attribute").val("");
        modalEditMovement.find("#edit-id_mpstock_mvt_reason").val("").trigger("chosen:updated");
        modalEditMovement.find("#edit-quantity").val("");
        modalEditMovement.find("#edit-id_supplier").val("").trigger("chosen:updated");
        modalEditMovement.find("#edit-id_employee").val("").trigger("chosen:updated");
        modalEditMovement.modal('show');
    }

    async function getMovement(id) {
        try {
            const response = await fetch(
                "{$admin_controller_url}",
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        ajax: true,
                        action: 'getMovement',
                        id: id
                    })
                });
            return response.json();
        } catch (error) {
            console.error(error);
            return null;
        }
    }

    async function fillFormEditMovement(id) {
        const movement = await getMovement(id);

        modalEditMovement.find("#edit-id_mpstock_movement").val(movement.id_mpstock_movement);
        modalEditMovement.find("#edit-ean13").val(movement.ean13);

        $("#edit-product_name").autocomplete("disable");
        modalEditMovement.find("#edit-product_name").val(movement.product_name);
        $("#edit-product_name").autocomplete("enable");

        modalEditMovement.find("#edit-id_product").val(movement.id_product);
        modalEditMovement.find("#edit-id_product_attribute").val(movement.id_product_attribute).trigger("chosen:updated");
        modalEditMovement.find("#edit-id_mpstock_mvt_reason").val(movement.id_mpstock_mvt_reason).trigger("chosen:updated");
        modalEditMovement.find("#edit-quantity").val(movement.quantity);
        modalEditMovement.find("#edit-id_supplier").val(movement.id_supplier).trigger("chosen:updated");
        modalEditMovement.find("#edit-id_employee").val(movement.id_employee).trigger("chosen:updated");
    }

    function fillCombinations(id) {
        $.ajax({
            url: "{$admin_controller_url}",
            type: "POST",
            data: {
                ajax: true,
                action: "getProductCombinations",
                id: id
            },
            success: function(response) {
                const combinations = response;
                const productCombinations = document.getElementById("edit-id_product_attribute");
                $(productCombinations).empty();
                combinations.forEach(function(combination) {
                    $(productCombinations).append("<option value='" + combination.value + "'>" + combination.label + "</option>");
                })

                $(productCombinations).trigger("chosen:updated");
            }
        });
    }

    async function getProductEan13(ean13) {
        if (ean13.length == 13) {
            const response = await fetch(
                "{$admin_controller_url}",
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        ajax: true,
                        action: 'getEan13',
                        ean13: ean13,
                        id_mvt_reason: $("#edit-id_mpstock_mvt_reason").val()
                    })
                }
            );

            const product = await response.json();

            if (product.length == 0) {
                swal.fire({
                    title: "Errore",
                    text: "Prodotto non trovato",
                    icon: "error"
                })
                return false;
            }

            $("#edit-id_mpstock_movement").val("0")
            $("#edit-product_name").val(product.name);
            $("#edit-id_product").val(product.id_product);
            $("#edit-id_product_attribute").html(product.attributes).trigger("chosen:updated").val(product.id_product_attribute);
            $("#edit-quantity-actual").val(product.stock_quantity);
            $("#edit-quantity").val("0")
            $("#edit-sign").val(product.sign);
            $("#edit-quantity-total").val(product.stock_quantity);
            $("#edit-id_supplier").val(product.id_supplier).trigger("chosen:updated");
            $("#edit-id_employee").val(product.employee);
        }
    }

    async function saveMovement(e, action = 'editMovement') {
        e.preventDefault();
        e.stopImmediatePropagation();
        e.stopPropagation();

        const formElem = document.getElementById('edit-movement-form');
        const formData = new FormData(formElem);
        formData.append('ajax', 1);
        formData.append('action', action);
        formData.append('id_document', $("#edit-id_document").val());
        formData.append('document_number', $("#edit-document_number").val());
        formData.append('document_date', $("#edit-document_date").val());

        Swal.fire({
            title: 'Conferma modifica movimento?',
            text: "Questo movimento verrà modificato!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'SI',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                sendMovement(formData);
            }
        });
    }

    async function sendMovement(formData) {
        const formDataObj = Object.fromEntries(formData.entries());
        const body = new URLSearchParams(formDataObj);

        const response = await fetch(
            '{$admin_controller_url}',
            {
                method: 'POST',
                body: body,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }
        );

        const result = await response.json();

        if (result.status == true) {
            Swal.fire(
                'Modificato!',
                'Il movimento è stato salvato correttamente.',
                'success'
            );
            $('#editMovementModal').modal('hide');
        } else {
            Swal.fire(
                'Errore!',
                result.error_msg,
                'error'
            );
        }
    }

    async function getProductQuantities(id_product_attribute) {
        console.log("firing getProductQuantities", id_product_attribute);

        const response = await fetch(
            '{$admin_controller_url}',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    ajax: 1,
                    action: 'getProductQuantity',
                    id_product: document.getElementById('edit-id_product').value,
                    id_product_attribute: id_product_attribute
                })
            }
        );

        const result = await response.json();
        if (result.success) {
            document.getElementById('edit-quantity-actual').value = result.quantity;
            document.getElementById('edit-quantity').value = '0';
            document.getElementById('edit-quantity-total').value = result.quantity;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initModalEditMovement();

        document.getElementById("edit-ean13").addEventListener("input", (e) => getProductEan13(e.target.value));
        document.getElementById("edit-id_product_attribute").addEventListener("click", (e) => getProductQuantities(e.target.value));

        $("#edit-reference").autocomplete({
            source: function(request, response) {
                $.ajax({
                        url: "{$admin_controller_url}",
                        dataType: "json",
                        data: {
                            ajax: 1,
                            action: 'getProductAutocomplete',
                            term: request.term,
                            type: 'reference'
                        }
                    })
                    .success(function(data) {
                        response(data);
                    })
                    .fail(function() {
                        jAlert('AJAX FAIL');
                    });
            },
            minLength: 3,
            select: function(e, ui) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();

                $("#edit-reference").val(ui.item.reference);
                $("#edit-id_product").val(ui.item.id_product);
                $("#edit-product_name").val(ui.item.product_name);
                $("#edit-id_product_attribute").html(ui.item.options).val(ui.item.id_product_attribute).trigger("chosen:updated");
                $("#edit-ean13").val(ui.item.ean13);
                $("#edit-quantity-actual").val("0");
                $("#edit-quantity").val("0");
                $("#edit-quantity-total").val("0");

                return false;
            }
        });

        $("#edit-product_name").autocomplete({
            source: function(request, response) {
                $.ajax({
                        url: "{$admin_controller_url}",
                        dataType: "json",
                        data: {
                            ajax: 1,
                            action: 'getProductAutocomplete',
                            term: request.term,
                            type: 'product'
                        }
                    })
                    .success(function(data) {
                        response(data);
                    })
                    .fail(function() {
                        jAlert('AJAX FAIL');
                    });
            },
            minLength: 3,
            select: function(e, ui) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();

                $("#edit-reference").val(ui.item.reference);
                $("#edit-id_product").val(ui.item.value);
                $("#edit-product_name").val(ui.item.product_name);
                $("#edit-id_product_attribute").html(ui.item.options).val(ui.item.id_product_attribute).trigger("chosen:updated");
                $("#edit-ean13").val(ui.item.ean13);
                $("#edit-quantity-actual").val("0");
                $("#edit-quantity").val("0");
                $("#edit-quantity-total").val("0");

                return false;
            }
        });

        $("#edit-quantity").on("input", function() {
            let quantity = parseInt($(this).val(), 10);
            if (isNaN(quantity)) quantity = 0;

            const sign = parseInt($("#edit-sign").val(), 10);
            const stock = parseInt($("#edit-quantity-actual").val(), 10);

            $("#edit-quantity-total").val(stock + (sign * quantity));
        });

        $("input").on("focus", function() {
            $(this).select();
        })

        $("#submit-edit-movement").on("click", (e) => saveMovement(e, 'addMovement'));
    });
</script>