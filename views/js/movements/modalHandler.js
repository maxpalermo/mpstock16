export const modalHandler = {
    initModalEditMovement() {
        const modalEditMovement = $("#modal-edit-movement");
        modalEditMovement.find("#edit-id_mpstock_movement").val("");
        modalEditMovement.find("#edit-ean13").val("");
        modalEditMovement.find("#edit-product_name").val("");
        modalEditMovement.find("#edit-id_product_attribute").val("");
        modalEditMovement.find("#edit-id_mpstock_mvt_reason").val("").trigger("chosen:updated");
        modalEditMovement.find("#edit-quantity").val("");
        modalEditMovement.find("#edit-id_supplier").val("").trigger("chosen:updated");
        modalEditMovement.find("#edit-unit_price").val("");
        modalEditMovement.find("#edit-sign").val("");
        modalEditMovement.find("#edit-quantity-actual").val("");
        modalEditMovement.find("#edit-quantity-total").val("");
    },

    fillCombinations(id_product, adminControllerUrl) {
        $.ajax({
            url: adminControllerUrl,
            type: "POST",
            dataType: "json",
            data: {
                ajax: 1,
                action: "getProductCombinations",
                id_product: id_product
            },
            success: function (response) {
                const combinations = response;
                const productCombinations = document.getElementById("edit-id_product_attribute");
                $(productCombinations).empty();
                combinations.forEach(function (combination) {
                    $(productCombinations).append("<option value='" + combination.value + "'>" + combination.label + "</option>");
                });
                $(productCombinations).trigger("chosen:updated");
            },
            error: function () {
                jAlert("AJAX FAIL");
            }
        });
    }
};
