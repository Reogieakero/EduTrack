<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

require_once '../../config/database.php';

/**
 * Generates a new unique student ID in the format YYYY-NNNN.
 * The NNNN is a 4-digit sequential number, reset yearly.
 * @param mysqli $conn The database connection object.
 * @return string|null The new student ID or null on error.
 */
function generate_student_id($conn) {
    // Get the current year (e.g., 2025)
    $current_year = date('Y');
    
    // Pattern to search for IDs of the current year, e.g., '2025-%'
    $search_pattern = $current_year . '-%';
    
    // Query to find the maximum sequence number (NNNN) for the current year
    // It finds the max of the substring after the hyphen, converting it to an unsigned integer.
    $sql = "SELECT MAX(CAST(SUBSTRING(id, 6) AS UNSIGNED)) as max_seq 
            FROM students 
            WHERE id LIKE ?";
            
    $max_seq = 0;
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $search_pattern);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                // max_seq will be null if no records exist for the year, so coalesce to 0
                $max_seq = (int)($row['max_seq'] ?? 0);
            }
        }
        $stmt->close();
    }
    
    // Calculate the next sequence number (NNNN)
    $next_seq = $max_seq + 1;
    
    // Check for overflow (max 9999)
    if ($next_seq > 9999) {
        error_log("Student ID sequence overflow for year " . $current_year);
        return null; 
    }
    
    // Format the sequence number to 4 digits (e.g., 1 -> 0001, 123 -> 0123)
    $formatted_seq = str_pad($next_seq, 4, '0', STR_PAD_LEFT);
    
    // Construct the final student ID (e.g., 2025-0001)
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
$student_to_edit = null; 
if (isset($_SESSION['student_to_edit'])) {
    $student_to_edit = $_SESSION['student_to_edit'];
    unset($_SESSION['student_to_edit']);
}

