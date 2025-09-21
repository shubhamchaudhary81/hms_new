<?php
include_once '../config/configdatabse.php';
session_start();

// Initialize separate session arrays for each customer type
if (!isset($_SESSION['order_items_inhouse'])) {
    $_SESSION['order_items_inhouse'] = [];
}
if (!isset($_SESSION['order_items_walkin'])) {
    $_SESSION['order_items_walkin'] = [];
}

// Determine current customer type from POST, GET, cookie, or default to inhouse
$customer_type = 'inhouse'; // default
if (isset($_POST['customer_type']) && in_array($_POST['customer_type'], ['inhouse', 'walkin'])) {
    $customer_type = $_POST['customer_type'];
} elseif (isset($_GET['customer_type']) && in_array($_GET['customer_type'], ['inhouse', 'walkin'])) {
    $customer_type = $_GET['customer_type'];
} elseif (isset($_COOKIE['restaurant_customer_type']) && in_array($_COOKIE['restaurant_customer_type'], ['inhouse', 'walkin'])) {
    $customer_type = $_COOKIE['restaurant_customer_type'];
}



// Use the appropriate session array based on customer type
$order_items_key = $customer_type === 'walkin' ? 'order_items_walkin' : 'order_items_inhouse';

// Define current_order_items for display purposes
$current_order_items = $customer_type === 'walkin' ? $_SESSION['order_items_walkin'] : $_SESSION['order_items_inhouse'];

