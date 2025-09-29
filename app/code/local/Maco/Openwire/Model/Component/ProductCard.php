<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

/**
 * Product Card component - demonstrates Magento block system integration
 */
class Maco_Openwire_Model_Component_ProductCard extends Maco_Openwire_Model_Component
{
    /**
     * @var Mage_Core_Block_Template
     */
    protected $block;

    /**
     * Mount initial parameters
     */
    public function mount($params = [])
    {
        parent::mount($params);

        // Set default values
        if (!isset($params['productId'])) {
            $this->setData('productId', 0);
        }
        if (!isset($params['showPrice'])) {
            $this->setData('showPrice', true);
        }
        if (!isset($params['showAddToCart'])) {
            $this->setData('showAddToCart', true);
        }
        if (!isset($params['quantity'])) {
            $this->setData('quantity', 1);
        }
        if (!isset($params['isWishlisted'])) {
            $this->setData('isWishlisted', false);
        }

        // Load product data
        $this->loadProductData();

        return $this;
    }

    /**
     * Load product data from Magento
     */
    protected function loadProductData()
    {
        $productId = $this->getData('productId');
        if ($productId) {
            try {
                $product = Mage::getModel('catalog/product')->load($productId);
                if ($product->getId()) {
                    $this->setData('product', $product);
                    $this->setData('productName', $product->getName());
                    $this->setData('productPrice', $product->getPrice());
                    $this->setData('productImage', $product->getImageUrl());
                    $this->setData('productUrl', $product->getProductUrl());
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Toggle price visibility
     */
    public function togglePriceVisibility()
    {
        $current = $this->getData('showPrice');
        $this->setData('showPrice', !$current);
        return $this;
    }

    /**
     * Toggle add to cart button
     */
    public function toggleAddToCart()
    {
        $current = $this->getData('showAddToCart');
        $this->setData('showAddToCart', !$current);
        return $this;
    }

    /**
     * Update quantity
     */
    public function updateQuantity($quantity)
    {
        $quantity = (int) $quantity;
        if ($quantity > 0) {
            $this->setData('quantity', $quantity);
        }
        return $this;
    }

    /**
     * Add to wishlist
     */
    public function addToWishlist()
    {
        $productId = $this->getData('productId');
        if ($productId) {
            try {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if ($customer->getId()) {
                    $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer);
                    $wishlist->addNewItem($productId);
                    $this->setData('isWishlisted', true);

                    // Add effect for notification
                    $this->addEffect('notify', [
                        'message' => 'Product added to wishlist!'
                    ]);
                } else {
                    $this->addEffect('redirect', [
                        'url' => Mage::getUrl('customer/account/login'),
                        'message' => 'Please login to add to wishlist'
                    ]);
                }
            } catch (Exception $e) {
                Mage::logException($e);
                $this->addEffect('notify', [
                    'message' => 'Error adding to wishlist'
                ]);
            }
        }
        return $this;
    }

    /**
     * Remove from wishlist
     */
    public function removeFromWishlist()
    {
        $productId = $this->getData('productId');
        if ($productId) {
            try {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if ($customer->getId()) {
                    $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer);
                    $item = $wishlist->getItemByProductId($productId);
                    if ($item) {
                        $item->delete();
                        $this->setData('isWishlisted', false);

                        $this->addEffect('notify', [
                            'message' => 'Product removed from wishlist!'
                        ]);
                    }
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    /**
     * Get child HTML from Magento block
     */
    public function getChildHtml($name = '', $useCache = true, $sorted = false)
    {
        if (!$this->block) {
            $this->block = Mage::app()->getLayout()->createBlock('core/template');
            $this->block->setTemplate('openwire/product_card.phtml');
        }

        return $this->block->getChildHtml($name, $useCache, $sorted);
    }

    /**
     * Get product price formatted
     */
    public function getFormattedPrice()
    {
        $price = $this->getData('productPrice');
        if ($price) {
            return Mage::helper('core')->currency($price, true, false);
        }
        return '';
    }

    /**
     * Get wishlist button text
     */
    public function getWishlistButtonText()
    {
        return $this->getData('isWishlisted') ? 'Remove from Wishlist' : 'Add to Wishlist';
    }

    /**
     * Get wishlist button class
     */
    public function getWishlistButtonClass()
    {
        return $this->getData('isWishlisted') ? 'wishlisted' : 'not-wishlisted';
    }

    /**
     * Add effect to component
     */
    protected function addEffect($type, $data = [])
    {
        $effects = $this->getData('effects') ?: [];
        $effects[] = [
            'type' => $type,
            'data' => $data
        ];
        $this->setData('effects', $effects);
    }

    /**
     * Get effects
     */
    public function getEffects()
    {
        return $this->getData('effects') ?: [];
    }

    /**
     * Get template path
     */
    public function getTemplate()
    {
        return 'openwire/product_card.phtml';
    }
}
