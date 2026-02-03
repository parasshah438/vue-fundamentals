<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce');

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Category Dropdown</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .location-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .custom-location-dropdown {
            position: relative;
        }
        
        .dropdown-panel {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 10000;
            display: none;
            min-width: 320px;
        }
        
        .dropdown-panel.show {
            display: block;
        }
        
        .left-panel {
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
            background-color: white;
            position: relative;
        }
        
        .back-header {
            padding: 12px 16px;
            background-color: #e7f3ff;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .select-item {
            padding: 11px 16px;
            margin: 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            position: relative;
        }
        
        .select-item:hover {
            background-color: #f8f9fa;
        }
        
        .select-item.selected {
            background-color: #e7f3ff;
            font-weight: 500;
        }
        
        .select-item-label {
            flex: 1;
            user-select: none;
        }
        
        .chevron-icon {
            color: #6c757d;
            font-size: 14px;
            margin-left: auto;
        }
        
        .right-panel {
            position: fixed;
            width: 280px;
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            box-shadow: 2px 2px 8px rgba(0,0,0,0.15);
            max-height: 400px;
            overflow-y: auto;
            display: none;
            z-index: 10001;
        }
        
        .right-panel.show {
            display: block;
        }
        
        .right-panel-header {
            padding: 12px 16px;
            background-color: #f8f9fa;
            color: #333;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 14px;
        }
        
        .option-item {
            padding: 11px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            background-color: white;
            position: relative;
        }
        
        .option-item:hover {
            background-color: #f8f9fa;
        }
        
        .option-item.selected {
            background-color: #e7f3ff;
            font-weight: 500;
        }
        
        .option-item-label {
            flex: 1;
            color: #333;
            font-size: 14px;
        }
        
        .selected-info {
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        
        .selected-info .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .selected-info .info-label {
            font-weight: 600;
            width: 150px;
            color: #495057;
        }
        
        .selected-info .info-value {
            color: #0d6efd;
            flex: 1;
        }
        
        .location-input {
            cursor: pointer;
        }
        
        .db-preview {
            margin-top: 20px;
            padding: 15px;
            background-color: #f1f3f5;
            border-radius: 4px;
            border: 1px dashed #6c757d;
        }
        
        .db-preview h6 {
            margin-bottom: 10px;
            color: #495057;
        }
        
        .db-preview pre {
            background-color: white;
            padding: 10px;
            border-radius: 4px;
            margin: 0;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="location-container">
        <h5 class="mb-4">Dynamic Category Selection (3-Level Hierarchy)</h5>
        
        <div class="mb-3 custom-location-dropdown">
            <label class="form-label">Select Category</label>
            <input type="text" 
                   class="form-control location-input" 
                   id="customCategoryInput" 
                   placeholder="Click to select category"
                   readonly>
            
            <div class="dropdown-panel" id="customDropdownPanel">
                <div class="left-panel" id="customMainPanel">
                    <div class="back-header">
                        <i class="bi bi-grid-3x3"></i>
                        <span>Main Categories</span>
                    </div>
                    <!-- Main categories will be loaded here -->
                </div>
            </div>
            
            <!-- Selected Information Display -->
            <div class="selected-info" id="selectedInfo" style="display: none;">
                <div class="info-row">
                    <span class="info-label">Main Category:</span>
                    <span class="info-value" id="displayMainCat">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Sub Category:</span>
                    <span class="info-value" id="displaySubCat">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Sub-Sub Category:</span>
                    <span class="info-value" id="displaySubSubCat">-</span>
                </div>
            </div>
        </div>
        
        <!-- Database Preview -->
        <div class="db-preview">
            <h6><i class="bi bi-database"></i> Database Structure (Ready to Save)</h6>
            <pre id="dbPreview">{
  "category_id_1": null,
  "category_id_2": null,
  "category_id_3": null,
  "category_name_1": null,
  "category_name_2": null,
  "category_name_3": null
}</pre>
        </div>
        
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary flex-fill" onclick="clearSelection()">Clear</button>
            <button type="button" class="btn btn-primary flex-fill" onclick="saveToDatabase()">Save to Database</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedCategories = {
            level1: { id: null, name: null },
            level2: { id: null, name: null },
            level3: { id: null, name: null }
        };
        
        // Track hover path for auto-filling hierarchy
        let hoverPath = {
            level1: { id: null, name: null },
            level2: { id: null, name: null },
            level3: { id: null, name: null }
        };
        
        const input = document.getElementById('customCategoryInput');
        const panel = document.getElementById('customDropdownPanel');
        
        // Load main categories on page load with Select2 compatibility check
        document.addEventListener('DOMContentLoaded', function() {
            // Small delay to ensure Select2 is fully initialized if present
            setTimeout(function() {
                loadCategories(null, 'customMainPanel', 1);
            }, 100);
        });
        
        // Toggle dropdown on input click
        input.addEventListener('click', function(e) {
            e.stopPropagation();
            panel.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside (avoid Select2 conflicts)
        document.addEventListener('click', function(e) {
            // Check if click is on Select2 elements
            if (e.target.closest('.select2-container') || 
                e.target.closest('.select2-dropdown') ||
                e.target.classList.contains('select2-search__field')) {
                return; // Don't interfere with Select2
            }
            
            if (!panel.contains(e.target) && e.target !== input) {
                panel.classList.remove('show');
                document.querySelectorAll('.right-panel').forEach(p => p.remove());
            }
        });
        
        panel.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        function loadCategories(parentId, targetElementId, level) {
            const formData = new FormData();
            formData.append('action', 'get_categories');
            formData.append('parent_id', parentId || '');
            
            fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    //alert('yessss');
                    renderCategories(data.categories, targetElementId, level);
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        function renderCategories(categories, targetElementId, level) {
            const targetElement = document.getElementById(targetElementId);
            if (!targetElement) return;
            
            // Clear existing items except header
            const existingItems = targetElement.querySelectorAll('.select-item, .option-item');
            existingItems.forEach(item => item.remove());
            
            categories.forEach(category => {
                const item = document.createElement('div');
                
                if (level === 1) {
                    item.className = 'select-item';
                    item.innerHTML = `
                        <span class="select-item-label">${category.name}</span>
                        ${category.has_children ? '<i class="bi bi-chevron-right chevron-icon"></i>' : ''}
                    `;
                } else {
                    item.className = 'option-item';
                    item.innerHTML = `
                        <span class="option-item-label">${category.name}</span>
                        ${category.has_children ? '<i class="bi bi-chevron-right chevron-icon"></i>' : ''}
                    `;
                }
                
                item.setAttribute('data-id', category.id);
                item.setAttribute('data-name', category.name);
                item.setAttribute('data-has-children', category.has_children);
                item.setAttribute('data-level', level);
                
                // Hover event for items with children
                if (category.has_children) {
                    item.addEventListener('mouseenter', function() {
                        const panelId = 'custom_panel_' + category.id;
                        // Track hover path
                        updateHoverPath(category.id, category.name, level);
                        showSubPanel(this, category.id, category.name, level, panelId);
                    });
                    
                    item.addEventListener('mouseleave', function() {
                        const panelId = 'custom_panel_' + category.id;
                        setTimeout(() => {
                            hideSubPanelIfNotHovered(panelId, level);
                        }, 150);
                    });
                } else {
                    // For items without children, also track hover path
                    item.addEventListener('mouseenter', function() {
                        updateHoverPath(category.id, category.name, level);
                    });
                }
                
                // Click event
                item.addEventListener('click', function() {
                    handleCategorySelection(category.id, category.name, level, category.has_children);
                });
                
                targetElement.appendChild(item);
            });
        }
        
        function showSubPanel(item, categoryId, categoryName, parentLevel, panelId) {
            // Remove other panels at the same or deeper level
            document.querySelectorAll('.right-panel').forEach(p => {
                const pLevel = parseInt(p.getAttribute('data-level') || 0);
                if (pLevel >= parentLevel + 1) {
                    p.remove();
                }
            });
            
            // Check if panel already exists
            let subPanel = document.getElementById(panelId);
            if (!subPanel) {
                subPanel = document.createElement('div');
                subPanel.id = panelId;
                subPanel.className = 'right-panel';
                subPanel.setAttribute('data-level', parentLevel + 1);
                
                const header = document.createElement('div');
                header.className = 'right-panel-header';
                header.textContent = categoryName + ' - Subcategories';
                
                subPanel.appendChild(header);
                document.body.appendChild(subPanel);
                
                // Load subcategories
                loadCategories(categoryId, panelId, parentLevel + 1);
                
                // Keep panel visible on hover
                subPanel.addEventListener('mouseenter', function() {
                    this.classList.add('show');
                });
                
                subPanel.addEventListener('mouseleave', function() {
                    const currentLevel = parseInt(this.getAttribute('data-level'));
                    setTimeout(() => {
                        hideSubPanelIfNotHovered(panelId, currentLevel - 1);
                    }, 150);
                });
            }
            
            // Position the panel
            const itemRect = item.getBoundingClientRect();
            let leftPos;
            
            if (parentLevel === 1) {
                const mainPanelRect = panel.getBoundingClientRect();
                leftPos = mainPanelRect.right + 5;
            } else {
                const parentPanel = item.closest('.right-panel');
                const parentRect = parentPanel.getBoundingClientRect();
                leftPos = parentRect.right + 5;
            }
            
            subPanel.style.left = leftPos + 'px';
            subPanel.style.top = itemRect.top + 'px';
            subPanel.classList.add('show');
        }
        
        function updateHoverPath(id, name, level) {
            if (level === 1) {
                hoverPath.level1 = { id, name };
                hoverPath.level2 = { id: null, name: null };
                hoverPath.level3 = { id: null, name: null };
            } else if (level === 2) {
                hoverPath.level2 = { id, name };
                hoverPath.level3 = { id: null, name: null };
            } else if (level === 3) {
                hoverPath.level3 = { id, name };
            }
        }
        
        function hideSubPanelIfNotHovered(panelId, parentLevel) {
            const subPanel = document.getElementById(panelId);
            if (!subPanel) return;
            
            // Check if mouse is hovering over the panel or any of its child panels
            if (subPanel.matches(':hover')) return;
            
            // Check if any child panels exist and are being hovered
            const childPanels = document.querySelectorAll('.right-panel');
            let hasHoveredChild = false;
            
            childPanels.forEach(childPanel => {
                const childLevel = parseInt(childPanel.getAttribute('data-level') || 0);
                if (childLevel > parentLevel + 1 && childPanel.matches(':hover')) {
                    hasHoveredChild = true;
                }
            });
            
            // Only remove if no child panels are being hovered
            if (!hasHoveredChild) {
                subPanel.remove();
                // Also remove any child panels of this panel
                childPanels.forEach(childPanel => {
                    const childLevel = parseInt(childPanel.getAttribute('data-level') || 0);
                    if (childLevel > parentLevel + 1) {
                        childPanel.remove();
                    }
                });
            }
        }
        
        function handleCategorySelection(id, name, level, hasChildren) {
            // Auto-fill hierarchy based on hover path
            if (level === 1) {
                selectedCategories.level1 = { id, name };
                selectedCategories.level2 = { id: null, name: null };
                selectedCategories.level3 = { id: null, name: null };
            } else if (level === 2) {
                // Auto-fill level 1 from hover path
                selectedCategories.level1 = { ...hoverPath.level1 };
                selectedCategories.level2 = { id, name };
                selectedCategories.level3 = { id: null, name: null };
            } else if (level === 3) {
                // Auto-fill level 1 and 2 from hover path
                selectedCategories.level1 = { ...hoverPath.level1 };
                selectedCategories.level2 = { ...hoverPath.level2 };
                selectedCategories.level3 = { id, name };
            }
            
            // Update selected class
            document.querySelectorAll('.select-item, .option-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            document.querySelectorAll(`[data-id="${id}"][data-level="${level}"]`).forEach(item => {
                item.classList.add('selected');
            });
            
            updateDisplay();
            
            // Close dropdown if no children or if it's level 3
            if (!hasChildren || level === 3) {
                setTimeout(() => {
                    panel.classList.remove('show');
                    document.querySelectorAll('.right-panel').forEach(p => p.remove());
                }, 200);
            }
        }
        
        function updateDisplay() {
            const selectedInfo = document.getElementById('selectedInfo');
            
            if (selectedCategories.level1.id) {
                selectedInfo.style.display = 'block';
                document.getElementById('displayMainCat').textContent = selectedCategories.level1.name || '-';
                document.getElementById('displaySubCat').textContent = selectedCategories.level2.name || '-';
                document.getElementById('displaySubSubCat').textContent = selectedCategories.level3.name || '-';
                
                let displayText = selectedCategories.level1.name;
                if (selectedCategories.level2.name) displayText += ' > ' + selectedCategories.level2.name;
                if (selectedCategories.level3.name) displayText += ' > ' + selectedCategories.level3.name;
                input.value = displayText;
            } else {
                selectedInfo.style.display = 'none';
                input.value = '';
            }
            
            updateDBPreview();
        }
        
        function updateDBPreview() {
            const dbData = {
                category_id_1: selectedCategories.level1.id,
                category_id_2: selectedCategories.level2.id,
                category_id_3: selectedCategories.level3.id,
                category_name_1: selectedCategories.level1.name,
                category_name_2: selectedCategories.level2.name,
                category_name_3: selectedCategories.level3.name
            };
            document.getElementById('dbPreview').textContent = JSON.stringify(dbData, null, 2);
        }
        
        function clearSelection() {
            selectedCategories = {
                level1: { id: null, name: null },
                level2: { id: null, name: null },
                level3: { id: null, name: null }
            };
            
            // Also clear hover path
            hoverPath = {
                level1: { id: null, name: null },
                level2: { id: null, name: null },
                level3: { id: null, name: null }
            };
            
            document.querySelectorAll('.select-item, .option-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            updateDisplay();
        }
        
        function saveToDatabase() {
            if (!selectedCategories.level1.id) {
                alert('Please select at least a main category!');
                return;
            }
            
            const dataToSave = {
                category_id_1: selectedCategories.level1.id,
                category_id_2: selectedCategories.level2.id,
                category_id_3: selectedCategories.level3.id,
                category_name_1: selectedCategories.level1.name,
                category_name_2: selectedCategories.level2.name,
                category_name_3: selectedCategories.level3.name
            };
            
            const formData = new FormData();
            formData.append('action', 'save_selection');
            formData.append('data', JSON.stringify(dataToSave));
            
            fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Selection saved successfully!');
                } else {
                    alert('Error saving: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving selection');
            });
        }
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>