$students = [];
$sections_list = []; 
$grades_by_student = []; 
$add_error = false;
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
        $middle_initial = trim($_POST['middle_initial'] ?? '');
        $section_id = (int)($_POST['section_id'] ?? 0);
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');

        if (empty($first_name) || empty($last_name) || $section_id <= 0 || empty($date_of_birth)) {
            $add_error = "All required student fields are necessary.";
        } else {
            // Generate the new unique student ID (YYYY-NNNN format)
            $new_id = generate_student_id($conn);
            
            if (is_null($new_id)) {
                $add_error = "ERROR: Could not generate a unique student ID. Sequence overflow or database error.";
            } else {
                $middle_initial = empty($middle_initial) ? null : strtoupper(substr($middle_initial, 0, 1));

                // UPDATE SQL to include 'id' field
                $sql = "INSERT INTO students (id, first_name, last_name, middle_initial, section_id, date_of_birth, enrollment_date) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                
                if ($stmt = $conn->prepare($sql)) {
                    // UPDATE bind_param to include 's' for the new ID
                    $stmt->bind_param("ssssis", $param_id, $param_first_name, $param_last_name, $param_middle_initial, $param_section_id, $param_dob);
                    
                    // Set the parameter for the new ID
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
                        header("Location: students.php");
                        exit;
                    } else {
                        $add_error = "ERROR: Could not execute the insert statement. " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $add_error = "ERROR: Could not prepare the insert statement. " . $conn->error;
                }
            }
        }
    }
    
    if ($_POST['action'] === 'delete_student') {
        $student_id = trim($_POST['student_id'] ?? ''); // Student ID is now a string
        
        if (!empty($student_id)) { // Check if ID is not empty
            $sql_select = "SELECT s.first_name, s.last_name, s.middle_initial, sec.year, sec.name as section_name, sec.teacher FROM students s JOIN sections sec ON s.section_id = sec.id WHERE s.id = ?";
            $deleted_details = null;

            if ($stmt_select = $conn->prepare($sql_select)) {
                $stmt_select->bind_param("s", $student_id); // Changed 'i' to 's'
                $stmt_select->execute();
                $result_select = $stmt_select->get_result();
                $deleted_details = $result_select->fetch_assoc();
                $stmt_select->close();
            }

            if ($deleted_details) {
                $sql_delete = "DELETE FROM students WHERE id = ?";
                if ($stmt_delete = $conn->prepare($sql_delete)) {
                    $stmt_delete->bind_param("s", $student_id); // Changed 'i' to 's'
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
                        $add_error = "ERROR: Could not delete student. " . $stmt_delete->error;
                    }
                    $stmt_delete->close();
                } else {
                    $add_error = "ERROR: Could not prepare delete statement. " . $conn->error;
                }
            } else {
                $add_error = "ERROR: Student not found for deletion.";
            }
        }
    }
    
    if ($_POST['action'] === 'fetch_edit_data') {
        $student_id = trim($_POST['student_id'] ?? ''); // Student ID is now a string
        
        if (!empty($student_id)) { // Check if ID is not empty
            $sql_fetch_student = "SELECT id, first_name, last_name, middle_initial, section_id, date_of_birth FROM students WHERE id = ?";
            
            if ($stmt_fetch = $conn->prepare($sql_fetch_student)) {
                $stmt_fetch->bind_param("s", $student_id); // Changed 'i' to 's'
                if ($stmt_fetch->execute()) {
                    $result_fetch = $stmt_fetch->get_result();
                    $student_data = $result_fetch->fetch_assoc();
                    $stmt_fetch->close();
                    
                    if ($student_data) {
                        $_SESSION['student_to_edit'] = $student_data;
                        header("Location: students.php");
                        exit;
                    } else {
                        $add_error = "ERROR: Student not found for editing.";
                    }
                } else {
                    $add_error = "ERROR: Could not fetch student data for editing. " . $stmt_fetch->error;
                }
            } else {
                $add_error = "ERROR: Could not prepare fetch statement for editing. " . $conn->error;
            }
        }
    }

    if ($_POST['action'] === 'edit_student') {
        $student_id = trim($_POST['student_id'] ?? ''); // Student ID is now a string
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $middle_initial = trim($_POST['middle_initial'] ?? '');
        $section_id = (int)($_POST['section_id'] ?? 0);
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');

        if (empty($student_id) || empty($first_name) || empty($last_name) || $section_id <= 0 || empty($date_of_birth)) { // Check for empty string ID
            $add_error = "All required student fields and ID are required for update.";
        } else {
            $middle_initial = empty($middle_initial) ? null : strtoupper(substr($middle_initial, 0, 1));
            
            $sql = "UPDATE students SET first_name = ?, last_name = ?, middle_initial = ?, section_id = ?, date_of_birth = ? WHERE id = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                // Changed binding types: first 5 are strings/ints, last one is 's' for student_id
                $stmt->bind_param("sssisss", $param_first_name, $param_last_name, $param_middle_initial, $param_section_id, $param_dob, $param_id);
                
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
                    $add_error = "ERROR: Could not execute the update statement. " . $stmt->error;
                }
                $stmt->close();
            } else {
                $add_error = "ERROR: Could not prepare the update statement. " . $conn->error;
            }
        }
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
    // Added s.id to the search clause
    $where_clauses[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ? OR CONCAT(s.last_name, ' ', s.first_name) LIKE ? OR s.id LIKE ?)";
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern; // Added search by the new ID format
    $types .= 'sssss'; // Added 's' for the new ID search
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
    
    // Using placeholders for prepared statement with dynamic string IDs
    $in_clause_placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    
    $sql_fetch_grades = "SELECT student_id, quarter, grade FROM grades WHERE student_id IN ($in_clause_placeholders)";

    // All student IDs are now strings ('s')
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

    <?php if ($add_error || $fetch_error): ?>
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center space-x-2 shadow-sm" role="alert">
            <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0"></i>
            <span><?php echo $add_error ?? $fetch_error; ?></span>
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
$edit_data_json = json_encode($student_to_edit);

$sections_list_json = json_encode($sections_list);


echo "<script>const successDetails = {$success_json};</script>";
echo "<script>const editSuccessDetails = {$edit_success_json};</script>"; 
echo "<script>const deleteSuccessDetails = {$delete_success_json};</script>"; 
echo "<script>const studentToEdit = {$edit_data_json};</script>"; 
echo "<script>const sectionsList = {$sections_list_json};</script>";

?>
<script src= "../js/student-manage.js"></script>
</body>
</html>