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
 * Task List component with drag and drop functionality
 */
class Maco_Openwire_Model_Component_TaskList extends Maco_Openwire_Model_Component
{
    /**
     * Mount initial parameters
     */
    public function mount($params = [])
    {
        parent::mount($params);

        // Load products from catalog instead of dummy tasks
        if (!$this->getData('tasks') || !is_array($this->getData('tasks'))) {
            $this->loadProductsAsTasks();
        }

        if (!$this->getData('newTaskTitle')) {
            $this->setData('newTaskTitle', '');
        }

        if (!$this->getData('filter')) {
            $this->setData('filter', 'all'); // all, active, completed
        }

        return $this;
    }

    /**
     * Load products from catalog and convert them to task format
     */
    protected function loadProductsAsTasks()
    {
        try {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect(['name', 'price', 'status', 'visibility'])
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->setPageSize(10)
                ->setCurPage(1);

            $tasks = [];
            foreach ($collection as $product) {
                $tasks[] = [
                    'id' => $product->getId(),
                    'title' => $product->getName(),
                    'completed' => false,
                    'priority' => $this->getProductPriority($product),
                    'product' => $product,
                    'price' => $product->getPrice(),
                    'sku' => $product->getSku()
                ];
            }
            $this->setData('tasks', $tasks);
        } catch (Exception $e) {
            Mage::logException($e);
            // Fallback to empty array if products can't be loaded
            $this->setData('tasks', []);
        }
    }

    /**
     * Determine priority based on product price
     */
    protected function getProductPriority($product)
    {
        $price = (float)$product->getPrice();
        if ($price > 100) {
            return 'high';
        } elseif ($price > 50) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Add a new task
     */
    public function addTask()
    {
        $title = trim($this->getData('newTaskTitle'));
        if (!empty($title)) {
            $tasks = $this->getData('tasks') ?: [];
            $newId = !empty($tasks) ? max(array_column($tasks, 'id')) + 1 : 1;

            $tasks[] = [
                'id' => $newId,
                'title' => $title,
                'completed' => false,
                'priority' => 'medium'
            ];

            $this->setData('tasks', $tasks);
            $this->setData('newTaskTitle', '');
        }
        return $this;
    }

    /**
     * Toggle task completion
     */
    public function toggleTask($taskId)
    {
        $tasks = $this->getData('tasks') ?: [];
        foreach ($tasks as &$task) {
            if ($task['id'] == $taskId) {
                $task['completed'] = !$task['completed'];
                break;
            }
        }
        $this->setData('tasks', $tasks);
        return $this;
    }

    /**
     * Delete a task
     */
    public function deleteTask($taskId)
    {
        $tasks = $this->getData('tasks') ?: [];
        $tasks = array_filter($tasks, function($task) use ($taskId) {
            return $task['id'] != $taskId;
        });
        $this->setData('tasks', array_values($tasks));
        return $this;
    }

    /**
     * Update task priority
     */
    public function updateTaskPriority($taskId, $priority)
    {
        $tasks = $this->getData('tasks') ?: [];
        foreach ($tasks as &$task) {
            if ($task['id'] == $taskId) {
                $task['priority'] = $priority;
                break;
            }
        }
        $this->setData('tasks', $tasks);
        return $this;
    }

    /**
     * Reorder tasks (called when drag and drop occurs)
     */
    public function reorderTasks($dragData)
    {
        $tasks = $this->getData('tasks') ?: [];
        $fromIndex = isset($dragData['fromIndex']) ? (int)$dragData['fromIndex'] : 0;
        $toIndex = isset($dragData['toIndex']) ? (int)$dragData['toIndex'] : 0;

        if ($fromIndex !== $toIndex && $fromIndex >= 0 && $toIndex >= 0 && $toIndex < count($tasks) && $fromIndex < count($tasks)) {
            // Remove task from original position
            $movedTask = array_splice($tasks, $fromIndex, 1)[0];
            // Insert at new position
            array_splice($tasks, $toIndex, 0, [$movedTask]);

            $this->setData('tasks', $tasks);
        }
        return $this;
    }

    /**
     * Set filter
     */
    public function setFilter($filter)
    {
        $this->setData('filter', $filter);
        return $this;
    }

    /**
     * Get filtered tasks
     */
    public function getFilteredTasks()
    {
        $tasks = $this->getData('tasks') ?: [];
        $filter = $this->getData('filter');

        return match ($filter) {
            'active' => array_filter($tasks, function($task) {
                return !$task['completed'];
            }),
            'completed' => array_filter($tasks, function($task) {
                return $task['completed'];
            }),
            default => $tasks,
        };
    }

    /**
     * Get task count by status
     */
    public function getTaskCounts()
    {
        $tasks = $this->getData('tasks') ?: [];
        $total = count($tasks);
        $completed = count(array_filter($tasks, function($task) {
            return $task['completed'];
        }));
        $active = $total - $completed;

        return [
            'total' => $total,
            'active' => $active,
            'completed' => $completed
        ];
    }

    /**
     * Clear completed tasks
     */
    public function clearCompleted()
    {
        $tasks = $this->getData('tasks') ?: [];
        $tasks = array_filter($tasks, function($task) {
            return !$task['completed'];
        });
        $this->setData('tasks', array_values($tasks));
        return $this;
    }

    /**
     * Get priority class for styling
     */
    public function getPriorityClass($priority)
    {
        return match ($priority) {
            'high' => 'priority-high',
            'medium' => 'priority-medium',
            'low' => 'priority-low',
            default => 'priority-medium',
        };
    }

    /**
     * Get template path
     */
    public function getTemplate()
    {
        return 'openwire/task_list.phtml';
    }
}
