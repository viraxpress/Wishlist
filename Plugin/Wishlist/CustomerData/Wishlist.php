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

namespace ViraXpress\Wishlist\Plugin\Wishlist\CustomerData;

use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Wishlist\Block\Customer\Sidebar;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Review\Model\Review\SummaryFactory;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Wishlist\CustomerData\Wishlist as WishlistCustomerData;

class Wishlist
{
    /**
     * @var string
     */
    public const SIDEBAR_ITEMS_NUMBER = 3;

    /**
     * @var WishlistHelper
     */
    protected $wishlistHelper;

    /**
     * @var ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * @var ViewInterface
     */
    protected $view;

    /**
     * @var Sidebar
     */
    protected $block;

    /**
     * @var ItemResolverInterface
     */
    private $itemResolver;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var SummaryFactory
     */
    protected $summaryFactory;

    /**
     * @param WishlistHelper $wishlistHelper
     * @param Sidebar $block
     * @param ImageFactory $imageHelperFactory
     * @param ViewInterface $view
     * @param ItemResolverInterface|null $itemResolver
     * @param CustomerSession $customerSession
     * @param HttpContext $httpContext
     * @param SummaryFactory $summaryFactory
     */
    public function __construct(
        WishlistHelper $wishlistHelper,
        Sidebar $block,
        ImageFactory $imageHelperFactory,
        ViewInterface $view,
        ItemResolverInterface $itemResolver = null,
        CustomerSession $customerSession,
        HttpContext $httpContext,
        SummaryFactory $summaryFactory
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->block = $block;
        $this->view = $view;
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(ItemResolverInterface::class);
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->summaryFactory = $summaryFactory;
    }

    /**
     * Around plugin to modify section data for wishlist.
     *
     * @param WishlistCustomerData $subject
     * @param \Closure $proceed
     * @return array
     */
    public function aroundGetSectionData(WishlistCustomerData $subject, \Closure $proceed)
    {
        $this->loadSessionCustomerId();
        $counter = $this->getCounter();
        return [
            'counter' => $counter,
            'items' => $counter ? $this->getItems() : [],
        ];
    }

    /**
     * Load Session Customer Id
     */
    public function loadSessionCustomerId()
    {
        $customerId =$this->httpContext->getValue('customer_id');
        $this->customerSession->setData('customer_id', $customerId);
    }

    /**
     * Get counter
     *
     * @return string
     */
    protected function getCounter()
    {
        return $this->createCounter($this->wishlistHelper->getItemCount());
    }

    /**
     * Create button label based on wishlist item quantity
     *
     * @param int $count
     * @return \Magento\Framework\Phrase|null
     */
    protected function createCounter($count)
    {
        if ($count > 1) {
            return __('%1 items', $count);
        } elseif ($count == 1) {
            return __('1 item');
        }
        return null;
    }

    /**
     * Get wishlist items
     *
     * @return array
     */
    protected function getItems()
    {
        $this->view->loadLayout();

        $collection = $this->wishlistHelper->getWishlistItemCollection();
        $collection->clear()->setPageSize(self::SIDEBAR_ITEMS_NUMBER)
            ->setInStockFilter(true)->setOrder('added_at');

        $items = [];
        foreach ($collection as $wishlistItem) {
            $items[] = $this->getItemData($wishlistItem);
        }
        return $items;
    }

    /**
     * Retrieve wishlist item data
     *
     * @param \Magento\Wishlist\Model\Item $wishlistItem
     * @return array
     */
    protected function getItemData(\Magento\Wishlist\Model\Item $wishlistItem)
    {
        $product = $wishlistItem->getProduct();
        $review = $this->summaryFactory->create()->load($product->getId());
        $reviewSummary = $review->getRatingSummary() ? $review->getRatingSummary() : 0;
        $percent = $reviewSummary."%";
        return [
            'image' => $this->getImageData($this->itemResolver->getFinalProduct($wishlistItem)),
            'product_sku' => $product->getSku(),
            'product_id' => $product->getId(),
            'product_url' => $this->wishlistHelper->getProductUrl($wishlistItem),
            'product_name' => $product->getName(),
            'review_rating_summary' => $percent,
            'productBaseUrl' => $this->imageHelperFactory->create()->init($product, 'product_base_image')->getUrl(),
            'product_price' => $this->block->getProductPriceHtml(
                $product,
                'wishlist_configured_price',
                \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                ['item' => $wishlistItem]
            ),
            'product_is_saleable_and_visible' => $product->isSaleable() && $product->isVisibleInSiteVisibility(),
            'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
            'add_to_cart_params' => $this->wishlistHelper->getAddToCartParams($wishlistItem),
            'delete_item_params' => $this->wishlistHelper->getRemoveParams($wishlistItem),
        ];
    }

    /**
     * Retrieve product image data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getImageData($product)
    {
        /** @var \Magento\Catalog\Helper\Image $helper */
        $helper = $this->imageHelperFactory->create()->init($product, 'wishlist_sidebar_block');

        return [
            'template' => 'Magento_Catalog/product/image_with_borders',
            'src' => $helper->getUrl(),
            'width' => $helper->getWidth(),
            'height' => $helper->getHeight(),
            'alt' => $helper->getLabel(),
        ];
    }
}
