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

<table class="table table-bordered table-striped table-condensed">
    <thead>
        <tr>
            <th>{l s='Combinazione'}</th>
            <th>{l s='Riferimento'}</th>
            <th>{l s='EAN13'}</th>
            <th>{l s='Quantità'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$combinations item=combination}
            <tr>
                <td>{$combination.combination|escape}</td>
                <td style="width: 32em; text-align: left;">
                    {if !$combination.reference}
                        --
                    {else}
                        <span>{$combination.reference|escape}</span>
                    {/if}
                </td>
                <td style="width: 13em; text-align: center;">
                    {if !$combination.ean13}
                        --
                    {else}
                        <span>{$combination.ean13|escape}</span>
                    {/if}
                </td>
                <td style="width: 8em; text-align: right;">
                    {if $combination.quantity==0}
                        <span class="badge badge-default">{$combination.quantity}</span>
                    {else if $combination.quantity<0}
                        <span class="badge badge-danger">{$combination.quantity}</span>
                    {else}
                        <span class="badge badge-success">{$combination.quantity}</span>
                    {/if}
                </td>
            </tr>
        {/foreach}
    </tbody>
</table>