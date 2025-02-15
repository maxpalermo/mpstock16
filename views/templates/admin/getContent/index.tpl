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
    .d-flex {
        display: flex;
    }

    .justify-content-start {
        justify-content: flex-start;
    }

    .ml-2 {
        margin-left: 1rem;
    }

    .mt-4 {
        margin-top: 2rem;
    }
</style>

<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i> {l s='Configurazione Gestione Magazzino MP V2' mod='mpstockv2'}
    </div>

    <div class="panel-body">
        <div class="row">
            <p class="alert alert-info">
                {l s='Questo modulo offre funzionalità avanzate di gestione del magazzino per il tuo negozio PrestaShop. Ti permette di tracciare i movimenti di magazzino, creare documenti e gestire le operazioni di magazzino con precisione e controllo migliorati.' mod='mpstockv2'}
            </p>
            <div class="col-lg-12">
                <h2>{l s='Gestione Magazzino MP V2' mod='mpstockv2'}</h2>
            </div>
        </div>

        <div class="row mt-4 ajax-message" style="display: none;">
            <div class="alert alert-info"></div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="form-group pull-left mr-2">
                    <label for="mvtReason">{l s='Causale di default per il carico' mod='mpstockv2'}</label>
                    <br>
                    <select class="form-control chosen" id="mvtLoadReasonId" name="mvtReason">
                        {foreach from=$mvtReasons item=mvtReason}
                            <option value={$mvtReason.id_mpstock_mvt_reason} {if $mvtReason.id_mpstock_mvt_reason == $mvtLoadReasonId}selected{/if}>{$mvtReason.name}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="form-group pull-left mr-2">
                    <label for="mvtReason">{l s='Causale di default per lo scarico' mod='mpstockv2'}</label>
                    <br>
                    <select class="form-control chosen" id="mvtUnloadReasonId" name="mvtReason">
                        {foreach from=$mvtReasons item=mvtReason}
                            <option value={$mvtReason.id_mpstock_mvt_reason} {if $mvtReason.id_mpstock_mvt_reason == $mvtUnloadReasonId}selected{/if}>{$mvtReason.name}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="form-group pull-left ml-2">
                    <button class="btn btn-primary" id="btnSaveMvtReason" style="min-width: 6em; min-height: 6em;">
                        <i class="icon icon-2x icon-save mb-2"></i>
                        <br>
                        {l s='Salva' mod='mpstockv2'}
                    </button>
                </div>
            </div>
            <hr>
        </div>


        <style>
            .btn-action {
                min-height: 6em;
            }

            .btn-action i {
                font-size: 3em;
                display: block;
                margin-bottom: 1em;
                padding-top: 5px;
            }

            .btn-action span {
                font-size: 1.3em;
            }

            .btn-action:hover {
                font-weight: bold;
                text-shadow: 2px 2px 4px #555555;
                box-shadow: 3px 3px 6px #555555;
            }
        </style>

        <div class="row mt-4">
            <div class="col-lg-6 text-right">
                <a href="{$link->getModuleLink('mpstockv2', 'ajax')}?action=createMvtReasonTable" class="btn btn-default btn-action btn-block d-flex flex-column align-items-center">
                    <i class="icon-plus-sign d-block my-2" style="font-size: 2em;"></i>
                    <span>{l s='Crea Tabella Causali Movimento' mod='mpstockv2'}</span>
                </a>
                <a href="{$link->getModuleLink('mpstockv2', 'ajax')}?action=createDocumentTable" class="btn btn-default btn-action btn-block d-flex flex-column align-items-center">
                    <i class="icon-plus-sign d-block my-2" style="font-size: 2em;"></i>
                    <span>{l s='Crea Tabella Documenti' mod='mpstockv2'}</span>
                </a>
                <a href="{$link->getModuleLink('mpstockv2', 'ajax')}?action=createMovementTable" class="btn btn-default btn-action btn-block d-flex flex-column align-items-center">
                    <i class="icon-plus-sign d-block my-2" style="font-size: 2em;"></i>
                    <span>{l s='Crea Tabella Movimenti' mod='mpstockv2'}</span>
                </a>
            </div>
            <div class="col-lg-6 text-left">
                <a href="{$link->getModuleLink('mpstockv2', 'ajax')}?action=importMvtReasonTable" class="btn btn-default btn-action btn-block d-flex flex-column align-items-center">
                    <i class="icon-download d-block my-2" style="font-size: 2em;"></i>
                    <span>{l s='Importa Causali Movimento' mod='mpstockv2'}</span>
                </a>
                <a href="{$link->getModuleLink('mpstockv2', 'ajax')}?action=importDocumentTable" class="btn btn-default btn-action btn-block d-flex flex-column align-items-center">
                    <i class="icon-download d-block my-2" style="font-size: 2em;"></i>
                    <span>{l s='Importa Documenti' mod='mpstockv2'}</span>
                </a>
                <a href="{$link->getModuleLink('mpstockv2', 'ajax')}?action=importMovementTable" class="btn btn-default btn-action btn-block d-flex flex-column align-items-center">
                    <i class="icon-download d-block my-2" style="font-size: 2em;"></i>
                    <span>{l s='Importa Movimenti' mod='mpstockv2'}</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    {literal}
        function displayMessage(classType, message, rowsAffected, errors) {
            Swal.fire({
                title: 'Operazione eseguita',
                html: `${message}<br>Righe aggiornate: ${rowsAffected}<br>${errors ? "Errori: " + errors : ""}`,
                icon: classType.replace("alert-", ""),
                confirmButtonText: "Ok",
            });
        }
    {/literal}

    document.addEventListener('DOMContentLoaded', function(e) {
        $("#btnSaveMvtReason").on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            Swal.fire({
                title: 'Attenzione',
                text: "Sei sicuro di voler salvare le modifiche?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si',
                cancelButtonText: 'Annulla'
            }).then((result) => {
                if (result.value) {
                    let mvtLoadReasonId = $("#mvtLoadReasonId").val();
                    let mvtUnloadReasonId = $("#mvtUnloadReasonId").val();

                    $.ajax({
                        url: "{$link->getModuleLink('mpstockv2', 'ajax')}",
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            ajax: 1,
                            action: 'SaveDefaultMvtReasonsId',
                            mvtLoadReasonId: mvtLoadReasonId,
                            mvtUnloadReasonId: mvtUnloadReasonId
                        },
                        success: function(response) {
                            if (response.success) {
                                displayMessage("alert-success", response.message, response.rows_affected, "");
                            } else {
                                let errors = response.errors.join("\n");
                                displayMessage("alert-danger", response.message, response.rows_affected, errors);
                            }
                        },
                        error: function() {
                            displayMessage("alert-danger", '{l s='Si è verificato un errore durante la richiesta.' mod='mpstockv2'}', "", "");
                        }
                    });
                }
            });
        });

        $('.btn-action').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            Swal.fire({
                title: 'Attenzione',
                text: "Procedere con l'operazione?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si',
                cancelButtonText: 'Annulla'
            }).then((result) => {
                if (result.value) {
                    var url = $(this).attr('href');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                displayMessage("alert-success", response.message, response.rows_affected, "");
                            } else {
                                let errors = response.errors.join("\n");
                                displayMessage("alert-danger", response.message, response.rows_affected, errors);
                            }
                        },
                        error: function() {
                            showErrorMessage('{l s='Si è verificato un errore durante la richiesta.' mod='mpstockv2'}');
                        }
                    });
                }
            });

            return false;
        });
    });
</script>