<?php
/**
 * ViraXpress - https://www.viraxpress.com
 *
 * LICENSE AGREEMENT
 *
 * This file is part of the ViraXpress package and is licensed under the ViraXpress license agreement.
 * You can view the full license at:
 * https://www.viraxpress.com/license
 *
 * By utilizing this file, you agree to comply with the terms outlined in the ViraXpress license.
 *
 * DISCLAIMER
 *
 * Modifications to this file are discouraged to ensure seamless upgrades and compatibility with future releases.
 *
 * @category    ViraXpress
 * @package     ViraXpress_Wishlist
 * @author      ViraXpress
 * @copyright   © 2024 ViraXpress (https://www.viraxpress.com/)
 * @license     https://www.viraxpress.com/license
 */

namespace ViraXpress\Wishlist\Helper;

use Magento\Catalog\Model\Product;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\Item;

class Data extends WishlistHelper
{
    /**
     * Retrieve URL for configuring item from wishlist
     *
     * @param Product|Item $item
     * @return string
     */
    public function getConfigureUrl($item)
    {
        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        if ($options) {
            $res = '';
            foreach ($options as $option) {
                if (isset($option['super_attribute'])) {
                    $res = base64_encode(json_encode($option['super_attribute']));
                }
            }
            if ($res) {
                return $this->_getUrl(
                    'wishlist/index/configure',
                    [
                        'id' => $item->getWishlistItemId(),
                        'product_id' => $item->getProductId(),
                        'qty' => (int)$item->getQty(),
                        'attr' => strtr($res, '+/=', '-_.')
                    ]
                );
            }
        }
        return $this->_getUrl(
            'wishlist/index/configure',
            [
                'id' => $item->getWishlistItemId(),
                'product_id' => $item->getProductId(),
                'qty' => (int)$item->getQty()
            ]
        );
    }
}
