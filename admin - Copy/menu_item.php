<?php
session_start();
// include database config if needed
include '../config/configdatabse.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs
    $item_name = trim($_POST['item_name']);
    $item_description = trim($_POST['item_description'] ?? '');
    $price = floatval($_POST['price']);
    $item_type = $_POST['item_type'];
    $category = trim($_POST['category'] ?? '');
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    // Handle image upload
    $menu_image = '';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image_file']['tmp_name'];
        $imageName = basename($_FILES['image_file']['name']);
        $uploadDir = '../uploads/menu_images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $targetFile = $uploadDir . time() . '_' . $imageName;
        if (move_uploaded_file($imageTmpPath, $targetFile)) {
            $menu_image = $targetFile;
        }
    }

    // Prepare and execute query
   // Prepare the query
$stmt = $conn->prepare("INSERT INTO menuitems 
    (item_name, item_description, price, item_type, category, is_available, menu_image) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");

// Bind the parameters (s = string, d = double, i = integer)
$stmt->bind_param("ssdssis", 
    $item_name,
    $item_description,
    $price,
    $item_type,
    $category,
    $is_available,
    $menu_image
);

// Execute the statement
if ($stmt->execute()) {
    echo '<div class="alert alert-success mt-3 shadow fw-semibold rounded-4" style="max-width:700px;margin:0 auto;font-size:17px;">
            <i class="bi bi-check-circle-fill me-2 text-success"></i> Menu item added successfully!
          </div>';
} else {
    echo '<div class="alert alert-danger mt-3 shadow fw-semibold rounded-4" style="max-width:700px;margin:0 auto;font-size:17px;">
            <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i> Error: ' . htmlspecialchars($stmt->error) . '
          </div>';
}

    // Close the statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hotel System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="../css/admin_menuitem.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="content animate__animated animate__fadeIn">
    <div class="container-fluid">
        <div class="header">
            <h2><i class="bi bi-menu-up"></i> Add New Menu Item</h2>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="row g-4 needs-validation" novalidate>
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-info-circle"></i> Basic Information
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="item_name" class="form-label required-field">Item Name</label>
                                <input type="text" class="form-control" id="item_name" name="item_name" maxlength="100" required>
                                <div class="invalid-feedback">Please provide a valid item name.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="item_type" class="form-label required-field">Item Type</label>
                                <select class="form-select" id="item_type" name="item_type" required>
                                    <option value="" selected disabled>Select type</option>
                                    <option value="Food">Food</option>
                                    <option value="Beverage">Beverage</option>
                                    <option value="Dessert">Dessert</option>
                                    <option value="Appetizer">Appetizer</option>
                                    <option value="Side Dish">Side Dish</option>
                                </select>
                                <div class="invalid-feedback">Please select an item type.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" maxlength="50" placeholder="e.g. Main Course, Drinks">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="price" class="form-label required-field">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">रु</span>
                                    <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" required>
                                    <div class="invalid-feedback">Please provide a valid price.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-card-text"></i> Description
                        </div>
                        <div class="col-12">
                            <label for="item_description" class="form-label">Item Description</label>
                            <textarea class="form-control" id="item_description" name="item_description" rows="3" placeholder="Describe the item (ingredients, special notes, etc.)"></textarea>
                        </div>
                    </div>
                    
                    <!-- Availability & Image Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-image"></i> Availability & Image
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="is_available" class="form-label">Availability Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_available" name="is_available" checked style="width: 3em; height: 1.5em;">
                                    <label class="form-check-label ms-2" for="is_available">Currently available</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="image_file" class="form-label">Image</label>
                                <input type="file" class="form-control" id="image_file" name="image_file" accept="image/*">
                                <div class="image-preview-container mt-3">
                                    <p class="small text-muted mb-2">Image Preview:</p>
                                    <img id="imagePreview" src="#" alt="Preview" class="image-preview">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="col-12 d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add Menu Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Form validation
    (function () {
        'use strict'
        
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        const forms = document.querySelectorAll('.needs-validation')
        
        // Loop over them and prevent submission
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                
                form.classList.add('was-validated')
            }, false)
        })
    })()
    
    // Image file preview
    const imageFileInput = document.getElementById('image_file');
    const previewContainer = document.querySelector('.image-preview-container');
    const preview = document.getElementById('imagePreview');
    imageFileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            previewContainer.style.display = 'none';
        }
    });
    
    // Dynamic category suggestions based on item type
    document.getElementById('item_type').addEventListener('change', function() {
        const categoryInput = document.getElementById('category');
        const type = this.value;
        let suggestions = '';
        
        switch(type) {
            case 'Food':
                suggestions = 'Main Course, Starters, Salads, Breakfast, Lunch, Dinner';
                break;
            case 'Beverage':
                suggestions = 'Drinks, Hot Beverages, Cold Beverages, Alcoholic, Non-Alcoholic';
                break;
            case 'Dessert':
                suggestions = 'Desserts, Ice Cream, Cakes, Pastries';
                break;
            case 'Appetizer':
                suggestions = 'Starters, Snacks, Finger Food';
                break;
            case 'Side Dish':
                suggestions = 'Sides, Accompaniments, Salads';
                break;
            default:
                suggestions = '';
        }
        
        categoryInput.placeholder = suggestions ? `e.g. ${suggestions}` : 'Enter category';
    });
</script>
</body>
</html>