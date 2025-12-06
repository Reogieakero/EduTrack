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
$delete_success_details = null; 
$student_to_edit = null; 

// State variables
$students = [];
$sections_list = []; 
$add_error = false;
$fetch_error = false;

// 3. Filtering and Selection
$selected_section_id = $_GET['section_id'] ?? 'all';

// Fetch all sections for filter and add/edit forms
// Assuming 'teacher' column exists in sections for the modal
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

// 4. Student Data Fetching
// *** MODIFICATION START: Updated SQL query to include quarterly grades ***
$sql_fetch = "
    SELECT 
        s.*, 
        sec.year as section_year, 
        sec.name as section_name,
        g1.grade AS q1_grade,
        g2.grade AS q2_grade,
        g3.grade AS q3_grade,
        g4.grade AS q4_grade
    FROM students s 
    JOIN sections sec ON s.section_id = sec.id
    LEFT JOIN grades g1 ON s.id = g1.student_id AND s.section_id = g1.section_id AND g1.quarter = 'Q1'
    LEFT JOIN grades g2 ON s.id = g2.student_id AND s.section_id = g2.section_id AND g2.quarter = 'Q2'
    LEFT JOIN grades g3 ON s.id = g3.student_id AND s.section_id = g3.section_id AND g3.quarter = 'Q3'
    LEFT JOIN grades g4 ON s.id = g4.student_id AND s.section_id = g4.section_id AND g4.quarter = 'Q4'
";
// *** MODIFICATION END ***

$where_clause = '';
$params = [];
$types = '';

if ($selected_section_id !== 'all' && is_numeric($selected_section_id)) {
    $where_clause = " WHERE s.section_id = ?";
    $params[] = $selected_section_id;
    $types .= 'i';
}

// *** MODIFICATION: Changed ORDER BY to sort by Section Year and Name first ***
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


// 5. POST Request Handling (Add Student)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_student') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $section_id = (int)($_POST['section_id'] ?? 0);
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');

    // Basic validation
    if (empty($first_name) || empty($last_name) || $section_id <= 0 || empty($date_of_birth)) {
        $add_error = "All student fields are required.";
    } else {
        // Assuming enrollment_date is set to the current timestamp in the database
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
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Q1</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Q2</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Q3</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Q4</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">No students found for this filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold"><?php echo htmlspecialchars($student['id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary-blue"><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($student['section_year'] . ' - ' . $student['section_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($student['date_of_birth'])); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700 text-center"><?php echo htmlspecialchars($student['q1_grade'] ?? '-'); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700 text-center"><?php echo htmlspecialchars($student['q2_grade'] ?? '-'); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700 text-center"><?php echo htmlspecialchars($student['q3_grade'] ?? '-'); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700 text-center"><?php echo htmlspecialchars($student['q4_grade'] ?? '-'); ?></td>
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
include '../components/success_modal.php'; 
include '../components/delete_confirmation_modal.php'; 


$success_json = json_encode($add_success_details);
$edit_success_json = json_encode($edit_success_details); // Pass null placeholders
$delete_success_json = json_encode($delete_success_details); // Pass null placeholders
$edit_data_json = json_encode($student_to_edit); // Pass null placeholder

// Pass section list to be used by the modal component
$sections_list_json = json_encode($sections_list);


echo "<script>const successDetails = {$success_json};</script>";
echo "<script>const editSuccessDetails = {$edit_success_json};</script>"; 
echo "<script>const deleteSuccessDetails = {$delete_success_json};</script>"; 
echo "<script>const studentToEdit = {$edit_data_json};</script>";
echo "<script>const sectionsList = {$sections_list_json};</script>";

// Correct path to the new JS file
?>
<script src= "../js/student-manage.js"></script>
</body>
</html>