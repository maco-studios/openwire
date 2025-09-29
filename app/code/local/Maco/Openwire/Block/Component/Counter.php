<?php

declare(strict_types=1);

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

/**
 * Counter Component - Example OpenWire component
 *
 * This is a canonical example component that demonstrates:
 * - Stateless operation with deterministic rendering
 * - Method calls (increment, decrement, reset)
 * - State management without session dependencies
 * - Effects handling (notifications)
 */
class Maco_Openwire_Block_Component_Counter extends Maco_Openwire_Block_Component_Abstract
{
    private array $effects = [];

    /**
     * Mount the component with initial parameters
     */
    public function mount($params = []): self
    {
        parent::mount($params);

        // Set initial count
        if (isset($params['initialCount'])) {
            $this->setData('count', (int) $params['initialCount']);
        } elseif ($this->getData('count') === null) {
            $this->setData('count', 0);
        }

        // Set component name
        if (isset($params['name'])) {
            $this->setData('name', $params['name']);
        } elseif ($this->getData('name') === null) {
            $this->setData('name', 'Counter');
        }

        // Set step size
        if (isset($params['step'])) {
            $this->setData('step', (int) $params['step']);
        } elseif ($this->getData('step') === null) {
            $this->setData('step', 1);
        }

        return $this;
    }

    /**
     * Increment the counter
     */
    public function increment(): self
    {
        $current = (int) $this->getData('count');
        $step = (int) $this->getData('step');
        $current += $step;

        $this->setData('count', $current);

        // Add notification effect
        $this->addEffect('notify', [
            'message' => "Count incremented to {$current}",
            'type' => 'success'
        ]);

        return $this;
    }

    /**
     * Decrement the counter
     */
    public function decrement(): self
    {
        $current = (int) $this->getData('count');
        $step = (int) $this->getData('step');
        $current -= $step;

        $this->setData('count', $current);

        // Add notification effect
        $this->addEffect('notify', [
            'message' => "Count decremented to {$current}",
            'type' => 'info'
        ]);

        return $this;
    }

    /**
     * Reset the counter to zero
     */
    public function reset(): self
    {
        $this->setData('count', 0);

        // Add notification effect
        $this->addEffect('notify', [
            'message' => 'Counter reset to 0',
            'type' => 'warning'
        ]);

        return $this;
    }

    /**
     * Set a specific count value
     */
    public function setCount(int $count): self
    {
        $this->setData('count', $count);
        return $this;
    }

    /**
     * Set the step size for increment/decrement
     */
    public function setStep(int $step): self
    {
        $this->setData('step', max(1, $step)); // Minimum step of 1
        return $this;
    }

    /**
     * Get the current count
     */
    public function getCount(): int
    {
        return (int) $this->getData('count');
    }

    /**
     * Get the component name
     */
    public function getName(): string
    {
        return (string) $this->getData('name');
    }

    /**
     * Get the step size
     */
    public function getStep(): int
    {
        return (int) $this->getData('step');
    }

    /**
     * Check if count is at maximum safe integer
     */
    public function isAtMax(): bool
    {
        return $this->getCount() >= PHP_INT_MAX - $this->getStep();
    }

    /**
     * Check if count is at minimum
     */
    public function isAtMin(): bool
    {
        return $this->getCount() <= PHP_INT_MIN + $this->getStep();
    }

    /**
     * Add an effect to be processed by the client
     */
    private function addEffect(string $type, array $data = []): void
    {
        $this->effects[] = [
            'type' => $type,
            'data' => $data
        ];
    }

    /**
     * Get all effects for this component
     */
    public function getEffects(): array
    {
        return $this->effects;
    }

    /**
     * Clear all effects
     */
    public function clearEffects(): self
    {
        $this->effects = [];
        return $this;
    }

    /**
     * Get the template for rendering
     */
    public function getTemplate(): string
    {
        return 'openwire/counter.phtml';
    }

    /**
     * Get component state for serialization
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        // Add computed properties
        $data['isAtMax'] = $this->isAtMax();
        $data['isAtMin'] = $this->isAtMin();

        return $data;
    }
}
