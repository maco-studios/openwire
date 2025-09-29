# Basic Usage

Now that you've created your first component, let's dive deeper into OpenWire's core concepts and patterns.

## Component Lifecycle

Understanding the component lifecycle is crucial for effective OpenWire development:

```mermaid
graph TD
    A[Component Created] --> B[mount() Called]
    B --> C[Component Rendered]
    C --> D[User Interaction]
    D --> E[Method Called]
    E --> F[State Updated]
    F --> G[Re-rendered]
    G --> H[DOM Updated]
    H --> I[Effects Executed]
    I --> D
```

### Lifecycle Methods

```php
<?php
class Your_Component extends Maco_Openwire_Model_Component
{
    /**
     * Called once when component is first created
     * Use this to set initial state and process parameters
     */
    public function mount($params = [])
    {
        parent::mount($params);
        
        // Initialize state
        $this->setData('initialized_at', time());
        $this->setData('user_id', Mage::getSingleton('customer/session')->getCustomerId());
        
        return $this;
    }
    
    /**
     * Called before each render
     * Use this for dynamic data that should update on every render
     */
    public function beforeRender()
    {
        $this->setData('current_time', date('Y-m-d H:i:s'));
    }
    
    /**
     * Called after each render
     * Use this for cleanup or logging
     */
    public function afterRender()
    {
        // Log component usage
        Mage::log("Component {$this->getId()} rendered");
    }
}
```

## State Management

### Setting and Getting Data

```php
// Set data
$this->setData('property_name', $value);

// Get data
$value = $this->getData('property_name');

// Get with default
$value = $this->getData('property_name') ?: 'default_value';

// Set multiple properties
$this->setData([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30
]);
```

### Data Types

OpenWire supports various data types:

```php
public function mount($params = [])
{
    parent::mount($params);
    
    // Strings
    $this->setData('message', 'Hello World');
    
    // Numbers
    $this->setData('count', 42);
    $this->setData('price', 19.99);
    
    // Booleans
    $this->setData('is_active', true);
    
    // Arrays
    $this->setData('items', ['apple', 'banana', 'orange']);
    
    // Objects (will be serialized)
    $this->setData('user', Mage::getSingleton('customer/session')->getCustomer());
    
    return $this;
}
```

## Template Syntax

### Basic Directives

OpenWire provides several directive types for different interactions:

```html
<div ow>
    <!-- Event Handlers -->
    <button @click="handleClick">Click Me</button>
    <form @submit="handleSubmit">
        <input type="text" name="message" />
        <button type="submit">Submit</button>
    </form>
    
    <!-- Data Binding -->
    <input #model="search_query" type="text" placeholder="Search..." />
    <textarea #model="description.lazy" rows="4"></textarea>
    
    <!-- Property Binding -->
    <input :value="$this->getData('current_value')" type="text" />
    <div :class="$this->getData('css_class')">Dynamic Content</div>
    
    <!-- Conditional Rendering -->
    <?php if ($this->getData('show_message')): ?>
        <div class="message"><?php echo $this->getData('message') ?></div>
    <?php endif; ?>
    
    <!-- Loading States -->
    <div #loading style="display: none;">
        <span>Processing...</span>
    </div>
</div>
```

### Advanced Template Features

#### Loops and Iteration

```html
<div ow>
    <h3>Product List</h3>
    <ul>
        <?php $products = $this->getData('products') ?: []; ?>
        <?php foreach ($products as $index => $product): ?>
            <li class="product-item">
                <span><?php echo htmlspecialchars($product['name']) ?></span>
                <span class="price">$<?php echo number_format($product['price'], 2) ?></span>
                <button @click="removeProduct" data-openwire-params='[<?php echo $index ?>]'>
                    Remove
                </button>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <?php if (empty($products)): ?>
        <p class="empty-state">No products found.</p>
    <?php endif; ?>
</div>
```

#### Nested Components

```html
<div ow>
    <h2>Shopping Cart</h2>
    
    <?php $cartItems = $this->getData('cart_items') ?: []; ?>
    <?php foreach ($cartItems as $item): ?>
        <!-- Each item could be its own component -->
        <div class="cart-item">
            <?php
            echo $this->getLayout()
                ->createBlock('shop/cart_item')
                ->setData('item', $item)
                ->toHtml();
            ?>
        </div>
    <?php endforeach; ?>
    
    <div class="cart-total">
        Total: $<?php echo number_format($this->getData('total'), 2) ?>
    </div>
</div>
```

## Event Handling

### Basic Events

```php
<?php
class Demo_EventHandling extends Maco_Openwire_Model_Component
{
    // Handle button clicks
    public function handleClick()
    {
        $count = (int) $this->getData('click_count') + 1;
        $this->setData('click_count', $count);
        $this->setData('last_clicked', date('H:i:s'));
    }
    
    // Handle form submissions
    public function handleSubmit($formData)
    {
        // Validate data
        if (empty($formData['name'])) {
            $this->setData('error', 'Name is required');
            return;
        }
        
        // Process submission
        $this->setData('submitted_name', $formData['name']);
        $this->setData('error', null);
        
        // Return success effect
        return [
            'effects' => [
                [
                    'type' => 'notify',
                    'data' => ['message' => 'Form submitted successfully!']
                ]
            ]
        ];
    }
    
    // Handle input changes
    public function updateSearch($query)
    {
        $this->setData('search_query', $query);
        
        // Perform search
        $results = $this->performSearch($query);
        $this->setData('search_results', $results);
    }
    
    private function performSearch($query)
    {
        // Implement your search logic
        return [];
    }
}
```

