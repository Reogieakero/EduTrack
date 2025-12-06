<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date; 

require_once '../../config/database.php';

function check_for_duplicate_student($conn, $first_name, $last_name, $middle_initial) {

    $sql = "SELECT id FROM students WHERE 
            LOWER(first_name) = LOWER(?) AND 
            LOWER(last_name) = LOWER(?)";

    $params = [$first_name, $last_name];
    $types = 'ss';

    if (!empty($middle_initial)) {
        $sql .= " AND LOWER(middle_initial) = LOWER(?)";
        $params[] = $middle_initial;
        $types .= 's';
    } else {
        $sql .= " AND middle_initial IS NULL";
    }

    if ($stmt = $conn->prepare($sql)) {
        $bind_names = [$types];
        for ($i=0; $i<count($params); $i++) {
            $bind_names[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
        
        if ($stmt->execute()) {
            $stmt->store_result();
            $is_duplicate = $stmt->num_rows > 0;
            $stmt->close();
            return $is_duplicate;
        }
        $stmt->close();
    }
    return false; 
}

function generate_student_id($conn) {
    $current_year = date('Y');
    
    $search_pattern = $current_year . '-%';
    
    $sql = "SELECT MAX(CAST(SUBSTRING(id, 6) AS UNSIGNED)) as max_seq 
            FROM students 
            WHERE id LIKE ?";
            
    $max_seq = 0;
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $search_pattern);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $max_seq = (int)($row['max_seq'] ?? 0);
            }
        }
        $stmt->close();
    }
    
    $next_seq = $max_seq + 1;
    
    if ($next_seq > 9999) {
        error_log("Student ID sequence overflow for year " . $current_year);
        return null; 
    }
    
    $formatted_seq = str_pad($next_seq, 4, '0', STR_PAD_LEFT);
    
    $new_student_id = $current_year . '-' . $formatted_seq;
    
    return $new_student_id;
}


$add_success_details = null;
if (isset($_SESSION['add_success_details'])) {
    $add_success_details = $_SESSION['add_success_details'];
    unset($_SESSION['add_success_details']);
} 
$edit_success_details = null; 
if (isset($_SESSION['edit_success_details'])) {
    $edit_success_details = $_SESSION['edit_success_details'];
    unset($_SESSION['edit_success_details']);
}
$delete_success_details = null; 
if (isset($_SESSION['delete_success_details'])) {
    $delete_success_details = $_SESSION['delete_success_details'];
    unset($_SESSION['delete_success_details']);
}
$bulk_success_details = null;
if (isset($_SESSION['bulk_success_details'])) {
    $bulk_success_details = $_SESSION['bulk_success_details'];
    unset($_SESSION['bulk_success_details']);
}
$add_error_details = null;
if (isset($_SESSION['add_error_details'])) {
    $add_error_details = $_SESSION['add_error_details'];
    unset($_SESSION['add_error_details']);
}

$student_to_edit = null; 
if (isset($_SESSION['student_to_edit'])) {
    $student_to_edit = $_SESSION['student_to_edit'];
    unset($_SESSION['student_to_edit']);
}

$students = [];
$sections_list = []; 
$grades_by_student = []; 
$fetch_error = false;

$selected_section_id = $_GET['section_id'] ?? 'all';
$search_term = trim($_GET['search'] ?? '');

