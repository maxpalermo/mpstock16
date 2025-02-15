import { utils } from "./utils.js";

export const tableHandler = {
    dataTable: null,
    dataTablesMovements: [],

    initDataTable(adminControllerUrl, baseUrl) {
        const table = $("#table-documents").DataTable({
            order: [[1, "desc"]],
            language: {
                url: "/modules/mpstockv2/views/js/plugins/datatables/lang/it_IT.json"
            },
            processing: true,
            serverSide: true,
            ajax: {
                url: adminControllerUrl,
                type: "POST",
                data: {
                    ajax: 1,
                    action: "getDocuments"
                }
            },
            columns: [
                {
                    data: "checkbox",
                    orderable: false,
                    searchable: false,
                    name: "checkbox",
                    render: function (data, type, row) {
                        let node = document.createElement("div");
                        $(node)
                            .addClass("d-flex justify-content-between")
                            .append($("<button>").addClass("btn btn-link toggleInvoiceDetail").attr("data-id_invoice", row.id_mpstock_document).append('<i class="material-icons">add_circle</i>'))
                            .append('<input type="checkbox" name="id_mpstock_document[]" value="' + row.id_mpstock_document + '" />');
                        return node;
                    }
                },
                {
                    data: "id_mpstock_document",
                    render: function (data, type, row) {
                        return '<a href="' + baseUrl + "admin/mpstockv2/documents/edit/" + data + '">' + data + "</a>";
                    },
                    name: "a.id_mpstock_document"
                },
                {
                    data: "number_document",
                    name: "a.number_document"
                },
                {
                    data: "date_document",
                    name: "a.date_document"
                },
                {
                    data: "mvt_reason",
                    name: "m.name"
                },
                {
                    data: "supplier",
                    name: "s.name"
                },
                {
                    data: "tot_document_ti",
                    name: "a.tot_document_ti",
                    render: utils.formatCurrency
                },
                {
                    data: "employee",
                    name: "e.lastname"
                },
                {
                    data: "date_add",
                    name: "a.date_add"
                },
                {
                    data: "actions",
                    orderable: false,
                    searchable: false
                }
            ],
            initComplete: (settings, json) => {
                this.dataTable = settings.oInstance.api();
                this.initTableFilters();
                this.initCheckAllHandler();
            }
        });

        this.dataTable = table;
    },

    initTableFilters() {
        this.dataTable.columns().every(function () {
            let column = this;
            let header = column.header();
            let data_field = header.dataset.field;

            if (data_field) {
                switch (data_field) {
                    case "id_mpstock_document":
                    case "number_document":
                    case "mvt_reason":
                    case "supplier":
                    case "tot_document_ti":
                    case "employee":
                        let input = document.createElement("input");
                        $(input).addClass("form-control");
                        header.replaceChildren(input);

                        $(input).on("keyup change", function () {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                        break;
                }
            }
        });
    },

    initCheckAllHandler() {
        $("#check-all-documents").on("change", function () {
            let checked = $(this).prop("checked");
            $('#table-documents tbody input[type="checkbox"]').prop("checked", checked);
        });
    }
};
