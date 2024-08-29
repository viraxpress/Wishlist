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

declare(strict_types=1);

namespace ViraXpress\Wishlist\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;

class CustomerAuth implements ArgumentInterface
{
    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * Constructor
     *
     * @param HttpContext $httpContext
     */
    public function __construct(
        HttpContext $httpContext
    ) {
        $this->httpContext = $httpContext;
    }

    /**
     * Is Csutomer Logged In
     */
    public function isLoggedIn()
    {
        return (bool) $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }
}
