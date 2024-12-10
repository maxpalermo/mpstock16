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
 *}

<!-- Modal for importing order products -->
<div class="modal fade" id="importOrderProductsModal" tabindex="-1" role="dialog" aria-labelledby="importOrderProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="importOrderProductsModalLabel">Importazione movimenti ordini</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="panel-body full-width d-flex justify-content-center mb-2">
                        <div class="box">
                            <label>Progresso</label>
                            <span class="badge badge-info mr-2" id="currentFetch">0</span>
                        </div>
                        <div class="box">
                            <label>Totale</label>
                            <span class="badge badge-warning" id="totalFetch">0</span>
                        </div>
                    </div>

                    <div class="panel-body d-flex justify-content-center mb-4" style="height: 32px;">
                        <div class="progress full-width" style="height: 32px;">
                            <div id="importProgressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                <button type="button" class="btn btn-primary" id="startImportButton">Start Import</button>
            </div>
        </div>
    </div>
</div>