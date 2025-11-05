<?php
session_start();
include "connection.php";

// Check if user is logged in as company
if (!isset($_SESSION['company_id'])) {
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$place_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$place_id) {
    header("Location: browse_places.php");
    exit();
}

// Create booking tables if they don't exist and add missing columns
$create_booking_table = "CREATE TABLE IF NOT EXISTS `place_bookings` (
    `booking_id` INT AUTO_INCREMENT PRIMARY KEY,
    `place_id` INT NOT NULL,
    `company_id` INT NOT NULL,
    `booking_type` ENUM('hourly', 'daily', 'weekly', 'monthly') NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `duration_days` INT NOT NULL DEFAULT 1,
    `duration_hours` DECIMAL(10,2) DEFAULT 0.00,
    `booking_days` JSON NULL,
    `total_days` INT NOT NULL DEFAULT 1,
    `base_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `equipment_cost` DECIMAL(10,2) DEFAULT 0.00,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(10,2) DEFAULT 0.00,
    `total_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(10) DEFAULT 'USD',
    `equipment_details` JSON NULL,
    `contact_person` VARCHAR(200) NOT NULL,
    `contact_email` VARCHAR(200) NOT NULL,
    `contact_phone` VARCHAR(50),
    `special_requirements` TEXT,
    `booking_notes` TEXT,
    `additional_requests` TEXT,
    `booking_status` ENUM('pending', 'confirmed', 'approved', 'rejected', 'cancelled', 'completed') DEFAULT 'pending',
    `payment_status` ENUM('pending', 'partial', 'paid', 'refunded') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `confirmed_at` TIMESTAMP NULL,
    `cancelled_at` TIMESTAMP NULL,
    INDEX `idx_place_id` (`place_id`),
    INDEX `idx_company_id` (`company_id`),
    INDEX `idx_booking_status` (`booking_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$conn->query($create_booking_table);

// Check if table exists and add missing columns
$check_table = "SHOW TABLES LIKE 'place_bookings'";
$table_exists = $conn->query($check_table);

if ($table_exists && $table_exists->num_rows > 0) {
    // Check and add missing columns
    $columns_to_add = [
        'duration_days' => "ALTER TABLE `place_bookings` ADD COLUMN `duration_days` INT NOT NULL DEFAULT 1 AFTER `end_time`",
        'duration_hours' => "ALTER TABLE `place_bookings` ADD COLUMN `duration_hours` DECIMAL(10,2) DEFAULT 0.00 AFTER `duration_days`",
        'booking_days' => "ALTER TABLE `place_bookings` ADD COLUMN `booking_days` JSON NULL AFTER `duration_hours`",
        'total_days' => "ALTER TABLE `place_bookings` ADD COLUMN `total_days` INT NOT NULL DEFAULT 1 AFTER `booking_days`",
        'base_rate' => "ALTER TABLE `place_bookings` ADD COLUMN `base_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `total_days`",
        'equipment_cost' => "ALTER TABLE `place_bookings` ADD COLUMN `equipment_cost` DECIMAL(10,2) DEFAULT 0.00 AFTER `base_rate`",
        'subtotal' => "ALTER TABLE `place_bookings` ADD COLUMN `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `equipment_cost`",
        'discount_amount' => "ALTER TABLE `place_bookings` ADD COLUMN `discount_amount` DECIMAL(10,2) DEFAULT 0.00 AFTER `subtotal`",
        'tax_amount' => "ALTER TABLE `place_bookings` ADD COLUMN `tax_amount` DECIMAL(10,2) DEFAULT 0.00 AFTER `discount_amount`",
        'currency' => "ALTER TABLE `place_bookings` ADD COLUMN `currency` VARCHAR(10) DEFAULT 'USD' AFTER `total_cost`",
        'equipment_details' => "ALTER TABLE `place_bookings` ADD COLUMN `equipment_details` JSON NULL AFTER `currency`",
        'additional_requests' => "ALTER TABLE `place_bookings` ADD COLUMN `additional_requests` TEXT AFTER `booking_notes`",
        'payment_status' => "ALTER TABLE `place_bookings` ADD COLUMN `payment_status` ENUM('pending', 'partial', 'paid', 'refunded') DEFAULT 'pending' AFTER `booking_status`",
        'confirmed_at' => "ALTER TABLE `place_bookings` ADD COLUMN `confirmed_at` TIMESTAMP NULL AFTER `updated_at`",
        'cancelled_at' => "ALTER TABLE `place_bookings` ADD COLUMN `cancelled_at` TIMESTAMP NULL AFTER `confirmed_at`"
    ];
    
    foreach ($columns_to_add as $column => $alter_sql) {
        $check_column = "SHOW COLUMNS FROM `place_bookings` LIKE '$column'";
        $column_result = $conn->query($check_column);
        if (!$column_result || $column_result->num_rows == 0) {
            $conn->query($alter_sql);
        }
    }
    
    // Update booking_status enum if needed
    $check_status = "SHOW COLUMNS FROM `place_bookings` WHERE Field = 'booking_status' AND Type LIKE '%completed%'";
    $status_result = $conn->query($check_status);
    if (!$status_result || $status_result->num_rows == 0) {
        $conn->query("ALTER TABLE `place_bookings` MODIFY COLUMN `booking_status` ENUM('pending', 'confirmed', 'approved', 'rejected', 'cancelled', 'completed') DEFAULT 'pending'");
    }
}

