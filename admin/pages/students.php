<?php
session_start();

// 1. Authentication Check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

// 2. Database Connection (Path: EDUTRACK/admin/pages/ -> EDUTRACK/config/)
require_once '../../config/database.php';

// Success/Error Message Handling (Similar to sections.php)
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
// Variable to hold student data for pre-filling the edit modal
$student_to_edit = null; 
if (isset($_SESSION['student_to_edit'])) {
    $student_to_edit = $_SESSION['student_to_edit'];
    unset($_SESSION['student_to_edit']);
}

// State variables
$students = [];
$sections_list = []; 
$add_error = false;
$fetch_error = false;

// 3. Filtering and Selection
// This variable MUST be defined for section_filter.php to work correctly.
$selected_section_id = $_GET['section_id'] ?? 'all';

// Fetch all sections for filter and add/edit forms
$sql_fetch_sections = "SELECT id, year, name, teacher FROM sections ORDER BY year ASC, name ASC";
if ($stmt_sections = $conn->prepare($sql_fetch_sections)) {
    if ($stmt_sections->execute()) {
        $result_sections = $stmt_sections->get_result();
        while ($row = $result_sections->fetch_assoc()) {
            // Store section data using ID as key
            $sections_list[$row['id']] = $row;
        }
    }
    $stmt_sections->close();
}


