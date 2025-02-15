import { productHandler } from "./productHandler.js";
import { quantityHandler } from "./quantityHandler.js";
import { modalHandler } from "./modalHandler.js";
import { movementHandler } from "./movementHandler.js";

document.addEventListener("DOMContentLoaded", function () {
    // Initialize modal
    modalHandler.initModalEditMovement();

    // Add event listeners

    document.getElementById("detail-quantity").addEventListener("input", () => quantityHandler.updateTotalQuantity());

    document.getElementById("submit-movement-detail").addEventListener("click", (e) => movementHandler.saveMovement(e, "addMovement", adminControllerUrl));

    // Initialize product autocomplete
    productHandler.initProductAutocomplete(adminControllerUrl);

    // Add focus handlers
    $("#detail-quantity, #detail-unit_price").on("focus", function () {
        $(this).select();
    });
});
