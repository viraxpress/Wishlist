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

namespace ViraXpress\Wishlist\Block;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Wishlist\Block\Link as WishlistLink;
use Magento\Wishlist\Helper\Data as WishlistHelper;

class Link extends WishlistLink
{
    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var WishlistHelper
     */
    protected $wishlistHelper;

    /**
     * @param TemplateContext $context
     * @param WishlistHelper $wishlistHelper
     * @param HttpContext $httpContext
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        WishlistHelper $wishlistHelper,
        HttpContext $httpContext,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        parent::__construct($context, $wishlistHelper, $data);
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }
}