// 5. POST Request Handling (Add/Delete/Edit Student)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // --- Handle Add Student ---
    if ($_POST['action'] === 'add_student') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $section_id = (int)($_POST['section_id'] ?? 0);
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');

        // Basic validation
        if (empty($first_name) || empty($last_name) || $section_id <= 0 || empty($date_of_birth)) {
            $add_error = "All student fields are required.";
        } else {
            $sql = "INSERT INTO students (first_name, last_name, section_id, date_of_birth, enrollment_date) VALUES (?, ?, ?, ?, NOW())";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssis", $param_first_name, $param_last_name, $param_section_id, $param_dob);
                
                $param_first_name = $first_name;
                $param_last_name = $last_name;
                $param_section_id = $section_id;
                $param_dob = $date_of_birth;
                
                if ($stmt->execute()) {
                    // Get section details for the success message
                    $section_info = $sections_list[$section_id] ?? ['name' => 'N/A', 'year' => 'N/A', 'teacher' => 'N/A'];
                    
                    $_SESSION['add_success_details'] = [
                        'name' => $first_name . ' ' . $last_name,
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
    
    // --- Handle Delete Student ---
    if ($_POST['action'] === 'delete_student') {
        $student_id = (int)($_POST['student_id'] ?? 0);
        
        if ($student_id > 0) {
            // 1. Select student details for the success message
            $sql_select = "SELECT s.first_name, s.last_name, sec.year, sec.name as section_name, sec.teacher FROM students s JOIN sections sec ON s.section_id = sec.id WHERE s.id = ?";
            $deleted_details = null;

            if ($stmt_select = $conn->prepare($sql_select)) {
                $stmt_select->bind_param("i", $student_id);
                $stmt_select->execute();
                $result_select = $stmt_select->get_result();
                $deleted_details = $result_select->fetch_assoc();
                $stmt_select->close();
            }

            if ($deleted_details) {
                // 2. Delete the student
                $sql_delete = "DELETE FROM students WHERE id = ?";
                if ($stmt_delete = $conn->prepare($sql_delete)) {
                    $stmt_delete->bind_param("i", $student_id);
                    if ($stmt_delete->execute()) {
                        $_SESSION['delete_success_details'] = [
                            'name' => $deleted_details['first_name'] . ' ' . $deleted_details['last_name'],
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
    
    // --- Handle Fetch Edit Data ---
    if ($_POST['action'] === 'fetch_edit_data') {
        $student_id = (int)($_POST['student_id'] ?? 0);
        
        if ($student_id > 0) {
            // Select all necessary fields for the edit form
            $sql_fetch_student = "SELECT id, first_name, last_name, section_id, date_of_birth FROM students WHERE id = ?";
            
            if ($stmt_fetch = $conn->prepare($sql_fetch_student)) {
                $stmt_fetch->bind_param("i", $student_id);
                if ($stmt_fetch->execute()) {
                    $result_fetch = $stmt_fetch->get_result();
                    $student_data = $result_fetch->fetch_assoc();
                    $stmt_fetch->close();
                    
                    if ($student_data) {
                        // Store student data in session to be picked up by JS on redirect
                        $_SESSION['student_to_edit'] = $student_data;
                        // Redirect back to students.php to render the modal with pre-filled data
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

    // --- Handle Edit Student Submission ---
    if ($_POST['action'] === 'edit_student') {
        $student_id = (int)($_POST['student_id'] ?? 0);
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $section_id = (int)($_POST['section_id'] ?? 0);
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');

        // Basic validation
        if ($student_id <= 0 || empty($first_name) || empty($last_name) || $section_id <= 0 || empty($date_of_birth)) {
            $add_error = "All student fields and ID are required for update.";
        } else {
            $sql = "UPDATE students SET first_name = ?, last_name = ?, section_id = ?, date_of_birth = ? WHERE id = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssisi", $param_first_name, $param_last_name, $param_section_id, $param_dob, $param_id);
                
                $param_first_name = $first_name;
                $param_last_name = $last_name;
                $param_section_id = $section_id;
                $param_dob = $date_of_birth;
                $param_id = $student_id;
                
                if ($stmt->execute()) {
                    // Get section details for the success message
                    $section_info = $sections_list[$section_id] ?? ['name' => 'N/A', 'year' => 'N/A', 'teacher' => 'N/A'];
                    
                    $_SESSION['edit_success_details'] = [
                        'name' => $first_name . ' ' . $last_name,
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


// 4. Student Data Fetching (Re-run if no POST-redirect occurred)
$sql_fetch = "SELECT s.*, sec.year as section_year, sec.name as section_name, sec.teacher as teacher_name FROM students s JOIN sections sec ON s.section_id = sec.id";
$where_clause = '';
$params = [];
$types = '';

if ($selected_section_id !== 'all' && is_numeric($selected_section_id)) {
    $where_clause = " WHERE s.section_id = ?";
    $params[] = $selected_section_id;
    $types .= 'i';
}

$sql_fetch .= $where_clause . " ORDER BY sec.year ASC, sec.name ASC, s.last_name ASC, s.first_name ASC";


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
// Use the same Tailwind config as sections.php
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
// Include existing components
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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center space-x-2 mb-4 sm:mb-0">
                <i data-lucide="users" class="w-7 h-7 text-gray-600"></i>
                <span>All Students (<?php echo count($students); ?>)</span>
            </h2>

            <?php include '../components/section_filter.php'; ?>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section / Year</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date of Birth</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">No students found for this filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold"><?php echo htmlspecialchars($student['id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary-blue"><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($student['section_year'] . ' - ' . $student['section_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($student['date_of_birth'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button onclick="initiateEditAction(<?php echo $student['id']; ?>)" class="text-primary-blue hover:text-blue-700 transition duration-150 p-1 rounded-md">
                                    <i data-lucide="square-pen" class="w-5 h-5"></i>
                                </button>
                                <button onclick="confirmDeleteAction(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['last_name'] . ' ' . $student['first_name']); ?>')" class="text-red-600 hover:text-red-900 transition duration-150 p-1 rounded-md">
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
// Include necessary modal components
include '../components/add_student_modal.php'; 
include '../components/edit_student_modal.php'; // <-- NEW
include '../components/success_modal.php'; 
include '../components/delete_confirmation_modal.php'; 


$success_json = json_encode($add_success_details);
$edit_success_json = json_encode($edit_success_details);
$delete_success_json = json_encode($delete_success_details);
$edit_data_json = json_encode($student_to_edit);

// Pass section list to be used by the modal component
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