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
<style>
    .btn-large {
        margin: 16px;
        padding-left: 12px;
        width: 100%;
        height: 72px;
    }

    .btn-large:hover {
        font-weight: bold;
        text-shadow: 2px 2px 4px #555555;
        box-shadow: 3px 3px 6px #555555;
    }

    .active-button {
        background-color: #72C279 !important;
        border-color: #60ba68 !important;
        color: #fcfcfc !important;
        font-weight: bold !important;
        font-size: 1.3em !important;
        text-shadow: 2px 2px 4px #555555 !important;
    }

    .active-button i {
        color: #fcfcfc !important;
    }
</style>
<div class="panel">
    <div class="d-flex justify-content-between align-items-center">
        <div class="panel-body">
            <div class="img-thumbnail">
                <img src="{$baseUrl}modules/mpstockv2/views/img/no-image.png" alt="no-image" style="max-width: 100px; max-height: 100px; object-fit: contain;">
            </div>
        </div>
        <div class="panel-body flex-grow-1">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="icon icon-barcode" style="font-size: 3em"></i></span>
                            <input type="text" class="form-control input text-center" id="ean13" name="ean13" placeholder="EAN13" style="font-size: 3em; height: auto;">
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="id_product">
            <input type="hidden" id="id_product_attribute">

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="icon icon-list" style="font-size: 3em"></i></span>
                            <input type="text" class="form-control input text-center" id="product_name" name="product_name" placeholder="PRODOTTO" style="font-size: 3em; height: auto;" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="icon icon-shopping-cart" style="font-size: 3em"></i></span>
                            <input type="text" class="form-control input text-center" id="quantity" name="quantity" placeholder="QUANTITÀ" style="font-size: 3em; height: auto;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6" class="text-right">
            <button type="button" class="btn btn-default btn-large" onclick="javascript:setMovementSign(1, event);">
                <i class="icon icon-2x icon-upload"></i>
                <span class="ml-1">{l s='CARICO' mod='mpstock'}</span>
            </button>
        </div>
        <div class="col-md-6" class="text-right">
            <button type="button" class="btn btn-default btn-large" onclick="javascript:setMovementSign(-1, event);">
                <i class="icon icon-2x icon-download"></i>
                <span class="ml-1">{l s='SCARICO' mod='mpstock'}</span>
            </button>
        </div>
    </div>
    <div class="row">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <button type="button" class="btn btn-success btn-large" onclick="javascript:submitQuickMovement(event);">
                        <i class="icon icon-4x icon-save"></i>
                        <span class="ml-1" style="font-size: 3rem;">{l s='SALVA' mod='mpstock'}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    let movement_sign = 0;

    document.addEventListener('DOMContentLoaded', function() {
        $("#ean13").on('input', function() {
            getProductByEan13(this.value)
        });

        $("ean13").on('focus', function() {
            $(this).select();
        });

        $("#quantity").on('focus', function() {
            $(this).select();
        });
    });

    function submitQuickMovement(event) {
        event.preventDefault();
        event.stopPropagation();
        if (movement_sign == 0) {
            Swal.fire({
                title: '{l s='Warning' mod='mpstock'}',
                text: '{l s='Seleziona un movimento' mod='mpstock'}',
                type: 'warning',
                buttonsStyling: true,
                confirmButtonClass: 'btn btn-success'
            });
            return false;
        }
        if ($('#ean13').val() == '') {
            Swal.fire({
                title: '{l s='Warning' mod='mpstock'}',
                text: '{l s='Seleziona un prodotto' mod='mpstock'}',
                type: 'warning',
                buttonsStyling: true,
                confirmButtonClass: 'btn btn-success'
            });
            return false;
        }
        const movement = {
            id_product: $('#id_product').val(),
            id_product_attribute: $('#id_product_attribute').val(),
            quantity: $('#quantity').val(),
            sign: movement_sign
        };

        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'addQuickMovement',
                movement: movement
            },
            success: function(response) {
                Swal.fire({
                    title: 'Salva Movimento',
                    html: response.message,
                    type: response.success ? 'success' : 'error',
                    icon: response.success ? 'success' : 'error',
                    buttonsStyling: true,
                    confirmButtonClass: 'btn btn-success'
                })
            },
            error: function(response) {
                console.log(response);
            }
        });
    }

    function only_num() {
        var keyCode = window.event.keyCode;
        console.log('key: ', keyCode, window.event);
        if (keyCode >= 48 && keyCode <= 57) {
            return true;
        } else {
            return true;
        }
    }

    function setMovementSign(sign, event) {
        movement_sign = sign;
        var button = document.activeElement;
        var buttons = $(button).closest(".row").find('button');

        if (movement_sign == 1) {
            $(buttons[0]).addClass('active-button');
            $(buttons[1]).removeClass('active-button');
        } else {
            $(buttons[1]).addClass('active-button');
            $(buttons[0]).removeClass('active-button');
        }

        $('#input_text_ean13').focus();
    }

    function getProductByEan13(ean13) {

        if (ean13 == '') {
            return false;
        }

        if (ean13.length != 13) {
            return false;
        }

        $.ajax({
            url: "{$admin_controller_url}",
            dataType: 'json',
            data: {
                ajax: true,
                action: 'getProductByEan13',
                ean13: ean13,
            },
            success: function(data) {
                $('#id_product').val(data.id_product);
                $('#id_product_attribute').val(data.id_product_attribute);
                $('#product_name').val(data.name);
                $('#quantity').val(data.quantity);
                $('.img-thumbnail img').attr('src', data.image_url);
                return true;
            },
            error: function() {
                return false;
            }
        });
    }
</script>