// Handle add item
if (isset($_POST['add_item'])) {
    $menu_item_id = intval($_POST['menu_item_id']);
    $quantity = intval($_POST['quantity']);
    if ($menu_item_id && $quantity > 0) {
        // Fetch item details from DB
        $stmt = $conn->prepare("SELECT * FROM menuitems WHERE menu_item_id = ?");
        $stmt->bind_param("i", $menu_item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($item = $result->fetch_assoc()) {
            // Add to the appropriate session array
            $_SESSION[$order_items_key][] = [
                'id' => $item['menu_item_id'],
                'name' => $item['item_name'],
                'type' => $item['item_type'],
                'category' => $item['category'],
                'price' => $item['price'],
                'quantity' => $quantity,
                'subtotal' => $item['price'] * $quantity,
                'menu_image' => $item['menu_image'],
                'item_description' => $item['item_description']
            ];
        }
        $stmt->close();
    }
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?customer_type=" . $customer_type);
    exit();
}

// Handle remove item
if (isset($_POST['remove_item'])) {
    $remove_index = intval($_POST['remove_index']);
    $customer_type = $_POST['customer_type'] ?? 'inhouse';
    $order_items_key = $customer_type === 'walkin' ? 'order_items_walkin' : 'order_items_inhouse';
    if (isset($_SESSION[$order_items_key][$remove_index])) {
        array_splice($_SESSION[$order_items_key], $remove_index, 1);
    }
    // Redirect to prevent resubmission and refresh UI
    header("Location: " . $_SERVER['PHP_SELF'] . "?customer_type=" . $customer_type);
    exit();
}

// Fix 2: Prevent full page refresh from retaining session items wrongly
if (!isset($_GET['customer_type']) && !isset($_POST['customer_type'])) {
    // Clear session arrays if user just landed or refreshed the page fully (no toggle data or form data)
    unset($_SESSION['order_items_walkin']);
    unset($_SESSION['order_items_inhouse']);
    $_SESSION['order_items_walkin'] = [];
    $_SESSION['order_items_inhouse'] = [];
}

// Handle final order submission
if (isset($_POST['place_order'])) {
    // Determine if walk-in or in-house
    $is_walkin = empty($_POST['customer_id']);
    $walkin_name = isset($_POST['walkin_name']) ? trim($_POST['walkin_name']) : null;
    $walkin_phone = isset($_POST['walkin_phone']) ? trim($_POST['walkin_phone']) : null;
    $customer_id = !$is_walkin && !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
    $table_number = isset($_POST['table_number']) ? intval($_POST['table_number']) : null;
    $discount_amount = floatval($_POST['discount_amount']);
    $notes = !empty($_POST['notes']) ? $_POST['notes'] : null;
    
    // Use the appropriate session array for the current customer type
    $current_order_items = $is_walkin ? $_SESSION['order_items_walkin'] : $_SESSION['order_items_inhouse'];
    
    $total_items_cost = 0;
    foreach ($current_order_items as $item) {
        $total_items_cost += $item['subtotal'];
    }
    $final_amount = max(0, $total_items_cost - $discount_amount);

    // Find the most recent active/confirmed booking for this customer (in-house only)
    $booking_id = null;
    if (!$is_walkin && $customer_id) {
        $booking_query = "
            SELECT b.booking_id
            FROM Bookings b
            JOIN Reservations r ON b.reservation_id = r.reservation_id
            WHERE r.customer_id = $customer_id
              AND b.status IN ('confirmed', 'checked_in', 'active')
            ORDER BY b.booking_id DESC
            LIMIT 1
        ";
        $booking_res = $conn->query($booking_query);
        if ($booking_res && $booking_row = $booking_res->fetch_assoc()) {
            $booking_id = $booking_row['booking_id'];
        }
    }

    // Only proceed if required fields are set and there are items
    $valid = false;
    if ($is_walkin) {
        if ($walkin_name && $table_number && !empty($current_order_items)) {
            $valid = true;
        }
    } else {
        if ($customer_id && $table_number && !empty($current_order_items)) {
            $valid = true;
        }
    }

    if ($valid) {
        // Add walkin_name and walkin_phone columns to restaurantorders if not already present
        $stmt = $conn->prepare("INSERT INTO restaurantorders (booking_id, customer_id, table_number, total_items_cost, discount_amount, final_amount, notes, walkin_name, walkin_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) { die("Prepare failed: " . $conn->error); }
        $stmt->bind_param(
            "iiidddsss",
            $booking_id,
            $customer_id,
            $table_number,
            $total_items_cost,
            $discount_amount,
            $final_amount,
            $notes,
            $walkin_name,
            $walkin_phone
        );
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            $stmt->close();
            // Insert items
            $item_stmt = $conn->prepare("INSERT INTO orderitems (order_id, menu_item_id, quantity, price_at_order, subtotal) VALUES (?, ?, ?, ?, ?)");
            if (!$item_stmt) { die("Prepare failed: " . $conn->error); }
            foreach ($current_order_items as $item) {
                $item_stmt->bind_param("iiidd", $order_id, $item['id'], $item['quantity'], $item['price'], $item['subtotal']);
                $item_stmt->execute();
            }
            $item_stmt->close();
            // Clear the appropriate session array after successful order
            if ($is_walkin) {
                $_SESSION['order_items_walkin'] = [];
            } else {
                $_SESSION['order_items_inhouse'] = [];
            }
            $success = true;
        } else {
            $error = true;
        }
    } else {
        // Use a specific error message instead of a generic flag
        $error_message = "Failed to place order. Please check the following:<ul>";
        if ($is_walkin && !$walkin_name) {
            $error_message .= "<li>Walk-in customer name is required.</li>";
        }
        if (!$is_walkin && !$customer_id) {
            $error_message .= "<li>A customer must be selected.</li>";
        }
        if (!$table_number) {
            $error_message .= "<li>A table number must be entered.</li>";
        }
        if (empty($current_order_items)) {
            $error_message .= "<li>The order must contain at least one item.</li>";
        }
        $error_message .= "</ul>";
    }
}
// Fetch menu items for dropdown
$menu_items = [];
$res = $conn->query("SELECT * FROM menuitems WHERE is_available = 1");
while ($row = $res->fetch_assoc()) {
    $menu_items[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Restaurant Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css">
    <style>
        :root {
            --primary-color: #5d78ff;
            --secondary-color: #ff7d5d;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --success-color: #28a745;
            --border-radius: 8px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-color);
        }
        
        .order-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 15px;
        }
        
        .order-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: none;
        }
        
        .order-header {
            background: linear-gradient(135deg, var(--primary-color), #6a5acd);
            color: white;
            padding: 20px 25px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }
        
        .order-header h4 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .order-header h4 i {
            margin-right: 12px;
            font-size: 1.4rem;
        }
        
        .order-body {
            padding: 25px;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border-radius: var(--border-radius);
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            transition: var(--transition);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(93, 120, 255, 0.25);
        }
        
        .menu-table {
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .menu-table thead {
            background-color: var(--primary-color);
            color: white;
        }
        
        .menu-table th {
            font-weight: 500;
            padding: 12px 15px;
        }
        
        .menu-table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 25px;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-primary:hover {
            background-color: #4a6bff;
            border-color: #4a6bff;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 8px 20px;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: #ff6b6b;
            border-color: #ff6b6b;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: var(--transition);
        }
        
        .btn-danger:hover {
            background-color: #ff5252;
            border-color: #ff5252;
            transform: scale(1.1);
        }
        
        .total-display {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .final-amount {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .add-item-btn {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .floating-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            animation: slideIn 0.5s forwards;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        /* Smooth transitions for customer type switching */
        #inhouse-section, #walkin-section {
            transition: all 0.25s ease-in-out;
            opacity: 1;
            transform: translateY(0);
        }
        
        #inhouse-section.fade-out, #walkin-section.fade-out {
            opacity: 0;
            transform: translateY(-10px);
        }
        
        .btn {
            transition: all 0.2s ease-in-out;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        /* Smooth table transitions */
        .menu-table tbody {
            transition: all 0.2s ease-in-out;
        }
        
        .total-display, .mb-4.mt-4, .d-flex.justify-content-end {
            transition: all 0.2s ease-in-out;
        }
        
        /* Loading animation for smooth switching */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .order-body {
                padding: 15px;
            }
            
            .menu-table td, .menu-table th {
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading overlay for smooth transitions -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>
    <?php include 'sidebar.php'; ?>
<div class="order-container">
    <div class="order-card">
        <div class="order-header">
            <h4><i class="fas fa-utensils"></i> Create New Restaurant Order</h4>
        </div>
        <div class="order-body">
            <!-- Customer Type Toggle -->
            <div class="mb-4 d-flex gap-3">
                <button type="button" id="btn-inhouse" class="btn btn-primary btn-sm active">In-House Customer</button>
                <button type="button" id="btn-walkin" class="btn btn-outline-secondary btn-sm">Walk-in Customer</button>
            </div>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">Order placed successfully!</div>
            <?php elseif (!empty($error_message)): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php elseif (!empty($error)): ?>
                <div class="alert alert-danger">An unknown error occurred. Please try again.</div>
            <?php endif; ?>
            <!-- Add Item Form -->
            <form method="post" id="addItemForm" class="mb-4">
                <input type="hidden" name="customer_type" id="add_customer_type" value="<?= $customer_type ?>">
                <h5 class="mb-3" style="color: var(--primary-color);">Add Order Item</h5>
                <div class="row mb-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Menu Item</label>
                        <select name="menu_item_id" class="form-select" required>
                            <option value="">-- Select Menu Item --</option>
                            <?php foreach ($menu_items as $item): ?>
                                <option value="<?= $item['menu_item_id'] ?>">
                                    <?= htmlspecialchars($item['item_name']) ?> (<?= htmlspecialchars($item['item_type']) ?>) - रु<?= number_format($item['price'],2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="add_item" class="btn btn-secondary"><i class="fas fa-plus"></i> Add Item</button>
                    </div>
                </div>
            </form>
            <!-- Place Order Form -->
            <form method="post" id="orderForm">
                <input type="hidden" name="customer_type" id="order_customer_type" value="<?= $customer_type ?>">
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Table Number <span class="text-danger">*</span></label>
                        <input type="number" name="table_number" class="form-control" placeholder="e.g. 12">
                    </div>
                </div>
                <div id="inhouse-section">
                    <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" id="customer_name" class="form-control" placeholder="Search customer name...">
                            <input type="hidden" name="customer_id" id="customer_id_hidden" value="<?= isset($_POST['customer_id']) ? htmlspecialchars($_POST['customer_id']) : '' ?>">
                        </div>
                    </div>
                </div>
                <div id="walkin-section" style="display:none;">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Walk-in Name <span class="text-danger">*</span></label>
                            <input type="text" name="walkin_name" class="form-control" placeholder="Enter customer name...">
                        </div>
                    <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="walkin_phone" class="form-control" placeholder="Enter phone (optional)">
                        </div>
                    </div>
                </div>
                <?php if (!empty($current_order_items)): ?>
                <h5 class="mb-3" style="color: var(--primary-color);">Order Items</h5>
                <div class="table-responsive">
                    <table class="table menu-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th>Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                <?php $total = 0; foreach ($current_order_items as $idx => $item): $total += $item['subtotal']; ?>
                                <tr>
                                    <td>
                                        <?php if ($item['menu_image']): ?>
                                            <img src="<?= htmlspecialchars($item['menu_image']) ?>" alt="Image" style="max-width:40px;max-height:40px;border-radius:8px;margin-right:8px;">
                                        <?php endif; ?>
                                        <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                        <small class="text-muted"> <?= htmlspecialchars($item['item_description']) ?> </small>
                                    </td>
                                    <td><?= htmlspecialchars($item['type']) ?></td>
                                    <td><?= htmlspecialchars($item['category']) ?></td>
                                    <td>रु<?= number_format($item['price'],2) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>रु<?= number_format($item['subtotal'],2) ?></td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="remove_index" value="<?= $idx ?>">
                                            <input type="hidden" name="customer_type" value="<?= $customer_type ?>">
                                            <button type="submit" name="remove_item" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item?')"><i class="fas fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                    </table>
                </div>
                <div class="total-display">
                    <div class="total-row">
                        <span>Total Items:</span>
                            <span id="total-items-amount" data-total="<?= $total ?>">रु<?= number_format($total,2) ?></span>
                    </div>
                    <div class="total-row">
                        <span>Discount:</span>
                        <span>
                                <input type="number" id="discount-input" name="discount_amount" class="form-control d-inline-block" style="width: 100px;" value="<?= isset($_POST['discount_amount']) ? htmlspecialchars($_POST['discount_amount']) : 0 ?>" min="0">
                        </span>
                    </div>
                    <div class="total-row final-amount">
                        <span>Final Amount:</span>
                            <span id="final-amount-display">रु<?= number_format(max(0, $total - (isset($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0)),2) ?></span>
                        </div>
                    </div>
                <div class="mb-4 mt-4">
                    <label class="form-label">Order Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Any special instructions..."></textarea>
                </div>
                <div class="d-flex justify-content-end">
                        <button type="submit" name="place_order" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i> Place Order
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(function() {
    // Persist form values on item add/remove
    $('input[name="table_number"]').on('keyup', function() {
        $('#table_number_hidden').val($(this).val());
    });
    
    $("#customer_name").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "search_customer.php",
                dataType: "json",
                data: { term: request.term },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $("#customer_id_hidden").val(ui.item.id);
            // Also update the hidden field in the final form
            $('form[method="post"]:has(button[name="place_order"]) input[name="customer_id"]').val(ui.item.id);
        }
    });

    // Ensure main form knows about the customer_id and table_number
    $('button[name="place_order"]').on('click', function() {
        // Carry over values to the main form before submitting
        $('form[method="post"]:has(button[name="place_order"]) input[name="customer_id"]').val($('#customer_id_hidden').val());
        $('form[method="post"]:has(button[name="place_order"]) input[name="table_number"]').val($('input[name="table_number"]').val());
    });

    // Dynamic discount calculation
    function updateFinalAmount() {
        const total = parseFloat($('#total-items-amount').data('total')) || 0;
        const discount = parseFloat($('#discount-input').val()) || 0;
        const finalAmount = Math.max(0, total - discount);
        const formattedAmount = 'रु' + finalAmount.toFixed(2);
        $('#final-amount-display').text(formattedAmount);
    }
    $('#discount-input').on('input', updateFinalAmount);

    // Smooth toggle between in-house and walk-in
    $('#btn-inhouse').on('click', function() {
        // Check if we're already on inhouse
        if ($('#btn-inhouse').hasClass('active')) return;
        
        // Check if there are items in the current view
        var currentItems = $('.menu-table tbody tr').length;
        if (currentItems > 0) {
            if (!confirm('Switching to In-House customer will clear any Walk-in customer items. Continue?')) {
                return;
            }
        }
        
        // Show loading overlay for smooth transition
        $('#loadingOverlay').fadeIn(150);
        
        // Smooth UI transition
        $('#btn-inhouse').addClass('btn-primary active').removeClass('btn-outline-secondary');
        $('#btn-walkin').addClass('btn-outline-secondary').removeClass('btn-primary active');
        
        // Smooth section transition with better timing
        $('#walkin-section').fadeOut(250, function() {
            $('#inhouse-section').fadeIn(250);
        });
        
        // Clear walk-in fields
        $('input[name="walkin_name"]').val('');
        $('input[name="walkin_phone"]').val('');
        
        // Set required attributes
        $('#customer_name').attr('required', true);
        $('input[name="walkin_name"]').removeAttr('required');
        
        // Update hidden fields
        $('#add_customer_type').val('inhouse');
        $('#order_customer_type').val('inhouse');
        
        // Store toggle state
        localStorage.setItem('restaurant_customer_type', 'inhouse');
        document.cookie = 'restaurant_customer_type=inhouse; path=/';
        
        // Clear the opposite session array silently
        $.ajax({
            url: 'clear_session_items.php',
            type: 'POST',
            data: { keep_type: 'inhouse' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Hide loading overlay
                    $('#loadingOverlay').fadeOut(200);
                    // Clear the order items display smoothly
                    $('.menu-table tbody').fadeOut(200, function() {
                        $(this).empty().fadeIn(200);
                    });
                    // Hide the order form section
                    $('.total-display, .mb-4.mt-4, .d-flex.justify-content-end').fadeOut(200);
                }
            },
            error: function() {
                $('#loadingOverlay').fadeOut(200);
                alert('Error switching customer type. Please try again.');
            }
        });
    });
    
    $('#btn-walkin').on('click', function() {
        // Check if we're already on walkin
        if ($('#btn-walkin').hasClass('active')) return;
        
        // Check if there are items in the current view
        var currentItems = $('.menu-table tbody tr').length;
        if (currentItems > 0) {
            if (!confirm('Switching to Walk-in customer will clear any In-House customer items. Continue?')) {
                return;
            }
        }
        
        // Show loading overlay for smooth transition
        $('#loadingOverlay').fadeIn(150);
        
        // Smooth UI transition
        $('#btn-walkin').addClass('btn-primary active').removeClass('btn-outline-secondary');
        $('#btn-inhouse').addClass('btn-outline-secondary').removeClass('btn-primary active');
        
        // Smooth section transition with better timing
        $('#inhouse-section').fadeOut(250, function() {
            $('#walkin-section').fadeIn(250);
        });
        
        // Clear in-house fields
        $('#customer_name').val('');
        $('#customer_id_hidden').val('');
        
        // Set required attributes
        $('input[name="walkin_name"]').attr('required', true);
        $('#customer_name').removeAttr('required');
        
        // Update hidden fields
        $('#add_customer_type').val('walkin');
        $('#order_customer_type').val('walkin');
        
        // Store toggle state
        localStorage.setItem('restaurant_customer_type', 'walkin');
        document.cookie = 'restaurant_customer_type=walkin; path=/';
        
        // Clear the opposite session array silently
        $.ajax({
            url: 'clear_session_items.php',
            type: 'POST',
            data: { keep_type: 'walkin' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Hide loading overlay
                    $('#loadingOverlay').fadeOut(200);
                    // Clear the order items display smoothly
                    $('.menu-table tbody').fadeOut(200, function() {
                        $(this).empty().fadeIn(200);
                    });
                    // Hide the order form section
                    $('.total-display, .mb-4.mt-4, .d-flex.justify-content-end').fadeOut(200);
                }
            },
            error: function() {
                $('#loadingOverlay').fadeOut(200);
                alert('Error switching customer type. Please try again.');
            }
        });
    });
    
    // Set initial required state
    $('#customer_name').attr('required', true);
    $('input[name="walkin_name"]').removeAttr('required');
    
    // On page load, set toggle from localStorage without triggering AJAX
    var storedType = localStorage.getItem('restaurant_customer_type');
    if (storedType === 'walkin') {
        // Set UI state without triggering AJAX
        $('#btn-walkin').addClass('btn-primary active').removeClass('btn-outline-secondary');
        $('#btn-inhouse').addClass('btn-outline-secondary').removeClass('btn-primary active');
        $('#inhouse-section').hide();
        $('#walkin-section').show();
        $('#add_customer_type').val('walkin');
        $('#order_customer_type').val('walkin');
        // Set required attributes
        $('input[name="walkin_name"]').attr('required', true);
        $('#customer_name').removeAttr('required');
    } else {
        // Set UI state without triggering AJAX
        $('#btn-inhouse').addClass('btn-primary active').removeClass('btn-outline-secondary');
        $('#btn-walkin').addClass('btn-outline-secondary').removeClass('btn-primary active');
        $('#inhouse-section').show();
        $('#walkin-section').hide();
        $('#add_customer_type').val('inhouse');
        $('#order_customer_type').val('inhouse');
        // Set required attributes
        $('#customer_name').attr('required', true);
        $('input[name="walkin_name"]').removeAttr('required');
    }
});
</script>
</body>
</html>