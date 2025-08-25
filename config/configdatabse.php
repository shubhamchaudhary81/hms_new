<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel";

// Step 1: Create connection to MySQL server (no DB selected yet)
$conn = new mysqli($servername, $username, $password);

// Check server connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // echo "Database '$dbname' created successfully<br>";
} else {
    // echo "Error creating database: " . $conn->error . "<br>";
}
$conn->close(); // Close the server connection

// Step 3: Reconnect using the created database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

   
// Create users table if it doesn't exist
// $create_users_table = "CREATE TABLE IF NOT EXISTS users (
//     user_id INT AUTO_INCREMENT PRIMARY KEY,
//     name VARCHAR(100) NOT NULL,
//     email VARCHAR(100) UNIQUE NOT NULL,
//     password VARCHAR(255) NOT NULL,
//     role ENUM('admin', 'manager', 'receptionist') NOT NULL,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// )";

// if ($conn->query($create_users_table) === TRUE) {
//     // Check if default admin exists
//     $check_admin = "SELECT * FROM users WHERE email = 'admin@example.com'";
//     $result = $conn->query($check_admin);
    
//     if ($result->num_rows == 0) {
//         // Insert default admin (password: admin123)
//         $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
//         $insert_admin = "INSERT INTO users (name, email, password, role) 
//                          VALUES ('Admin', 'admin@example.com', '$hashed_password', 'admin')";
        
//         if ($conn->query($insert_admin) === TRUE) {
//             // Default admin created successfully
//         } else {
//             // Error inserting default admin
//         }
//     }
// } else {
//     // Error creating users table
// }

// Create room type table if it doesn't exist
$create_room_type_table = "CREATE TABLE IF NOT EXISTS RoomType (
    room_type_id INT AUTO_INCREMENT PRIMARY KEY,
    room_type_name VARCHAR(100) NOT NULL,
    description TEXT,
    capacity INT NOT NULL,
    base_price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($create_room_type_table) === TRUE) {
    // echo "Table 'roomtype' created successfully<br>";
} else {
    // echo "Error creating 'roomtype' table: " . $conn->error . "<br>";
}



// Create amenity table if it doesn't exist
$create_amenity_table = "CREATE TABLE IF NOT EXISTS Amenity (
    amenity_id INT AUTO_INCREMENT PRIMARY KEY,
    amenity_name VARCHAR(100) NOT NULL,
    description TEXT,
    icon_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($create_amenity_table) === TRUE) {
    // echo "Table 'Amenity' created successfully<br>";
} else {
    // echo "Error creating 'Amenity' table: " . $conn->error . "<br>";
}

// Create room service table if it doesn't exist
$create_room_service_table = "CREATE TABLE IF NOT EXISTS RoomService (
    room_service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    availability_status ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if ($conn->query($create_room_service_table) === TRUE) {
    // echo "Table 'RoomService' created successfully<br>";
} else {
    // echo "Error creating 'RoomService' table: " . $conn->error . "<br>";
}

// Create room table if it doesn't exist
$create_room_table = "CREATE TABLE IF NOT EXISTS Room (
     room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) NOT NULL UNIQUE,
    room_type INT NOT NULL,
    price_per_night DECIMAL(10, 2) NOT NULL,
    weekend_price DECIMAL(10, 2) DEFAULT NULL,
    season_price DECIMAL(10, 2) DEFAULT NULL,
    capacity INT NOT NULL DEFAULT 1,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('available', 'booked', 'maintenance') NOT NULL DEFAULT 'available',
    floor_number INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_type) REFERENCES RoomType(room_type_id) ON DELETE CASCADE
)";
if ($conn->query($create_room_table) === TRUE) {
    // echo "Table 'Room' created successfully<br>";
} else {
    // echo "Error creating 'Room' table: " . $conn->error . "<br>";
}

// Create room amenity table if it doesn't exist
$create_room_amenity_table = "CREATE TABLE IF NOT EXISTS RoomAmenity (
    room_amenity_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    amenity_id INT NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES Room(room_id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES Amenity(amenity_id) ON DELETE CASCADE
)";
if ($conn->query($create_room_amenity_table) === TRUE) {
    // echo "Table 'RoomAmenity' created successfully<br>";
} else {
    // echo "Error creating 'RoomAmenity' table: " . $conn->error . "<br>";
}