### Event Parameters

Pass parameters to your event handlers:

```html
<div ow>
    <!-- Static parameters -->
    <button @click="addToCart" data-openwire-params='[123, 1, "large"]'>
        Add Large Size to Cart
    </button>
    
    <!-- Dynamic parameters from PHP -->
    <?php $productId = $this->getData('product_id'); ?>
    <?php $quantity = $this->getData('quantity'); ?>
    <button @click="addToCart" data-openwire-params='[<?php echo $productId ?>, <?php echo $quantity ?>]'>
        Add to Cart
    </button>
    
    <!-- Form-based parameters -->
    <form @submit="processOrder">
        <input type="hidden" name="product_id" value="<?php echo $productId ?>" />
        <input type="number" name="quantity" value="1" min="1" />
        <select name="size">
            <option value="small">Small</option>
            <option value="medium">Medium</option>
            <option value="large">Large</option>
        </select>
        <button type="submit">Order Now</button>
    </form>
</div>
```

## Data Binding

### Two-Way Binding

Automatically synchronize form inputs with component state:

```html
<div ow>
    <!-- Immediate updates (on input) -->
    <input #model="first_name" type="text" placeholder="First Name" />
    
    <!-- Lazy updates (on blur/change) -->
    <textarea #model="description.lazy" placeholder="Description"></textarea>
    
    <!-- Checkboxes -->
    <label>
        <input #model="agree_terms" type="checkbox" />
        I agree to the terms
    </label>
    
    <!-- Radio buttons -->
    <input #model="size" type="radio" value="small" id="size_small" />
    <label for="size_small">Small</label>
    
    <input #model="size" type="radio" value="large" id="size_large" />
    <label for="size_large">Large</label>
    
    <!-- Select dropdowns -->
    <select #model="category">
        <option value="">Select Category</option>
        <option value="electronics">Electronics</option>
        <option value="clothing">Clothing</option>
    </select>
    
    <!-- Display current values -->
    <div class="debug-info">
        <h4>Current State:</h4>
        <p>First Name: <?php echo htmlspecialchars($this->getData('first_name')) ?></p>
        <p>Description: <?php echo htmlspecialchars($this->getData('description')) ?></p>
        <p>Agree Terms: <?php echo $this->getData('agree_terms') ? 'Yes' : 'No' ?></p>
        <p>Size: <?php echo htmlspecialchars($this->getData('size')) ?></p>
        <p>Category: <?php echo htmlspecialchars($this->getData('category')) ?></p>
    </div>
</div>
```

### Handling Data Updates

```php
public function mount($params = [])
{
    parent::mount($params);
    
    // Set default values for form
    $this->setData([
        'first_name' => '',
        'description' => '',
        'agree_terms' => false,
        'size' => 'medium',
        'category' => ''
    ]);
    
    return $this;
}

// This method is automatically called when bound properties change
public function updated($property, $value)
{
    // Perform actions when specific properties change
    if ($property === 'category') {
        $this->loadSubcategories($value);
    }
    
    if ($property === 'first_name') {
        // Validate name
        if (strlen($value) < 2) {
            $this->setData('name_error', 'Name must be at least 2 characters');
        } else {
            $this->setData('name_error', null);
        }
    }
}

private function loadSubcategories($category)
{
    // Load relevant subcategories
    $subcategories = [];
    
    if ($category === 'electronics') {
        $subcategories = ['phones', 'laptops', 'tablets'];
    } elseif ($category === 'clothing') {
        $subcategories = ['shirts', 'pants', 'shoes'];
    }
    
    $this->setData('subcategories', $subcategories);
}
```

## Error Handling

### Validation and Error States

```php
public function validateAndSave($formData)
{
    $errors = [];
    
    // Validate required fields
    if (empty($formData['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($formData['password']) || strlen($formData['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    // Set errors
    $this->setData('errors', $errors);
    
    if (!empty($errors)) {
        return; // Don't proceed if there are errors
    }
    
    // Save data
    try {
        $this->saveUserData($formData);
        $this->setData('success_message', 'User saved successfully!');
        $this->setData('errors', []);
    } catch (Exception $e) {
        $this->setData('errors', ['general' => 'Failed to save user: ' . $e->getMessage()]);
    }
}
```

Display errors in template:

```html
<div ow>
    <form @submit="validateAndSave">
        <!-- Email field -->
        <div class="form-group">
            <label>Email:</label>
            <input #model="email" type="email" />
            <?php $errors = $this->getData('errors') ?: []; ?>
            <?php if (isset($errors['email'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Password field -->
        <div class="form-group">
            <label>Password:</label>
            <input #model="password" type="password" />
            <?php if (isset($errors['password'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
        </div>
        
        <!-- General errors -->
        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>
        
        <!-- Success message -->
        <?php if ($this->getData('success_message')): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($this->getData('success_message')) ?></div>
        <?php endif; ?>
        
        <button type="submit">Save User</button>
    </form>
</div>
```

## Next Steps

You now understand the fundamentals of OpenWire! Continue with:

- **[Components Guide](../guide/components.md)** - Advanced component patterns
- **[Template System](../guide/templates.md)** - Template directives and syntax
- **[Events](../guide/events.md)** - Complete event handling reference
- **[Data Binding](../guide/data-binding.md)** - Advanced data binding techniques