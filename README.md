<p align="center">
  <img src=".github/assets/img/logo.svg" alt="OpenWire Logo" width="200"/>
</p>

# OpenWire

OpenWire is a Magento 1 module that introduces a modern component-based architecture to the Magento ecosystem. It enables developers to create dynamic, interactive, and reusable components with minimal JavaScript knowledge. This project draws heavy inspiration from the Magento 2 Magewire and Laravel Livewire projects, adapting their concepts to the Magento 1 platform.

## 📚 Documentation

**[📖 View Full Documentation](https://maco-studios.github.io/openwire/)**

- **[Getting Started](https://maco-studios.github.io/openwire/getting-started/installation/)** - Installation and quick start guide
- **[Component Guide](https://maco-studios.github.io/openwire/guide/components/)** - Learn how to build components
- **[Examples](https://maco-studios.github.io/openwire/examples/counter/)** - Real-world component examples
- **[API Reference](https://maco-studios.github.io/openwire/api/php-classes/)** - Complete API documentation

## 🚀 Quick Start

### Installation

```bash
# Clone the repository
git clone https://github.com/maco-studios/openwire.git

# Copy to your Magento installation
cp -r openwire/app/code/local/Maco /path/to/magento/app/code/local/
cp -r openwire/app/design /path/to/magento/app/design/
cp -r openwire/js /path/to/magento/js/
```

### Basic Example

Create a simple counter component:

```php
<?php
class Demo_Counter extends Maco_Openwire_Model_Component
{
    public function mount($params = [])
    {
        parent::mount($params);
        $this->setData('count', 0);
        return $this;
    }

    public function increment()
    {
        $count = (int) $this->getData('count');
        $this->setData('count', $count + 1);
        return $this;
    }

    public function getTemplate()
    {
        return 'demo/counter.phtml';
    }
}
```

```html
<!-- Template: demo/counter.phtml -->
<div ow>
    <h3>Counter Example</h3>
    <button @click="increment">
        Count: <?php echo $this->getData('count') ?>
    </button>
</div>
```

## Inspiration
OpenWire is heavily inspired by:
- [Magewire](https://github.com/magewirephp/magewire): A Magento 2 module for building reactive components.
- [Livewire](https://laravel-livewire.com/): A Laravel framework for building dynamic interfaces without JavaScript.

## License
This project is licensed under the terms specified in the `LICENSE` file.
