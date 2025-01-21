{*
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
 *}

<style>
    .p-error {
        color: red;
        font-size: 0.70rem;
    }

    .border-error {
        border: 1px solid red !important;
    }
</style>

<div class="modal fade" id="modal-new-document" tabindex="-1" role="dialog" aria-labelledby="modaNewDocumentLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modaNewDocumentLabel">Nuovo Documento</h4>
            </div>
            <div class="modal-body">
                <div class="panel-body">
                    <form action="{$current}&amp;token={$token}" method="post" class="form-horizontal">
                        <div class="form-wrapper">
                            <input type="hidden" name="doc-id_mpstock_document" id="doc-id_mpstock_document" value="0">
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="number_document">Numero Documento:</label>
                                <div class="col-lg-9">
                                    <input type="text" name="number_document" id="doc-number_document" value="" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="date_document">Data Documento:</label>
                                <div class="col-lg-9">
                                    <input type="date" name="date_document" id="doc-date_document" value="" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="id_mpstock_mvt_reason">Tipo di movimento:</label>
                                <div class="col-lg-9">
                                    <select class="form-control chosen" id="doc-id_mpstock_mvt_reason" name="id_mpstock_mvt_reason" required="required">
                                        <option value=""></option>
                                        {foreach from=$mvtReasons item=mvtReason}
                                            <option value="{$mvtReason.id_mpstock_mvt_reason}">{$mvtReason.name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3" for="id_supplier">Fornitore:</label>
                                <div class="col-lg-9">
                                    <select class="form-control chosen" id="doc-id_supplier" name="id_supplier" required="required">
                                        <option value=""></option>
                                        {foreach from=$suppliers item=supplier}
                                            <option value="{$supplier.id_supplier}">{$supplier.name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div style="display: none;">
                                <div class="form-group">
                                    <label class="control-label col-lg-3" for="tot_qty">Quantità Totale:</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="tot_qty" id="tot_qty" value="" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3" for="tot_document_te">Totale Documento TE:</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="tot_document_te" id="tot_document_te" value="" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3" for="tot_document_taxes">Totale Documento Tasse:</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="tot_document_taxes" id="tot_document_taxes" value="" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3" for="tot_document_ti">Totale Documento TI:</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="tot_document_ti" id="tot_document_ti" value="" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3" for="id_employee">Operatore:</label>
                                    <div class="col-lg-9">
                                        <select class="form-control chosen" id="doc-id_employee" name="id_employee" required="required">
                                            <option value=""></option>
                                            {foreach from=$employees item=employee}
                                                <option value="{$employee.id_employee}" {if $employee.id_employee == Context::getContext()->employee->id}selected{/if}>{$employee.firstname} {$employee.lastname}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <button type="button" class="btn btn-default pull-right" name="submitSaveDocument" id="submitSaveDocument">
                                <i class="process-icon-save"></i>
                                <span>{l s='Salva' mod='mpstockv2'}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function checkValue(elem) {
        const id = $(elem).attr("id");

        switch (id) {
            case "doc-number_document":
                if ($("#" + id).val() == "") {
                    $("#" + id).addClass("border-error");
                    $("#" + id).after("<p class='p-error'>Inserire il numero del documento</p>");

                    return false;
                }
                break;
            case "doc-date_document":
                if ($("#" + id).val() == "") {
                    $("#" + id).addClass("border-error");
                    $("#" + id).after("<p class='p-error'>Inserire la data del documento</p>");

                    return false;
                }
                break;
            case "doc-id_mpstock_mvt_reason":
                if ($("#" + id).val() == "") {
                    $("#" + id).addClass("border-error");
                    $("#" + id).closest(".col-lg-9").append("<p class='p-error'>Selezionare il tipo di movimento</p>");

                    return false;
                }
                break;
            case "doc-id_supplier":
                if ($("#" + id).val() == "") {
                    $("#" + id).addClass("border-error");
                    $("#" + id).closest(".col-lg-9").append("<p class='p-error'>Selezionare il fornitore</p>");

                    return false;
                }
                break;
            case "doc-id_employee":
                if ($("#" + id).val() == "") {
                    $("#" + id).addClass("border-error");
                    $("#" + id).after("<p class='p-error'>Selezionare l'operatore</p>");

                    return false;
                }
                break;
        }

        return true;
    }

    function clearErrors() {
        $("#doc-id_mpstock_document").removeClass("border-error");
        $("#doc-number_document").removeClass("border-error");
        $("#doc-date_document").removeClass("border-error");
        $("#doc-id_mpstock_mvt_reason").removeClass("border-error");
        $("#doc-id_supplier").removeClass("border-error");
        $("#doc-id_employee").removeClass("border-error");
        $(".p-error").remove();
    }

    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded');

        const modalNewDocument = document.getElementById('modal-new-document');
        const btnSaveDocument = document.getElementById('submitSaveDocument');
        const formNumberDocument = document.getElementById('doc-number_document');

        $(modalNewDocument).on('show.bs.modal', function() {
            console.log('shown');

            $(btnSaveDocument)
                .html("")
                .append('<i class="process-icon-save"></i>')
            .append('<span>{l s='Salva' mod='mpstockv2'}</span>');

            clearErrors();
        })

        $(modalNewDocument).on('shown.bs.modal', function() {
            formNumberDocument.focus();
        });

        $(document).on("click", "#submitSaveDocument", function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            clearErrors();

            if (!confirm("Procedere con l'operazione?")) {
                return false;
            }


            const id_document = $("#doc-id_mpstock_document");
            const number_document = $("#doc-number_document");
            const date_document = $("#doc-date_document");
            const id_mpstock_mvt_reason = $("#doc-id_mpstock_mvt_reason");
            const id_supplier = $("#doc-id_supplier");
            const id_employee = $("#doc-id_employee");

            if (
                !checkValue(number_document) ||
                !checkValue(date_document) ||
                !checkValue(id_mpstock_mvt_reason) ||
                !checkValue(id_supplier) ||
                !checkValue(id_employee)
            ) {
                return false;
            }

            $("#submitSaveMovement").prop("disabled", true);
            $("#submitSaveMovement").html('<i class="process-icon-loading"></i>');

            $.ajax({
                type: "POST",
                url: "{$admin_controller_url}",
                data: {
                    ajax: true,
                    action: "save_document",
                    id_document: $(id_document).val(),
                    number_document: $(number_document).val(),
                    date_document: $(date_document).val(),
                    id_mpstock_mvt_reason: $(id_mpstock_mvt_reason).val(),
                    id_supplier: $(id_supplier).val(),
                    id_employee: $(id_employee).val(),
                },
                success: function(response) {
                    $("#submitSaveMovement").prop("disabled", false);
                    $("#submitSaveMovement").html('Salva');

                    $('#newMovementModal').modal('hide');
                    dataTable.ajax.reload();

                    if (response.success) {
                        $.growl.notice({
                            'title': 'Salva Documento',
                            'message': 'Documento Salvato'
                        })
                    }
                }
            })
        });
    })
</script>