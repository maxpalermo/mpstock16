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

{include file="../forms/form-new-document.tpl"}
{include file="../forms/form-edit-movement.tpl"}

<div class="panel">
    <div class="panel-heading">
        <i class="material-icons">description</i>
        <span>Elenco documenti</span>
    </div>
    <div class="panel-body">
        <table class="table table-striped table-bordered table-hover" id="table-documents">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="check-all-documents" />
                    </th>
                    <th>Id</th>
                    <th>Numero</th>
                    <th>Data</th>
                    <th>Movimento</th>
                    <th>Fornitore</th>
                    <th>Totale</th>
                    <th>Operatore</th>
                    <th>Data inserimento</th>
                    <th>Azioni</th>
                </tr>
                <tr>
                    <th data-field=""></th>
                    <th data-field="id_mpstock_document"></th>
                    <th data-field="number_document"></th>
                    <th data-field="date_document"></th>
                    <th data-field="mvt_reason"></th>
                    <th data-field="supplier"></th>
                    <th data-field="tot_document_ti"></th>
                    <th data-field="employee"></th>
                    <th data-field="date_add"></th>
                    <th data-field=""></th>
                </tr>
            </thead>
            <tbody>
                <!-- DATI CARICATI VIA AJAX -->
            </tbody>
        </table>
    </div>
</div>

{include file="../scripts/script-document-movements.tpl"}

<script type="text/javascript">
    const adminControllerUrl = '{$admin_controller_url}';
    const adminURL = '{$admin_url}';
    const baseUrl = '{$base_url}';
    const base_url_js = '{$base_url_js}';
</script>

<script type="module" src="{$base_url_js}/documents/index.js"></script>
<script type="module" src="{$base_url_js}/movements/index.js"></script>