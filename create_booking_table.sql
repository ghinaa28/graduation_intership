-- Create comprehensive booking table for place bookings
-- This table includes duration, booking days, pricing, equipment, and all booking details

CREATE TABLE IF NOT EXISTS `place_bookings` (
    `booking_id` INT AUTO_INCREMENT PRIMARY KEY,
    `place_id` INT NOT NULL,
    `company_id` INT NOT NULL,
    
    -- Booking Type and Duration
    `booking_type` ENUM('hourly', 'daily', 'weekly', 'monthly') NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `duration_days` INT NOT NULL DEFAULT 1,
    `duration_hours` DECIMAL(10,2) DEFAULT 0.00,
    
    -- Booking Days Details (JSON format for flexibility)
    `booking_days` JSON NULL COMMENT 'Array of dates booked: ["2025-01-15", "2025-01-16"]',
    `total_days` INT NOT NULL DEFAULT 1,
    
    -- Pricing Information
    `base_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `equipment_cost` DECIMAL(10,2) DEFAULT 0.00,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(10,2) DEFAULT 0.00,
    `total_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(10) DEFAULT 'USD',
    
    -- Equipment Information (JSON format)
    `equipment_details` JSON NULL COMMENT 'Array of equipment items with prices and quantities',
    
    -- Contact Information
    `contact_person` VARCHAR(200) NOT NULL,
    `contact_email` VARCHAR(200) NOT NULL,
    `contact_phone` VARCHAR(50),
    
    -- Additional Information
    `special_requirements` TEXT,
    `booking_notes` TEXT,
    `additional_requests` TEXT,
    
    -- Booking Status
    `booking_status` ENUM('pending', 'confirmed', 'approved', 'rejected', 'cancelled', 'completed') DEFAULT 'pending',
    `payment_status` ENUM('pending', 'partial', 'paid', 'refunded') DEFAULT 'pending',
    
    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `confirmed_at` TIMESTAMP NULL,
    `cancelled_at` TIMESTAMP NULL,
    
    -- Foreign Keys
    FOREIGN KEY (`place_id`) REFERENCES `places`(`place_id`) ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`company_id`) ON DELETE CASCADE,
    
    -- Indexes for performance
    INDEX `idx_place_id` (`place_id`),
    INDEX `idx_company_id` (`company_id`),
    INDEX `idx_booking_status` (`booking_status`),
    INDEX `idx_start_date` (`start_date`),
    INDEX `idx_end_date` (`end_date`),
    INDEX `idx_dates_range` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create booking equipment junction table (for detailed equipment tracking per booking)
CREATE TABLE IF NOT EXISTS `booking_equipment` (
    `booking_equipment_id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL,
    `item_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`booking_id`) REFERENCES `place_bookings`(`booking_id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `equipment_items`(`item_id`) ON DELETE CASCADE,
    
    INDEX `idx_booking_id` (`booking_id`),
    INDEX `idx_item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
