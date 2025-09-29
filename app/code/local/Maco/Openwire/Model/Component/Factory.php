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
 * Component Factory - Instantiates and configures block components
 *
 * This class handles the creation of component instances without relying on
 * session or registry state. It uses the Resolver to get component classes
 * and the Hydrator to set their state.
 */
class Maco_Openwire_Model_Component_Factory
{
    private readonly Maco_Openwire_Model_Component_Resolver $resolver;
    private readonly Maco_Openwire_Model_Component_Hydrator $hydrator;

    public function __construct()
    {
        $this->resolver = Mage::getModel('openwire/component_resolver');
        $this->hydrator = Mage::getModel('openwire/component_hydrator');
    }

    /**
     * Create a component instance from class/alias and initial state
     *
     * @param string $classOrAlias Component class name or alias (e.g., 'openwire/component_counter')
     * @param array $initialState Initial state to hydrate the component with
     * @return Maco_Openwire_Block_Component_Abstract
     * @throws Exception If component cannot be created or hydrated
     */
    public function create(string $classOrAlias, array $initialState = []): Maco_Openwire_Block_Component_Abstract
    {
        // Resolve the component class to a block instance
        $component = $this->resolver->resolve($classOrAlias);

        if (!$component) {
            throw new Exception("Unable to resolve component: {$classOrAlias}");
        }

        if (!($component instanceof Maco_Openwire_Block_Component_Abstract)) {
            throw new Exception("Resolved component must extend Maco_Openwire_Block_Component_Abstract");
        }

        // Hydrate the component with the provided state
        return $this->hydrator->hydrate($component, $initialState);
    }

    /**
     * Legacy method for backwards compatibility
     * @deprecated Use create() instead
     */
    public function make($classOrAlias, array $state = [])
    {
        return $this->create($classOrAlias, $state);
    }

    /**
     * Create multiple components from a batch configuration
     *
     * @param array $components Array of ['class' => string, 'state' => array] configurations
     * @return array Array of created components
     */
    public function createBatch(array $components): array
    {
        $created = [];

        foreach ($components as $config) {
            if (!isset($config['class'])) {
                continue;
            }

            $state = $config['state'] ?? [];
            $created[] = $this->create($config['class'], $state);
        }

        return $created;
    }
}
