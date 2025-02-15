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
 **}

<style>
    .drop-zone {
        min-height: 100px;
        border: 1px dashed #ccc;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 10px;
    }

    .drop-zone-hover {
        border: 1px dashed #666;
        background-color: #f0f0f0;
    }

    .drop-zone-label {
        text-align: center;
        font-size: 2rem;
        color: #666;
    }
</style>

<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-upload"></i>
        <span>{l s='Importa documento XML' mod='mpstock'}</span>
    </div>
    <div class="panel-body">
        <div class="col-md-4">
            <div class="form-group">
                <label for="mvtReason">{l s='Tipo movimento' mod='mpstock'}</label>
                <select name="mvtReason" id="mvtReason" class="form-control chosen">
                    {foreach $mvtReasons as $mvtReason}
                        <option value="{$mvtReason.id_mpstock_mvt_reason}">{$mvtReason.name}</option>
                    {/foreach}
                </select>
            </div>
            <div class="form-wrapper">
                <div class="form-group">
                    <label for="document_xml" class="form-control-label">{l s='Seleziona file XML' mod='mpstock'}</label>
                    <input type="file" id="document_xml" name="document_xml" accept=".xml" style="display: none;">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="icon icon-file"></i>
                        </span>
                        <input type="text" class="form-control bg-light" id="fake-file" readonly>
                        <span class="input-group-addon pointer" onclick="$('#document_xml').trigger('click');">
                            <span class="item-link" for="document_xml">{l s='Scegli file' mod='mpstock'}</span>
                        </span>
                    </div>
                    <script type="text/javascript">
                        document.addEventListener('DOMContentLoaded', function() {
                            document.getElementById('document_xml').addEventListener('change', function() {
                                const file = this.files[0];
                                if (typeof file === 'undefined') {
                                    document.getElementById('fake-file').value = "";
                                    return;
                                }

                                const fileName = file.name;

                                document.getElementById('fake-file').value = fileName;
                            });
                        });
                    </script>
                </div>
                <div class="form-group">
                    <div class="drop-zone pointer">
                        <div class="drop-zone-label">
                            <span class="fa fa-file-import"></span>
                            <span>{l s='Trascina e rilascia il file qui o clicca per selezionare' mod='mpstock'}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group" id="xml_preview">
            <div class="d-flex justify-content-center" id="xml_preview_content">
                <div class="panel" style="width: 100%;">
                    <div class="panel-heading">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="panel-title">Elenco prodotti da importare:</span>
                                <span id="tot-rows-list" class="ml-3 text-info text-bold">0</span>
                            </div>
                            <div>
                                <button class="btn btn-primary btn-import">
                                    <i class="icon icon-download"></i>
                                    <span>Importa</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table id="productTable" class="table table-striped table-bordered"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    const fetchURL = "{$admin_controller_url}";
    let dataSource = [];
    let dataTable = null;

    async function createTableResponse(parsedDocument) {
        const formData = new FormData();
        formData.append('ajax', 1);
        formData.append('action', 'renderTableImport');
        formData.append('document', JSON.stringify(parsedDocument));

        const response = await fetch(
            "{$admin_controller_url}", 
            {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            }
        );

        data = await response.json();

        if (data.success) {
            const previewDiv = document.getElementById('xml_preview');
            previewDiv.innerHTML = data.html;
            $(previewDiv).show();
        }

        return data;
    }

    async function processFile(file) {
        dataSource = [];
        $(dataTable).DataTable().clear();

        if (typeof file === 'undefined') {
            $("#fake-file").val("");
            return false;
        }

        var formData = new FormData();
        formData.append('document_xml', file);
        formData.append('ajax', true);
        formData.append('action', 'parseFile');

        const response = await fetch(
            "{$admin_controller_url}", 
            {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            }
        );

        const data = await response.json();
        console.log("DATA", data);

        if (data.success) {
            console.log("response data:", data);

            $('#mvtReason').val(data.mvtReasonId).trigger('chosen:updated');
            dataSource = data.rows;
            {literal}
                console.log("Totale righe: ", data.rows.length);

                $("#tot-rows-list").html(`${data.rows.length} prodotti`)
            {/literal}

            dataTable.clear();
            dataTable.rows.add(dataSource)
            dataTable.draw();
        }
    }

    async function importData(dataRows) {
        let filename = $('#document_xml').val().split('\\').pop();

        let data = {
            filename: filename,
            mvtReasonId: $('#mvtReason').val(),
            rows: dataRows,
            ajax: 1,
            action: 'importData'
        };

        const response = await fetch(fetchURL, {
            headers: {
                'credentials': 'same-origin',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
            method: 'POST',
            body: JSON.stringify(data)
        });
        const dataJson = await response.json();

        console.log(dataJson);

        if (dataJson.success) {
            Swal.fire({
                title: 'Importazione',
                text: dataJson.message,
                icon: 'success'
            })
        } else {
            Swal.fire({
                title: 'Importazione',
                text: dataJson.message,
                icon: 'error'
            })
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        dataTable = $('#productTable').DataTable({
            pageLength: 10,
            lengthMenu: [
                [10, 20, 50, 100, 200, 500, -1],
                [10, 20, 50, 100, 200, 500, "Tutti"]
            ],
            pageLength: -1,
            paging: true,
            searching: true,
            ordering: true,
            order: [
                [1, "asc"]
            ],
            language: {
                "url": "/modules/mpstockv2/views/js/plugins/datatables/lang/it_IT.json"
            },
            data: dataSource,
            columns: [{
                    title: '<input type="checkbox" id="check-all-movements">',
                    name: 'checkbox',
                    data: 'checkbox',
                    defaultContent: false,
                    render: function(data, type, row) {
                        if (data == 1) {
                            return '<input type="checkbox" class="row-checkbox" checked>';
                        }
                        return '<input type="checkbox" class="row-checkbox">';
                    }
                },
                { title: 'EAN13', data: 'ean13', defaultContent: '--' },
                { title: 'Reference', data: 'reference', defaultContent: '--' },
                { title: 'Prodotto', data: 'product_name', defaultContent: '--' },
                { title: 'Combinazione', data: 'combination', defaultContent: '--' },
                { title: 'Quantità', data: 'qty', defaultContent: '0' },
                {
                    title: 'Prezzo',
                    data: 'price',
                    defaultContent: '0',
                    render: function(data, type, row) {
                        return Number(data).toLocaleString("it-IT", {
                            maximumFractionDigits: 2,
                            minimumFractionDigits: 2,
                            style: 'currency',
                            currency: 'EUR'
                        });
                    }
                },
                {
                    title: 'Prezzo acq',
                    data: 'wholesale_price',
                    defaultContent: '0',
                    render: function(data, type, row) {
                        return Number(data).toLocaleString("it-IT", {
                            maximumFractionDigits: 2,
                            minimumFractionDigits: 2,
                            style: 'currency',
                            currency: 'EUR'
                        });
                    }
                },
                {
                    title: 'Esiste',
                    data: 'exists',
                    defaultContent: false,
                    className: 'dt-center',
                    render: function(data, type, row) {
                        if (data == 1) {
                            return '<span class="text-success"><i class="fa fa-check"></i></span>';
                        } else {
                            return '<span class="text-danger"><i class="fa fa-times"></i></span>';
                        }
                    }
                },
            ],
            initComplete: function(e) {
                document.getElementById('check-all-movements').addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    console.log("CHECKED", this.checked);
                    $(".row-checkbox").prop('checked', this.checked);
                });
            }
        });

        $(document).on('click', '.btn-import', function(e) {
            e.stopPropagation();
            e.stopImmediatePropagation();

            Swal.fire({
                title: 'Conferma importazione?',
                text: "Saranno importati i record selezionati!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si, importa!',
                cancelButtonText: 'No, ho cambiato idea...'
            }).then((result) => {
                if (result.isConfirmed) {
                    let dtData = dataTable.rows().data();
                    let rowsData = [];
                    $.each(dtData, function() {
                        if (this.checkbox == 1) {
                            rowsData.push(this);
                        }
                    })

                    Swal.fire({
                        title: 'Attendere...',
                        html: 'Sto importando i record selezionati...',
                        icon: 'info',
                        showCancelButton: false,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        onBeforeOpen: () => {
                            Swal.showLoading()
                        }
                    })

                    importData(rowsData);
                }
            });

            return false;
        })

        $('#document_xml').on('change', function(event) {
            event.preventDefault();
            var file = event.target.files[0];

            processFile(file);

            return false;
        });

        $('#parse_xml').on('click', function(event) {
            event.preventDefault();

            var file = $('#document_xml')[0].files[0];

            if (typeof file === 'undefined') {
                alert('{l s='Per favore seleziona un file' mod='mpstock'}');
                return false;
            }

            var formData = new FormData();
            formData.append('ajax', true);
            formData.append('action', 'importXML');
            formData.append('document_xml', file);
            formData.append('mvtReason', $('#mvtReason').val());

            $.ajax({
                url: "{$admin_controller_url}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success === false) {
                        alert(response.message);
                    } else {
                        alert(response.message);
                    }
                }
            });

            return false;
        });

        $('.drop-zone').on('click', function(event) {
            event.preventDefault();
            $('#document_xml').trigger('click');
        });

        $('.drop-zone').on('dragover', function(event) {
            event.preventDefault();
            $(this).addClass('drop-zone-hover');
        });

        $('.drop-zone').on('dragleave', function(event) {
            event.preventDefault();
            $(this).removeClass('drop-zone-hover');
        });

        $('.drop-zone').on('drop', function(event) {
            event.preventDefault();
            $(this).removeClass('drop-zone-hover');
            const file = event.originalEvent.dataTransfer.files[0];
            const fileInput = document.getElementById('document_xml');

            // Create a new DataTransfer object
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);

            // Assign the files to the input
            fileInput.files = dataTransfer.files;

            // Set the value of the input
            $("#fake-file").val(file.name);
            // Trigger change event
            $('#document_xml').trigger('change');
        });
    });
</script>