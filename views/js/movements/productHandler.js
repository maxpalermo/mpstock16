export const productHandler = {
    async getProductEan13(ean13, adminControllerUrl) {
        if (ean13.length == 13) {
            const response = await fetch(adminControllerUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({
                    ajax: 1,
                    action: "getEan13",
                    ean13: ean13,
                    id_mvt_reason: $("#edit-id_mpstock_mvt_reason").val()
                })
            });

            const product = await response.json();

            if (product.length == 0) {
                swal.fire({
                    title: "Errore",
                    text: "Prodotto non trovato",
                    icon: "error"
                });
                return false;
            }

            $("#edit-id_mpstock_movement").val("0");
            $("#edit-product_name").val(product.name);
            $("#edit-id_product").val(product.id_product);
            $("#edit-id_product_attribute").html(product.attributes).trigger("chosen:updated").val(product.id_product_attribute);
            $("#edit-quantity-actual").val(product.stock_quantity);
            $("#edit-quantity").val("0");
            $("#edit-sign").val(product.sign);
            $("#edit-quantity-total").val(product.stock_quantity);
        }
    },

    async getProductAttributeDetails(id_product_attribute) {
        const response = await fetch(adminControllerUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                ajax: 1,
                action: "getProductAttributeDetails",
                id_product_attribute: id_product_attribute
            })
        });

        const result = await response.json();
        if (!result.success) {
            swal.fire({
                title: "Errore",
                text: result.message,
                icon: "error"
            });
        } else {
            let quantity = 0;
            if (result.data.image_src) {
                document.getElementById("detail-image-preview").src = result.data.image_src;
            }
            if (result.data.quantity) {
                quantity = parseInt(result.data.quantity, 10);
            }

            document.getElementById("detail-quantity-actual").value = quantity;
            document.getElementById("detail-quantity").value = "0";
            document.getElementById("detail-quantity-total").value = quantity;
        }
    },

    initProductAutocomplete(adminControllerUrl) {
        const ean13_elem = document.getElementById("detail-ean13");
        if (ean13_elem) {
            ean13_elem.addEventListener("input", (e) => productHandler.getProductEan13(e.target.value, adminControllerUrl));
        }

        const reference_elem = document.getElementById("detail-reference");
        if (reference_elem) {
            $(reference_elem).autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: adminControllerUrl,
                        dataType: "json",
                        data: {
                            ajax: 1,
                            action: "getProductAutocomplete",
                            term: request.term,
                            type: "reference"
                        }
                    })
                        .success(function (data) {
                            response(data);
                        })
                        .fail(function () {
                            jAlert("AJAX FAIL");
                        });
                },
                minLength: 3,
                select: function (e, ui) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.stopPropagation();

                    $("#detail-reference").val(ui.item.reference);
                    $("#detail-id_product").val(ui.item.id_product);
                    $("#detail-product_name").val(ui.item.product_name);
                    $("#detail-id_product_attribute").html(ui.item.options).val(ui.item.id_product_attribute).trigger("chosen:updated");
                    $("#detail-ean13").val(ui.item.ean13);
                    $("#detail-quantity-actual").val("0");
                    $("#detail-quantity").val("0");
                    $("#detail-quantity-total").val("0");
                    if (ui.item.cover) {
                        $("#detail-image-preview").attr("src", ui.item.cover);
                    } else {
                        $("#detail-image-preview").attr("src", "https://placehold.co/160x240");
                    }

                    return false;
                }
            });
        }

        const product_elem = document.getElementById("detail-product_name");
        if (product_elem) {
            $(product_elem).autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: adminURL,
                        dataType: "json",
                        data: {
                            ajax: 1,
                            action: "getProductAutocomplete",
                            term: request.term,
                            type: "product"
                        }
                    })
                        .success(function (data) {
                            response(data);
                        })
                        .fail(function () {
                            jAlert("AJAX FAIL");
                        });
                },
                minLength: 3,
                select: function (e, ui) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.stopPropagation();

                    $("#detail-product_name").val(ui.item.product_name);
                    $("#detail-id_product").val(ui.item.id_product);
                    $("#detail-ean13").val(ui.item.ean13);
                    $("#detail-quantity-actual").val("0");
                    $("#detail-quantity").val("0");
                    $("#detail-quantity-total").val("0");

                    return false;
                }
            });
        }

        $("#edit-reference").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: adminControllerUrl,
                    dataType: "json",
                    data: {
                        ajax: 1,
                        action: "getProductAutocomplete",
                        term: request.term,
                        type: "reference"
                    }
                })
                    .success(function (data) {
                        response(data);
                    })
                    .fail(function () {
                        jAlert("AJAX FAIL");
                    });
            },
            minLength: 3,
            select: function (e, ui) {
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
            source: function (request, response) {
                $.ajax({
                    url: adminControllerUrl,
                    dataType: "json",
                    data: {
                        ajax: 1,
                        action: "getProductAutocomplete",
                        term: request.term,
                        type: "product"
                    }
                })
                    .success(function (data) {
                        response(data);
                    })
                    .fail(function () {
                        jAlert("AJAX FAIL");
                    });
            },
            minLength: 3,
            select: function (e, ui) {
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

        const detail_product_attribute = document.getElementById("detail-id_product_attribute");
        if (detail_product_attribute) {
            detail_product_attribute.addEventListener("click", (e) => productHandler.getProductAttributeDetails(e.target.value));
        }
    }
};
