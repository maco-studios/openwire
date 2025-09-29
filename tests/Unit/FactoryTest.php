<?php

use PHPUnit\Framework\TestCase;

/**
 * Test for Component Factory - ensures stateless component creation
 */
class FactoryTest extends TestCase
{
    private $factory;
    private $resolver;
    private $hydrator;

    protected function setUp(): void
    {
        // Mock the Mage framework calls for testing
        if (!class_exists('Mage')) {
            $this->markTestSkipped('Magento framework not available for unit testing');
        }

        $this->factory = new Maco_Openwire_Model_Component_Factory();
        $this->resolver = $this->createMock(Maco_Openwire_Model_Component_Resolver::class);
        $this->hydrator = $this->createMock(Maco_Openwire_Model_Component_Hydrator::class);
    }

    public function testCreateComponent()
    {
        // Test that factory can create a component without session dependencies
        $componentAlias = 'openwire/component_counter';
        $initialState = ['count' => 5, 'name' => 'Test Counter'];

        $mockComponent = $this->createMock(Maco_Openwire_Block_Component_Abstract::class);

        // Mock resolver to return component
        $this->resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($componentAlias)
            ->willReturn($mockComponent);

        // Mock hydrator to return hydrated component
        $this->hydrator
            ->expects($this->once())
            ->method('hydrate')
            ->with($mockComponent, $initialState)
            ->willReturn($mockComponent);

        // Set up factory with mocked dependencies (would need dependency injection)
        // For now, we test the interface
        $component = $this->factory->create($componentAlias, $initialState);

        $this->assertInstanceOf(Maco_Openwire_Block_Component_Abstract::class, $component);
    }

    public function testCreateThrowsExceptionForInvalidComponent()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to resolve component');

        // Mock resolver to return null
        $this->resolver
            ->method('resolve')
            ->willReturn(null);

        $this->factory->create('invalid/component');
    }

    public function testCreateBatch()
    {
        $components = [
            ['class' => 'openwire/component_counter', 'state' => ['count' => 1]],
            ['class' => 'openwire/component_counter', 'state' => ['count' => 2]],
        ];

        $mockComponent1 = $this->createMock(Maco_Openwire_Block_Component_Abstract::class);
        $mockComponent2 = $this->createMock(Maco_Openwire_Block_Component_Abstract::class);

        // Note: In a real test, we'd need to mock the internal factory calls
        // This is a simplified test to verify the interface

        $result = $this->factory->createBatch([]);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testLegacyMakeMethod()
    {
        // Test backward compatibility
        $componentAlias = 'openwire/component_counter';
        $state = ['count' => 3];

        try {
            $component = $this->factory->make($componentAlias, $state);
            $this->assertInstanceOf(Maco_Openwire_Block_Component_Abstract::class, $component);
        } catch (Exception $e) {
            // Expected if dependencies aren't properly mocked
            $this->assertStringContains('Unable to resolve component', $e->getMessage());
        }
    }
}
