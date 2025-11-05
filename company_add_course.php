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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Required fields
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $duration = trim($_POST['duration']);
    $mode = $_POST['mode'];
    $category = $_POST['category'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // Optional fields
    $instructor_id = !empty($_POST['instructor_id']) ? intval($_POST['instructor_id']) : null;
    $location = trim($_POST['location'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $learning_outcomes = trim($_POST['learning_outcomes'] ?? '');
    $certificate_option = isset($_POST['certificate_option']) ? 1 : 0;
    
    // Additional fields from database schema
    $related_internship_id = !empty($_POST['related_internship_id']) ? intval($_POST['related_internship_id']) : null;
    $course_level = !empty($_POST['course_level']) ? $_POST['course_level'] : 'beginner';
    $course_price = !empty($_POST['course_price']) ? floatval($_POST['course_price']) : 0.00;
    $currency = !empty($_POST['currency']) ? trim($_POST['currency']) : 'USD';
    $max_students = !empty($_POST['max_students']) ? intval($_POST['max_students']) : null;
    $course_image = trim($_POST['course_image'] ?? '');
    $course_materials = trim($_POST['course_materials'] ?? '');
    $prerequisites = trim($_POST['prerequisites'] ?? '');
    $course_schedule = trim($_POST['course_schedule'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Convert mode to is_online boolean (Online = 1, Onsite = 0)
    $is_online = ($mode === 'Online') ? 1 : 0;
    
    // Validation
    if (empty($title) || empty($description) || empty($duration) || empty($mode) || empty($category) || empty($start_date) || empty($end_date)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Insert course into database - all 24 parameters
        $sql = "INSERT INTO courses (instructor_id, created_by_type, created_by_id, related_internship_id, course_title, course_description, course_category, course_level, course_duration, course_price, currency, is_online, location, requirements, max_students, course_image, course_materials, prerequisites, learning_outcomes, course_schedule, status, is_featured, category, start_date, end_date, certificate_option, created_at) 
                VALUES (?, 'company', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // 24 parameters total - type string must be exactly 24 characters
            // Built programmatically from 24 parameter types
            // Correct verified string: "iiisssssdsississsssisssi" = 24 chars
            $stmt->bind_param("iiisssssdsississsssisssi", 
                $instructor_id,           // 1: i
                $company_id,              // 2: i
                $related_internship_id,   // 3: i
                $title,                   // 4: s
                $description,             // 5: s
                $category,                // 6: s
                $course_level,            // 7: s
                $duration,                // 8: s
                $course_price,            // 9: d
                $currency,                // 10: s
                $is_online,               // 11: i
                $location,                // 12: s
                $requirements,            // 13: s
                $max_students,            // 14: i
                $course_image,            // 15: s
                $course_materials,        // 16: s
                $prerequisites,           // 17: s
                $learning_outcomes,        // 18: s
                $course_schedule,          // 19: s
                $is_featured,             // 20: i
                $category,                // 21: s
                $start_date,              // 22: s
                $end_date,                // 23: s
                $certificate_option       // 24: i
            );
            
            if ($stmt->execute()) {
                $success_message = "Course created successfully!";
                // Clear form data
                $title = $description = $duration = $requirements = $learning_outcomes = $location = '';
                $mode = $category = $start_date = $end_date = '';
                $course_level = 'beginner';
                $course_price = 0.00;
                $currency = 'USD';
                $max_students = $course_image = $course_materials = $prerequisites = $course_schedule = '';
                $instructor_id = $related_internship_id = null;
                $certificate_option = $is_featured = 0;
            } else {
                $error_message = "Error creating course: " . $stmt->error;
            }
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    }
}

// Get available instructors for dropdown
$instructors_sql = "SELECT instructor_id, first_name, last_name, department FROM instructors ORDER BY last_name, first_name";
$instructors_result = $conn->query($instructors_sql);
$instructors = $instructors_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - Company Portal</title>
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
            max-width: 900px;
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

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            cursor: pointer;
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

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--line);
        }

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

        .section-divider {
            margin: 2rem 0;
            border: none;
            border-top: 2px solid var(--line);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 1rem;
            margin-top: 1rem;
        }

        /* Schedule Styles */
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
                <a href="company_dashboard.php"><i class="fas fa-home"></i> Company Portal</a>
                <i class="fas fa-chevron-right"></i>
                <span>Create Course</span>
            </div>
            <h1 class="page-title">Create New Course</h1>
            <p class="page-subtitle">Add a new training course for students and employees</p>
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
                    <label for="title" class="form-label required">Course Title</label>
                    <input type="text" id="title" name="title" class="form-input" 
                           value="<?php echo htmlspecialchars($title ?? ''); ?>" 
                           placeholder="Enter course title" required>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label required">Course Description</label>
                    <textarea id="description" name="description" class="form-input form-textarea" 
                              placeholder="Describe the course content, objectives, and what students will learn" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="duration" class="form-label required">Duration</label>
                        <input type="text" id="duration" name="duration" class="form-input" 
                               value="<?php echo htmlspecialchars($duration ?? ''); ?>" 
                               placeholder="e.g., 4 weeks, 40 hours" required>
                    </div>
                    <div class="form-group">
                        <label for="mode" class="form-label required">Mode</label>
                        <select id="mode" name="mode" class="form-select" required>
                            <option value="">Select mode</option>
                            <option value="Online" <?php echo (isset($mode) && $mode === 'Online') ? 'selected' : ''; ?>>Online</option>
                            <option value="Onsite" <?php echo (isset($mode) && $mode === 'Onsite') ? 'selected' : ''; ?>>Onsite</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category" class="form-label required">Category</label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">Select category</option>
                            <option value="technical" <?php echo (isset($category) && $category === 'technical') ? 'selected' : ''; ?>>Technical</option>
                            <option value="business" <?php echo (isset($category) && $category === 'business') ? 'selected' : ''; ?>>Business</option>
                            <option value="language" <?php echo (isset($category) && $category === 'language') ? 'selected' : ''; ?>>Language</option>
                            <option value="soft_skills" <?php echo (isset($category) && $category === 'soft_skills') ? 'selected' : ''; ?>>Soft Skills</option>
                            <option value="certification" <?php echo (isset($category) && $category === 'certification') ? 'selected' : ''; ?>>Certification</option>
                            <option value="workshop" <?php echo (isset($category) && $category === 'workshop') ? 'selected' : ''; ?>>Workshop</option>
                            <option value="seminar" <?php echo (isset($category) && $category === 'seminar') ? 'selected' : ''; ?>>Seminar</option>
                            <option value="other" <?php echo (isset($category) && $category === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="course_level" class="form-label">Course Level</label>
                        <select id="course_level" name="course_level" class="form-select">
                            <option value="beginner" <?php echo (isset($course_level) && $course_level === 'beginner') ? 'selected' : ''; ?>>Beginner</option>
                            <option value="intermediate" <?php echo (isset($course_level) && $course_level === 'intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="advanced" <?php echo (isset($course_level) && $course_level === 'advanced') ? 'selected' : ''; ?>>Advanced</option>
                            <option value="expert" <?php echo (isset($course_level) && $course_level === 'expert') ? 'selected' : ''; ?>>Expert</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date" class="form-label required">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-input" 
                               value="<?php echo htmlspecialchars($start_date ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date" class="form-label required">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-input" 
                               value="<?php echo htmlspecialchars($end_date ?? ''); ?>" required>
                    </div>
                </div>

                <hr class="section-divider">
                <h2 class="section-title">Course Details</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="course_price" class="form-label">Course Price</label>
                        <input type="number" id="course_price" name="course_price" class="form-input" 
                               value="<?php echo htmlspecialchars($course_price ?? '0.00'); ?>" 
                               placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label for="currency" class="form-label">Currency</label>
                        <select id="currency" name="currency" class="form-select">
                            <option value="USD" <?php echo (isset($currency) && $currency === 'USD') ? 'selected' : 'selected'; ?>>USD ($)</option>
                            <option value="EUR" <?php echo (isset($currency) && $currency === 'EUR') ? 'selected' : ''; ?>>EUR (€)</option>
                            <option value="GBP" <?php echo (isset($currency) && $currency === 'GBP') ? 'selected' : ''; ?>>GBP (£)</option>
                            <option value="JPY" <?php echo (isset($currency) && $currency === 'JPY') ? 'selected' : ''; ?>>JPY (¥)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="max_students" class="form-label">Max Students</label>
                        <input type="number" id="max_students" name="max_students" class="form-input" 
                               value="<?php echo htmlspecialchars($max_students ?? ''); ?>" 
                               placeholder="Maximum number of students" min="1">
                    </div>
                    <div class="form-group">
                        <label for="instructor_id" class="form-label">Instructor (Optional)</label>
                        <select id="instructor_id" name="instructor_id" class="form-select">
                            <option value="">Select instructor (optional)</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?php echo $instructor['instructor_id']; ?>" 
                                        <?php echo (isset($instructor_id) && $instructor_id == $instructor['instructor_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name'] . ' (' . $instructor['department'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" id="location" name="location" class="form-input" 
                           value="<?php echo htmlspecialchars($location ?? ''); ?>" 
                           placeholder="Enter course location (for onsite courses)">
                </div>

                <div class="form-group">
                    <label for="course_image" class="form-label">Course Image URL</label>
                    <input type="text" id="course_image" name="course_image" class="form-input" 
                           value="<?php echo htmlspecialchars($course_image ?? ''); ?>" 
                           placeholder="Enter image URL (optional)">
                </div>

                <hr class="section-divider">
                <h2 class="section-title">Course Content</h2>

                <div class="form-group">
                    <label for="prerequisites" class="form-label">Prerequisites</label>
                    <textarea id="prerequisites" name="prerequisites" class="form-input form-textarea" 
                              placeholder="List any prerequisites or required knowledge for this course"><?php echo htmlspecialchars($prerequisites ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="requirements" class="form-label">Requirements</label>
                    <textarea id="requirements" name="requirements" class="form-input form-textarea" 
                              placeholder="List any prerequisites, skills, or requirements for this course"><?php echo htmlspecialchars($requirements ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="course_materials" class="form-label">Course Materials</label>
                    <textarea id="course_materials" name="course_materials" class="form-input form-textarea" 
                              placeholder="List course materials, textbooks, or resources needed"><?php echo htmlspecialchars($course_materials ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="learning_outcomes" class="form-label">Learning Outcomes</label>
                    <textarea id="learning_outcomes" name="learning_outcomes" class="form-input form-textarea" 
                              placeholder="Describe what students will learn and achieve after completing this course"><?php echo htmlspecialchars($learning_outcomes ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="course_schedule" class="form-label">Course Schedule</label>
                    <p class="form-help">Select days of the week and set time for each day</p>
                    
                    <div class="schedule-container">
                        <?php
                        // Parse existing schedule if available
                        $schedule_data = [];
                        if (!empty($course_schedule)) {
                            try {
                                $schedule_data = json_decode($course_schedule, true);
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
                    <input type="hidden" id="course_schedule" name="course_schedule" value="<?php echo htmlspecialchars($course_schedule ?? ''); ?>">
                </div>

                <hr class="section-divider">
                <h2 class="section-title">Additional Options</h2>

                <div class="form-group">
                    <label for="related_internship_id" class="form-label">Related Internship ID</label>
                    <input type="number" id="related_internship_id" name="related_internship_id" class="form-input" 
                           value="<?php echo htmlspecialchars($related_internship_id ?? ''); ?>" 
                           placeholder="Enter related internship ID if applicable">
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="certificate_option" name="certificate_option" value="1" 
                               <?php echo (isset($certificate_option) && $certificate_option) ? 'checked' : ''; ?>>
                        <label for="certificate_option" class="form-label" style="margin-bottom: 0;">
                            Certificate Option
                        </label>
                    </div>
                    <p class="form-help">Check this box if students will receive a certificate upon completion</p>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                               <?php echo (isset($is_featured) && $is_featured) ? 'checked' : ''; ?>>
                        <label for="is_featured" class="form-label" style="margin-bottom: 0;">
                            Featured Course
                        </label>
                    </div>
                    <p class="form-help">Check this box to feature this course prominently</p>
                </div>

                <div class="form-actions">
                    <a href="company_dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Create Course
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-calculate end date based on duration and start date
        function calculateEndDate() {
            const durationInput = document.getElementById('duration');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            const duration = durationInput.value.trim();
            const startDate = startDateInput.value;
            
            // Only calculate if both duration and start date are provided
            if (!duration || !startDate) {
                return;
            }
            
            // Parse duration string (e.g., "4 months", "40 hours", "2 weeks", "6 days")
            const durationLower = duration.toLowerCase();
            const durationMatch = durationLower.match(/(\d+)\s*(month|months|week|weeks|day|days|hour|hours|year|years)/);
            
            if (!durationMatch) {
                // If format doesn't match, try to extract just the number and assume months
                const numberMatch = duration.match(/\d+/);
                if (numberMatch) {
                    const months = parseInt(numberMatch[0]);
                    if (!isNaN(months)) {
                        setEndDate(startDate, months, 'month');
                        return;
                    }
                }
                return;
            }
            
            const amount = parseInt(durationMatch[1]);
            const unit = durationMatch[2].replace(/s$/, ''); // Remove plural 's'
            
            if (isNaN(amount) || amount <= 0) {
                return;
            }
            
            setEndDate(startDate, amount, unit);
        }
        
        function setEndDate(startDateStr, amount, unit) {
            const startDate = new Date(startDateStr);
            const endDate = new Date(startDate);
            const endDateInput = document.getElementById('end_date');
            
            // Calculate end date based on unit
            switch(unit) {
                case 'month':
                    endDate.setMonth(endDate.getMonth() + amount);
                    break;
                case 'week':
                    endDate.setDate(endDate.getDate() + (amount * 7));
                    break;
                case 'day':
                    endDate.setDate(endDate.getDate() + amount);
                    break;
                case 'hour':
                    // For hours, assume 8 hours per day
                    const days = Math.ceil(amount / 8);
                    endDate.setDate(endDate.getDate() + days);
                    break;
                case 'year':
                    endDate.setFullYear(endDate.getFullYear() + amount);
                    break;
                default:
                    return;
            }
            
            // Format date as YYYY-MM-DD for input field
            const year = endDate.getFullYear();
            const month = String(endDate.getMonth() + 1).padStart(2, '0');
            const day = String(endDate.getDate()).padStart(2, '0');
            const formattedDate = `${year}-${month}-${day}`;
            
            endDateInput.value = formattedDate;
        }
        
        // Add event listeners
        // Schedule management functions
        function updateSchedule() {
            const scheduleInput = document.getElementById('course_schedule');
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
            const durationInput = document.getElementById('duration');
            const startDateInput = document.getElementById('start_date');
            
            if (durationInput && startDateInput) {
                // Calculate when duration changes
                durationInput.addEventListener('input', calculateEndDate);
                durationInput.addEventListener('blur', calculateEndDate);
                
                // Calculate when start date changes
                startDateInput.addEventListener('change', calculateEndDate);
                startDateInput.addEventListener('input', calculateEndDate);
            }
            
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
        });
    </script>
</body>
</html>

