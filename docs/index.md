# OpenWire

![OpenWire Logo](assets/logo.svg){ width="200" }

**OpenWire** is a Magento 1 module that introduces a modern component-based architecture to the Magento ecosystem. It enables developers to create dynamic, interactive, and reusable components with minimal JavaScript knowledge.

## 🌟 Key Features

- **🧩 Component-Based Architecture**: Build reusable UI components with server-side state management
- **⚡ Reactive Updates**: Automatic DOM updates when component state changes
- **🎯 Event Handling**: Simple event binding with `@click`, `@submit`, and more
- **🔄 Two-Way Data Binding**: Seamless synchronization between client and server
- **🖱️ Drag & Drop Support**: Built-in sortable lists and drag-drop interactions
- **🔌 Plugin System**: Extensible architecture for custom functionality
- **🧪 Well Tested**: Comprehensive test suite with modern JavaScript testing tools

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

### Basic Usage

Create a simple counter component:

```php
<?php
// app/code/local/Your/Module/Model/Component/Counter.php
class Your_Module_Model_Component_Counter extends Maco_Openwire_Model_Component
{
    public function mount($params = [])
    {
        parent::mount($params);
        if (!$this->getData('count')) {
            $this->setData('count', 0);
        }
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
        return 'your_module/counter.phtml';
    }
}
```

```html
<!-- app/design/frontend/base/default/template/your_module/counter.phtml -->
<div ow>
    <h3>Counter Example</h3>
    <button @click="increment">
        Count: <?php echo $this->getData('count') ?: 0 ?>
    </button>
</div>
```

## 📖 Documentation Sections

<div class="grid cards" markdown>

-   :material-rocket-launch-outline: **Getting Started**

    ---

    Learn how to install OpenWire and create your first component

    [:octicons-arrow-right-24: Quick Start](getting-started/quick-start.md)

-   :material-puzzle-outline: **Components**

    ---

    Deep dive into creating and managing OpenWire components

    [:octicons-arrow-right-24: Component Guide](guide/components.md)

-   :material-code-braces: **Developer Guide**

    ---

    Advanced topics for extending and customizing OpenWire

    [:octicons-arrow-right-24: Developer Guide](developer/architecture.md)

-   :material-lightbulb-outline: **Examples**

    ---

    Real-world examples and practical implementations

    [:octicons-arrow-right-24: Examples](examples/counter.md)

</div>

## 🎯 Inspiration

OpenWire is heavily inspired by:

- **[Magewire](https://github.com/magewirephp/magewire)**: A Magento 2 module for building reactive components
- **[Livewire](https://laravel-livewire.com/)**: A Laravel framework for building dynamic interfaces

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](contributing.md) for details.

## 📄 License

This project is licensed under the terms specified in the [LICENSE](https://github.com/maco-studios/openwire/blob/main/LICENSE) file.

## 🏢 About MACO Studios

OpenWire is developed and maintained by [MACO Studios](https://github.com/maco-studios), bringing modern development practices to legacy platforms.
