/*
    id_mpstock_movement = "524371"
    ean13 = "8050687029896"
    reference = null
    product_name = "Pantalone con elastico Bianco Cotone 190 gr/mq ISACCO 044000"
    product_combination = "Bianco - M - Monocolore"
    stock_movement = "0"
    price_te = "0.000000"

    id_document = "19133"
    id_warehouse = "0"
    id_supplier = "3"
    document_number = "15856223"
    document_date = "0000-00-00"
    id_mpstock_mvt_reason = "66"
    mvt_reason = "Ordine Banco"
    id_product = "2486"
    id_product_attribute = "31588"
    upc = null
    wholesale_price_te = "0.000000"
    id_employee = "16"
    id_order = "0"
    id_order_detail = "0"
    stock_quantity_before = "103"
    stock_quantity_after = "103"
    date_add = "2025-02-14 16:48:00"
    date_upd = "0000-00-00 00:00:00"
    employee = "Massimiliano Palermo"
*/

export const detailsHandler = {
    addMovementToInvoice: function (id_invoice) {
        const modal = $("#modal-movement-detail");

        // Resetta il form
        modal.find("form")[0].reset();

        // Imposta l'ID documento nel form
        $("#detail-id_mpstock_document").val(id_invoice);

        // Mostra la modale
        modal.modal("show");

        console.log(`Aggiungi movimento al documento ${id_invoice}`);
    },

    createMovementRows(movements) {
        return movements
            .map((movement) => {
                return `
                        <tr>
                            <td>${movement.id_mpstock_movement}</td>
                            <td>${movement.ean13}</td>
                            <td>${movement.reference}</td>
                            <td>${movement.product_name}</td>
                            <td>${movement.product_combination}</td>
                            <td>${movement.stock_quantity_before}</td>
                            <td>${movement.stock_movement}</td>
                            <td>${movement.stock_quantity_after}</td>
                            <td>${movement.price_te}</td>
                            <td>${detailsHandler.createActionButtons(movement.id_mpstock_movement)}</td>
                        </tr>
                    `;
            })
            .join("");
    },

    createActionButtons(movementId) {
        return `
            <div class="actions d-flex justify-content-center">
                <button class="btn btn-primary btn-small mr-1 btn-movement-action" data-action="edit" data-id="${movementId}"><i class="material-icons">edit</i><span>Modifica</span></button>
                <button class="btn btn-danger btn-small btn-movement-action" data-action="delete" data-id="${movementId}"><i class="material-icons">delete</i><span>Elimina</span></button>
            </div>
        `;
    },

    calculateTotalQuantity: function (e) {
        const quantityInput = document.getElementById("detail-quantity");
        const signInput = document.getElementById("detail-sign");
        const currentStockInput = document.getElementById("detail-quantity-actual");
        const totalStockInput = document.getElementById("detail-quantity-total");

        if (!quantityInput || !signInput || !currentStockInput || !totalStockInput) {
            console.error("Elementi input non trovati");
            return;
        }

        const quantity = parseFloat(quantityInput.value) || 0;
        const sign = parseInt(signInput.value) || 1;
        const currentStock = parseFloat(currentStockInput.value) || 0;

        totalStockInput.value = currentStock + quantity * sign;
    },

    async actionMovement(button, e) {
        e.stopPropagation();
        e.stopImmediatePropagation();

        const action = button.dataset.action;
        const id = button.dataset.id;

        console.log("CLICKED ", button, action, id);

        switch (action) {
            case "edit":
                console.log("Editing movement:", id);
                //visualizzo il pannello dei dettagli del movimento e riempio i campi
                showEditMovementModal(id);
                break;
            case "delete":
                Swal.fire({
                    title: "Sei sicuro?",
                    text: "Vuoi eliminare questo movimento?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sì, elimina!",
                    cancelButtonText: "Annulla"
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log("Deleting movement:", id);
                        // Implementa qui la logica per eliminare il movimento
                    }
                });
                break;
        }
    },

    async toggleInvoiceDetail(e, adminControllerUrl) {
        let button = e.target;
        if (button.tagName === "I") {
            button = button.parentElement;
        }

        const tr = button.closest("tr");
        const id_invoice = button.dataset.id_invoice;
        const icon = button.querySelector("i");

        if (tr.nextElementSibling && tr.nextElementSibling.classList.contains("details-row")) {
            tr.nextElementSibling.remove();
            icon.textContent = "add_circle";
            return;
        }

        const response = await fetch(adminControllerUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                ajax: true,
                action: "getDocumentDetails",
                id: id_invoice
            })
        });
        const json = await response.json();
        const movements = json.movements;

        const detailRow = document.createElement("tr");
        detailRow.classList.add("details-row");

        const detailCell = document.createElement("td");
        detailCell.setAttribute("colspan", "10");

        const detailTable = document.createElement("table");
        detailTable.classList.add("table", "table-striped", "table-bordered", "table-hover", "details-table");

        detailTable.innerHTML = `
            <thead>
                <tr>
                    <th colspan="10">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn-add-movement btn btn-sm btn-primary d-flex align-items-center" data-id_document="${id_invoice}">
                                <i class="material-icons mr-1">add_circle</i>
                                <span>Aggiungi movimento</span>
                            </button>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th>Id</th>
                    <th>EAN13</th>
                    <th>Riferimento</th>
                    <th>Prodotto</th>
                    <th>Attributo</th>
                    <th>Qtà iniziale</th>
                    <th>Quantità</th>
                    <th>Qtà magazzino</th>
                    <th>Prezzo</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                ${this.createMovementRows(movements)}
            </tbody>
        `;

        detailCell.appendChild(detailTable);
        detailRow.appendChild(detailCell);
        tr.after(detailRow);

        icon.textContent = "remove_circle";

        document.querySelectorAll(".btn-movement-action").forEach((btn) => {
            btn.addEventListener("click", () => detailsHandler.actionMovement(btn, e));
        });

        const btnNewMovement = document.querySelectorAll(".btn-add-movement");
        console.log("Buttons", btnNewMovement.length);

        btnNewMovement.forEach((btn) => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                const id_document = btn.dataset.id_document;

                console.log("btnNewMovement", btn, id_document);

                showNewMovementModal(id_document);
            });
        });
    }
};

