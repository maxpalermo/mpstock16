export const documentHandler = {
    newDocument() {
        $("#modal-new-document").modal('show');
    },

    async getDocument(id, adminControllerUrl) {
        try {
            const response = await fetch(
                adminControllerUrl,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        ajax: true,
                        action: 'getDocument',
                        id: id
                    })
                });
            return response.json();
        } catch (error) {
            console.error('Error fetching document:', error);
            return null;
        }
    },

    async fillFormMovement(response, modalEditMovement) {
        $(modalEditMovement).find("#edit-id_mpstock_movement").val(response.id_mpstock_movement);
        $(modalEditMovement).find("#edit-id_mpstock_mvt_reason").val(response.id_mpstock_mvt_reason).trigger("chosen:updated");
        $(modalEditMovement).find("#edit-ean13").val(response.ean13).trigger("chosen:updated");
        $(modalEditMovement).find("#edit-product_name").val(response.product_name);
        $(modalEditMovement).find("#edit-id_product").val(response.id_product);
        $(modalEditMovement).find("#edit-id_product_attribute").html(response.attributes).trigger("chosen:updated");
        $(modalEditMovement).find("#edit-quantity").val(response.quantity);
        $(modalEditMovement).find("#edit-id_supplier").val(response.id_supplier).trigger("chosen:updated");
        $(modalEditMovement).find("#edit-unit_price").val(response.unit_price);
        $(modalEditMovement).find("#edit-sign").val(response.sign);
        $(modalEditMovement).find("#edit-quantity-actual").val(response.stock_quantity);
        $(modalEditMovement).find("#edit-quantity-total").val(response.stock_quantity);
    }
};
