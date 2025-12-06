<?php

include '../controllers/student_controller.php';

if (!function_exists('get_grade_class')) {
    function get_grade_class($grade) {
        if ($grade === '-' || $grade === null) return 'text-gray-500';
        return ((int)$grade >= 75) ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold';
    }
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