export const quantityHandler = {
    async getProductQuantities(id_product_attribute, adminControllerUrl) {
        const response = await fetch(adminControllerUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                ajax: 1,
                action: "getProductQuantity",
                id_product: document.getElementById("detail-id_product").value,
                id_product_attribute: id_product_attribute
            })
        });

        const result = await response.json();
        if (result.success) {
            document.getElementById("detail-quantity-actual").value = result.quantity;
            document.getElementById("detail-quantity").value = "0";
            document.getElementById("detail-quantity-total").value = result.quantity;
        }
    },

    updateTotalQuantity() {
        const quantity = parseInt($("#detail-quantity").val(), 10) || 0;
        const sign = parseInt($("#detail-sign").val(), 10) || 0;
        const stock = parseInt($("#detail-quantity-actual").val(), 10) || 0;

        $("#edit-quantity-total").val(stock + sign * quantity);
    }
};
