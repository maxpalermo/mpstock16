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

<div class="panel" id="mpstockv2-reason-panel">
    <div class="panel-heading">
        <i class="icon-list-ul"></i>
        Gestione ragioni movimento
    </div>
    <div class="panel-collapse">
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <button type="button" class="btn btn-primary" id="btn-add-reason">
                            <i class="process-icon-new"></i> {l s='Aggiungi ragione' mod='mpstockv2'}
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <table id="reason-datatable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">{l s='ID' mod='mpstockv2'}</th>
                                <th class="text-center">{l s='Nome' mod='mpstockv2'}</th>
                                <th class="text-center">{l s='Attivo' mod='mpstockv2'}</th>
                                <th class="text-center">{l s='Azioni' mod='mpstockv2'}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal fade" id="reason-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" id="exampleModalLabel">
                                {if isset($id_mpstock_mvt_reason)}
                                    {l s='Modifica ragione' mod='mpstockv2'}
                                {else}
                                    {l s='Aggiungi ragione' mod='mpstockv2'}
                                {/if}
                            </h4>
                        </div>
                        <div class="modal-body">
                            <form id="reason-form">
                                <div class="form-group">
                                    <label class="form-control-label" for="reason_code">{l s='Codice' mod='mpstockv2'}</label>
                                    <input type="text" class="form-control" id="reason_code" name="reason_code" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-control-label" for="reason_name">{l s='Nome' mod='mpstockv2'}</label>
                                    <input type="text" class="form-control" id="reason_name" name="reason_name" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-control-label" for="reason_sign">{l s='Segno' mod='mpstockv2'}</label>
                                    <select class="form-control" id="reason_sign" name="reason_sign" required>
                                        <option value="1">+</option>
                                        <option value="-1">-</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{l s='Chiudi' mod='mpstockv2'}</button>
                            <button type="button" class="btn btn-primary" id="btn-save-reason">{l s='Salva' mod='mpstockv2'}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function(e) {
        const reasonDatatable = $('#reason-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{$admin_controller_url}",
                type: "POST",
                data: {
                    ajax: true,
                    action: "GetMvtReasonsList"
                }
            },
            "columns": [
                { data: "id_mpstock_mvt_reason", name: "id_mpstock_mvt_reason" },
                {
                    data: "name",
                    name: "name",
                    render: function(data, type, row) {
                        return row.sign == 1 ? '<span style="color: green">' + data + '</span>' : '<span style="color: red">' + data + '</span>';
                    }
                },
                {
                    width: "96px",
                    align: "center",
                    data: "active",
                    name: "active",
                    className: "dt-center",
                    render: function(data, type, row) {
                        return data ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';
                    }
                },
                {
                    width: "96px",
                    align: "center",
                    data: "actions",
                    name: "actions",
                    render: function(data, type, row) {
                        return '<button type="button" class="btn btn-default btn-edit-reason" title="Modifica" data-id="' + row.id_mpstock_mvt_reason + '"><i class="fa fa-edit"></i></button>' +
                            '<button type="button" class="btn btn-default btn-delete-reason" title="Elimina" data-id="' + row.id_mpstock_mvt_reason + '"><i class="fa fa-trash"></i></button>';
                    }
                }
            ],
            "language": {
                "url": "modules/mpstockv2/views/js/datatables/lang/it_IT.json"
            },
            "order": [
                [1, "asc"]
            ],
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "pageLength": 10,
            order: [
                [1, "asc"]
            ],
            language: {
                "url": "/modules/mpstockv2/views/js/plugins/datatables/datatables-it.json"
            }

        });

        $('#btn-add-reason').click(function(e) {
            e.preventDefault();
            $('#reason-modal').modal('show');
        });

        $('#btn-save-reason').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: '{$admin_controller_url}',
                type: 'POST',
                data: {
                    ajax: 1,
                    action: 'save',
                    reason_code: $('#reason_code').val(),
                    reason_sign: $('#reason_sign').val(),
                    reason_name: $('#reason_name').val()
                },
                success: function(data) {
                    if (data.success == true) {
                        reasonDatatable.ajax.reload(null, false);
                        showSuccessGrowl(data.message);
                    } else {
                        showErrorGrowl(data.message);
                        showErrorGrowl(data.error);
                    }
                    $('#reason-modal').modal('hide');
                    $('#reason_name').val('');
                    $('#reason_code').val('');
                    $('#reason_sign').val('1');
                }
            });
        });


        $(document).on('click', '.btn-edit-reason', function(e) {
            e.preventDefault();
            var id = $(this).data('id');

            $.ajax({
                url: '{$admin_controller_url}',
                type: 'POST',
                data: {
                    ajax: 1,
                    action: 'get',
                    id_mpstock_mvt_reason: id
                },
                success: function(data) {
                    console.log("DATA");
                    console.table(data);
                    $('#reason_code').val(data.id_mpstock_mvt_reason);
                    $('#reason_sign').val(data.sign);
                    $('#reason_name').val(data.name);
                    $('#reason-modal').modal('show');
                    $('#btn-save-reason').data('id', data.id_mpstock_mvt_reason);
                }
            });
        });

        $(document).on('click', '.btn-delete-reason', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            if (!confirm("{l s='Sicuro di voler eliminare la ragione?' mod='mpstockv2'}"))
            {
                return false;
            }

            $.ajax({
                url: '{$admin_controller_url}',
                type: 'POST',
                data: {
                    action: 'delete',
                    id_mpstock_mvt_reason: id
                },
                success: function(data) {
                    if (data.status === 'ok') {
                        reasonDatatable.ajax.reload();
                    } else {
                        alert(data.message);
                    }
                }
            });
        });
    });
</script>