$create_customer_table = "CREATE TABLE IF NOT EXISTS Customers(
    
)";

// Create customer table if it doesn't exist
$create_customer_table = "CREATE TABLE IF NOT EXISTS Customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    number VARCHAR(15) NOT NULL UNIQUE,
    dob DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL DEFAULT 'Male',
    province VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL,
    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($create_customer_table) === TRUE) {
    // echo "Table 'Customers' created successfully<br>";
} else {
    // echo "Error creating 'Customers' table: " . $conn->error . "<br>";
}

//Create Reservation table if it doesn't exist
$create_reservation_table = "CREATE TABLE IF NOT EXISTS Reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    room_type_id INT NOT NULL,
    requested_check_in_date DATETIME NOT NULL,
    requested_check_out_date DATETIME NOT NULL,
    num_guests INT NOT NULL DEFAULT 1,
    estimated_total_amount DECIMAL(10, 2),
    status VARCHAR(50) NOT NULL,
    special_requests TEXT,
    reservation_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES Customers(id),
    FOREIGN KEY (room_type_id) REFERENCES RoomType(room_type_id)
)";
if ($conn->query($create_reservation_table) === TRUE) {
    // echo "Table 'Reservation' created successfully<br>";
} else {
    // echo "Error creating 'Reservation' table: " . $conn->error . "<br>";
}


//Create booking table if it doesn't exist
$create_bookings_table = "CREATE TABLE IF NOT EXISTS Bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL UNIQUE,
    room_id INT NOT NULL,
    actual_check_in DATETIME NOT NULL,
    actual_check_out DATETIME DEFAULT NULL,
    advance_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (reservation_id) REFERENCES Reservations(reservation_id),
    FOREIGN KEY (room_id) REFERENCES Room(room_id)
)";

if ($conn->query($create_bookings_table) === TRUE) {
    // echo "Table 'Bookings' created successfully<br>";
} else {
    // echo "Error creating 'Bookings' table: " . $conn->error . "<br>";
}

//Create BookingRoomService table if it doesn't exist
$create_booking_room_service_table = "CREATE TABLE IF NOT EXISTS BookingRoomService (
    booking_room_service_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    room_service_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    service_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    charge_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Requested',

    FOREIGN KEY (booking_id) REFERENCES Bookings(booking_id),
    FOREIGN KEY (room_service_id) REFERENCES RoomService(room_service_id)
)";
if ($conn->query($create_booking_room_service_table) === TRUE) {
    // echo "Table 'BookingRoomService' created successfully<br>";
} else {
    // echo "Error creating 'BookingRoomService' table: " . $conn->error . "<br>";
}

//Create Menuitems table if it doesn't exist
$create_menu_items_table = "CREATE TABLE IF NOT EXISTS MenuItems (
    menu_item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) UNIQUE NOT NULL,
    item_description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    item_type VARCHAR(50) NOT NULL, 
    category VARCHAR(50),     
    is_available BOOLEAN DEFAULT TRUE,
    menu_image VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if ($conn->query($create_menu_items_table) === TRUE) {
    // echo "Table 'MenuItems' created successfully<br>";
} else {
    // echo "Error creating 'MenuItems' table: " . $conn->error . "<br>";
}

// Create RestaurantOrders table if it doesn't exist
$create_restaurant_orders_table = "CREATE TABLE IF NOT EXISTS RestaurantOrders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT, 
    customer_id INT, 
    walkin_name VARCHAR(100) NULL,
    walkin_phone VARCHAR(20) NULL,
    table_number INT,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_items_cost DECIMAL(10, 2) NOT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    final_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending', 
    notes TEXT,
    payment_status VARCHAR(50) DEFAULT 'Pending', 
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (booking_id) REFERENCES Bookings(booking_id),
    FOREIGN KEY (customer_id) REFERENCES Customers(id)
)";
if ($conn->query($create_restaurant_orders_table) === TRUE) {
    // echo "Table 'RestaurantOrders' created successfully<br>";
} else {
    // echo "Error creating 'RestaurantOrders' table: " . $conn->error . "<br>";
}

