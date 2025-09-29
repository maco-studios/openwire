# Components

Components are the building blocks of OpenWire. They encapsulate both the server-side logic and the client-side presentation, providing a reactive interface between PHP and JavaScript.

## Component Anatomy

A typical OpenWire component consists of:

1. **PHP Class** - Contains the server-side logic and state
2. **Template File** - Defines the HTML structure and OpenWire directives
3. **Optional CSS/JS** - Additional styling and client-side enhancements

```php
<?php
// Component Class
class Demo_Shopping_Model_Component_ProductCard extends Maco_Openwire_Model_Component
{
    public function mount($params = [])
    {
        parent::mount($params);
        $this->loadProduct($params['product_id'] ?? null);
        return $this;
    }

    public function addToCart($quantity = 1)
    {
        // Server-side logic
        $product = $this->getProduct();
        $cart = Mage::getSingleton('checkout/cart');
        $cart->addProduct($product, $quantity);

        return [
            'effects' => [
                ['type' => 'notify', 'data' => ['message' => 'Added to cart!']]
            ]
        ];
    }

    private function loadProduct($productId)
    {
        if ($productId) {
            $product = Mage::getModel('catalog/product')->load($productId);
            $this->setData('product', $product->getData());
        }
    }

    public function getTemplate()
    {
        return 'demo/shopping/product_card.phtml';
    }
}
```

```html
<!-- Template File -->
<div ow class="product-card">
    <?php $product = $this->getData('product'); ?>
    <div class="product-image">
        <img src="<?php echo $product['image'] ?>" alt="<?php echo $product['name'] ?>" />
    </div>

    <div class="product-info">
        <h3><?php echo htmlspecialchars($product['name']) ?></h3>
        <p class="price">$<?php echo number_format($product['price'], 2) ?></p>

        <div class="quantity-selector">
            <input #model="quantity" type="number" value="1" min="1" />
            <button @click="addToCart" data-openwire-params="[quantity]">
                Add to Cart
            </button>
        </div>
    </div>

    <div #loading style="display: none;">Adding...</div>
</div>
```

## Component Lifecycle

### 1. Instantiation

Components are created using the factory pattern:

```php
$factory = Mage::getModel('openwire/component_factory');
$component = $factory->make('demo_shopping/component_productCard', [
    'product_id' => 123
]);
```

### 2. Mounting

The `mount()` method is called once when the component is first created:

```php
public function mount($params = [])
{
    parent::mount($params);

    // Initialize component state
    $this->setData('initialized_at', time());
    $this->setData('user_id', $this->getCurrentUserId());

    // Process mount parameters
    if (isset($params['product_id'])) {
        $this->loadProduct($params['product_id']);
    }

    return $this;
}
```

### 3. Rendering

Components render their HTML through the template system:

```php
public function render()
{
    // This method is inherited from the base component
    // It automatically handles template loading and compilation
    return parent::render();
}

// Override the template path
public function getTemplate()
{
    return 'path/to/your/template.phtml';
}
```

### 4. User Interactions

When users interact with the component, methods are called:

```php
public function handleUserAction($param1, $param2)
{
    // Update component state
    $this->setData('last_action', 'handleUserAction');
    $this->setData('action_params', [$param1, $param2]);

    // Perform business logic
    $result = $this->performSomeOperation($param1, $param2);

    // Return effects (optional)
    return [
        'effects' => [
            ['type' => 'notify', 'data' => ['message' => 'Action completed!']]
        ]
    ];
}
```

## State Management

### Setting and Getting Data

Components use `setData()` and `getData()` for state management:

```php
// Set individual properties
$this->setData('username', 'john_doe');
$this->setData('email', 'john@example.com');

// Set multiple properties
$this->setData([
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'profile_complete' => false
]);

// Get data with optional default
$username = $this->getData('username');
$email = $this->getData('email') ?: 'No email set';
```

### Data Persistence

Component state is automatically persisted across requests:

```php
public function incrementCounter()
{
    $current = (int) $this->getData('counter') + 1;
    $this->setData('counter', $current);

    // State is automatically saved and restored on next request
}
```

### Data Validation

Implement validation in your components:

```php
public function updateProfile($profileData)
{
    $errors = $this->validateProfileData($profileData);

    if (!empty($errors)) {
        $this->setData('validation_errors', $errors);
        return;
    }

    // Save valid data
    $this->setData('profile', $profileData);
    $this->setData('validation_errors', []);

    return [
        'effects' => [
            ['type' => 'notify', 'data' => ['message' => 'Profile updated!']]
        ]
    ];
}

private function validateProfileData($data)
{
    $errors = [];

    if (empty($data['name'])) {
        $errors['name'] = 'Name is required';
    }

    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email is required';
    }

    return $errors;
}
```

## Component Communication

### Parent-Child Communication

Pass data from parent to child components:

```php
// Parent component template
<div ow>
    <h2>Order Summary</h2>

    <?php $orderItems = $this->getData('order_items'); ?>
    <?php foreach ($orderItems as $item): ?>
        <?php
        echo $this->getLayout()
            ->createBlock('openwire/component')
            ->setComponentClass('shop/component_orderItem')
            ->setConfig([
                'item_id' => $item['id'],
                'quantity' => $item['quantity'],
                'parent_order_id' => $this->getData('order_id')
            ])
            ->toHtml();
        ?>
    <?php endforeach; ?>
</div>
```

### Event Broadcasting

Use effects to communicate between components:

