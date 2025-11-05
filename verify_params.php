<?php
// Verify the 24 parameters match the SQL placeholders
echo "SQL Placeholders (excluding fixed values):\n";
$sql_params = [
    'instructor_id',           // 1
    'created_by_id',           // 2
    'related_internship_id',   // 3
    'course_title',            // 4
    'course_description',      // 5
    'course_category',         // 6
    'course_level',            // 7
    'course_duration',         // 8
    'course_price',            // 9
    'currency',                // 10
    'is_online',               // 11
    'location',                // 12
    'requirements',            // 13
    'max_students',            // 14
    'course_image',            // 15
    'course_materials',        // 16
    'prerequisites',           // 17
    'learning_outcomes',       // 18
    'course_schedule',         // 19
    'is_featured',             // 20
    'category',                // 21
    'start_date',              // 22
    'end_date',                // 23
    'certificate_option'       // 24
];

echo "Total: " . count($sql_params) . "\n\n";

$types = [
    'i', 'i', 'i', 's', 's', 's', 's', 's', 'd', 's', 'i', 's', 's', 'i',
    's', 's', 's', 's', 's', 'i', 's', 's', 's', 'i'
];

echo "Type string: " . implode('', $types) . "\n";
echo "Length: " . strlen(implode('', $types)) . "\n";
echo "Count: " . count($types) . "\n";
?>