// Create OrderItems table if it doesn't exist
$create_order_items_table = "CREATE TABLE IF NOT EXISTS OrderItems (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price_at_order DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES RestaurantOrders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES MenuItems(menu_item_id) ON DELETE CASCADE
)";
if ($conn->query($create_order_items_table) === TRUE) {
    // echo "Table 'OrderItems' created successfully<br>";
} else {
    // echo "Error creating 'OrderItems' table: " . $conn->error . "<br>";
}

//Create Payment table if it doesn't exist
$create_payment_table = "CREATE TABLE IF NOT EXISTS Payments (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT DEFAULT NULL,
  restaurant_order_id INT DEFAULT NULL,
  customer_id INT NOT NULL,
  payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  amount DECIMAL(10, 2) NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  transaction_id VARCHAR(100),
  currency VARCHAR(10) DEFAULT 'NPR',
  status VARCHAR(50) NOT NULL DEFAULT 'Completed',
  notes TEXT,

  -- Foreign Keys
  CONSTRAINT fk_payment_booking FOREIGN KEY (booking_id)
    REFERENCES Bookings(booking_id) ON DELETE SET NULL,

  CONSTRAINT fk_payment_order FOREIGN KEY (restaurant_order_id)
    REFERENCES RestaurantOrders(order_id) ON DELETE SET NULL,

  CONSTRAINT fk_payment_customer FOREIGN KEY (customer_id)
    REFERENCES Customers(id) ON DELETE CASCADE
)";
if ($conn->query($create_payment_table) === TRUE) {
    // echo "Table 'Payments' created successfully<br>";
} else {
    // echo "Error creating 'Payments' table: " . $conn->error . "<br>";
}

//Create Invoice table if it doesn't exist
$create_invoice_table = "CREATE TABLE IF NOT EXISTS Invoices (
 invoice_id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL UNIQUE,
  customer_id INT NOT NULL,
  invoice_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_amount DECIMAL(10, 2) NOT NULL,
  amount_paid DECIMAL(10, 2) NOT NULL,
  balance_due DECIMAL(10, 2) NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'Generated',
  notes TEXT,
  due_date DATETIME,

  -- Foreign Key Constraints
  CONSTRAINT fk_invoice_booking FOREIGN KEY (booking_id)
    REFERENCES Bookings(booking_id) ON DELETE CASCADE,

  CONSTRAINT fk_invoice_customer FOREIGN KEY (customer_id)
    REFERENCES Customers(id) ON DELETE CASCADE
)";
if ($conn->query($create_invoice_table) === TRUE) {
    // echo "Table 'Invoices' created successfully<br>";
} else {
    // echo "Error creating 'Invoices' table: " . $conn->error . "<br>";
}

//Create Reviews table if it doesn't exist
$create_reviews_table = "CREATE TABLE IF NOT EXISTS Reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  booking_id INT DEFAULT NULL,
  room_id INT DEFAULT NULL,
  rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  review_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_approved BOOLEAN DEFAULT FALSE,

  -- Foreign Key Constraints
  CONSTRAINT fk_review_customer FOREIGN KEY (customer_id)
    REFERENCES Customers(id) ON DELETE CASCADE,

  CONSTRAINT fk_review_booking FOREIGN KEY (booking_id)
    REFERENCES Bookings(booking_id) ON DELETE SET NULL,

  CONSTRAINT fk_review_room FOREIGN KEY (room_id)
    REFERENCES Room(room_id) ON DELETE SET NULL
)";
if ($conn->query($create_reviews_table) === TRUE) {
    // echo "Table 'Reviews' created successfully<br>";
} else {
    // echo "Error creating 'Reviews' table: " . $conn->error . "<br>";
}

//Create Role table

$create_roles_table = "CREATE TABLE IF NOT EXISTS Roles (
   role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
 )";
if ($conn->query($create_roles_table) === TRUE) {
    // echo "Table 'Reviews' created successfully<br>";
} else {
    // echo "Error creating 'Reviews' table: " . $conn->error . "<br>";
}    

// Create Staff table

$create_staffs_table = "CREATE TABLE IF NOT EXISTS Staffs(
staff_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    role_id INT NOT NULL,
    is_active ENUM('Active', 'Inactive') DEFAULT 'Active',
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
    )";
if ($conn->query($create_staffs_table) === TRUE) {
    // echo "Table 'Reviews' created successfully<br>";
} else {
    // echo "Error creating 'Reviews' table: " . $conn->error . "<br>";
}  