$sql_fetch_sections = "SELECT id, year, name, teacher FROM sections ORDER BY year ASC, name ASC";
if ($stmt_sections = $conn->prepare($sql_fetch_sections)) {
    if ($stmt_sections->execute()) {
        $result_sections = $stmt_sections->get_result();
        while ($row = $result_sections->fetch_assoc()) {
            $sections_list[$row['id']] = $row;
        }
    }
    $stmt_sections->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'add_student') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $middle_initial_raw = trim($_POST['middle_initial'] ?? '');
        $section_id = (int)($_POST['section_id'] ?? 0);
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');
        $middle_initial = empty($middle_initial_raw) ? null : strtoupper(substr($middle_initial_raw, 0, 1));

        if (empty($first_name) || empty($last_name) || $section_id <= 0 || empty($date_of_birth)) {
            $_SESSION['add_error_details'] = "All required student fields are necessary.";
        } 
        else if (check_for_duplicate_student($conn, $first_name, $last_name, $middle_initial)) {
            $full_name = $last_name . ', ' . $first_name . ($middle_initial ? ' ' . $middle_initial . '.' : '');
            $_SESSION['add_error_details'] = "ERROR: A student with the name **{$full_name}** already exists in the system.";
        }
        else {
            $new_id = generate_student_id($conn);
            
            if (is_null($new_id)) {
                $_SESSION['add_error_details'] = "ERROR: Could not generate a unique student ID. Sequence overflow or database error.";
            } else {
                
                $sql = "INSERT INTO students (id, first_name, last_name, middle_initial, section_id, date_of_birth, enrollment_date) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssssis", $param_id, $param_first_name, $param_last_name, $param_middle_initial, $param_section_id, $param_dob);
                    
                    $param_id = $new_id;
                    $param_first_name = $first_name;
                    $param_last_name = $last_name;
                    $param_middle_initial = $middle_initial;
                    $param_section_id = $section_id;
                    $param_dob = $date_of_birth;
                    
                    if ($stmt->execute()) {
                        $section_info = $sections_list[$section_id] ?? ['name' => 'N/A', 'year' => 'N/A', 'teacher' => 'N/A'];
                        
                        $full_name_display = $last_name . ', ' . $first_name . ($middle_initial ? ' ' . $middle_initial . '.' : '');
                        
                        $_SESSION['add_success_details'] = [
                            'name' => $full_name_display,
                            'section_name' => $section_info['name'],
                            'section_year' => $section_info['year'],
                            'teacher_name' => $section_info['teacher']
                        ];
                    } else {
                        $_SESSION['add_error_details'] = "ERROR: Could not execute the insert statement. " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $_SESSION['add_error_details'] = "ERROR: Could not prepare the insert statement. " . $conn->error;
                }
            }
        }
        header("Location: students.php");
        exit;
    }
    
    if ($_POST['action'] === 'bulk_add_students') {
        if (isset($_FILES['student_file']) && $_FILES['student_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['student_file']['tmp_name'];
            $file_extension = strtolower(pathinfo($_FILES['student_file']['name'], PATHINFO_EXTENSION));

            $allowed_extensions = ['csv', 'xlsx', 'xls'];

            if (!in_array($file_extension, $allowed_extensions)) {
                $_SESSION['add_error_details'] = "ERROR: Only CSV, XLSX, and XLS files are supported for bulk upload.";
            } else {
                $students_added = 0;
                $students_failed = 0;
                $errors = [];
                
                try {
                    $spreadsheet = IOFactory::load($file_tmp_path);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray(); 

                    $row_count = 1;
                    
                    $sql_insert = "INSERT INTO students (id, first_name, last_name, middle_initial, section_id, date_of_birth, enrollment_date) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                    
                    if ($stmt_insert = $conn->prepare($sql_insert)) {
                        
                        foreach ($rows as $index => $data) {
                            if ($index === 0) {
                                continue; 
                            }
                            
                            $row_count++;
                            
                            $first_name = trim($data[0] ?? '');
                            $last_name = trim($data[1] ?? '');
                            $middle_initial_raw = trim($data[2] ?? '');
                            $section_name_raw = trim($data[3] ?? '');
                            $date_of_birth_raw = trim($data[4] ?? '');
                            
                            $middle_initial = empty($middle_initial_raw) ? null : strtoupper(substr($middle_initial_raw, 0, 1));
                            
                            $section_id = 0;
                            foreach ($sections_list as $id => $section) {
                                $full_section_name = $section['year'] . ' - ' . $section['name'];
                                if (strcasecmp($full_section_name, $section_name_raw) === 0) {
                                    $section_id = $id;
                                    break;
                                }
                            }
                            
                            $date_of_birth = null;
                            if (!empty($date_of_birth_raw)) {
                                if (is_numeric($date_of_birth_raw) && $date_of_birth_raw > 1) {
                                    try {
                                        $date_of_birth = Date::excelToDateTimeObject($date_of_birth_raw)->format('Y-m-d');
                                    } catch (\Exception $e) {
                                        $date_of_birth = $date_of_birth_raw;
                                    }
                                } else {
                                    $date_of_birth = $date_of_birth_raw;
                                }
                            }

                            if (empty($first_name) || empty($last_name) || $section_id <= 0 || empty($date_of_birth)) {
                                $students_failed++;
                                $errors[] = "Row {$row_count} (Name: {$last_name}, {$first_name}): Missing or invalid data (First Name, Last Name, Section, or DOB).";
                                continue;
                            }
                            
                            if (check_for_duplicate_student($conn, $first_name, $last_name, $middle_initial)) {
                                $students_failed++;
                                $errors[] = "Row {$row_count} (Name: {$last_name}, {$first_name}): Duplicate student found. Skipping insertion.";
                                continue;
                            }
                            
                            $new_id = generate_student_id($conn);

                            if (is_null($new_id)) {
                                $students_failed++;
                                $errors[] = "Row {$row_count} (Name: {$last_name}, {$first_name}): Could not generate unique student ID.";
                                continue;
                            }
                            
                            $new_id_param = $new_id;
                            $first_name_param = $first_name;
                            $last_name_param = $last_name;
                            $middle_initial_param = $middle_initial;
                            $section_id_param = $section_id;
                            $date_of_birth_param = $date_of_birth;

                            $stmt_insert->bind_param("ssssis", $new_id_param, $first_name_param, $last_name_param, $middle_initial_param, $section_id_param, $date_of_birth_param);

                            if ($stmt_insert->execute()) {
                                $students_added++;
                            } else {
                                $students_failed++;
                                $errors[] = "Row {$row_count} (Name: {$last_name}, {$first_name}): Database insertion failed. " . $stmt_insert->error;
                            }
                        }
                        $stmt_insert->close();
                    } else {
                        $_SESSION['add_error_details'] = "ERROR: Could not prepare the bulk insert statement. " . $conn->error;
                    }
                } catch (\Exception $e) {
                    $_SESSION['add_error_details'] = "ERROR: File processing failed. Ensure the file is a valid CSV or Excel format. Details: " . $e->getMessage();
                }


                if ($students_added > 0 || $students_failed > 0) {
                    $_SESSION['bulk_success_details'] = [
                        'added' => $students_added,
                        'failed' => $students_failed,
                        'errors' => $errors
                    ];
                    header("Location: students.php");
                    exit;
                }
                
                if (isset($_SESSION['add_error_details'])) {
                     header("Location: students.php");
                     exit;
                }
            }
        } else {
            $_SESSION['add_error_details'] = "ERROR: No file uploaded or upload error occurred.";
        }
        header("Location: students.php");
        exit;
    }
    
    if ($_POST['action'] === 'delete_student') {
        $student_id = trim($_POST['student_id'] ?? '');

        if (!empty($student_id)) {
            $sql_select = "SELECT s.first_name, s.last_name, s.middle_initial, sec.year, sec.name as section_name, sec.teacher FROM students s JOIN sections sec ON s.section_id = sec.id WHERE s.id = ?";
            $deleted_details = null;

            if ($stmt_select = $conn->prepare($sql_select)) {
                $stmt_select->bind_param("s", $student_id); 
                $stmt_select->execute();
                $result_select = $stmt_select->get_result();
                $deleted_details = $result_select->fetch_assoc();
                $stmt_select->close();
            }

            if ($deleted_details) {
                $sql_delete = "DELETE FROM students WHERE id = ?";
                if ($stmt_delete = $conn->prepare($sql_delete)) {
                    $stmt_delete->bind_param("s", $student_id); 
                    if ($stmt_delete->execute()) {
                        
                        $mi = $deleted_details['middle_initial'];
                        $full_name_display = $deleted_details['last_name'] . ', ' . $deleted_details['first_name'] . ($mi ? ' ' . $mi . '.' : '');

                        $_SESSION['delete_success_details'] = [
                            'name' => $full_name_display,
                            'section_name' => $deleted_details['section_name'],
                            'section_year' => $deleted_details['year'],
                            'teacher_name' => $deleted_details['teacher']
                        ];
                        header("Location: students.php");
                        exit;
                    } else {
                        $_SESSION['add_error_details'] = "ERROR: Could not delete student. " . $stmt_delete->error;
                    }
                    $stmt_delete->close();
                } else {
                    $_SESSION['add_error_details'] = "ERROR: Could not prepare delete statement. " . $conn->error;
                }
            } else {
                $_SESSION['add_error_details'] = "ERROR: Student not found for deletion.";
            }
        }
        header("Location: students.php");
        exit;
    }
    
    if ($_POST['action'] === 'fetch_edit_data') {
        $student_id = trim($_POST['student_id'] ?? ''); 
        
        if (!empty($student_id)) { 
            $sql_fetch_student = "SELECT id, first_name, last_name, middle_initial, section_id, date_of_birth FROM students WHERE id = ?";
            
            if ($stmt_fetch = $conn->prepare($sql_fetch_student)) {
                $stmt_fetch->bind_param("s", $student_id);
                if ($stmt_fetch->execute()) {
                    $result_fetch = $stmt_fetch->get_result();
                    $student_data = $result_fetch->fetch_assoc();
                    $stmt_fetch->close();
                    
                    if ($student_data) {
                        $_SESSION['student_to_edit'] = $student_data;
                        header("Location: students.php");
                        exit;
                    } else {
                        $_SESSION['add_error_details'] = "ERROR: Student not found for editing.";
                    }
                } else {
                    $_SESSION['add_error_details'] = "ERROR: Could not fetch student data for editing. " . $stmt_fetch->error;
                }
            } else {
                $_SESSION['add_error_details'] = "ERROR: Could not prepare fetch statement for editing. " . $conn->error;
            }
        }
         header("Location: students.php");
         exit;
    }

    if ($_POST['action'] === 'edit_student') {
        $student_id = trim($_POST['student_id'] ?? ''); 
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $middle_initial = trim($_POST['middle_initial'] ?? '');
        $section_id = (int)($_POST['section_id'] ?? 0);
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');

        if (empty($student_id) || empty($first_name) || empty($last_name) || $section_id <= 0 || empty($date_of_birth)) {
            $_SESSION['add_error_details'] = "All required student fields and ID are required for update.";
        } else {
            $middle_initial = empty($middle_initial) ? null : strtoupper(substr($middle_initial, 0, 1));
            
            $sql = "UPDATE students SET first_name = ?, last_name = ?, middle_initial = ?, section_id = ?, date_of_birth = ? WHERE id = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sssiss", $param_first_name, $param_last_name, $param_middle_initial, $param_section_id, $param_dob, $param_id);
                
                $param_first_name = $first_name;
                $param_last_name = $last_name;
                $param_middle_initial = $middle_initial;
                $param_section_id = $section_id;
                $param_dob = $date_of_birth;
                $param_id = $student_id;
                
                if ($stmt->execute()) {
                    $section_info = $sections_list[$section_id] ?? ['name' => 'N/A', 'year' => 'N/A', 'teacher' => 'N/A'];
                    
                    $full_name_display = $last_name . ', ' . $first_name . ($middle_initial ? ' ' . $middle_initial . '.' : '');

                    $_SESSION['edit_success_details'] = [
                        'name' => $full_name_display,
                        'section_name' => $section_info['name'],
                        'section_year' => $section_info['year'],
                        'teacher_name' => $section_info['teacher']
                    ];
                    header("Location: students.php");
                    exit;
                } else {
                    $_SESSION['add_error_details'] = "ERROR: Could not execute the update statement. " . $stmt->error;
                }
                $stmt->close();
            } else {
                $_SESSION['add_error_details'] = "ERROR: Could not prepare the update statement. " . $conn->error;
            }
        }
         header("Location: students.php");
         exit;
    }
}


