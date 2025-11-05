<?php
session_start();
include "connection.php";

// Check if user is logged in as company
if (!isset($_SESSION['company_id'])) {
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// Get company information
$stmt = $conn->prepare("SELECT * FROM companies WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();

if (!$company) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Create equipment tables if they don't exist
$create_equipment_categories = "CREATE TABLE IF NOT EXISTS equipment_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    icon_class VARCHAR(50) DEFAULT 'fas fa-cog',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($create_equipment_categories);

$create_equipment_items = "CREATE TABLE IF NOT EXISTS equipment_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    item_name VARCHAR(200) NOT NULL,
    description TEXT,
    standard_price DECIMAL(10,2) DEFAULT 0.00,
    unit_type ENUM('per_hour', 'per_day', 'per_week', 'per_month') DEFAULT 'per_hour',
    is_custom BOOLEAN DEFAULT FALSE,
    company_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($create_equipment_items);

$create_equipment_packages = "CREATE TABLE IF NOT EXISTS equipment_packages (
    package_id INT AUTO_INCREMENT PRIMARY KEY,
    package_name VARCHAR(200) NOT NULL,
    package_description TEXT,
    total_price DECIMAL(10,2) DEFAULT 0.00,
    package_type ENUM('predefined', 'custom') DEFAULT 'predefined',
    company_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($create_equipment_packages);

$create_package_items = "CREATE TABLE IF NOT EXISTS package_items (
    package_item_id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT,
    item_id INT,
    quantity INT DEFAULT 1
)";
$conn->query($create_package_items);

$create_place_equipment = "CREATE TABLE IF NOT EXISTS place_equipment (
    place_equipment_id INT AUTO_INCREMENT PRIMARY KEY,
    place_id INT,
    item_id INT,
    quantity_available INT DEFAULT 1,
    custom_price DECIMAL(10,2) NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($create_place_equipment);

// Get all equipment categories and items for selection
$categories_sql = "SELECT * FROM equipment_categories WHERE status = 'active' ORDER BY category_name";
$categories_result = $conn->query($categories_sql);
$categories = $categories_result ? $categories_result->fetch_all(MYSQLI_ASSOC) : [];

$items_sql = "SELECT ei.*, ec.category_name 
              FROM equipment_items ei 
              LEFT JOIN equipment_categories ec ON ei.category_id = ec.category_id 
              WHERE ei.status = 'active' 
              ORDER BY ec.category_name, ei.item_name";
$items_result = $conn->query($items_sql);
$equipment_items = $items_result ? $items_result->fetch_all(MYSQLI_ASSOC) : [];

// Group items by category
$items_by_category = [];
foreach ($equipment_items as $item) {
    $cat_name = $item['category_name'] ?? 'Other';
    if (!isset($items_by_category[$cat_name])) {
        $items_by_category[$cat_name] = [];
    }
    $items_by_category[$cat_name][] = $item;
}

// Get equipment packages (for place type mapping)
$packages_sql = "SELECT * FROM equipment_packages WHERE status = 'active' AND package_type = 'predefined'";
$packages_result = $conn->query($packages_sql);
$equipment_packages = $packages_result ? $packages_result->fetch_all(MYSQLI_ASSOC) : [];

// Map place types to equipment packages
$place_type_to_package = [
    'laboratory' => 'Laboratory Package',
    'training_room' => 'Training Room Package',
    'conference_room' => 'Conference Room Package',
    'workshop_space' => 'Workshop Space Package',
    'event_hall' => 'Event Hall Package',
    'office_space' => 'Office Space Package'
];

// Create package mapping: place_type => package items
$package_items_map = [];
foreach ($equipment_packages as $package) {
    $package_items_sql = "SELECT pi.*, ei.item_id, ei.item_name, ei.description, ei.standard_price, ei.unit_type, ec.category_name
                          FROM package_items pi
                          JOIN equipment_items ei ON pi.item_id = ei.item_id
                          LEFT JOIN equipment_categories ec ON ei.category_id = ec.category_id
                          WHERE pi.package_id = ? AND ei.status = 'active'";
    $stmt = $conn->prepare($package_items_sql);
    if ($stmt) {
        $stmt->bind_param("i", $package['package_id']);
        $stmt->execute();
        $package_items_result = $stmt->get_result();
        $package_items_map[$package['package_name']] = $package_items_result->fetch_all(MYSQLI_ASSOC);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Required fields
    $place_name = trim($_POST['place_name']);
    $description = trim($_POST['description']);
    $place_type = $_POST['place_type'];
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $country = trim($_POST['country']);
    
    // Optional fields
    $space_type = trim($_POST['space_type'] ?? '');
    $capacity = !empty($_POST['capacity']) ? intval($_POST['capacity']) : null;
    
    // Standard rates
    $hourly_rate = !empty($_POST['hourly_rate']) ? floatval($_POST['hourly_rate']) : 0.00;
    $daily_rate = !empty($_POST['daily_rate']) ? floatval($_POST['daily_rate']) : 0.00;
    $weekly_rate = !empty($_POST['weekly_rate']) ? floatval($_POST['weekly_rate']) : 0.00;
    $monthly_rate = !empty($_POST['monthly_rate']) ? floatval($_POST['monthly_rate']) : 0.00;
    
    // Weekend rates
    $weekend_hourly_rate = !empty($_POST['weekend_hourly_rate']) ? floatval($_POST['weekend_hourly_rate']) : 0.00;
    $weekend_daily_rate = !empty($_POST['weekend_daily_rate']) ? floatval($_POST['weekend_daily_rate']) : 0.00;
    $weekend_weekly_rate = !empty($_POST['weekend_weekly_rate']) ? floatval($_POST['weekend_weekly_rate']) : 0.00;
    $weekend_monthly_rate = !empty($_POST['weekend_monthly_rate']) ? floatval($_POST['weekend_monthly_rate']) : 0.00;
    
    // Weekday rates
    $weekday_hourly_rate = !empty($_POST['weekday_hourly_rate']) ? floatval($_POST['weekday_hourly_rate']) : 0.00;
    $weekday_daily_rate = !empty($_POST['weekday_daily_rate']) ? floatval($_POST['weekday_daily_rate']) : 0.00;
    $weekday_weekly_rate = !empty($_POST['weekday_weekly_rate']) ? floatval($_POST['weekday_weekly_rate']) : 0.00;
    $weekday_monthly_rate = !empty($_POST['weekday_monthly_rate']) ? floatval($_POST['weekday_monthly_rate']) : 0.00;
    
    // Location fields
    $postal_code = trim($_POST['postal_code'] ?? '');
    $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
    $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
    
    // Additional fields
    $amenities = trim($_POST['amenities'] ?? '');
    $images = trim($_POST['images'] ?? '');
    $availability_schedule = trim($_POST['availability_schedule'] ?? '');
    $booking_policy = trim($_POST['booking_policy'] ?? '');
    $cancellation_policy = trim($_POST['cancellation_policy'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($place_name) || empty($description) || empty($place_type) || empty($address) || empty($city) || empty($country)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Insert place into database
        // 30 parameters: company_id(i), place_name(s), place_type(s), space_type(s), description(s), capacity(i), 
        // hourly_rate(d), daily_rate(d), weekly_rate(d), monthly_rate(d), weekend_hourly_rate(d), weekend_daily_rate(d), 
        // weekend_weekly_rate(d), weekend_monthly_rate(d), weekday_hourly_rate(d), weekday_daily_rate(d), 
        // weekday_weekly_rate(d), weekday_monthly_rate(d), address(s), city(s), country(s), postal_code(s), 
        // latitude(d), longitude(d), amenities(s), images(s), availability_schedule(s), booking_policy(s), 
        // cancellation_policy(s), status(s)
        $sql = "INSERT INTO places (company_id, place_name, place_type, space_type, description, capacity, hourly_rate, daily_rate, weekly_rate, monthly_rate, weekend_hourly_rate, weekend_daily_rate, weekend_weekly_rate, weekend_monthly_rate, weekday_hourly_rate, weekday_daily_rate, weekday_weekly_rate, weekday_monthly_rate, address, city, country, postal_code, latitude, longitude, amenities, images, availability_schedule, booking_policy, cancellation_policy, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // Type string: i=1, s=4, i=1, d=12, s=4, d=2, s=6 = 30 characters total
            // Parameters: company_id(i), place_name(s), place_type(s), space_type(s), description(s), capacity(i),
            // 12 rates(d), address(s), city(s), country(s), postal_code(s), latitude(d), longitude(d),
            // amenities(s), images(s), availability_schedule(s), booking_policy(s), cancellation_policy(s), status(s)
            $stmt->bind_param("issssiddddddddddddssssddssssss", 
                $company_id,              // 1: i
                $place_name,               // 2: s
                $place_type,               // 3: s
                $space_type,               // 4: s
                $description,              // 5: s
                $capacity,                 // 6: i
                $hourly_rate,              // 7: d
                $daily_rate,               // 8: d
                $weekly_rate,              // 9: d
                $monthly_rate,             // 10: d
                $weekend_hourly_rate,      // 11: d
                $weekend_daily_rate,       // 12: d
                $weekend_weekly_rate,      // 13: d
                $weekend_monthly_rate,     // 14: d
                $weekday_hourly_rate,      // 15: d
                $weekday_daily_rate,       // 16: d
                $weekday_weekly_rate,      // 17: d
                $weekday_monthly_rate,     // 18: d
                $address,                  // 19: s
                $city,                     // 20: s
                $country,                  // 21: s
                $postal_code,              // 22: s
                $latitude,                 // 23: d
                $longitude,                // 24: d
                $amenities,                // 25: s
                $images,                   // 26: s
                $availability_schedule,    // 27: s
                $booking_policy,           // 28: s
                $cancellation_policy,      // 29: s
                $status                    // 30: s
            );
            
            if ($stmt->execute()) {
                $place_id = $conn->insert_id;
                
                // Handle equipment selection
                if (!empty($_POST['selected_equipment']) && is_array($_POST['selected_equipment'])) {
                    $equipment_insert_sql = "INSERT INTO place_equipment (place_id, item_id, quantity_available, custom_price, is_available) VALUES (?, ?, ?, ?, 1)";
                    $equipment_stmt = $conn->prepare($equipment_insert_sql);
                    
                    if ($equipment_stmt) {
                        foreach ($_POST['selected_equipment'] as $key => $equip) {
                            $item_id = !empty($equip['item_id']) ? intval($equip['item_id']) : 0;
                            $quantity = !empty($equip['quantity']) ? intval($equip['quantity']) : 1;
                            $custom_price = !empty($equip['custom_price']) && floatval($equip['custom_price']) > 0 ? floatval($equip['custom_price']) : null;
                            
                            if ($item_id > 0) {
                                $equipment_stmt->bind_param("iiid", $place_id, $item_id, $quantity, $custom_price);
                                $equipment_stmt->execute();
                            }
                        }
                        $equipment_stmt->close();
                    }
                }
                
                $success_message = "Place posted successfully!";
                // Clear form data
                $place_name = $description = $address = $city = $country = '';
                $space_type = $postal_code = $amenities = $images = $availability_schedule = '';
                $booking_policy = $cancellation_policy = '';
                $place_type = '';
                $capacity = null;
                $latitude = $longitude = null;
                $hourly_rate = $daily_rate = $weekly_rate = $monthly_rate = 0.00;
                $weekend_hourly_rate = $weekend_daily_rate = $weekend_weekly_rate = $weekend_monthly_rate = 0.00;
                $weekday_hourly_rate = $weekday_daily_rate = $weekday_weekly_rate = $weekday_monthly_rate = 0.00;
                $status = 'active';
            } else {
                $error_message = "Error posting place: " . $stmt->error;
            }
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Place - Company Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ============================================
           CSS VARIABLES
           ============================================ */
        :root {
            --brand: #0ea5a8;
            --brand-2: #22d3ee;
            --ink: #0b1f3a;
            --muted: #475569;
            --panel: #ffffff;
            --line: #e5e7eb;
            --success: #4ade80;
            --error: #f87171;
            --warning: #fbbf24;
            --text-dark: #0f172a;
            --text-light: #475569;
            --text-white: #ffffff;
            --bg-primary: #ffffff;
            --bg-secondary: #f6f8fb;
            --border-light: #e5e7eb;
            --border-focus: #0ea5a8;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ============================================
           BASE STYLES / RESET
           ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", sans-serif;
            background: var(--bg-secondary);
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* ============================================
           LAYOUT
           ============================================ */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: var(--panel);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--line);
        }

        .form-container {
            background: var(--panel);
            border-radius: var(--radius-xl);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--line);
        }

        /* ============================================
           TYPOGRAPHY
           ============================================ */
        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--muted);
            font-size: 1.1rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 1rem;
            margin-top: 1rem;
        }

        .section-divider {
            margin: 2rem 0;
            border: none;
            border-top: 2px solid var(--line);
        }

        /* ============================================
           BREADCRUMB NAVIGATION
           ============================================ */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: var(--brand);
            text-decoration: none;
            transition: var(--transition);
        }

        .breadcrumb a:hover {
            color: var(--brand-2);
        }

        /* ============================================
           FORM ELEMENTS
           ============================================ */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-label.required::after {
            content: " *";
            color: var(--error);
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-lg);
            font-size: 1rem;
            font-family: inherit;
            transition: var(--transition);
            background: var(--bg-primary);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(14, 165, 168, 0.1);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-help {
            font-size: 0.85rem;
            color: var(--muted);
            margin-top: 0.5rem;
            font-style: italic;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            cursor: pointer;
            accent-color: var(--brand);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--line);
        }

        /* ============================================
           BUTTONS
           ============================================ */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            text-align: center;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: var(--text-white);
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-dark);
            border: 2px solid var(--border-light);
        }

        .btn-secondary:hover {
            background: var(--brand);
            color: var(--text-white);
            border-color: var(--brand);
        }

        /* ============================================
           ALERTS
           ============================================ */
        .alert {
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: rgba(74, 222, 128, 0.1);
            color: #059669;
            border: 1px solid rgba(74, 222, 128, 0.3);
        }

        .alert-error {
            background: rgba(248, 113, 113, 0.1);
            color: #dc2626;
            border: 1px solid rgba(248, 113, 113, 0.3);
        }

        /* ============================================
           RATE SECTIONS
           ============================================ */
        .rate-section {
            background: var(--bg-secondary);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
        }

        .rate-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 1rem;
        }

        .rate-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        /* ============================================
           SCHEDULE SECTION
           ============================================ */
        .schedule-container {
            background: var(--bg-secondary);
            border: 2px solid var(--border-light);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .schedule-day-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            background: var(--panel);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-light);
            transition: var(--transition);
        }

        .schedule-day-row:hover {
            border-color: var(--brand);
            box-shadow: var(--shadow-sm);
        }

        .schedule-day-checkbox {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 120px;
        }

        .schedule-day-check {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--brand);
        }

        .schedule-day-label {
            font-weight: 600;
            color: var(--ink);
            cursor: pointer;
            margin: 0;
            user-select: none;
        }

        .schedule-time-inputs {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .schedule-time-input {
            flex: 1;
            max-width: 150px;
            padding: 0.5rem;
        }

        .schedule-time-separator {
            color: var(--muted);
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* ============================================
           EQUIPMENT SECTION
           ============================================ */
        .equipment-categories {
            margin-top: 1rem;
        }

        .equipment-category-section {
            background: var(--bg-secondary);
            border: 2px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .equipment-category-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .equipment-category-title i {
            color: var(--brand);
        }

        .equipment-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }

        .equipment-item-checkbox {
            background: var(--panel);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            padding: 0.75rem;
            transition: var(--transition);
        }

        .equipment-item-checkbox:hover {
            border-color: var(--brand);
            box-shadow: var(--shadow-sm);
        }

        .equipment-item-checkbox label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-weight: 500;
            color: var(--ink);
        }

        .equipment-item-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--brand);
        }

        .equipment-item-label {
            flex: 1;
        }

        .equipment-standard-price {
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 400;
        }

        .equipment-custom-inputs {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .equipment-empty {
            text-align: center;
            padding: 2rem;
            color: var(--muted);
            font-style: italic;
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            border: 2px dashed var(--border-light);
        }

        .selected-equipment-container {
            background: var(--bg-secondary);
            border: 2px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .selected-equipment-list {
            margin-top: 1rem;
        }

        .selected-equipment-item {
            background: var(--panel);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .selected-equipment-item-info {
            flex: 1;
        }

        .selected-equipment-item-name {
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 0.25rem;
        }

        .selected-equipment-item-price {
            font-size: 0.9rem;
            color: var(--muted);
        }

        .selected-equipment-remove {
            background: var(--error);
            color: var(--text-white);
            border: none;
            border-radius: var(--radius-md);
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .selected-equipment-remove:hover {
            background: #dc2626;
            transform: scale(1.05);
        }

        /* ============================================
           PACKAGE EQUIPMENT DISPLAY
           ============================================ */
        #package-equipment-display {
            display: none;
            margin-top: 1rem;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .package-equipment-container {
            background: linear-gradient(135deg, rgba(14, 165, 168, 0.1) 0%, rgba(14, 165, 168, 0.05) 100%);
            border: 2px solid var(--brand);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-top: 0.5rem;
        }

        .package-equipment-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--brand);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .package-equipment-description {
            color: var(--muted);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .package-equipment-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
        }

        .package-equipment-item {
            background: var(--panel);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            padding: 1rem;
            transition: var(--transition);
        }

        .package-equipment-item:hover {
            border-color: var(--brand);
            box-shadow: var(--shadow-sm);
        }

        .package-equipment-item-name {
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .package-equipment-item-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
        }

        .package-equipment-control-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .package-control-label {
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 500;
            white-space: nowrap;
        }

        .package-equipment-item-total {
            padding-top: 0.75rem;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* ============================================
           RESPONSIVE DESIGN
           ============================================ */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .rate-grid {
                grid-template-columns: 1fr;
            }

            .header {
                padding: 1.5rem;
            }

            .form-container {
                padding: 1.5rem;
            }

            .schedule-day-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .schedule-day-checkbox {
                min-width: auto;
                width: 100%;
            }

            .schedule-time-inputs {
                width: 100%;
                flex-direction: column;
                gap: 0.5rem;
            }

            .schedule-time-input {
                max-width: 100%;
                width: 100%;
            }

            .schedule-time-separator {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="breadcrumb">
                <a href="company_dashboard.php">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <span>/</span>
                <a href="manage_places.php">Manage Places</a>
                <span>/</span>
                <span>Post Place</span>
            </div>
            <h1 class="page-title">Post New Place</h1>
            <p class="page-subtitle">Add a new venue or location for training, events, or workshops</p>
        </div>

        <div class="form-container">
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <h2 class="section-title">Basic Information</h2>

                <div class="form-group">
                    <label for="place_name" class="form-label required">Place Name</label>
                    <input type="text" id="place_name" name="place_name" class="form-input" 
                           value="<?php echo htmlspecialchars($place_name ?? ''); ?>" 
                           placeholder="Enter place name" required>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label required">Description</label>
                    <textarea id="description" name="description" class="form-input form-textarea" 
                              placeholder="Describe the place, its features, and what it's suitable for" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="capacity" class="form-label">Capacity</label>
                    <input type="number" id="capacity" name="capacity" class="form-input" 
                           value="<?php echo htmlspecialchars($capacity ?? ''); ?>" 
                           placeholder="Maximum number of people" min="1">
                </div>

                <hr class="section-divider">
                <h2 class="section-title">Location Details</h2>

                <div class="form-group">
                    <label for="address" class="form-label required">Address</label>
                    <textarea id="address" name="address" class="form-input form-textarea" 
                              placeholder="Enter complete address" required><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city" class="form-label required">City</label>
                        <input type="text" id="city" name="city" class="form-input" 
                               value="<?php echo htmlspecialchars($city ?? ''); ?>" 
                               placeholder="Enter city" required>
                    </div>

                    <div class="form-group">
                        <label for="country" class="form-label required">Country</label>
                        <input type="text" id="country" name="country" class="form-input" 
                               value="<?php echo htmlspecialchars($country ?? ''); ?>" 
                               placeholder="Enter country" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="postal_code" class="form-label">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-input" 
                               value="<?php echo htmlspecialchars($postal_code ?? ''); ?>" 
                               placeholder="Enter postal code">
                    </div>

                    <div class="form-group">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="number" id="latitude" name="latitude" class="form-input" 
                               value="<?php echo htmlspecialchars($latitude ?? ''); ?>" 
                               placeholder="e.g., 33.8547" step="any">
                        <p class="form-help">Optional: For map integration</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="longitude" class="form-label">Longitude</label>
                    <input type="number" id="longitude" name="longitude" class="form-input" 
                           value="<?php echo htmlspecialchars($longitude ?? ''); ?>" 
                           placeholder="e.g., 35.8621" step="any">
                    <p class="form-help">Optional: For map integration</p>
                </div>

                <hr class="section-divider">
                <h2 class="section-title">Pricing</h2>

                <div class="rate-section">
                    <div class="rate-section-title">Standard Rates</div>
                    <div class="rate-grid">
                        <div class="form-group">
                            <label for="hourly_rate" class="form-label">Hourly Rate</label>
                            <input type="number" id="hourly_rate" name="hourly_rate" class="form-input" 
                                   value="<?php echo htmlspecialchars($hourly_rate ?? '0.00'); ?>" 
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="daily_rate" class="form-label">Daily Rate</label>
                            <input type="number" id="daily_rate" name="daily_rate" class="form-input" 
                                   value="<?php echo htmlspecialchars($daily_rate ?? '0.00'); ?>" 
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="weekly_rate" class="form-label">Weekly Rate</label>
                            <input type="number" id="weekly_rate" name="weekly_rate" class="form-input" 
                                   value="<?php echo htmlspecialchars($weekly_rate ?? '0.00'); ?>" 
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="monthly_rate" class="form-label">Monthly Rate</label>
                            <input type="number" id="monthly_rate" name="monthly_rate" class="form-input" 
                                   value="<?php echo htmlspecialchars($monthly_rate ?? '0.00'); ?>" 
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                    </div>
                </div>

                <div class="rate-section">
                    <div class="rate-section-title">Weekend Rates (Optional)</div>
                    <div class="rate-grid">
                        <div class="form-group">
                            <label for="weekend_hourly_rate" class="form-label">Weekend Hourly Rate</label>
                            <input type="number" id="weekend_hourly_rate" name="weekend_hourly_rate" class="form-input" 
                                   value="<?php echo htmlspecialchars($weekend_hourly_rate ?? '0.00'); ?>" 
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="weekend_daily_rate" class="form-label">Weekend Daily Rate</label>
                            <input type="number" id="weekend_daily_rate" name="weekend_daily_rate" class="form-input" 
                                   value="<?php echo htmlspecialchars($weekend_daily_rate ?? '0.00'); ?>" 
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="weekend_weekly_rate" class="form-label">Weekend Weekly Rate</label>
                            <input type="number" id="weekend_weekly_rate" name="weekend_weekly_rate" class="form-input" 
                                   value="<?php echo htmlspecialchars($weekend_weekly_rate ?? '0.00'); ?>" 
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="weekend_monthly_rate" class="form-label">Weekend Monthly Rate</label>
                            <input type="number" id="weekend_monthly_rate" name="weekend_monthly_rate" class="form-input" 
                                   value="<?php echo htmlspecialchars($weekend_monthly_rate ?? '0.00'); ?>" 
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                    </div>
                </div>

                <div class="rate-section">
                    <div class="rate-section-title">Weekday Rates (Optional)</div>
                    <div class="rate-grid">
                        <div class="form-group">
                            <label for="weekday_hourly_rate" class="form-label">Weekday Hourly Rate</label>
                            <input type="number" id="weekday_hourly_rate" name="weekday_hourly_rate" class="form-input" 
                                   value="<?php echo htmlspecialchars($weekday_hourly_rate ?? '0.00'); ?>" 
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="weekday_daily_rate" class="form-label">Weekday Daily Rate</label>
                            <input type="number" id="weekday_daily_rate" name="weekday_daily_rate" class="form-input" 
                                   value="<?php echo htmlspecialchars($weekday_daily_rate ?? '0.00'); ?>" 
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="weekday_weekly_rate" class="form-label">Weekday Weekly Rate</label>
                            <input type="number" id="weekday_weekly_rate" name="weekday_weekly_rate" class="form-input" 
                                   value="<?php echo htmlspecialchars($weekday_weekly_rate ?? '0.00'); ?>" 
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="weekday_monthly_rate" class="form-label">Weekday Monthly Rate</label>
                            <input type="number" id="weekday_monthly_rate" name="weekday_monthly_rate" class="form-input" 
                                   value="<?php echo htmlspecialchars($weekday_monthly_rate ?? '0.00'); ?>" 
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                    </div>
                </div>

                <hr class="section-divider">
                <h2 class="section-title">Additional Information</h2>

                <div class="form-group">
                    <label for="amenities" class="form-label">Amenities</label>
                    <textarea id="amenities" name="amenities" class="form-input form-textarea" 
                              placeholder="List available amenities (e.g., WiFi, Projector, Whiteboard, Kitchen, Parking, Air Conditioning, etc.)"><?php echo htmlspecialchars($amenities ?? ''); ?></textarea>
                    <p class="form-help">Separate amenities with commas or list them on separate lines</p>
                </div>

                <div class="form-group">
                    <label for="images" class="form-label">Images</label>
                    <textarea id="images" name="images" class="form-input form-textarea" 
                              placeholder="Enter image URLs (one per line) or comma-separated"><?php echo htmlspecialchars($images ?? ''); ?></textarea>
                    <p class="form-help">Enter image URLs or paths, one per line or comma-separated</p>
                </div>

                <div class="form-group">
                    <label for="availability_schedule" class="form-label">Availability Schedule</label>
                    <p class="form-help">Select days of the week and set time for each day</p>
                    
                    <div class="schedule-container">
                        <?php
                        // Parse existing schedule if available
                        $schedule_data = [];
                        if (!empty($availability_schedule)) {
                            try {
                                $schedule_data = json_decode($availability_schedule, true);
                                if (!is_array($schedule_data)) {
                                    $schedule_data = [];
                                }
                            } catch (Exception $e) {
                                $schedule_data = [];
                            }
                        }
                        
                        $days = [
                            ['value' => 'monday', 'label' => 'Monday'],
                            ['value' => 'tuesday', 'label' => 'Tuesday'],
                            ['value' => 'wednesday', 'label' => 'Wednesday'],
                            ['value' => 'thursday', 'label' => 'Thursday'],
                            ['value' => 'friday', 'label' => 'Friday'],
                            ['value' => 'saturday', 'label' => 'Saturday'],
                            ['value' => 'sunday', 'label' => 'Sunday']
                        ];
                        
                        foreach ($days as $day):
                            $day_value = $day['value'];
                            $is_selected = isset($schedule_data[$day_value]);
                            $start_time = $is_selected && isset($schedule_data[$day_value]['start']) ? $schedule_data[$day_value]['start'] : '';
                            $end_time = $is_selected && isset($schedule_data[$day_value]['end']) ? $schedule_data[$day_value]['end'] : '';
                        ?>
                            <div class="schedule-day-row">
                                <div class="schedule-day-checkbox">
                                    <input type="checkbox" 
                                           id="schedule_<?php echo $day_value; ?>" 
                                           class="schedule-day-check" 
                                           data-day="<?php echo $day_value; ?>"
                                           <?php echo $is_selected ? 'checked' : ''; ?>>
                                    <label for="schedule_<?php echo $day_value; ?>" class="schedule-day-label">
                                        <?php echo $day['label']; ?>
                                    </label>
                                </div>
                                <div class="schedule-time-inputs" id="time_<?php echo $day_value; ?>" style="<?php echo $is_selected ? '' : 'display: none;'; ?>">
                                    <input type="time" 
                                           id="start_<?php echo $day_value; ?>" 
                                           class="form-input schedule-time-input" 
                                           placeholder="Start Time"
                                           value="<?php echo htmlspecialchars($start_time); ?>"
                                           data-day="<?php echo $day_value; ?>">
                                    <span class="schedule-time-separator">to</span>
                                    <input type="time" 
                                           id="end_<?php echo $day_value; ?>" 
                                           class="form-input schedule-time-input" 
                                           placeholder="End Time"
                                           value="<?php echo htmlspecialchars($end_time); ?>"
                                           data-day="<?php echo $day_value; ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Hidden input to store schedule as JSON -->
                    <input type="hidden" id="availability_schedule" name="availability_schedule" value="<?php echo htmlspecialchars($availability_schedule ?? ''); ?>">
                </div>

                <hr class="section-divider">
                <h2 class="section-title">Policies</h2>

                <div class="form-group">
                    <label for="booking_policy" class="form-label">Booking Policy</label>
                    <textarea id="booking_policy" name="booking_policy" class="form-input form-textarea" 
                              placeholder="Describe your booking policies, terms, and conditions"><?php echo htmlspecialchars($booking_policy ?? ''); ?></textarea>
                    <p class="form-help">Explain how bookings work, minimum duration, advance notice required, etc.</p>
                </div>

                <div class="form-group">
                    <label for="cancellation_policy" class="form-label">Cancellation Policy</label>
                    <textarea id="cancellation_policy" name="cancellation_policy" class="form-input form-textarea" 
                              placeholder="Describe your cancellation and refund policies"><?php echo htmlspecialchars($cancellation_policy ?? ''); ?></textarea>
                    <p class="form-help">Explain cancellation terms, refund policies, notice periods, etc.</p>
                </div>

                <hr class="section-divider">
                <h2 class="section-title">Place Type & Equipment</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="place_type" class="form-label required">Place Type</label>
                        <select id="place_type" name="place_type" class="form-select" required>
                            <option value="">Select type</option>
                            <option value="conference_room" <?php echo (isset($place_type) && $place_type === 'conference_room') ? 'selected' : ''; ?>>Conference Room</option>
                            <option value="training_room" <?php echo (isset($place_type) && $place_type === 'training_room') ? 'selected' : ''; ?>>Training Room</option>
                            <option value="event_hall" <?php echo (isset($place_type) && $place_type === 'event_hall') ? 'selected' : ''; ?>>Event Hall</option>
                            <option value="workshop_space" <?php echo (isset($place_type) && $place_type === 'workshop_space') ? 'selected' : ''; ?>>Workshop Space</option>
                            <option value="laboratory" <?php echo (isset($place_type) && $place_type === 'laboratory') ? 'selected' : ''; ?>>Laboratory</option>
                            <option value="office_space" <?php echo (isset($place_type) && $place_type === 'office_space') ? 'selected' : ''; ?>>Office Space</option>
                            <option value="other" <?php echo (isset($place_type) && $place_type === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <p class="form-help">Select a place type to see available equipment packages</p>
                    </div>

                    <div class="form-group">
                        <label for="space_type" class="form-label">Space Type</label>
                        <input type="text" id="space_type" name="space_type" class="form-input" 
                               value="<?php echo htmlspecialchars($space_type ?? ''); ?>" 
                               placeholder="e.g., Open Space, Partitioned, Private">
                    </div>
                </div>

                <!-- Package Equipment Display (shown immediately after place type selection) -->
                <div id="package-equipment-display" class="form-group" style="display: none;">
                    <label class="form-label">Available Equipment Package</label>
                    <div class="package-equipment-container">
                        <h4 class="package-equipment-title">
                            <i class="fas fa-box"></i>
                            <span id="package-equipment-title">Package Equipment</span>
                        </h4>
                        <p id="package-equipment-description" class="package-equipment-description"></p>
                        <div id="package-equipment-list" class="package-equipment-list">
                            <!-- Package equipment items will be displayed here -->
                        </div>
                        <p class="form-help" style="margin-top: 1rem; font-style: italic;">
                            <i class="fas fa-info-circle"></i> Edit the quantity and price for each item above. Changes will automatically sync with the equipment selection below.
                        </p>
                    </div>
                </div>

                <hr class="section-divider">
                <h3 class="section-title" style="font-size: 1.1rem; margin-top: 0;">Equipment Selection</h3>
                
                <div class="form-group">
                    <label class="form-label">Select Equipment</label>
                    <p class="form-help">
                        <strong>Auto-selection:</strong> Equipment from the selected place type package has been pre-selected. 
                        You can add or remove equipment from any category as needed. Custom prices and quantities can be set for each item.
                    </p>
                    
                    <!-- Equipment Selection by Category -->
                    <?php if (!empty($categories)): ?>
                        <div class="equipment-categories" id="equipment-categories">
                            <?php foreach ($categories as $category): ?>
                                <div class="equipment-category-section">
                                    <h4 class="equipment-category-title">
                                        <i class="<?php echo htmlspecialchars($category['icon_class'] ?? 'fas fa-cog'); ?>"></i>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </h4>
                                    <div class="equipment-items-grid">
                                        <?php 
                                        $category_items = $items_by_category[$category['category_name']] ?? [];
                                        foreach ($category_items as $item): 
                                        ?>
                                            <div class="equipment-item-checkbox">
                                                <label>
                                                    <input type="checkbox" 
                                                           class="equipment-item-check" 
                                                           data-item-id="<?php echo $item['item_id']; ?>"
                                                           data-item-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                                                           data-standard-price="<?php echo $item['standard_price']; ?>">
                                                    <span class="equipment-item-label">
                                                        <?php echo htmlspecialchars($item['item_name']); ?>
                                                        <?php if ($item['standard_price'] > 0): ?>
                                                            <span class="equipment-standard-price">($<?php echo number_format($item['standard_price'], 2); ?>/<?php echo $item['unit_type']; ?>)</span>
                                                        <?php endif; ?>
                                                    </span>
                                                </label>
                                                <div class="equipment-custom-inputs" style="display: none;">
                                                    <input type="number" 
                                                           class="equipment-custom-price form-input" 
                                                           placeholder="Custom price (optional)"
                                                           step="0.01" 
                                                           min="0"
                                                           data-item-id="<?php echo $item['item_id']; ?>"
                                                           value="<?php echo $item['standard_price']; ?>">
                                                    <input type="number" 
                                                           class="equipment-quantity form-input" 
                                                           placeholder="Quantity"
                                                           min="1"
                                                           value="1"
                                                           data-item-id="<?php echo $item['item_id']; ?>">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="equipment-empty">
                            <p>No equipment categories found. Please run the populate_equipment_data.sql file to add default equipment.</p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Selected Equipment Display -->
                    <div id="selected-equipment-container" class="selected-equipment-container" style="display: none;">
                        <h4 class="form-label">Selected Equipment</h4>
                        <div id="selected-equipment-list" class="selected-equipment-list">
                            <!-- Selected items will appear here -->
                        </div>
                    </div>
                </div>

                <hr class="section-divider">
                <h2 class="section-title">Status</h2>

                <div class="form-group">
                    <label for="status" class="form-label required">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="active" <?php echo (isset($status) && $status === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="pending" <?php echo (isset($status) && $status === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="inactive" <?php echo (isset($status) && $status === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    <p class="form-help">Active places will be visible to other users. Pending places need approval.</p>
                </div>

                <div class="form-actions">
                    <a href="manage_places.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Post Place
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Schedule management functions
        function updateSchedule() {
            const scheduleInput = document.getElementById('availability_schedule');
            const schedule = {};
            
            const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            
            days.forEach(day => {
                const checkbox = document.getElementById('schedule_' + day);
                if (checkbox && checkbox.checked) {
                    const startTime = document.getElementById('start_' + day).value;
                    const endTime = document.getElementById('end_' + day).value;
                    
                    if (startTime || endTime) {
                        schedule[day] = {
                            start: startTime || '',
                            end: endTime || ''
                        };
                    }
                }
            });
            
            scheduleInput.value = JSON.stringify(schedule);
        }
        
        function toggleDaySchedule(day) {
            const checkbox = document.getElementById('schedule_' + day);
            const timeInputs = document.getElementById('time_' + day);
            
            if (checkbox.checked) {
                timeInputs.style.display = 'flex';
            } else {
                timeInputs.style.display = 'none';
                // Clear time inputs when unchecked
                document.getElementById('start_' + day).value = '';
                document.getElementById('end_' + day).value = '';
            }
            
            updateSchedule();
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize schedule event listeners
            const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            
            days.forEach(day => {
                // Day checkbox change
                const checkbox = document.getElementById('schedule_' + day);
                if (checkbox) {
                    checkbox.addEventListener('change', () => toggleDaySchedule(day));
                }
                
                // Time input changes
                const startInput = document.getElementById('start_' + day);
                const endInput = document.getElementById('end_' + day);
                
                if (startInput) {
                    startInput.addEventListener('change', updateSchedule);
                    startInput.addEventListener('input', updateSchedule);
                }
                
                if (endInput) {
                    endInput.addEventListener('change', updateSchedule);
                    endInput.addEventListener('input', updateSchedule);
                }
            });
            
            // Initial schedule update
            updateSchedule();
            
            // Rate calculation functions - automatically calculate daily and weekly based on hourly rate
            function calculateRates(baseHourlyId, dailyId, weeklyId, monthlyId) {
                const hourlyInput = document.getElementById(baseHourlyId);
                const dailyInput = document.getElementById(dailyId);
                const weeklyInput = document.getElementById(weeklyId);
                const monthlyInput = monthlyId ? document.getElementById(monthlyId) : null;
                
                if (hourlyInput && dailyInput && weeklyInput) {
                    const calculate = function() {
                        const hourlyRate = parseFloat(hourlyInput.value) || 0;
                        if (hourlyRate > 0) {
                            // Calculate daily rate: hourly * 8 hours
                            const dailyRate = hourlyRate * 8;
                            // Calculate weekly rate: hourly * 40 hours (5 days * 8 hours)
                            const weeklyRate = hourlyRate * 40;
                            // Calculate monthly rate: hourly * 160 hours (20 working days * 8 hours)
                            const monthlyRate = hourlyRate * 160;
                            
                            // Automatically update daily and weekly rates
                            dailyInput.value = dailyRate.toFixed(2);
                            weeklyInput.value = weeklyRate.toFixed(2);
                            
                            // Update monthly rate if field exists and is empty
                            if (monthlyInput && (!monthlyInput.value || parseFloat(monthlyInput.value) === 0)) {
                                monthlyInput.value = monthlyRate.toFixed(2);
                            }
                        } else if (hourlyRate === 0) {
                            // Clear rates if hourly is set to 0
                            dailyInput.value = '';
                            weeklyInput.value = '';
                            if (monthlyInput) {
                                monthlyInput.value = '';
                            }
                        }
                    };
                    
                    hourlyInput.addEventListener('input', calculate);
                    hourlyInput.addEventListener('blur', calculate);
                    hourlyInput.addEventListener('change', calculate);
                }
            }
            
            // Set up rate calculations for standard rates (includes monthly)
            calculateRates('hourly_rate', 'daily_rate', 'weekly_rate', 'monthly_rate');
            
            // Set up rate calculations for weekend rates (includes monthly)
            calculateRates('weekend_hourly_rate', 'weekend_daily_rate', 'weekend_weekly_rate', 'weekend_monthly_rate');
            
            // Set up rate calculations for weekday rates (includes monthly)
            calculateRates('weekday_hourly_rate', 'weekday_daily_rate', 'weekday_weekly_rate', 'weekday_monthly_rate');
            
            // Equipment Management
            const placeTypeToPackage = <?php echo json_encode($place_type_to_package); ?>;
            const packageItemsMap = <?php echo json_encode($package_items_map); ?>;
            const equipmentItemsData = <?php echo json_encode($equipment_items); ?>;
            const equipmentPackages = <?php echo json_encode($equipment_packages); ?>;
            
            let selectedEquipment = new Map(); // Map of item_id -> {item_id, name, price, quantity}
            const selectedEquipmentList = document.getElementById('selected-equipment-list');
            const selectedEquipmentContainer = document.getElementById('selected-equipment-container');
            
            // Create item lookup map
            const itemDataMap = {};
            equipmentItemsData.forEach(item => {
                itemDataMap[item.item_id] = item;
            });
            
            // Display package equipment when place type is selected
            function displayPackageEquipment(placeType) {
                const packageDisplay = document.getElementById('package-equipment-display');
                const packageTitle = document.getElementById('package-equipment-title');
                const packageDescription = document.getElementById('package-equipment-description');
                const packageList = document.getElementById('package-equipment-list');
                
                const packageName = placeTypeToPackage[placeType];
                if (!packageName || !packageItemsMap[packageName]) {
                    if (packageDisplay) packageDisplay.style.display = 'none';
                    return;
                }
                
                // Get package info
                const packageInfo = equipmentPackages.find(pkg => pkg.package_name === packageName);
                const packageItems = packageItemsMap[packageName];
                
                // Show the display section
                if (packageDisplay) {
                    packageDisplay.style.display = 'block';
                    
                    // Update title with place type name
                    const placeTypeNames = {
                        'laboratory': 'Laboratory',
                        'training_room': 'Training Room',
                        'conference_room': 'Conference Room',
                        'workshop_space': 'Workshop Space',
                        'event_hall': 'Event Hall',
                        'office_space': 'Office Space'
                    };
                    
                    if (packageTitle) {
                        packageTitle.textContent = `${placeTypeNames[placeType] || placeType} Package Equipment`;
                    }
                    
                    if (packageDescription) {
                        packageDescription.textContent = packageInfo ? packageInfo.package_description : 
                            `Default equipment included with ${placeTypeNames[placeType] || placeType}. These items will be automatically selected.`;
                    }
                    
                    // Display package items with editable price and quantity
                    if (packageList) {
                        packageList.innerHTML = packageItems.map((pkgItem, index) => {
                            const itemId = pkgItem.item_id;
                            const quantity = pkgItem.quantity || 1;
                            const standardPrice = parseFloat(pkgItem.standard_price) || 0;
                            const itemTotal = standardPrice * quantity;
                            return `
                                <div class="package-equipment-item" data-item-id="${itemId}">
                                    <div class="package-equipment-item-name">${pkgItem.item_name}</div>
                                    <div class="package-equipment-item-controls">
                                        <div class="package-equipment-control-group">
                                            <label class="package-control-label">Quantity:</label>
                                            <input type="number" 
                                                   class="package-equipment-qty-input form-input" 
                                                   data-item-id="${itemId}"
                                                   value="${quantity}"
                                                   min="1"
                                                   style="width: 80px; padding: 0.5rem;">
                                        </div>
                                        <div class="package-equipment-control-group">
                                            <label class="package-control-label">Price ($):</label>
                                            <input type="number" 
                                                   class="package-equipment-price-input form-input" 
                                                   data-item-id="${itemId}"
                                                   value="${standardPrice.toFixed(2)}"
                                                   step="0.01"
                                                   min="0"
                                                   style="width: 100px; padding: 0.5rem;">
                                        </div>
                                    </div>
                                    <div class="package-equipment-item-total">
                                        <span style="color: var(--muted); font-size: 0.85rem;">Total: </span>
                                        <strong class="package-item-total-value" data-item-id="${itemId}" style="color: var(--brand);">$${itemTotal.toFixed(2)}</strong>
                                    </div>
                                </div>
                            `;
                        }).join('');
                        
                        // Add event listeners for price and quantity changes in package display
                        packageList.querySelectorAll('.package-equipment-qty-input, .package-equipment-price-input').forEach(input => {
                            input.addEventListener('input', function() {
                                const itemId = parseInt(this.dataset.itemId);
                                const qtyInput = packageList.querySelector(`.package-equipment-qty-input[data-item-id="${itemId}"]`);
                                const priceInput = packageList.querySelector(`.package-equipment-price-input[data-item-id="${itemId}"]`);
                                const totalDisplay = packageList.querySelector(`.package-item-total-value[data-item-id="${itemId}"]`);
                                
                                const qty = parseInt(qtyInput.value) || 1;
                                const price = parseFloat(priceInput.value) || 0;
                                const total = qty * price;
                                
                                if (totalDisplay) {
                                    totalDisplay.textContent = `$${total.toFixed(2)}`;
                                }
                                
                                // If equipment is already selected, update it
                                if (selectedEquipment.has(itemId)) {
                                    const equipmentItem = selectedEquipment.get(itemId);
                                    equipmentItem.quantity = qty;
                                    equipmentItem.price = price;
                                    selectedEquipment.set(itemId, equipmentItem);
                                    
                                    // Update the checkbox inputs below
                                    const checkbox = document.querySelector(`.equipment-item-check[data-item-id="${itemId}"]`);
                                    if (checkbox && checkbox.checked) {
                                        const customInputs = checkbox.closest('.equipment-item-checkbox').querySelector('.equipment-custom-inputs');
                                        if (customInputs) {
                                            const equipQtyInput = customInputs.querySelector('.equipment-quantity');
                                            const equipPriceInput = customInputs.querySelector('.equipment-custom-price');
                                            if (equipQtyInput) equipQtyInput.value = qty;
                                            if (equipPriceInput) equipPriceInput.value = price;
                                        }
                                    }
                                    
                                    updateSelectedEquipmentDisplay();
                                } else {
                                    // If not selected yet, auto-select it with the edited values
                                    const checkbox = document.querySelector(`.equipment-item-check[data-item-id="${itemId}"]`);
                                    if (checkbox && !checkbox.checked) {
                                        checkbox.checked = true;
                                        
                                        // Show custom inputs
                                        const customInputs = checkbox.closest('.equipment-item-checkbox').querySelector('.equipment-custom-inputs');
                                        if (customInputs) {
                                            customInputs.style.display = 'block';
                                            
                                            const equipQtyInput = customInputs.querySelector('.equipment-quantity');
                                            const equipPriceInput = customInputs.querySelector('.equipment-custom-price');
                                            if (equipQtyInput) equipQtyInput.value = qty;
                                            if (equipPriceInput) equipPriceInput.value = price;
                                        }
                                        
                                        // Get item name from data attributes
                                        const itemName = checkbox.dataset.itemName || '';
                                        addEquipment(itemId, itemName, price, qty);
                                    }
                                }
                            });
                        });
                    }
                }
            }
            
            // Auto-select equipment based on place type
            function autoSelectEquipmentByPlaceType(placeType) {
                // First display the package equipment
                displayPackageEquipment(placeType);
                
                const packageName = placeTypeToPackage[placeType];
                if (!packageName || !packageItemsMap[packageName]) {
                    return;
                }
                
                const packageItems = packageItemsMap[packageName];
                
                // Small delay to ensure package display is rendered first
                setTimeout(() => {
                    packageItems.forEach(pkgItem => {
                        const itemId = pkgItem.item_id;
                        
                        // Get values from package display inputs if they exist, otherwise use defaults
                        const packageQtyInput = document.querySelector(`.package-equipment-qty-input[data-item-id="${itemId}"]`);
                        const packagePriceInput = document.querySelector(`.package-equipment-price-input[data-item-id="${itemId}"]`);
                        
                        const quantity = packageQtyInput ? parseInt(packageQtyInput.value) || pkgItem.quantity || 1 : (pkgItem.quantity || 1);
                        const standardPrice = packagePriceInput ? parseFloat(packagePriceInput.value) || parseFloat(pkgItem.standard_price) || 0 : (parseFloat(pkgItem.standard_price) || 0);
                        
                        // Check if checkbox exists and not already selected
                        const checkbox = document.querySelector(`.equipment-item-check[data-item-id="${itemId}"]`);
                        if (checkbox && !checkbox.checked) {
                            checkbox.checked = true;
                            
                            // Show custom inputs
                            const customInputs = checkbox.closest('.equipment-item-checkbox').querySelector('.equipment-custom-inputs');
                            if (customInputs) {
                                customInputs.style.display = 'block';
                                
                                // Set price and quantity from package display or defaults
                                const priceInput = customInputs.querySelector('.equipment-custom-price');
                                const quantityInput = customInputs.querySelector('.equipment-quantity');
                                if (priceInput) priceInput.value = standardPrice;
                                if (quantityInput) quantityInput.value = quantity;
                            }
                            
                            // Add to selected equipment
                            addEquipment(itemId, pkgItem.item_name, standardPrice, quantity);
                        } else if (checkbox && checkbox.checked && selectedEquipment.has(itemId)) {
                            // Update existing selection with new values
                            const equipmentItem = selectedEquipment.get(itemId);
                            equipmentItem.quantity = quantity;
                            equipmentItem.price = standardPrice;
                            selectedEquipment.set(itemId, equipmentItem);
                            
                            // Update the checkbox inputs
                            const customInputs = checkbox.closest('.equipment-item-checkbox').querySelector('.equipment-custom-inputs');
                            if (customInputs) {
                                const priceInput = customInputs.querySelector('.equipment-custom-price');
                                const quantityInput = customInputs.querySelector('.equipment-quantity');
                                if (priceInput) priceInput.value = standardPrice;
                                if (quantityInput) quantityInput.value = quantity;
                            }
                            
                            updateSelectedEquipmentDisplay();
                        }
                    });
                }, 100);
            }
            
            // Add equipment
            function addEquipment(itemId, itemName, price, quantity = 1) {
                const priceInput = document.querySelector(`.equipment-custom-price[data-item-id="${itemId}"]`);
                const quantityInput = document.querySelector(`.equipment-quantity[data-item-id="${itemId}"]`);
                const customPrice = priceInput ? parseFloat(priceInput.value) || price : price;
                const equipQuantity = quantityInput ? parseInt(quantityInput.value) || quantity : quantity;
                
                selectedEquipment.set(itemId, {
                    item_id: itemId,
                    name: itemName,
                    price: customPrice,
                    quantity: equipQuantity
                });
                updateSelectedEquipmentDisplay();
            }
            
            // Remove equipment
            function removeEquipment(itemId) {
                selectedEquipment.delete(itemId);
                const checkbox = document.querySelector(`.equipment-item-check[data-item-id="${itemId}"]`);
                const customInputs = checkbox ? checkbox.closest('.equipment-item-checkbox').querySelector('.equipment-custom-inputs') : null;
                
                if (checkbox) checkbox.checked = false;
                if (customInputs) customInputs.style.display = 'none';
                
                updateSelectedEquipmentDisplay();
            }
            
            // Update selected equipment display
            function updateSelectedEquipmentDisplay() {
                if (!selectedEquipmentList) return;
                
                if (selectedEquipment.size === 0) {
                    selectedEquipmentList.innerHTML = '<p style="color: var(--muted); font-style: italic; text-align: center; padding: 1rem;">No equipment selected yet.</p>';
                    if (selectedEquipmentContainer) selectedEquipmentContainer.style.display = 'none';
                    return;
                }
                
                if (selectedEquipmentContainer) selectedEquipmentContainer.style.display = 'block';
                
                let totalPrice = 0;
                selectedEquipmentList.innerHTML = Array.from(selectedEquipment.values()).map(item => {
                    const itemTotal = (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 1);
                    totalPrice += itemTotal;
                    return `
                        <div class="selected-equipment-item" data-item-id="${item.item_id}">
                            <div class="selected-equipment-item-info">
                                <div class="selected-equipment-item-name">${item.name} (Qty: ${item.quantity})</div>
                                <div class="selected-equipment-item-price">$${parseFloat(item.price).toFixed(2)} each = $${itemTotal.toFixed(2)} total</div>
                            </div>
                            <button type="button" class="selected-equipment-remove" data-item-id="${item.item_id}">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    `;
                }).join('') + `
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 1.1rem; font-weight: 700; color: var(--ink);">Total Equipment Price:</span>
                        <span style="font-size: 1.5rem; font-weight: 800; color: var(--brand);">$${totalPrice.toFixed(2)}</span>
                    </div>
                `;
                
                // Add remove button listeners
                selectedEquipmentList.querySelectorAll('.selected-equipment-remove').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const itemId = parseInt(this.dataset.itemId);
                        removeEquipment(itemId);
                    });
                });
            }
            
            // Initialize equipment checkboxes
            document.querySelectorAll('.equipment-item-check').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const itemId = parseInt(this.dataset.itemId);
                    const itemName = this.dataset.itemName;
                    const standardPrice = parseFloat(this.dataset.standardPrice) || 0;
                    const customInputs = this.closest('.equipment-item-checkbox').querySelector('.equipment-custom-inputs');
                    
                    if (this.checked) {
                        if (customInputs) customInputs.style.display = 'block';
                        addEquipment(itemId, itemName, standardPrice, 1);
                    } else {
                        if (customInputs) customInputs.style.display = 'none';
                        removeEquipment(itemId);
                    }
                });
            });
            
            // Handle price and quantity changes
            document.querySelectorAll('.equipment-custom-price, .equipment-quantity').forEach(input => {
                input.addEventListener('input', function() {
                    const itemId = parseInt(this.dataset.itemId);
                    if (selectedEquipment.has(itemId)) {
                        const item = selectedEquipment.get(itemId);
                        const priceInput = document.querySelector(`.equipment-custom-price[data-item-id="${itemId}"]`);
                        const quantityInput = document.querySelector(`.equipment-quantity[data-item-id="${itemId}"]`);
                        
                        item.price = priceInput ? parseFloat(priceInput.value) || 0 : item.price;
                        item.quantity = quantityInput ? parseInt(quantityInput.value) || 1 : item.quantity;
                        selectedEquipment.set(itemId, item);
                        updateSelectedEquipmentDisplay();
                    }
                });
            });
            
            // Listen for place type changes
            const placeTypeSelect = document.getElementById('place_type');
            if (placeTypeSelect) {
                placeTypeSelect.addEventListener('change', function() {
                    const selectedPlaceType = this.value;
                    if (selectedPlaceType) {
                        // Clear previous selections if switching place types
                        if (selectedEquipment.size > 0) {
                            const confirmClear = confirm('Changing place type will clear current equipment selections. Continue?');
                            if (confirmClear) {
                                // Clear all selections
                                selectedEquipment.forEach((item, itemId) => {
                                    removeEquipment(itemId);
                                });
                            } else {
                                // Revert selection
                                this.value = '';
                                return;
                            }
                        }
                        autoSelectEquipmentByPlaceType(selectedPlaceType);
                    } else {
                        // Hide package display if no place type selected
                        const packageDisplay = document.getElementById('package-equipment-display');
                        if (packageDisplay) packageDisplay.style.display = 'none';
                    }
                });
                
                // Auto-select if place type is already selected on page load
                if (placeTypeSelect.value) {
                    setTimeout(() => {
                        autoSelectEquipmentByPlaceType(placeTypeSelect.value);
                    }, 100);
                }
            }
            
            // Update form submission to include equipment
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Create hidden inputs for selected equipment
                    const equipmentInputs = document.querySelectorAll('input[name^="selected_equipment"]');
                    equipmentInputs.forEach(input => input.remove());
                    
                    selectedEquipment.forEach((item, itemId) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `selected_equipment[${itemId}][item_id]`;
                        input.value = item.item_id;
                        form.appendChild(input);
                        
                        const priceInput = document.createElement('input');
                        priceInput.type = 'hidden';
                        priceInput.name = `selected_equipment[${itemId}][custom_price]`;
                        priceInput.value = item.price;
                        form.appendChild(priceInput);
                        
                        const qtyInput = document.createElement('input');
                        qtyInput.type = 'hidden';
                        qtyInput.name = `selected_equipment[${itemId}][quantity]`;
                        qtyInput.value = item.quantity;
                        form.appendChild(qtyInput);
                    });
                });
            }
        });
    </script>
</body>
</html>
