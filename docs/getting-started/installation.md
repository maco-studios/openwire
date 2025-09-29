# Installation

## Prerequisites

Before installing OpenWire, ensure you have:

- **Magento 1.9.x** (recommended) or Magento 1.7+
- **PHP 5.6+** (PHP 7.x recommended)
- **Composer** (for development dependencies)
- **Node.js 16+** (for building JavaScript assets)

## Installation Methods

### Method 1: Manual Installation

1. **Download the latest release** from the [GitHub releases page](https://github.com/maco-studios/openwire/releases)

2. **Extract and copy files** to your Magento installation:

```bash
# Extract the downloaded archive
unzip openwire-latest.zip

# Copy module files
cp -r openwire/app/code/local/Maco /path/to/magento/app/code/local/
cp -r openwire/app/design /path/to/magento/app/design/
cp -r openwire/app/etc/modules/Maco_Openwire.xml /path/to/magento/app/etc/modules/
cp -r openwire/js /path/to/magento/js/
```

3. **Clear Magento cache**:

```bash
# From your Magento root directory
rm -rf var/cache/*
rm -rf var/full_page_cache/*
```

4. **Check module installation** in Magento Admin:
   - Go to **System > Configuration > Advanced > Advanced**
   - Verify that **Maco_Openwire** appears in the module list and is enabled

### Method 2: Using modman

If you're using [modman](https://github.com/colinmollenhour/modman) for module management:

1. **Clone the repository**:

```bash
cd /path/to/magento/.modman
git clone https://github.com/maco-studios/openwire.git
```

2. **Deploy with modman**:

```bash
modman deploy openwire
```

3. **Clear cache** as described above.

### Method 3: Git Submodule (Development)

For development or if you want to contribute:

1. **Add as git submodule**:

```bash
cd /path/to/magento
git submodule add https://github.com/maco-studios/openwire.git .modman/openwire
```

2. **Create symlinks manually** or use modman:

```bash
modman deploy openwire
```

## Verify Installation

### 1. Check Module Status

In Magento Admin:

1. Go to **System > Configuration > Advanced > Advanced**
2. Look for **Maco_Openwire** in the module list
3. Ensure it's enabled (not disabled)

### 2. Test JavaScript Loading

Add this to any template to verify JavaScript is working:

```html
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof OpenWire !== 'undefined') {
        console.log('OpenWire is loaded successfully!');
    } else {
        console.error('OpenWire failed to load');
    }
});
</script>
```

### 3. Create a Test Component

Create a simple test component to verify everything is working:

```php
<?php
// app/code/local/Test/Openwire/Model/Component/Hello.php
class Test_Openwire_Model_Component_Hello extends Maco_Openwire_Model_Component
{
    public function mount($params = [])
    {
        parent::mount($params);
        $this->setData('message', 'Hello, OpenWire!');
        return $this;
    }

    public function updateMessage()
    {
        $this->setData('message', 'OpenWire is working!');
        return $this;
    }

    public function getTemplate()
    {
        return 'test/hello.phtml';
    }
}
```

```html
<!-- app/design/frontend/base/default/template/test/hello.phtml -->
<div ow>
    <h3><?php echo $this->getData('message') ?></h3>
    <button @click="updateMessage">Test OpenWire</button>
</div>
```

## JavaScript Asset Building

### Development Build

If you're developing or modifying OpenWire:

1. **Install dependencies**:

```bash
cd /path/to/openwire
npm install
```

2. **Build for development**:

```bash
npm run dev
```

3. **Build for production**:

```bash
npm run build
```

### Using Pre-built Assets

For production deployments, use the pre-built JavaScript files included in releases.

## Configuration

### Layout XML

Include OpenWire JavaScript in your theme's layout:

```xml
<!-- app/design/frontend/your_package/your_theme/layout/openwire.xml -->
<?xml version="1.0"?>
<layout version="0.1.0">
    <default>
        <reference name="head">
            <action method="addJs">
                <script>openwire/dist/openwire.js</script>
            </action>
        </reference>
    </default>
</layout>
```

### System Configuration

OpenWire works out of the box with minimal configuration. Advanced settings can be configured via:

**System > Configuration > MACO > OpenWire**

Available options:
- Enable/disable debug mode
- Configure component cache settings
- Set default component timeouts

## Troubleshooting

### Common Issues

!!! warning "Module Not Appearing"
    If the module doesn't appear in the admin module list:
    
    - Check file permissions (755 for directories, 644 for files)
    - Verify the `app/etc/modules/Maco_Openwire.xml` file exists
    - Clear cache and refresh the page

!!! error "JavaScript Errors"
    If you see JavaScript console errors:
    
    - Ensure the OpenWire JS file is loading correctly
    - Check for JavaScript conflicts with other modules
    - Verify the correct file path in your layout XML

!!! info "Components Not Updating"
    If components aren't responding to interactions:
    
    - Check browser console for AJAX errors
    - Verify Magento's form key validation
    - Ensure component templates have proper `ow` attributes

### Getting Help

- 📖 Check the [documentation](../guide/components.md)
- 🐛 [Report issues](https://github.com/maco-studios/openwire/issues) on GitHub
- 💬 Join discussions in [GitHub Discussions](https://github.com/maco-studios/openwire/discussions)

## Next Steps

Now that OpenWire is installed, you can:

1. **[Quick Start Guide](quick-start.md)** - Create your first component
2. **[Basic Usage](basic-usage.md)** - Learn fundamental concepts
3. **[Components Guide](../guide/components.md)** - Deep dive into component development