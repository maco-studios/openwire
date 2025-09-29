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
 * Kanban Board component with drag and drop between columns
 */
class Maco_Openwire_Model_Component_KanbanBoard extends Maco_Openwire_Model_Component
{
    /**
     * Mount initial parameters
     */
    public function mount($params = [])
    {
        parent::mount($params);

        // Initialize columns with real product data
        if (!$this->getData('columns') || !is_array($this->getData('columns'))) {
            $this->loadProductsAsKanbanCards();
        }

        if (!$this->getData('newCardTitle')) {
            $this->setData('newCardTitle', '');
        }

        if (!$this->getData('newCardContent')) {
            $this->setData('newCardContent', '');
        }

        if (!$this->getData('newCardColumn')) {
            $this->setData('newCardColumn', 'new');
        }

        return $this;
    }

    /**
     * Load products from catalog and organize them into kanban columns
     */
    protected function loadProductsAsKanbanCards()
    {
        try {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect(['name', 'price', 'status', 'visibility', 'description'])
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->setPageSize(20)
                ->setCurPage(1);

            $columns = [
                'new' => [
                    'title' => 'New Products',
                    'cards' => []
                ],
                'featured' => [
                    'title' => 'Featured',
                    'cards' => []
                ],
                'sale' => [
                    'title' => 'On Sale',
                    'cards' => []
                ],
                'popular' => [
                    'title' => 'Popular',
                    'cards' => []
                ]
            ];

            $cardId = 1;
            foreach ($collection as $product) {
                $card = [
                    'id' => $cardId++,
                    'title' => $product->getName(),
                    'content' => $product->getShortDescription() ?: 'No description available',
                    'color' => $this->getProductColor($product),
                    'product' => $product,
                    'price' => $product->getPrice(),
                    'sku' => $product->getSku()
                ];

                // Distribute products across columns based on price
                $price = (float)$product->getPrice();
                if ($price > 200) {
                    $columns['featured']['cards'][] = $card;
                } elseif ($price < 50) {
                    $columns['sale']['cards'][] = $card;
                } elseif ($price > 100) {
                    $columns['popular']['cards'][] = $card;
                } else {
                    $columns['new']['cards'][] = $card;
                }
            }

            $this->setData('columns', $columns);
        } catch (Exception $e) {
            Mage::logException($e);
            // Fallback to empty columns if products can't be loaded
            $this->setData('columns', [
                'new' => ['title' => 'New Products', 'cards' => []],
                'featured' => ['title' => 'Featured', 'cards' => []],
                'sale' => ['title' => 'On Sale', 'cards' => []],
                'popular' => ['title' => 'Popular', 'cards' => []]
            ]);
        }
    }

    /**
     * Determine card color based on product price
     */
    protected function getProductColor($product)
    {
        $price = (float)$product->getPrice();
        if ($price > 200) {
            return 'purple'; // High-end products
        } elseif ($price > 100) {
            return 'blue'; // Mid-range products
        } elseif ($price > 50) {
            return 'green'; // Affordable products
        } else {
            return 'orange'; // Budget products
        }
    }

    /**
     * Add a new card to a column
     */
    public function addCard()
    {
        $title = trim($this->getData('newCardTitle'));
        $content = trim($this->getData('newCardContent'));
        $column = $this->getData('newCardColumn');

        if (!empty($title) && !empty($column)) {
            $columns = $this->getData('columns') ?: [];

            if (isset($columns[$column])) {
                // Generate new card ID
                $maxId = 0;
                foreach ($columns as $col) {
                    if (isset($col['cards']) && is_array($col['cards'])) {
                        foreach ($col['cards'] as $card) {
                            $maxId = max($maxId, $card['id']);
                        }
                    }
                }

                $newCard = [
                    'id' => $maxId + 1,
                    'title' => $title,
                    'content' => $content,
                    'color' => 'blue'
                ];

                $columns[$column]['cards'][] = $newCard;
                $this->setData('columns', $columns);

                // Clear form
                $this->setData('newCardTitle', '');
                $this->setData('newCardContent', '');
            }
        }
        return $this;
    }