$sql_fetch = "SELECT s.*, sec.year as section_year, sec.name as section_name, sec.teacher as teacher_name FROM students s JOIN sections sec ON s.section_id = sec.id";
$where_clauses = [];
$params = [];
$types = '';

if ($selected_section_id !== 'all' && is_numeric($selected_section_id)) {
    $where_clauses[] = "s.section_id = ?";
    $params[] = $selected_section_id;
    $types .= 'i';
}

if (!empty($search_term)) {
    $search_pattern = '%' . $search_term . '%';
    $where_clauses[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ? OR CONCAT(s.last_name, ' ', s.first_name) LIKE ? OR s.id LIKE ?)";
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern; 
    $types .= 'sssss'; 
}

if (!empty($where_clauses)) {
    $sql_fetch .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql_fetch .= " ORDER BY sec.year ASC, sec.name ASC, s.last_name ASC, s.first_name ASC";


if ($stmt = $conn->prepare($sql_fetch)) {
    if (!empty($params)) {
        $bind_names = [$types];
        for ($i=0; $i<count($params); $i++) {
            $bind_names[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }
        }
    } else {
        $fetch_error = "ERROR: Could not execute the student fetch statement. " . $stmt->error;
    }
    $stmt->close();
} else {
    $fetch_error = "ERROR: Could not prepare the student fetch statement. " . $conn->error;
}


if (!empty($students)) {
    $student_ids = array_column($students, 'id');
    
    $in_clause_placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    
    $sql_fetch_grades = "SELECT student_id, quarter, grade FROM grades WHERE student_id IN ($in_clause_placeholders)";

    $types_grades = str_repeat('s', count($student_ids)); 

    if ($stmt_grades = $conn->prepare($sql_fetch_grades)) {
        $bind_names = [$types_grades];
        foreach ($student_ids as &$id) {
            $bind_names[] = &$id;
        }
        call_user_func_array([$stmt_grades, 'bind_param'], $bind_names);

        if ($stmt_grades->execute()) {
            $result_grades = $stmt_grades->get_result();
            
            while ($row = $result_grades->fetch_assoc()) {
                $student_id = $row['student_id'];
                $quarter = strtoupper($row['quarter']); 

                if (!isset($grades_by_student[$student_id])) {
                    $grades_by_student[$student_id] = [
                        'Q1' => null, 
                        'Q2' => null, 
                        'Q3' => null, 
                        'Q4' => null
                    ];
                }
                
                if (in_array($quarter, ['Q1', 'Q2', 'Q3', 'Q4'])) {
                    $grades_by_student[$student_id][$quarter] = number_format($row['grade'], 0); 
                }
            }
        } else {
            error_log("Grade Fetch Error: " . $stmt_grades->error);
        }
        $stmt_grades->close();
    } else {
         error_log("Grade Prepare Error: " . $conn->error);
    }
}


if (isset($conn)) {
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Management</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                'sidebar-bg': '#1B3C53',
                'sidebar-text': '#E5E7EB',
                'page-bg': '#F8F9FB', 
                'primary-blue': '#3B82F6', 
                'primary-green': '#10B981',
                'friendly-blue': '#6CB4EE',
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
        }
    }
}
</script>
</head>
<body class="bg-page-bg min-h-screen flex">

