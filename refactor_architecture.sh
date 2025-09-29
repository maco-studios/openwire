#!/bin/bash

# OpenWire Architecture Refactoring Script
# Reorganizes the module to follow proper Magento patterns

set -e  # Exit on any error

echo "🔄 Starting OpenWire architecture refactoring..."

# Define paths
APP_DIR="app/code/local/Maco/Openwire"
BACKUP_DIR="backup_$(date +%Y%m%d_%H%M%S)"

# Create backup
echo "📦 Creating backup..."
mkdir -p "$BACKUP_DIR"
cp -r "$APP_DIR" "$BACKUP_DIR/"

# Create new directory structure
echo "📁 Creating new directory structure..."

# Create Block directory structure
mkdir -p "$APP_DIR/Block/Component"
mkdir -p "$APP_DIR/Block/Template"

# Create Component directory (for component definitions)
mkdir -p "$APP_DIR/Component"

# Keep existing directories that are correctly placed
# Model/ - for data models and business logic
# Helper/ - for utility functions
# controllers/ - for controllers
# etc/ - for configuration

echo "🚚 Moving files to correct locations..."

# Move component classes from Model/Component/ to Block/Component/
if [ -d "$APP_DIR/Model/Component" ]; then
    echo "  Moving component classes to Block/Component/..."
    for file in "$APP_DIR/Model/Component"/*.php; do
        if [ -f "$file" ]; then
            filename=$(basename "$file")
            echo "    Moving $filename"
            mv "$file" "$APP_DIR/Block/Component/"
        fi
    done
    # Remove empty Component directory from Model
    rmdir "$APP_DIR/Model/Component" 2>/dev/null || true
fi

# Move base Component.php to Block/Component/Abstract.php
if [ -f "$APP_DIR/Model/Component.php" ]; then
    echo "  Moving base Component.php to Block/Component/Abstract.php"
    mv "$APP_DIR/Model/Component.php" "$APP_DIR/Block/Component/Abstract.php"
fi

# Move template-related models that should stay in Model (they are business logic)
echo "  Template processing classes remain in Model/ (correct location)"

# Move some Model classes to Helper if they're utilities
if [ -f "$APP_DIR/Model/SessionStore.php" ]; then
    echo "  Moving SessionStore to Helper/"
    mv "$APP_DIR/Model/SessionStore.php" "$APP_DIR/Helper/SessionStore.php"
fi

echo "✏️  Updating class names and namespaces..."

# Function to update class names in files
update_class_references() {
    local file="$1"
    if [ -f "$file" ]; then
        # Update class declarations
        sed -i 's/class Maco_Openwire_Model_Component_/class Maco_Openwire_Block_Component_/g' "$file"
        sed -i 's/class Maco_Openwire_Model_Component /class Maco_Openwire_Block_Component_Abstract /g' "$file"
        sed -i 's/extends Maco_Openwire_Model_Component/extends Maco_Openwire_Block_Component_Abstract/g' "$file"
        
        # Update model references in code
        sed -i "s/Mage::getModel('openwire\/component'/Mage::getBlockSingleton('openwire\/component_abstract')/g" "$file"
        sed -i "s/openwire\/component_counter/openwire\/component_counter/g" "$file"
        sed -i "s/openwire\/component_/openwire\/component_/g" "$file"
        
        # Update SessionStore references
        sed -i 's/Maco_Openwire_Model_SessionStore/Maco_Openwire_Helper_SessionStore/g' "$file"
        sed -i "s/Mage::getModel('openwire\/sessionStore')/Mage::helper('openwire\/sessionStore')/g" "$file"
        
        echo "    Updated: $file"
    fi
}

# Update all PHP files
echo "  Updating Block/Component files..."
for file in "$APP_DIR/Block/Component"/*.php; do
    update_class_references "$file"
done

# Update controllers
echo "  Updating controllers..."
for file in "$APP_DIR/controllers"/*.php; do
    update_class_references "$file"
done

# Update helpers
echo "  Updating helpers..."
for file in "$APP_DIR/Helper"/*.php; do
    update_class_references "$file"
done

# Update remaining Model files
echo "  Updating Model files..."
for file in "$APP_DIR/Model"/*.php; do
    update_class_references "$file"
done

for file in "$APP_DIR/Model"/*/*.php; do
    update_class_references "$file"
done

for file in "$APP_DIR/Model"/*/*/*.php; do
    update_class_references "$file"
done

# Create updated Abstract.php with correct class name
echo "🔧 Updating Abstract component class..."
if [ -f "$APP_DIR/Block/Component/Abstract.php" ]; then
    # Update the main class declaration in Abstract.php
    sed -i 's/^class Maco_Openwire_Model_Component/class Maco_Openwire_Block_Component_Abstract/g' "$APP_DIR/Block/Component/Abstract.php"
    sed -i 's/extends Mage_Core_Block_Template/extends Mage_Core_Block_Template/g' "$APP_DIR/Block/Component/Abstract.php"
