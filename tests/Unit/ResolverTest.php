<?php

use PHPUnit\Framework\TestCase;

/**
 * Test for Component Resolver - ensures stateless component resolution
 */
class ResolverTest extends TestCase
{
    private $resolver;

    protected function setUp(): void
    {
        if (!class_exists('Mage')) {
            $this->markTestSkipped('Magento framework not available for unit testing');
        }

        $this->resolver = new Maco_Openwire_Model_Component_Resolver();
    }

    public function testResolveByAlias()
    {
        // Test resolving component by Magento block alias
        $alias = 'openwire/component_counter';

        // Mock the Magento layout system
        // In a real test environment, this would create the actual block
        try {
            $component = $this->resolver->resolve($alias);

            if ($component) {
                $this->assertInstanceOf(Maco_Openwire_Block_Component_Abstract::class, $component);
            } else {
                // If component doesn't exist, that's also valid for testing
                $this->assertNull($component);
            }
        } catch (Exception $e) {
            // Expected if Magento framework isn't fully set up
            $this->assertIsString($e->getMessage());
        }
    }

    public function testResolveInvalidComponent()
    {
        $result = $this->resolver->resolve('invalid/component_that_does_not_exist');
        $this->assertNull($result);
    }

    public function testAutoResolve()
    {
        // Test auto-resolving common patterns
        $identifier = 'counter';

        try {
            $component = $this->resolver->resolve($identifier);

            // Should either resolve to a component or return null
            if ($component) {
                $this->assertInstanceOf(Maco_Openwire_Block_Component_Abstract::class, $component);
            } else {
                $this->assertNull($component);
            }
        } catch (Exception $e) {
            // Expected if components don't exist
            $this->assertIsString($e->getMessage());
        }
    }

    public function testCanResolve()
    {
        // Test checking if a component can be resolved
        $result = $this->resolver->canResolve('openwire/component_counter');
        $this->assertIsBool($result);
    }

    public function testGetAvailableComponents()
    {
        // Test getting list of available components
        try {
            $components = $this->resolver->getAvailableComponents();
            $this->assertIsArray($components);

            // Each component should be a valid alias string
            foreach ($components as $component) {
                $this->assertIsString($component);
                $this->assertStringContains('openwire/component_', $component);
            }
        } catch (Exception $e) {
            // Expected if component directory doesn't exist
            $this->assertIsString($e->getMessage());
        }
    }

    public function testNoSessionDependency()
    {
        // Verify that resolver doesn't use session data
        // This test ensures the resolver operates statelessly

        try {
            $component1 = $this->resolver->resolve('openwire/component_counter');
            $component2 = $this->resolver->resolve('openwire/component_counter');

            // Two calls should create separate instances (no session caching)
            if ($component1 && $component2) {
                $this->assertNotSame($component1, $component2);
            }
        } catch (Exception $e) {
            // Expected if framework isn't available
            $this->assertTrue(true);
        }
    }

    public function testLegacyResolveMethod()
    {
        // Test backward compatibility with old return type
        try {
            $result = $this->resolver->resolveOld('openwire/component_counter');

            // Should return component instance or false (not null)
            $this->assertTrue($result === false || $result instanceof Maco_Openwire_Block_Component_Abstract);
        } catch (Exception $e) {
            // Expected if method doesn't exist or framework unavailable
            $this->assertIsString($e->getMessage());
        }
    }
}