<?php 
include '../components/loading_overlay.php'; 
include '../components/sidebar.php'; 
?>

<main class="flex-grow ml-16 md:ml-56 p-8">
    <header class="mb-10 pb-4 flex justify-between items-center border-b">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-900">Student Management</h1>
            <p class="text-gray-500 mt-2">View, enroll, and manage all student records.</p>
        </div>
        
        <button id="openModalBtn" class="flex items-center space-x-2 bg-primary-green hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition duration-150">
            <i data-lucide="user-plus" class="w-5 h-5"></i>
            <span>Enroll New Student</span>
        </button>
    </header>

    <?php 
    // Display Errors
    if ($add_error_details || $fetch_error): 
        $display_error = $add_error_details ?? $fetch_error;
    ?>
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center space-x-2 shadow-sm" role="alert">
            <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0"></i>
            <span><?php echo $display_error; ?></span>
        </div>
    <?php endif; ?>


    <div class="lg:col-span-3">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0 md:space-x-4">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center space-x-2">
                <i data-lucide="users" class="w-7 h-7 text-gray-600"></i>
                <span>All Students (<?php echo count($students); ?>)</span>
            </h2>

            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 w-full md:w-auto">
                <?php 
                include '../components/search_bar.php'; 
                include '../components/section_filter.php'; 
                ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Name</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">M.I.</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Q1</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Q2</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Q3</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Q4</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section / Year</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date of Birth</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="11" class="px-6 py-12 text-center text-gray-500">
                            <?php if (!empty($search_term)): ?>
                                No students found matching "<?php echo htmlspecialchars($search_term); ?>".
                            <?php else: ?>
                                No students found for this filter.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    if (!function_exists('get_grade_class')) {
                        function get_grade_class($grade) {
                            if ($grade === '-' || $grade === null) return 'text-gray-500';
                            return ((int)$grade >= 75) ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold';
                        }
                    }
                    ?>
                    <?php foreach ($students as $student): ?>
                        <?php
                            $student_grades = $grades_by_student[$student['id']] ?? [
                                'Q1' => '-', 
                                'Q2' => '-', 
                                'Q3' => '-', 
                                'Q4' => '-'
                            ];

                            $q1_grade = $student_grades['Q1'] ?? '-';
                            $q2_grade = $student_grades['Q2'] ?? '-';
                            $q3_grade = $student_grades['Q3'] ?? '-';
                            $q4_grade = $student_grades['Q4'] ?? '-';
                            
                            
                            $q1_class = get_grade_class($q1_grade);
                            $q2_class = get_grade_class($q2_grade);
                            $q3_class = get_grade_class($q3_grade);
                            $q4_class = get_grade_class($q4_grade);
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold"><?php echo htmlspecialchars($student['id']); ?></td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($student['last_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-primary-blue"><?php echo htmlspecialchars($student['first_name']); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-center text-gray-500"><?php echo htmlspecialchars($student['middle_initial'] ?? '-'); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-center <?php echo $q1_class; ?>"><?php echo htmlspecialchars($q1_grade); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-center <?php echo $q2_class; ?>"><?php echo htmlspecialchars($q2_grade); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-center <?php echo $q3_class; ?>"><?php echo htmlspecialchars($q3_grade); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-center <?php echo $q4_class; ?>"><?php echo htmlspecialchars($q4_grade); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($student['section_year'] . ' - ' . $student['section_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($student['date_of_birth'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button onclick="initiateEditAction('<?php echo $student['id']; ?>')" class="text-primary-blue hover:text-blue-700 transition duration-150 p-1 rounded-md">
                                    <i data-lucide="square-pen" class="w-5 h-5"></i>
                                </button>
                                <button onclick="confirmDeleteAction('<?php echo $student['id']; ?>', '<?php echo htmlspecialchars($student['last_name'] . ' ' . $student['first_name'], ENT_QUOTES); ?>')" class="text-red-600 hover:text-red-900 transition duration-150 p-1 rounded-md">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php 
include '../components/loading_overlay.php'; 
include '../components/sidebar.php'; 
include '../components/add_student_modal.php'; 
include '../components/edit_student_modal.php'; 
include '../components/success_modal.php'; 
include '../components/delete_confirmation_modal.php'; 


$success_json = json_encode($add_success_details);
$edit_success_json = json_encode($edit_success_details);
$delete_success_json = json_encode($delete_success_details);
$bulk_success_json = json_encode($bulk_success_details);
$edit_data_json = json_encode($student_to_edit);

$sections_list_json = json_encode($sections_list);


echo "<script>const successDetails = {$success_json};</script>";
echo "<script>const editSuccessDetails = {$edit_success_json};</script>"; 
echo "<script>const deleteSuccessDetails = {$delete_success_json};</script>"; 
echo "<script>const bulkSuccessDetails = {$bulk_success_json};</script>"; 
echo "<script>const studentToEdit = {$edit_data_json};</script>"; 
echo "<script>const sectionsList = {$sections_list_json};</script>";

?>
<script src= "../js/student-manage.js"></script>
</body>
</html>