    /**
     * Move card between columns (called when drag and drop occurs)
     */
    public function moveCard($dragData)
    {
        $cardId = $dragData['itemId'] ?? null;
        $fromColumn = $dragData['fromColumn'] ?? null;
        $toColumn = $dragData['toColumn'] ?? null;

        if ($fromColumn && $toColumn && $fromColumn !== $toColumn && $cardId) {
            $columns = $this->getData('columns') ?: [];

            // Find and remove card from source column
            $movedCard = null;
            if (isset($columns[$fromColumn]) && isset($columns[$fromColumn]['cards']) && is_array($columns[$fromColumn]['cards'])) {
                foreach ($columns[$fromColumn]['cards'] as $index => $card) {
                    if ($card['id'] == $cardId) {
                        $movedCard = array_splice($columns[$fromColumn]['cards'], $index, 1)[0];
                        break;
                    }
                }
            }

            // Add card to destination column
            if ($movedCard && isset($columns[$toColumn])) {
                if (!isset($columns[$toColumn]['cards'])) {
                    $columns[$toColumn]['cards'] = [];
                }
                $columns[$toColumn]['cards'][] = $movedCard;
                $this->setData('columns', $columns);

                $this->addEffect([
                    'type' => 'notify',
                    'data' => ['message' => "Moved '{$movedCard['title']}' from {$fromColumn} to {$toColumn}"]
                ]);
            }
        }
        return $this;
    }

    /**
     * Delete a card
     */
    public function deleteCard($cardId, $columnId)
    {
        $columns = $this->getData('columns') ?: [];

        if (isset($columns[$columnId]) && isset($columns[$columnId]['cards']) && is_array($columns[$columnId]['cards'])) {
            $columns[$columnId]['cards'] = array_filter(
                $columns[$columnId]['cards'],
                function($card) use ($cardId) {
                    return $card['id'] != $cardId;
                }
            );
            $columns[$columnId]['cards'] = array_values($columns[$columnId]['cards']);
            $this->setData('columns', $columns);
        }
        return $this;
    }

    /**
     * Update card details
     */
    public function updateCard($cardId, $columnId, $title, $content)
    {
        $columns = $this->getData('columns') ?: [];

        if (isset($columns[$columnId]) && isset($columns[$columnId]['cards']) && is_array($columns[$columnId]['cards'])) {
            foreach ($columns[$columnId]['cards'] as &$card) {
                if ($card['id'] == $cardId) {
                    $card['title'] = $title;
                    $card['content'] = $content;
                    break;
                }
            }
            $this->setData('columns', $columns);
        }
        return $this;
    }

    /**
     * Get column statistics
     */
    public function getColumnStats()
    {
        $columns = $this->getData('columns') ?: [];
        $stats = [];

        foreach ($columns as $id => $column) {
            $stats[$id] = [
                'title' => $column['title'] ?? 'Unknown',
                'count' => isset($column['cards']) && is_array($column['cards']) ? count($column['cards']) : 0
            ];
        }

        return $stats;
    }

    /**
     * Get total cards count
     */
    public function getTotalCards()
    {
        $columns = $this->getData('columns') ?: [];
        $total = 0;

        foreach ($columns as $column) {
            if (isset($column['cards']) && is_array($column['cards'])) {
                $total += count($column['cards']);
            }
        }

        return $total;
    }

    /**
     * Get available column options for new cards
     */
    public function getColumnOptions()
    {
        $columns = $this->getData('columns') ?: [];
        $options = [];

        foreach ($columns as $id => $column) {
            $options[$id] = $column['title'] ?? 'Unknown';
        }

        return $options;
    }

    /**
     * Get card color value
     */
    public function getCardColor($colorName)
    {
        $colors = [
            'blue' => '#2196F3',
            'green' => '#4CAF50',
            'orange' => '#FF9800',
            'red' => '#F44336',
            'purple' => '#9C27B0',
            'teal' => '#009688'
        ];

        return $colors[$colorName] ?? $colors['blue'];
    }

    /**
     * Get template path
     */
    public function getTemplate()
    {
        return 'openwire/kanban_board.phtml';
    }
}