fi

# Update config.xml to reflect new structure
echo "📝 Updating config.xml..."
if [ -f "$APP_DIR/etc/config.xml" ]; then
    # Add block definitions if they don't exist
    if ! grep -q "<blocks>" "$APP_DIR/etc/config.xml"; then
        # Insert blocks section before </global>
        sed -i '/<\/global>/i\
            <blocks>\
                <openwire>\
                    <class>Maco_Openwire_Block</class>\
                </openwire>\
            </blocks>' "$APP_DIR/etc/config.xml"
    fi
fi

# Create a Component Factory in Helper (since it's a utility)
echo "🏭 Creating Component Factory helper..."
cat > "$APP_DIR/Helper/ComponentFactory.php" << 'EOF'
<?php

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

/**
 * Component Factory Helper
 * Creates and manages OpenWire component instances
 */
class Maco_Openwire_Helper_ComponentFactory extends Mage_Core_Helper_Abstract
{
    /**
     * Create a component instance
     *
     * @param string $componentType
     * @param array $params
     * @return Maco_Openwire_Block_Component_Abstract
     */
    public function create($componentType, $params = [])
    {
        $blockAlias = 'openwire/component_' . strtolower($componentType);
        
        try {
            $component = Mage::app()->getLayout()->createBlock($blockAlias);
            
            if (!$component) {
                throw new Exception("Component type not found: {$componentType}");
            }
            
            if (method_exists($component, 'mount')) {
                $component->mount($params);
            }
            
            return $component;
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Exception("Failed to create component: {$componentType}");
        }
    }
    
    /**
     * Get available component types
     *
     * @return array
     */
    public function getAvailableComponents()
    {
        // This could be enhanced to scan the Block/Component directory
        return [
            'counter',
            'todolist',
            'kanbanboard',
            'userprofile',
            'productcard'
        ];
    }
}
EOF

# Update the UpdateController to use the new architecture
echo "🎮 Updating UpdateController..."
if [ -f "$APP_DIR/controllers/UpdateController.php" ]; then
    # Update the component factory reference
    sed -i "s/Mage::getModel('openwire\/component_factory')/Mage::helper('openwire\/componentFactory')/g" "$APP_DIR/controllers/UpdateController.php"
    
    # Update component creation calls
    sed -i 's/\$factory->make(\$serverClass, (array) \$initialState)/\$factory->create(\$serverClass, (array) \$initialState)/g' "$APP_DIR/controllers/UpdateController.php"
    sed -i 's/\$factory->make(\$entry\['\''class'\''\], \$entry\['\''state'\''\] ?? \[\])/\$factory->create(\$entry['\''class'\''], \$entry['\''state'\''] ?? [])/g' "$APP_DIR/controllers/UpdateController.php"
fi

echo "🧹 Cleaning up empty directories..."
find "$APP_DIR" -type d -empty -delete 2>/dev/null || true

echo "📋 Creating migration summary..."
cat > "REFACTORING_SUMMARY.md" << EOF
# OpenWire Architecture Refactoring Summary

## Changes Made

### Directory Structure
- **MOVED**: \`Model/Component/\` → \`Block/Component/\`
- **MOVED**: \`Model/Component.php\` → \`Block/Component/Abstract.php\`
- **MOVED**: \`Model/SessionStore.php\` → \`Helper/SessionStore.php\`
- **CREATED**: \`Helper/ComponentFactory.php\`

### Class Name Changes
- \`Maco_Openwire_Model_Component\` → \`Maco_Openwire_Block_Component_Abstract\`
- \`Maco_Openwire_Model_Component_*\` → \`Maco_Openwire_Block_Component_*\`
- \`Maco_Openwire_Model_SessionStore\` → \`Maco_Openwire_Helper_SessionStore\`

### New Architecture Benefits
1. **Semantically Correct**: Components are now Blocks (UI logic)
2. **Template Integration**: Blocks naturally integrate with Magento's template system
3. **Layout System**: Can be used in layout XML files
4. **Proper Separation**: Models for data, Blocks for UI, Helpers for utilities

### Files Backed Up
Original structure backed up to: \`$BACKUP_DIR/\`

### Next Steps
1. Test component functionality
2. Update any layout XML files
3. Verify JavaScript integration still works
4. Update documentation
EOF

echo "✅ Refactoring complete!"
echo ""
echo "📁 New structure:"
echo "   Block/Component/     - UI component classes"
echo "   Helper/              - Utility functions and factories"
echo "   Model/               - Data models and business logic"
echo "   Template/            - Template processing (stays in Model)"
echo ""
echo "📄 Summary saved to: REFACTORING_SUMMARY.md"
echo "💾 Backup created in: $BACKUP_DIR/"
echo ""
echo "🔧 Next: Update config.xml and test the refactored structure"