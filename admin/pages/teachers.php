<?php
include '../controllers/teacher_controller.php'; 

$teachers = $teachers ?? [];
$sections_list = $sections_list ?? [];
$add_success_details = $add_success_details ?? null; 
$edit_success_details = $edit_success_details ?? null; 
$delete_success_details = $delete_success_details ?? null; 
$assign_success_details = $assign_success_details ?? null; 
$teacher_to_edit = $teacher_to_edit ?? null;
$add_error = $add_error ?? null;
$fetch_error = $fetch_error ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Management</title>
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
            <h1 class="text-4xl font-extrabold text-gray-900">Teacher Management</h1>
            <p class="text-gray-500 mt-2">View, register, and assign teachers to sections.</p>
        </div>
        
        <button id="openAddModalBtn" class="flex items-center space-x-2 bg-primary-blue hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition duration-150">
            <i data-lucide="user-plus" class="w-5 h-5"></i>
            <span>Register New Teacher</span>
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
                <span>Registered Teachers (<?php echo count($teachers); ?>)</span>
            </h2>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Section</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($teachers)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No teachers have been registered yet. Click "Register New Teacher" to begin.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($teachers as $teacher): ?>
                        <?php 
                            $assigned_section = $teacher['section_name'] 
                                ? htmlspecialchars($teacher['section_year'] . ' - ' . $teacher['section_name'])
                                : '<span class="text-red-500 font-medium">None Assigned</span>';
                            $full_name_encoded = htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name'], ENT_QUOTES);
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($teacher['id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($teacher['last_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-primary-blue"><?php echo htmlspecialchars($teacher['first_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($teacher['email']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                <?php echo $assigned_section; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2 flex justify-end">
                                <button 
                                    onclick="initiateAssignAction(
                                        '<?php echo $teacher['id']; ?>', 
                                        '<?php echo $full_name_encoded; ?>',
                                        '<?php echo $teacher['assigned_section_id'] ?? 0; ?>' 
                                    )" 
                                    class="text-primary-green hover:text-green-700 transition duration-150 p-1 rounded-md" 
                                    title="Assign Section"
                                >
                                    <i data-lucide="link" class="w-5 h-5"></i>
                                </button>
                                <button onclick="initiateEditAction('<?php echo $teacher['id']; ?>')" class="text-primary-blue hover:text-blue-700 transition duration-150 p-1 rounded-md" title="Edit Teacher">
                                    <i data-lucide="square-pen" class="w-5 h-5"></i>
                                </button>
                                <button onclick="confirmDeleteAction('<?php echo $teacher['id']; ?>', '<?php echo $full_name_encoded; ?>')" class="text-red-600 hover:text-red-900 transition duration-150 p-1 rounded-md" title="Delete Teacher">
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
include '../components/edit_teacher_modal.php'; 
include '../components/teacher_assignment_modal.php'; 
include '../components/add_teacher_modal.php'; 
include '../components/success_modal.php'; 
include '../components/delete_confirmation_modal.php'; 
?>

<form id="deleteConfirmationForm" method="POST" action="teachers.php" class="hidden">
    <input type="hidden" name="action" id="delete_action_input" value="">
    <input type="hidden" name="teacher_id" id="delete_id_input" value="">
</form>
<?php
// CRITICAL: These variables must be set by the controller to trigger the success modals
$teachers_list_json = json_encode($teachers);
$add_success_json = json_encode($add_success_details);
$edit_success_json = json_encode($edit_success_details); 
$delete_success_json = json_encode($delete_success_details); 
$assign_success_json = json_encode($assign_success_details); 
$edit_data_json = json_encode($teacher_to_edit);
$sections_list_json = json_encode($sections_list); 

echo "<script>const teachersList = {$teachers_list_json};</script>";
echo "<script>const successDetails = {$add_success_json};</script>";
echo "<script>const editSuccessDetails = {$edit_success_json};</script>"; // Trigger for Update Success
echo "<script>const deleteSuccessDetails = {$delete_success_json};</script>"; 
echo "<script>const assignSuccessDetails = {$assign_success_json};</script>"; 
echo "<script>const teacherToEdit = {$edit_data_json};</script>";
echo "<script>const sectionsList = {$sections_list_json};</script>";
?>

<script src= "../js/teacher-manage.js"></script> 
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
</body>
</html>