```php
public function removeItem($itemId)
{
    // Remove item logic
    $this->removeItemFromOrder($itemId);

    // Broadcast event to update other components
    return [
        'effects' => [
            [
                'type' => 'broadcast',
                'data' => [
                    'event' => 'item_removed',
                    'item_id' => $itemId,
                    'order_total' => $this->calculateTotal()
                ]
            ]
        ]
    ];
}
```

## Advanced Patterns

### Repository Pattern

Separate data access from component logic:

```php
class Demo_Shopping_Model_Component_ProductList extends Maco_Openwire_Model_Component
{
    private $productRepository;

    public function __construct()
    {
        parent::__construct();
        $this->productRepository = Mage::getModel('demo_shopping/repository_product');
    }

    public function mount($params = [])
    {
        parent::mount($params);
        $this->loadProducts($params);
        return $this;
    }

    private function loadProducts($filters = [])
    {
        $products = $this->productRepository->findByFilters($filters);
        $this->setData('products', $products);
    }

    public function updateFilters($filters)
    {
        $this->setData('filters', $filters);
        $this->loadProducts($filters);
    }
}
```

### Service Layer

Use services for complex business logic:

```php
public function processCheckout($orderData)
{
    try {
        $checkoutService = Mage::getModel('demo_shopping/service_checkout');
        $order = $checkoutService->processOrder($orderData);

        $this->setData('order', $order);
        $this->setData('checkout_complete', true);

        return [
            'effects' => [
                ['type' => 'redirect', 'data' => ['url' => '/checkout/success']]
            ]
        ];

    } catch (Exception $e) {
        $this->setData('checkout_error', $e->getMessage());

        return [
            'effects' => [
                ['type' => 'notify', 'data' => [
                    'message' => 'Checkout failed: ' . $e->getMessage(),
                    'type' => 'error'
                ]]
            ]
        ];
    }
}
```

### Computed Properties

Create computed properties for derived data:

```php
public function mount($params = [])
{
    parent::mount($params);
    $this->setData('items', []);
    return $this;
}

public function addItem($item)
{
    $items = $this->getData('items');
    $items[] = $item;
    $this->setData('items', $items);

    // Update computed properties
    $this->updateComputedProperties();
}

private function updateComputedProperties()
{
    $items = $this->getData('items');

    // Calculate totals
    $total = array_sum(array_column($items, 'price'));
    $this->setData('total', $total);

    // Calculate tax
    $tax = $total * 0.08;
    $this->setData('tax', $tax);

    // Calculate grand total
    $this->setData('grand_total', $total + $tax);
}
```

## Testing Components

### Unit Testing

Test component logic with PHPUnit:

```php
class ProductCardComponentTest extends PHPUnit\Framework\TestCase
{
    public function testMountSetsProductData()
    {
        $component = new Demo_Shopping_Model_Component_ProductCard();
        $component->mount(['product_id' => 123]);

        $this->assertNotNull($component->getData('product'));
        $this->assertEquals(123, $component->getData('product')['entity_id']);
    }

    public function testAddToCartUpdatesQuantity()
    {
        $component = new Demo_Shopping_Model_Component_ProductCard();
        $component->mount(['product_id' => 123]);

        $result = $component->addToCart(2);

        $this->assertArrayHasKey('effects', $result);
        $this->assertEquals('notify', $result['effects'][0]['type']);
    }
}
```

### Integration Testing

Test component rendering and interactions:

```php
public function testComponentRendersCorrectly()
{
    $factory = Mage::getModel('openwire/component_factory');
    $component = $factory->make('demo_shopping/component_productCard', [
        'product_id' => 123
    ]);

    $html = $component->render();

    $this->assertStringContains('product-card', $html);
    $this->assertStringContains('data-openwire-component', $html);
}
```

## Best Practices

### 1. Single Responsibility

Each component should have one clear purpose:

```php
// Good - focused on product display
class ProductCard extends Maco_Openwire_Model_Component { }

// Bad - too many responsibilities
class ProductCardWithCartAndWishlistAndReviews extends Maco_Openwire_Model_Component { }
```

### 2. Immutable State Updates

Always create new state rather than modifying existing:

```php
// Good
public function addItem($item)
{
    $items = $this->getData('items') ?: [];
    $items[] = $item;
    $this->setData('items', $items);
}

// Bad
public function addItem($item)
{
    $this->getData('items')[] = $item; // Modifies existing array
}
```

### 3. Error Handling

Always handle errors gracefully:

```php
public function processPayment($paymentData)
{
    try {
        $result = $this->paymentService->process($paymentData);
        $this->setData('payment_result', $result);
    } catch (PaymentException $e) {
        $this->setData('payment_error', $e->getMessage());

        return [
            'effects' => [
                ['type' => 'notify', 'data' => [
                    'message' => 'Payment failed: ' . $e->getMessage(),
                    'type' => 'error'
                ]]
            ]
        ];
    }
}
```

### 4. Security

Validate and sanitize all user input:

```php
public function updateProfile($profileData)
{
    // Validate input
    $profileData = $this->sanitizeProfileData($profileData);

    if (!$this->validateProfileData($profileData)) {
        return $this->handleValidationError();
    }

    // Process safe data
    $this->saveProfile($profileData);
}

private function sanitizeProfileData($data)
{
    return [
        'name' => htmlspecialchars(trim($data['name'] ?? '')),
        'email' => filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL),
        'age' => (int) ($data['age'] ?? 0)
    ];
}
```

## Next Steps

- **[Templates](templates.md)** - Learn about template directives and syntax
- **[Events](events.md)** - Master event handling and user interactions
- **[Data Binding](data-binding.md)** - Explore two-way data binding
- **[Examples](../examples/counter.md)** - See real-world component examples