async function showNewMovementModal(id_document) {
    // Azzero il form
    $("#movement-detail-form")[0].reset();

    //Carico i dati del documento tramite fetch
    try {
        const response = await fetch(adminURL, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                ajax: 1,
                action: "getDocumentDetails",
                id: id_document
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || "Errore nel recupero dei dati");
        }

        const document = data;

        // Mostra il modale
        $("#modal-movement-detail").modal("show");
    } catch (error) {
        Swal.fire("Errore", error.message, "error");
        console.error("showEditMovementModal error:", error);
    }
}

async function showEditMovementModal(id) {
    try {
        const response = await fetch(adminURL, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                action: "getMovementDetails",
                id_mpstock_movement: id
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || "Errore nel recupero dei dati");
        }

        const movement = data;

        // Popola i campi con gestione degli errori
        const setValue = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.value = value;
            else console.error(`Elemento non trovato: ${id}`);
        };

        //ripristino l'immagine di default
        document.getElementById("detail-image-preview").src = "https://placehold.co/160x240";

        setValue("detail-id_mpstock_movement", movement.id);
        setValue("detail-id_product", movement.id_product);
        $(document.getElementById("detail-id_product")).trigger("chosen:updated");

        setValue("detail-id_mpstock_document", movement.id_mpstock_document);
        setValue("detail-reference", movement.reference);
        setValue("detail-ean13", movement.ean13);
        setValue("detail-product_name", movement.product_name);
        document.getElementById("detail-id_product_attribute").innerHTML = movement.options;
        setValue("detail-id_product_attribute", movement.id_product_attribute);
        $(document.getElementById("detail-id_product_attribute")).trigger("chosen:updated");
        setValue("detail-id_mpstock_mvt_reason", movement.id_mpstock_mvt_reason);
        $(document.getElementById("detail-id_mpstock_mvt_reason")).trigger("chosen:updated");
        setValue("detail-quantity-actual", movement.stock_quantity_before);
        setValue("detail-quantity", Math.abs(movement.stock_movement));
        setValue("detail-quantity-total", movement.stock_quantity_after);
        setValue("detail-sign", movement.sign);
        setValue("detail-id_supplier", movement.id_supplier);
        $(document.getElementById("detail-id_supplier")).trigger("chosen:updated");
        setValue("detail-id_employee", movement.employee_name);

        console.log("IMAGE SRC:", movement.image_src);

        if (movement.image_src) {
            document.getElementById("detail-image-preview").src = movement.image_src;
        }

        // Aggiorna quantità
        updateQuantityFields();

        // Mostra il modale
        $("#modal-movement-detail").modal("show");
    } catch (error) {
        Swal.fire("Errore", error.message, "error");
        console.error("showEditMovementModal error:", error);
    }
}

function updateQuantityFields() {
    const quantity = parseFloat(document.getElementById("detail-quantity").value) || 0;
    const sign = parseInt(document.getElementById("detail-sign").value);
    const current = parseFloat(document.getElementById("detail-quantity-actual").value) || 0;

    document.getElementById("detail-quantity-total").value = current + quantity * sign;
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".btn-movement-action").forEach((btn) => {
        btn.addEventListener("click", (e) => detailsHandler.actionMovement(btn, e));
    });

    document.getElementById("detail-quantity").addEventListener("input", (e) => {
        detailsHandler.calculateTotalQuantity(e);
    });
});

$("#edit-quantity").on("input", function () {
    let quantity = parseInt($(this).val(), 10);
    if (isNaN(quantity)) quantity = 0;

    const sign = parseInt($("#edit-sign").val(), 10);
    const stock = parseInt($("#edit-quantity-actual").val(), 10);

    $("#edit-quantity-total").val(stock + sign * quantity);
});
