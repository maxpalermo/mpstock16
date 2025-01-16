<?php
/**
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
 */

namespace MpSoft\MpStockV2\Helpers;

class GetProductImage
{
    public static function getProductImageCover($id_product)
    {
        /** @var array */
        $cover = \Image::getCover($id_product);
        $basePath = \Context::getContext()->shop->getBaseURL(true);
        if ($cover) {
            $image = new \Image($cover['id_image']);
            $image_path = $basePath . 'p/' . $image->getExistingImgPath() . '-small_default.' . $image->image_format;

            return $image_path;
        }

        return \Context::getContext()->shop->getBaseURL(true) . '/modules/mpstockv2/views/img/no-image.png';
    }
}
