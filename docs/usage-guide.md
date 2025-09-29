# Using OpenWire in Magento 1

This guide explains how to create and use OpenWire components in your Magento 1 project.

## Component Basics

OpenWire components are PHP classes that render HTML and handle interactions through AJAX requests.

### Component Structure

A basic component has:

1. Public properties - Data accessible from the template
2. Methods - Actions triggered by user interactions
3. A render method - Returns the component's HTML

## Creating a Component

### 1. Create a Component Class

Create a PHP class that extends `\Maco\Openwire\Component`:

```php
<?php
// app/code/local/Vendor/Module/Component/ProductCounter.php
class Vendor_Module_Component_ProductCounter extends Maco_Openwire_Component
{
    // Public properties are accessible in the template
    public $count = 0;
    public $productId;

    // Initialize component (optional)
    public function initialize($config = array())
    {
        parent::initialize($config);

        // Set properties from config
        if (isset($config['product_id'])) {
            $this->productId = $config['product_id'];
        }

        // Load initial count
        $this->loadCount();
    }

    // Private method to load count
    private function loadCount()
    {
        if ($this->productId) {
            // Example: Load count from database
            $this->count = Mage::getModel('catalog/product')
                ->load($this->productId)
                ->getData('view_count');
        }
    }

    // Public method callable from the frontend
    public function increment()
    {
        $this->count++;

        // Example: Save to database
        if ($this->productId) {
            $product = Mage::getModel('catalog/product')->load($this->productId);
            $product->setData('view_count', $this->count);
            $product->save();
        }

        // Optional: Return effects to execute on the client
        return [
            'effects' => [
                [
                    'type' => 'toast',
                    'params' => [
                        'message' => 'Counter updated!',
                        'type' => 'success'
                    ]
                ]
            ]
        ];
    }

    // Render the component
    public function render()
    {
        // Get component ID and name
        $id = $this->getId();
        $name = $this->getName();

        // Initial data is automatically serialized
        $initialData = $this->getInitialDataAttribute();

        return <<<HTML
        <div data-openwire-component data-openwire-id="{$id}" data-openwire-name="{$name}" {$initialData}>
            <h3>Product View Counter</h3>
            <p>This product has been viewed <span>{$this->count}</span> times.</p>
            <button data-openwire-click="increment">Increment</button>
        </div>
        HTML;
    }
}
```

### 2. Add the Component to a Template

You can add an OpenWire component to any Magento template:

```php
<?php
// In your template file (.phtml)
echo $this->getLayout()
    ->createBlock('openwire/component')
    ->setComponentClass('Vendor_Module_Component_ProductCounter')
    ->setConfig(array(
        'product_id' => $this->getProduct()->getId()
    ))
    ->toHtml();
?>
```

### 3. Include Required Scripts

Make sure to include the OpenWire JavaScript in your layout:

```xml
<!-- In your layout XML file -->
<reference name="head">
    <action method="addJs"><script>openwire/openwire.min.js</script></action>
</reference>
```

## Component Attributes

OpenWire components use data attributes for binding:

### Common Attributes

- `data-openwire-component` - Marks an element as a component
- `data-openwire-id` - Unique component ID
- `data-openwire-name` - Component name/class
- `data-openwire-initial-data` - Initial data serialized as JSON

### Event Attributes

- `data-openwire-click="methodName"` - Call method on click
- `data-openwire-submit="methodName"` - Call method on form submit
- `data-openwire-model="propertyName"` - Two-way data binding

### Parameter Attributes

- `data-openwire-params="[param1, param2]"` - JSON array of parameters

## Advanced Usage

### Two-way Data Binding

Bind form inputs to component properties:

```html
<input type="text" data-openwire-model="searchQuery">
```

Changes to the input will automatically update the `searchQuery` property on the server.

### Lazy Loading

Defer updates until focus is lost:

```html
<textarea data-openwire-model="description" data-openwire-model-mode="lazy"></textarea>
```

### Form Handling

Process entire forms:

```html
<form data-openwire-submit="saveForm">
    <input type="text" name="title">
    <textarea name="content"></textarea>
    <button type="submit">Save</button>
</form>
```

The `saveForm` method will receive all form fields as an array.

### Passing Parameters

Pass static parameters to methods:

```html
<button data-openwire-click="addToCart" data-openwire-params="[123, 1]">Add to Cart</button>
```

This will call `addToCart(123, 1)` on the server.

### Loading States

OpenWire automatically adds loading states:

```css
/* Style loading state */
[data-openwire-component].openwire-loading {
    opacity: 0.7;
    pointer-events: none;
}
```

### Server Responses

Component methods can return data to modify the client:

