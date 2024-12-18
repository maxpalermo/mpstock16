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
        .<div class="form-group">
            <label for="mvtReason">{l s='Tipo movimento' mod='mpstock'}</label>
            <select name="mvtReason" id="mvtReason" class="form-control chosen">
                {foreach $mvtReasons as $mvtReason}
                    <option value="{$mvtReason.id_mpstock_mvt_reason}">{$mvtReason.name}</option>
                {/foreach}
            </select>
        </div>
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
        <div class="form-group">
            <button type="button" id="parse_xml" class="btn btn-primary">
                <span class="fa fa-play"></span>
                {l s='Processa XML' mod='mpstock'}
            </button>
        </div>
        <div class="form-group" style="display: none;" id="xml_preview">
            <div class="alert alert-info">
                <h4 class="alert-heading">{l s='Anteprima' mod='mpstock'}</h4>
            </div>
            <div class="d-flex justify-content-center" id="xml_preview_content">
                <!-- Table content will be inserted here -->
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function createTableResponse(response) {
        const dateIta = new Date(response.date[0]).toLocaleString('it-IT');

        let summaryHtml = '<div class="document-summary">';
        summaryHtml += `<p><strong>Documento:</strong> ${ response.document }</p>`;
        summaryHtml += `<p><strong>Tipo:</strong> ${ response.type }</p>`;
        summaryHtml += `<p><strong>Data:</strong> ${ dateIta }</p>`;
        summaryHtml += `<p><strong>Movimento:</strong> ${ response.movement }</p>`;
        summaryHtml += '</div>';

        let tableHtml = '<table class="table table-striped table-condensed table-bordered" style="margin: 10px auto; width: auto;">';
        tableHtml += '<thead><tr>';
        tableHtml += '<th>EAN13</th><th>Riferimento</th><th>Quantità</th><th>Prezzo</th><th>Prezzo di acquisto</th>';
        tableHtml += '</tr></thead>';
        tableHtml += '<tbody>';

        response.content.forEach(item => {
            let price = item.price;
            if (price === null || price == 0) {
                price = '<span class="text-danger">--</span>';
            } else {
                price = Number(price).toLocaleString('it-IT', {
                    maximumFractionDigits: 2,
                    minimumFractionDigits: 2,
                    style: 'currency',
                    currency: 'EUR'
                });
            }

            let wholesalePrice = item.wholesale_price;
            if (wholesalePrice === null || wholesalePrice == 0) {
                wholesalePrice = '<span class="text-danger">--</span>';
            } else {
                wholesalePrice = Number(wholesalePrice).toLocaleString('it-IT', {
                    maximumFractionDigits: 2,
                    minimumFractionDigits: 2,
                    style: 'currency',
                    currency: 'EUR'
                });
            }

            let qty = item.qty;
            if (qty === null || qty <= 0) {
                qty = '<span class="text-danger">--</span>';
            } else {
                qty = Number(qty).toLocaleString('it-IT', {
                    maximumFractionDigits: 0,
                    minimumFractionDigits: 0
                });
            }

            tableHtml += '<tr>';
            tableHtml += `<td>${ item.ean13 }</td>`;
            tableHtml += `<td>${ item.reference }</td>`;
            tableHtml += `<td class="text-right">${ qty }</td>`;
            tableHtml += `<td class="text-right">${ price }</td>`;
            tableHtml += `<td class="text-right">${ wholesalePrice }</td>`;
            tableHtml += '</tr>';
        });

        tableHtml += '</tbody></table>';

        $('#xml_preview_content').html(summaryHtml + tableHtml);
        $('#xml_preview').show();
    }

    $(document).ready(function() {
        $('#document_xml').on('change', function(event) {
            event.preventDefault();
            var file = event.target.files[0];

            $("#xml_preview").hide();
            $("#xml_preview_content").html("");

            if (typeof file === 'undefined') {
                $("#fake-file").val("");
                return false;
            }

            var formData = new FormData();
            formData.append('document_xml', file);
            formData.append('ajax', true);
            formData.append('action', 'loadFile');

            $.ajax({
                url: "{$admin_controller_url}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success === false) {
                        alert(response.message);
                        return false;
                    }

                    createTableResponse(response);
                }
            });

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