$create_booking_equipment = "CREATE TABLE IF NOT EXISTS `booking_equipment` (
    `booking_equipment_id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL,
    `item_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_booking_id` (`booking_id`),
    INDEX `idx_item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$conn->query($create_booking_equipment);

// Get place details
$place_sql = "SELECT 
    p.*,
    c.company_name,
    c.logo_path
    FROM places p
    JOIN companies c ON p.company_id = c.company_id
    WHERE p.place_id = ? AND p.status = 'active'";

$stmt = $conn->prepare($place_sql);
$stmt->bind_param("i", $place_id);
$stmt->execute();
$place = $stmt->get_result()->fetch_assoc();

if (!$place) {
    header("Location: browse_places.php");
    exit();
}

// Check if this is the user's own place
if ($place['company_id'] == $company_id) {
    header("Location: place_details.php?id=" . $place_id);
    exit();
}

// Get existing bookings for this place
$booked_dates_query = "SELECT start_date, end_date, booking_status 
                       FROM place_bookings 
                       WHERE place_id = ? 
                       AND company_id != ? 
                       AND booking_status IN ('pending', 'confirmed', 'approved')";
$booked_stmt = $conn->prepare($booked_dates_query);
$booked_stmt->bind_param("ii", $place_id, $company_id);
$booked_stmt->execute();
$booked_results = $booked_stmt->get_result();

$booked_dates = [];
while ($booking = $booked_results->fetch_assoc()) {
    $start = new DateTime($booking['start_date']);
    $end = new DateTime($booking['end_date']);
    $end->modify('+1 day');
    
    $current = clone $start;
    while ($current < $end) {
        $booked_dates[] = $current->format('Y-m-d');
        $current->modify('+1 day');
    }
}
$booked_dates = array_unique($booked_dates);
sort($booked_dates);

// Get place equipment
$equipment_sql = "SELECT pe.*, ei.item_name, ei.description, ei.standard_price, ei.unit_type, ec.category_name
                  FROM place_equipment pe
                  JOIN equipment_items ei ON pe.item_id = ei.item_id
                  LEFT JOIN equipment_categories ec ON ei.category_id = ec.category_id
                  WHERE pe.place_id = ? AND pe.is_available = 1
                  ORDER BY ec.category_name, ei.item_name";