```php
public function saveItem()
{
    // Process save...

    return [
        // Update component data (optional)
        'data' => [
            'message' => 'Item saved successfully'
        ],

        // Execute client-side effects
        'effects' => [
            [
                'type' => 'redirect',
                'params' => ['url' => $this->getUrl('*/*/index')]
            ],
            [
                'type' => 'toast',
                'params' => [
                    'message' => 'Item saved successfully',
                    'type' => 'success'
                ]
            ]
        ]
    ];
}
```

## Best Practices

1. **Keep components focused**: Each component should do one thing well
2. **Use proper encapsulation**: Private methods for internal logic
3. **Validate input**: Always validate data from the client
4. **Optimize database access**: Cache results when appropriate
5. **Use lifecycle hooks**: Initialize components with clean code

## Example Components

### Product List with Filtering

```php
<?php
class Vendor_Module_Component_ProductList extends Maco_Openwire_Component
{
    public $products = [];
    public $filters = [
        'category' => '',
        'price_from' => '',
        'price_to' => '',
        'in_stock' => false
    ];

    public function initialize($config = [])
    {
        parent::initialize($config);
        $this->loadProducts();
    }

    public function updateFilter($filter, $value)
    {
        if (array_key_exists($filter, $this->filters)) {
            $this->filters[$filter] = $value;
        }

        $this->loadProducts();
    }

    private function loadProducts()
    {
        $collection = Mage::getModel('catalog/product')->getCollection();

        // Apply filters
        if ($this->filters['category']) {
            $collection->addCategoryFilter($this->filters['category']);
        }

        if ($this->filters['price_from']) {
            $collection->addAttributeToFilter('price', ['gteq' => $this->filters['price_from']]);
        }

        if ($this->filters['price_to']) {
            $collection->addAttributeToFilter('price', ['lteq' => $this->filters['price_to']]);
        }

        if ($this->filters['in_stock']) {
            $collection->joinField(
                'stock_status',
                'cataloginventory/stock_status',
                'stock_status',
                'product_id=entity_id',
                ['stock_status' => \Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK]
            );
        }

        $this->products = $collection->load();
    }

    public function render()
    {
        $id = $this->getId();
        $name = $this->getName();
        $initialData = $this->getInitialDataAttribute();

        $html = <<<HTML
        <div data-openwire-component data-openwire-id="{$id}" data-openwire-name="{$name}" {$initialData}>
            <div class="filters">
                <h3>Filters</h3>

                <div class="filter-item">
                    <label>Category:</label>
                    <select data-openwire-model="filters.category">
                        <option value="">All Categories</option>
HTML;

        // Add categories
        $categories = Mage::getModel('catalog/category')->getCollection()->load();
        foreach ($categories as $category) {
            $selected = ($this->filters['category'] == $category->getId()) ? 'selected' : '';
            $html .= "<option value=\"{$category->getId()}\" {$selected}>{$category->getName()}</option>";
        }

        $html .= <<<HTML
                    </select>
                </div>

                <div class="filter-item">
                    <label>Price:</label>
                    <input type="number" placeholder="From" data-openwire-model="filters.price_from" value="{$this->filters['price_from']}">
                    <input type="number" placeholder="To" data-openwire-model="filters.price_to" value="{$this->filters['price_to']}">
                </div>

                <div class="filter-item">
                    <label>
                        <input type="checkbox" data-openwire-model="filters.in_stock"
                               <?php echo $this->filters['in_stock'] ? 'checked' : ''; ?>>
                        In Stock Only
                    </label>
                </div>
            </div>

            <div class="product-list">
HTML;

        // Add products
        foreach ($this->products as $product) {
            $html .= <<<HTML
                <div class="product-item">
                    <h4>{$product->getName()}</h4>
                    <div class="price">{$this->helper('core')->currency($product->getPrice())}</div>
                    <button data-openwire-click="addToCart" data-openwire-params="[{$product->getId()}]">
                        Add to Cart
                    </button>
                </div>
HTML;
        }

        // Close containers
        $html .= <<<HTML
            </div>
        </div>
HTML;

        return $html;
    }

    public function addToCart($productId)
    {
        try {
            $cart = Mage::getSingleton('checkout/cart');
            $product = Mage::getModel('catalog/product')->load($productId);

            $cart->addProduct($product, 1);
            $cart->save();

            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

            return [
                'effects' => [
                    [
                        'type' => 'toast',
                        'params' => [
                            'message' => $product->getName() . ' was added to your cart',
                            'type' => 'success'
                        ]
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                'effects' => [
                    [
                        'type' => 'toast',
                        'params' => [
                            'message' => $e->getMessage(),
                            'type' => 'error'
                        ]
                    ]
                ]
            ];
        }
    }
}
```

This component example demonstrates:
- Complex data binding with filters
- Database integration with Magento
- Dynamic form handling
- Method calls with parameters
- Server response effects
