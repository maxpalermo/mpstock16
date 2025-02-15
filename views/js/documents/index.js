import { tableHandler } from "./tableHandler.js";
import { documentHandler } from "./documentHandler.js";
import { detailsHandler } from "./detailsHandler.js";

document.addEventListener("DOMContentLoaded", function () {
    // Initialize DataTable
    tableHandler.initDataTable(adminControllerUrl, baseUrl);

    // Event delegation for invoice details toggle
    document.querySelector("#table-documents tbody").addEventListener("click", function (e) {
        const toggleButton = e.target.closest(".toggleInvoiceDetail");
        if (toggleButton) {
            detailsHandler.toggleInvoiceDetail(e, adminControllerUrl);
        }
    });

    // Event listener for new document button
    document.getElementById("page-header-desc-mpstock_document_v2-new_document").addEventListener("click", documentHandler.newDocument);

    document.querySelectorAll("input").forEach((input) => {
        input.addEventListener("focus", function () {
            this.select();
        });
    });
});
