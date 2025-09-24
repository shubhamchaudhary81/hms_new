<?php
session_start();

// Get the customer type to keep (the one we're switching TO)
$keep_type = isset($_POST['keep_type']) ? $_POST['keep_type'] : '';

if (in_array($keep_type, ['inhouse', 'walkin'])) {
    // Clear the opposite session array
    if ($keep_type === 'inhouse') {
        $_SESSION['order_items_walkin'] = [];
    } else {
        $_SESSION['order_items_inhouse'] = [];
    }
    
    echo json_encode(['success' => true, 'message' => 'Session cleared successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid customer type']);
}
?> 