$equipment_stmt = $conn->prepare($equipment_sql);
$place_equipment = [];
if ($equipment_stmt) {
    $equipment_stmt->bind_param("i", $place_id);
    $equipment_stmt->execute();
    $place_equipment = $equipment_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$success_message = "";
$error_message = "";

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $booking_type = $conn->real_escape_string($_POST['booking_type']);
        $start_date = $conn->real_escape_string($_POST['start_date']);
        $end_date = $conn->real_escape_string($_POST['end_date']);
        $start_time = $conn->real_escape_string($_POST['start_time']);
        $end_time = $conn->real_escape_string($_POST['end_time']);
        $contact_person = $conn->real_escape_string($_POST['contact_person']);
        $contact_email = $conn->real_escape_string($_POST['contact_email']);
        $contact_phone = $conn->real_escape_string($_POST['contact_phone'] ?? '');
        $special_requirements = $conn->real_escape_string($_POST['special_requirements'] ?? '');
        $booking_notes = $conn->real_escape_string($_POST['booking_notes'] ?? '');
        $additional_requests = $conn->real_escape_string($_POST['additional_requests'] ?? '');
        
        // Get selected days of week
        $selected_days_of_week = isset($_POST['selected_days']) && is_array($_POST['selected_days']) 
            ? $_POST['selected_days'] 
            : [];
        
        if (empty($selected_days_of_week)) {
            $error_message = "Please select at least one day of the week to book.";
        } else {
        
        // Calculate duration
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $duration_days = $start->diff($end)->days + 1;
        
        // Calculate duration hours
        $start_datetime = new DateTime($start_date . ' ' . $start_time);
        $end_datetime = new DateTime($end_date . ' ' . $end_time);
        $duration_hours = ($end_datetime->getTimestamp() - $start_datetime->getTimestamp()) / 3600;
        
        // Generate booking days array filtered by selected days of week
        $booking_days = [];
        $weekday_count = 0;
        $weekend_count = 0;
        $current = clone $start;
        
        // Map day names to numbers (0=Sunday, 1=Monday, etc.)
        $day_map = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6
        ];
        
        // Convert selected day names to numbers
        $selected_day_numbers = [];
        foreach ($selected_days_of_week as $day_name) {
            if (isset($day_map[strtolower($day_name)])) {
                $selected_day_numbers[] = $day_map[strtolower($day_name)];
            }
        }
        
        while ($current <= $end) {
            $day_of_week = (int)$current->format('w'); // 0=Sunday, 6=Saturday
            if (in_array($day_of_week, $selected_day_numbers)) {
                $date_str = $current->format('Y-m-d');
                $booking_days[] = $date_str;
                
                // Count weekdays (Mon-Fri) vs weekends (Sat-Sun)
                if ($day_of_week >= 1 && $day_of_week <= 5) {
                    $weekday_count++;
                } else {
                    $weekend_count++;
                }
            }
            $current->modify('+1 day');
        }
        
        if (empty($booking_days)) {
            $error_message = "No valid days found in the selected date range based on your day selection. Please adjust your dates or selected days.";
        } else {
        
        // Check for conflicts with other companies' bookings
        $conflict_dates = array_intersect($booking_days, $booked_dates);
        if (!empty($conflict_dates)) {
            // Get details of conflicting bookings
            $conflict_dates_str = "'" . implode("','", $conflict_dates) . "'";
            $conflict_query = "SELECT DISTINCT 
                                DATE_FORMAT(start_date, '%M %d, %Y') as start_formatted,
                                DATE_FORMAT(end_date, '%M %d, %Y') as end_formatted,
                                company_id,
                                c.company_name
                              FROM place_bookings pb
                              JOIN companies c ON pb.company_id = c.company_id
                              WHERE pb.place_id = ? 
                              AND pb.company_id != ?
                              AND pb.booking_status IN ('pending', 'confirmed', 'approved')
                              AND (
                                (pb.start_date <= ? AND pb.end_date >= ?) OR
                                (pb.start_date <= ? AND pb.end_date >= ?)
                              )";
            $conflict_stmt = $conn->prepare($conflict_query);
            $conflict_stmt->bind_param("iiisss", $place_id, $company_id, $end_date, $start_date, $start_date, $end_date);
            $conflict_stmt->execute();
            $conflict_bookings = $conflict_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            $conflict_dates_formatted = array_map(function($d) {
                return date('M d, Y', strtotime($d));
            }, $conflict_dates);
            
            $error_message = "<strong>Booking Conflict Detected!</strong><br>";
            $error_message .= "The following dates are already booked by another company and cannot be booked:<br>";
            $error_message .= "<ul style='margin-top: 0.5rem; padding-left: 1.5rem;'>";
            foreach ($conflict_dates_formatted as $date) {
                $error_message .= "<li><strong>$date</strong> - Already booked by another company</li>";
            }
            $error_message .= "</ul>";
            
            if (!empty($conflict_bookings)) {
                $error_message .= "<br><strong>Existing Bookings:</strong><br>";
                $error_message .= "<ul style='margin-top: 0.5rem; padding-left: 1.5rem;'>";
                foreach ($conflict_bookings as $booking) {
                    $period = $booking['start_formatted'];
                    if ($booking['end_formatted'] != $booking['start_formatted']) {
                        $period .= " to " . $booking['end_formatted'];
                    }
                    $error_message .= "<li>" . htmlspecialchars($booking['company_name']) . " - " . $period . "</li>";
                }
                $error_message .= "</ul>";
            }
            
            $error_message .= "<br>Please select different dates that are available.";
        } else {
            // Calculate base rate based on weekday/weekend rates
            $base_rate = 0;
            $total_selected_days = count($booking_days);
            
            // Check if weekday/weekend rates are available
            $has_weekday_rates = ($place['weekday_daily_rate'] > 0 || $place['weekday_hourly_rate'] > 0);
            $has_weekend_rates = ($place['weekend_daily_rate'] > 0 || $place['weekend_hourly_rate'] > 0);
            
            if ($has_weekday_rates || $has_weekend_rates) {
                // Use weekday/weekend specific rates
                $weekday_rate = 0;
                $weekend_rate = 0;
                
        switch ($booking_type) {
            case 'hourly':
                        $hours_per_day = $duration_hours / max($total_selected_days, 1);
                        $weekday_rate = ($place['weekday_hourly_rate'] > 0 ? $place['weekday_hourly_rate'] : $place['hourly_rate']) * $hours_per_day * $weekday_count;
                        $weekend_rate = ($place['weekend_hourly_rate'] > 0 ? $place['weekend_hourly_rate'] : $place['hourly_rate']) * $hours_per_day * $weekend_count;
                break;
            case 'daily':
                        $weekday_rate = ($place['weekday_daily_rate'] > 0 ? $place['weekday_daily_rate'] : $place['daily_rate']) * $weekday_count;
                        $weekend_rate = ($place['weekend_daily_rate'] > 0 ? $place['weekend_daily_rate'] : $place['daily_rate']) * $weekend_count;
                break;
            case 'weekly':
                        $weeks = ceil($total_selected_days / 7);
                        $weekday_rate = ($place['weekday_weekly_rate'] > 0 ? $place['weekday_weekly_rate'] : $place['weekly_rate']) * $weeks;
                        $weekend_rate = ($place['weekend_weekly_rate'] > 0 ? $place['weekend_weekly_rate'] : $place['weekly_rate']) * $weeks;
                break;
            case 'monthly':
                        $months = ceil($total_selected_days / 30);
                        $weekday_rate = ($place['weekday_monthly_rate'] > 0 ? $place['weekday_monthly_rate'] : $place['monthly_rate']) * $months;
                        $weekend_rate = ($place['weekend_monthly_rate'] > 0 ? $place['weekend_monthly_rate'] : $place['monthly_rate']) * $months;
                break;
                }
                $base_rate = $weekday_rate + $weekend_rate;
            } else {
                // Use standard rates
                switch ($booking_type) {
                    case 'hourly':
                        $base_rate = $place['hourly_rate'] * $duration_hours;
                        break;
                    case 'daily':
                        $base_rate = $place['daily_rate'] * $total_selected_days;
                        break;
                    case 'weekly':
                        $weeks = ceil($total_selected_days / 7);
                        $base_rate = $place['weekly_rate'] * $weeks;
                        break;
                    case 'monthly':
                        $months = ceil($total_selected_days / 30);
                        $base_rate = $place['monthly_rate'] * $months;
                        break;
                }
            }
            
            // Process equipment
            $equipment_details = [];
            $equipment_cost = 0;
            if (isset($_POST['selected_equipment']) && is_array($_POST['selected_equipment'])) {
                foreach ($_POST['selected_equipment'] as $item) {
                    $item_id = (int)($item['item_id'] ?? 0);
                    $quantity = (int)($item['quantity'] ?? 1);
                    $price = (float)($item['price'] ?? 0);
                    
                    if ($item_id > 0 && $quantity > 0) {
                        $item_total = $price * $quantity * $duration_days;
                        $equipment_details[] = [
                            'item_id' => $item_id,
                            'quantity' => $quantity,
                            'unit_price' => $price,
                            'total_price' => $item_total
                        ];
                        $equipment_cost += $item_total;
                    }
                }
            }
            
            $subtotal = $base_rate + $equipment_cost;
            $discount_amount = 0;
            $tax_amount = $subtotal * 0.10; // 10% tax example
            $total_cost = $subtotal + $tax_amount - $discount_amount;
            
            // Update duration_days to reflect actual selected days
            $duration_days = $total_selected_days;
        
        // Insert booking
            $booking_stmt = $conn->prepare("INSERT INTO place_bookings 
                (place_id, company_id, booking_type, start_date, end_date, start_time, end_time, 
                duration_days, duration_hours, booking_days, total_days, base_rate, equipment_cost, 
                subtotal, discount_amount, tax_amount, total_cost, currency, equipment_details, 
                contact_person, contact_email, contact_phone, special_requirements, booking_notes, 
                additional_requests) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $booking_days_json = json_encode($booking_days);
            $equipment_details_json = json_encode($equipment_details);
            
            $currency = 'USD';
            // Parameters (25 total): 
            // 1. place_id(i), 2. company_id(i), 3. booking_type(s), 4. start_date(s), 5. end_date(s), 
            // 6. start_time(s), 7. end_time(s), 8. duration_days(i), 9. duration_hours(d), 
            // 10. booking_days_json(s), 11. total_days(i), 12. base_rate(d), 13. equipment_cost(d),
            // 14. subtotal(d), 15. discount_amount(d), 16. tax_amount(d), 17. total_cost(d), 
            // 18. currency(s), 19. equipment_details_json(s), 20. contact_person(s), 21. contact_email(s), 
            // 22. contact_phone(s), 23. special_requirements(s), 24. booking_notes(s), 25. additional_requests(s)
            // Type string breakdown (25 characters total):
            // ii(2) + ssssss(6) + i(1) + d(1) + s(1) + i(1) + dddddd(6) + s(1) + sssssss(7) = 25
            // Correct string: "iisssssidsiddddddssssssss"
            $booking_stmt->bind_param("iisssssidsiddddddssssssss", 
                $place_id, $company_id, $booking_type, $start_date, $end_date, $start_time, $end_time,
                $duration_days, $duration_hours, $booking_days_json, $duration_days, $base_rate, $equipment_cost,
                $subtotal, $discount_amount, $tax_amount, $total_cost, $currency, $equipment_details_json,
                $contact_person, $contact_email, $contact_phone, $special_requirements, $booking_notes,
                $additional_requests);
        
        if ($booking_stmt->execute()) {
                $booking_id = $conn->insert_id;
                
                // Insert booking equipment
                if (!empty($equipment_details)) {
                    $equip_stmt = $conn->prepare("INSERT INTO booking_equipment (booking_id, item_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
                    foreach ($equipment_details as $equip) {
                        $equip_stmt->bind_param("iiidd", $booking_id, $equip['item_id'], $equip['quantity'], $equip['unit_price'], $equip['total_price']);
                        $equip_stmt->execute();
                    }
                }
                
            $success_message = "Booking request submitted successfully! The place owner will review and confirm your booking.";
        } else {
            $error_message = "Error submitting booking: " . $conn->error;
            }
        }
        }
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Place - <?php echo htmlspecialchars($place['place_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

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

        .form-container {
            background: var(--panel);
            border-radius: var(--radius-xl);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--line);
            margin-top: 2rem;
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

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            font-weight: 500;
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

        .alert-error ul {
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .alert-error li {
            margin-bottom: 0.25rem;
        }

        .btn-day-selector {
            padding: 0.5rem 1rem;
            background: var(--bg-secondary);
            border: 2px solid var(--border-light);
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            color: var(--ink);
        }

        .btn-day-selector:hover {
            background: var(--brand);
            color: var(--text-white);
            border-color: var(--brand);
            transform: translateY(-2px);
        }

        .day-checkbox-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            background: var(--bg-secondary);
            border: 2px solid var(--border-light);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition);
        }

        .day-checkbox-label:hover {
            border-color: var(--brand);
            background: rgba(14, 165, 168, 0.05);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .day-checkbox-label input[type="checkbox"] {
            margin-bottom: 0.5rem;
            width: 20px;
            height: 20px;
            accent-color: var(--brand);
            cursor: pointer;
        }

        .day-checkbox-label i {
            font-size: 1.25rem;
            color: var(--muted);
            margin-bottom: 0.25rem;
            transition: var(--transition);
        }

        .day-checkbox-label span {
            font-weight: 600;
            color: var(--ink);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .day-checkbox-label input[type="checkbox"]:checked ~ i,
        .day-checkbox-label input[type="checkbox"]:checked ~ span {
            color: var(--brand);
        }

        .day-checkbox-label:has(input[type="checkbox"]:checked) {
            border-color: var(--brand);
            background: rgba(14, 165, 168, 0.1);
        }

        .day-selection-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .days-display {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .day-card {
            padding: 0.75rem;
            border-radius: var(--radius-md);
            text-align: center;
            border: 2px solid;
        }

        .day-card.available {
            border-color: var(--success);
            background: rgba(74, 222, 128, 0.1);
        }

        .day-card.booked {
            border-color: var(--error);
            background: rgba(248, 113, 113, 0.1);
        }

        .availability-status {
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
        }

        .availability-status.success {
            background: rgba(74, 222, 128, 0.1);
            border: 2px solid var(--success);
            color: var(--success);
        }

        .availability-status.error {
            background: rgba(248, 113, 113, 0.1);
            border: 2px solid var(--error);
            color: var(--error);
        }

        .equipment-selection {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .equipment-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            background: var(--panel);
            border-radius: var(--radius-md);
            margin-bottom: 0.5rem;
        }

        .cost-summary {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .cost-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .total-cost {
            border-top: 2px solid var(--line);
            padding-top: 1rem;
            margin-top: 1rem;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--brand);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--line);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .day-selection-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .days-display {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="company_dashboard.php">Company Portal</a>
            <i class="fas fa-chevron-right"></i>
            <a href="browse_places.php">Browse Places</a>
            <i class="fas fa-chevron-right"></i>
            <a href="place_details.php?id=<?php echo $place_id; ?>"><?php echo htmlspecialchars($place['place_name']); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span>Book Place</span>
        </div>

        <h1 class="page-title">Book This Place</h1>
        <p class="page-subtitle">Submit a booking request for <?php echo htmlspecialchars($place['place_name']); ?></p>

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

        <div class="form-container">
                <form method="POST" id="bookingForm">
                <h2 class="section-title">Booking Details</h2>

                <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Booking Type</label>
                                <select name="booking_type" class="form-select" required onchange="updateCost()">
                                    <option value="">Select Type</option>
                                    <?php if ($place['hourly_rate'] > 0): ?>
                                        <option value="hourly" data-rate="<?php echo $place['hourly_rate']; ?>">Hourly</option>
                                    <?php endif; ?>
                                    <?php if ($place['daily_rate'] > 0): ?>
                                        <option value="daily" data-rate="<?php echo $place['daily_rate']; ?>">Daily</option>
                                    <?php endif; ?>
                                    <?php if ($place['weekly_rate'] > 0): ?>
                                        <option value="weekly" data-rate="<?php echo $place['weekly_rate']; ?>">Weekly</option>
                                    <?php endif; ?>
                                    <?php if ($place['monthly_rate'] > 0): ?>
                                        <option value="monthly" data-rate="<?php echo $place['monthly_rate']; ?>">Monthly</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                </div>

                <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Start Date</label>
                        <input type="date" name="start_date" class="form-input" required id="start_date">
                            </div>
                            <div class="form-group">
                                <label class="form-label required">End Date</label>
                        <input type="date" name="end_date" class="form-input" required id="end_date">
                            </div>
                </div>

                <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Start Time</label>
                                <input type="time" name="start_time" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label required">End Time</label>
                                <input type="time" name="end_time" class="form-input" required>
                            </div>
                        </div>

                <!-- Day Selection (Weekdays/Weekends) -->
                <div class="form-group">
                    <label class="form-label required">Select Days to Book</label>
                    <p class="form-help">Choose which days of the week you want to book within your selected date range</p>
                    
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
                        <button type="button" class="btn-day-selector" id="select-weekdays">
                            <i class="fas fa-calendar-week"></i> Weekdays Only (Mon-Fri)
                        </button>
                        <button type="button" class="btn-day-selector" id="select-weekends">
                            <i class="fas fa-calendar-weekend"></i> Weekends Only (Sat-Sun)
                        </button>
                        <button type="button" class="btn-day-selector" id="select-all">
                            <i class="fas fa-calendar-check"></i> All Days
                        </button>
                        <button type="button" class="btn-day-selector" id="clear-selection">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>

                    <div class="day-selection-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.75rem;">
                        <?php
                        $days = [
                            ['value' => 'monday', 'label' => 'Mon', 'icon' => 'fas fa-calendar-day'],
                            ['value' => 'tuesday', 'label' => 'Tue', 'icon' => 'fas fa-calendar-day'],
                            ['value' => 'wednesday', 'label' => 'Wed', 'icon' => 'fas fa-calendar-day'],
                            ['value' => 'thursday', 'label' => 'Thu', 'icon' => 'fas fa-calendar-day'],
                            ['value' => 'friday', 'label' => 'Fri', 'icon' => 'fas fa-calendar-day'],
                            ['value' => 'saturday', 'label' => 'Sat', 'icon' => 'fas fa-calendar-weekend'],
                            ['value' => 'sunday', 'label' => 'Sun', 'icon' => 'fas fa-calendar-weekend']
                        ];
                        foreach ($days as $day): ?>
                            <label class="day-checkbox-label">
                                <input type="checkbox" 
                                       name="selected_days[]" 
                                       value="<?php echo $day['value']; ?>" 
                                       class="day-checkbox" 
                                       data-day="<?php echo $day['value']; ?>">
                                <i class="<?php echo $day['icon']; ?>"></i>
                                <span><?php echo $day['label']; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="form-help" style="margin-top: 0.5rem;" id="selected-days-info">
                        <i class="fas fa-info-circle"></i> No days selected. Please select at least one day.
                    </p>
                </div>

                <!-- Selected Days Display -->
                <div id="selected-days-display" style="display: none;">
                    <h3 class="section-title">Selected Days Availability</h3>
                    <div id="days-list" class="days-display"></div>
                    <div id="availability-status" class="availability-status"></div>
                </div>

                <hr class="section-divider">
                <h2 class="section-title">Equipment Selection</h2>

                <?php if (!empty($place_equipment)): ?>
                    <div class="equipment-selection">
                        <?php 
                        $equipment_by_category = [];
                        foreach ($place_equipment as $equip) {
                            $cat = $equip['category_name'] ?? 'Other';
                            if (!isset($equipment_by_category[$cat])) {
                                $equipment_by_category[$cat] = [];
                            }
                            $equipment_by_category[$cat][] = $equip;
                        }
                        ?>
                        <?php foreach ($equipment_by_category as $category => $items): ?>
                            <h4 style="margin-bottom: 0.5rem; color: var(--ink);"><?php echo htmlspecialchars($category); ?></h4>
                            <?php foreach ($items as $item): ?>
                                <div class="equipment-item">
                                    <input type="checkbox" 
                                           class="equipment-check" 
                                           data-item-id="<?php echo $item['item_id']; ?>"
                                           data-item-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                                           data-price="<?php echo $item['custom_price'] ?? $item['standard_price']; ?>">
                                    <label style="flex: 1;">
                                        <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                        <span style="color: var(--muted); font-size: 0.9rem;">
                                            - $<?php echo number_format($item['custom_price'] ?? $item['standard_price'], 2); ?> per day
                                        </span>
                                    </label>
                                    <input type="number" 
                                           class="equipment-quantity form-input" 
                                           data-item-id="<?php echo $item['item_id']; ?>"
                                           value="1" 
                                           min="1" 
                                           style="width: 80px;"
                                           disabled>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="form-help">No equipment available for this place.</p>
                <?php endif; ?>

                <hr class="section-divider">
                <h2 class="section-title">Contact Information</h2>

                <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Contact Person</label>
                                <input type="text" name="contact_person" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label required">Email Address</label>
                                <input type="email" name="contact_email" class="form-input" required>
                            </div>
                </div>

                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="contact_phone" class="form-input">
                    </div>

                <hr class="section-divider">
                <h2 class="section-title">Additional Information</h2>

                <div class="form-group">
                            <label class="form-label">Special Requirements</label>
                            <textarea name="special_requirements" class="form-textarea" placeholder="Any special requirements or requests..."></textarea>
                        </div>

                <div class="form-group">
                            <label class="form-label">Booking Notes</label>
                            <textarea name="booking_notes" class="form-textarea" placeholder="Additional notes for the place owner..."></textarea>
                        </div>

                <div class="form-group">
                    <label class="form-label">Additional Requests</label>
                    <textarea name="additional_requests" class="form-textarea" placeholder="Any additional requests..."></textarea>
                    </div>

                    <!-- Cost Summary -->
                    <div class="cost-summary">
                    <h3 class="section-title">Cost Summary</h3>
                        <div class="cost-item">
                            <span>Base Rate</span>
                            <span id="base-cost">$0.00</span>
                        </div>
                    <div class="cost-item">
                        <span>Equipment Cost</span>
                        <span id="equipment-cost">$0.00</span>
                    </div>
                    <div class="cost-item">
                        <span>Subtotal</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="cost-item">
                        <span>Tax (10%)</span>
                        <span id="tax">$0.00</span>
                        </div>
                        <div class="cost-item total-cost">
                            <span>Total Cost</span>
                            <span id="total-cost">$0.00</span>
                        </div>
                    </div>

                <!-- Hidden inputs for equipment -->
                <div id="selected-equipment-inputs"></div>

                <div class="form-actions">
                        <a href="place_details.php?id=<?php echo $place_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Details
                        </a>
                    <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                            <i class="fas fa-calendar-plus"></i>
                            Submit Booking Request
                        </button>
                    </div>
                </form>
        </div>
    </div>

    <script>
        const bookedDates = <?php echo json_encode($booked_dates); ?>;
        const submitButton = document.getElementById('submit-btn');
        const daysDisplay = document.getElementById('selected-days-display');
        const daysList = document.getElementById('days-list');
        const availabilityStatus = document.getElementById('availability-status');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        let selectedEquipment = new Map();
        
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        startDateInput.min = today;
        endDateInput.min = today;

        function isDateBooked(dateString) {
            return bookedDates.includes(dateString);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return `${days[date.getDay()]}, ${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
        }

        // Day selection handlers
        const dayCheckboxes = document.querySelectorAll('.day-checkbox');
        const selectedDaysInfo = document.getElementById('selected-days-info');
        
        function updateSelectedDaysInfo() {
            const checked = document.querySelectorAll('.day-checkbox:checked');
            const selected = Array.from(checked).map(cb => cb.dataset.day);
            
            if (selected.length === 0) {
                selectedDaysInfo.innerHTML = '<i class="fas fa-info-circle"></i> No days selected. Please select at least one day.';
                selectedDaysInfo.style.color = 'var(--error)';
            } else {
                const dayLabels = {
                    'monday': 'Mon', 'tuesday': 'Tue', 'wednesday': 'Wed', 'thursday': 'Thu',
                    'friday': 'Fri', 'saturday': 'Sat', 'sunday': 'Sun'
                };
                const labels = selected.map(d => dayLabels[d] || d).join(', ');
                selectedDaysInfo.innerHTML = `<i class="fas fa-check-circle"></i> Selected: <strong>${labels}</strong> (${selected.length} day${selected.length > 1 ? 's' : ''})`;
                selectedDaysInfo.style.color = 'var(--success)';
            }
        }
        
        // Quick selection buttons
        document.getElementById('select-weekdays').addEventListener('click', function() {
            dayCheckboxes.forEach(cb => {
                const day = cb.dataset.day;
                cb.checked = (day === 'monday' || day === 'tuesday' || day === 'wednesday' || day === 'thursday' || day === 'friday');
            });
            updateSelectedDaysInfo();
            displaySelectedDays();
            updateCost();
        });
        
        document.getElementById('select-weekends').addEventListener('click', function() {
            dayCheckboxes.forEach(cb => {
                const day = cb.dataset.day;
                cb.checked = (day === 'saturday' || day === 'sunday');
            });
            updateSelectedDaysInfo();
            displaySelectedDays();
            updateCost();
        });
        
        document.getElementById('select-all').addEventListener('click', function() {
            dayCheckboxes.forEach(cb => cb.checked = true);
            updateSelectedDaysInfo();
            displaySelectedDays();
            updateCost();
        });
        
        document.getElementById('clear-selection').addEventListener('click', function() {
            dayCheckboxes.forEach(cb => cb.checked = false);
            updateSelectedDaysInfo();
            displaySelectedDays();
            updateCost();
        });
        
        // Update info when checkboxes change
        dayCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateSelectedDaysInfo();
                displaySelectedDays();
                updateCost();
            });
        });
        
        function displaySelectedDays() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            
            if (!startDate || !endDate) {
                daysDisplay.style.display = 'none';
                if (submitButton) {
                    submitButton.disabled = true;
                }
                return;
            }
            
            // Get selected days of week
            const selectedDaysOfWeek = Array.from(document.querySelectorAll('.day-checkbox:checked')).map(cb => cb.value);
            if (selectedDaysOfWeek.length === 0) {
                daysDisplay.style.display = 'none';
                if (submitButton) {
                    submitButton.disabled = true;
                }
                return;
            }
            
            // Map day names to numbers
            const dayMap = {
                'sunday': 0, 'monday': 1, 'tuesday': 2, 'wednesday': 3,
                'thursday': 4, 'friday': 5, 'saturday': 6
            };
            const selectedDayNumbers = selectedDaysOfWeek.map(d => dayMap[d.toLowerCase()]).filter(n => n !== undefined);
            
            const start = new Date(startDate);
            const end = new Date(endDate);
            const selectedDays = [];
            const bookedDays = [];
            
            const current = new Date(start);
            while (current <= end) {
                const dayOfWeek = current.getDay(); // 0=Sunday, 6=Saturday
                if (selectedDayNumbers.includes(dayOfWeek)) {
                    const dateStr = current.toISOString().split('T')[0];
                    const dayInfo = {
                        date: dateStr,
                        formatted: formatDate(dateStr),
                        booked: isDateBooked(dateStr),
                        dayName: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][dayOfWeek]
                    };
                    
                    selectedDays.push(dayInfo);
                    if (dayInfo.booked) {
                        bookedDays.push(dayInfo);
                    }
                }
                current.setDate(current.getDate() + 1);
            }
            
            daysList.innerHTML = '';
            selectedDays.forEach(day => {
                const dayCard = document.createElement('div');
                dayCard.className = `day-card ${day.booked ? 'booked' : 'available'}`;
                dayCard.innerHTML = `
                    <div style="font-weight: 600; color: ${day.booked ? 'var(--error)' : 'var(--success)'}; margin-bottom: 0.25rem;">
                        ${day.booked ? '<i class="fas fa-times-circle"></i>' : '<i class="fas fa-check-circle"></i>'}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--muted); margin-bottom: 0.25rem;">${day.dayName}</div>
                    <div style="font-size: 0.85rem; color: var(--text-dark);">${day.formatted}</div>
                    <div style="font-size: 0.75rem; color: var(--muted); margin-top: 0.25rem;">
                        ${day.booked ? '<strong style="color: var(--error);">Booked</strong>' : '<strong style="color: var(--success);">Available</strong>'}
                    </div>
                `;
                daysList.appendChild(dayCard);
            });
            
            const allAvailable = bookedDays.length === 0;
            if (allAvailable) {
                availabilityStatus.className = 'availability-status success';
                availabilityStatus.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
                        <div>
                            <strong>All Selected Days Are Available!</strong>
                            <div style="font-size: 0.9rem; margin-top: 0.25rem;">You can proceed with booking ${selectedDays.length} day(s).</div>
                        </div>
                    </div>
                `;
                submitButton.disabled = false;
            } else {
                availabilityStatus.className = 'availability-status error';
                const bookedDatesList = bookedDays.map(d => d.formatted).join(', ');
                availabilityStatus.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem;"></i>
                        <div style="flex: 1;">
                            <strong>Booking Conflict - Some Days Cannot Be Booked</strong>
                            <div style="font-size: 0.9rem; margin-top: 0.25rem;">
                                <strong>${bookedDays.length}</strong> out of <strong>${selectedDays.length}</strong> day(s) are already booked by another company.
                            </div>
                            <div style="font-size: 0.85rem; margin-top: 0.5rem; color: var(--muted);">
                                <strong>Booked Days:</strong> ${bookedDatesList}
                            </div>
                            <div style="font-size: 0.85rem; margin-top: 0.5rem; color: var(--error); font-weight: 600;">
                                <i class="fas fa-ban"></i> These dates cannot be booked. Please select different dates.
                            </div>
                        </div>
                    </div>
                `;
                submitButton.disabled = true;
            }
            
            daysDisplay.style.display = 'block';
            updateCost();
        }

        function updateCost() {
            const bookingType = document.querySelector('select[name="booking_type"]');
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            
            if (!bookingType.value || !startDate || !endDate) {
                document.getElementById('base-cost').textContent = '$0.00';
                document.getElementById('equipment-cost').textContent = '$0.00';
                document.getElementById('subtotal').textContent = '$0.00';
                document.getElementById('tax').textContent = '$0.00';
                document.getElementById('total-cost').textContent = '$0.00';
                return;
            }
            
                const selectedOption = bookingType.options[bookingType.selectedIndex];
                const rate = parseFloat(selectedOption.getAttribute('data-rate'));
            
            // Calculate duration
            const start = new Date(startDate);
            const end = new Date(endDate);
            const durationDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
            
            let baseRate = 0;
            switch (bookingType.value) {
                case 'hourly':
                    baseRate = rate * 8 * durationDays; // Assume 8 hours per day
                    break;
                case 'daily':
                    baseRate = rate * durationDays;
                    break;
                case 'weekly':
                    baseRate = rate * Math.ceil(durationDays / 7);
                    break;
                case 'monthly':
                    baseRate = rate * Math.ceil(durationDays / 30);
                    break;
            }
            
            // Calculate equipment cost
            let equipmentCost = 0;
            selectedEquipment.forEach((item, itemId) => {
                equipmentCost += item.price * item.quantity * durationDays;
            });
            
            const subtotal = baseRate + equipmentCost;
            const tax = subtotal * 0.10;
            const total = subtotal + tax;
            
            document.getElementById('base-cost').textContent = '$' + baseRate.toFixed(2);
            document.getElementById('equipment-cost').textContent = '$' + equipmentCost.toFixed(2);
            document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('tax').textContent = '$' + tax.toFixed(2);
            document.getElementById('total-cost').textContent = '$' + total.toFixed(2);
        }

        // Equipment selection
        document.querySelectorAll('.equipment-check').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const itemId = parseInt(this.dataset.itemId);
                const quantityInput = document.querySelector(`.equipment-quantity[data-item-id="${itemId}"]`);
                
                if (this.checked) {
                    quantityInput.disabled = false;
                    selectedEquipment.set(itemId, {
                        item_id: itemId,
                        name: this.dataset.itemName,
                        price: parseFloat(this.dataset.price),
                        quantity: parseInt(quantityInput.value)
                    });
            } else {
                    quantityInput.disabled = true;
                    selectedEquipment.delete(itemId);
                }
                updateCost();
                updateEquipmentInputs();
            });
        });

        document.querySelectorAll('.equipment-quantity').forEach(input => {
            input.addEventListener('change', function() {
                const itemId = parseInt(this.dataset.itemId);
                if (selectedEquipment.has(itemId)) {
                    const item = selectedEquipment.get(itemId);
                    item.quantity = parseInt(this.value) || 1;
                    selectedEquipment.set(itemId, item);
                    updateCost();
                    updateEquipmentInputs();
                }
            });
        });

        function updateEquipmentInputs() {
            const container = document.getElementById('selected-equipment-inputs');
            container.innerHTML = '';
            selectedEquipment.forEach((item, itemId) => {
                container.innerHTML += `
                    <input type="hidden" name="selected_equipment[${itemId}][item_id]" value="${item.item_id}">
                    <input type="hidden" name="selected_equipment[${itemId}][quantity]" value="${item.quantity}">
                    <input type="hidden" name="selected_equipment[${itemId}][price]" value="${item.price}">
                `;
            });
        }

        startDateInput.addEventListener('change', function() {
            endDateInput.min = this.value;
            displaySelectedDays();
            updateCost();
        });

        endDateInput.addEventListener('change', function() {
            if (startDateInput.value && this.value < startDateInput.value) {
                alert('End date must be after start date.');
                this.value = '';
                return;
            }
            displaySelectedDays();
            updateCost();
        });

        document.querySelector('select[name="booking_type"]').addEventListener('change', function() {
            updateCost();
        });
        
        // Initial update
        updateSelectedDaysInfo();
    </script>
</body>
</html>

