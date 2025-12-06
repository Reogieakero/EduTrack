<?php
// Include the controller to handle logic and fetch data
include '../controllers/subject_controller.php'; 

// Ensure variables are defined even if controller didn't run fully
$subjects = $subjects ?? [];
$valid_years = $year_levels ?? [7, 8, 9, 10, 11, 12]; 
// Prepend 'all' and convert to strings as expected by year_filter.php
$valid_years = array_map(fn($y) => "Grade {$y}", $valid_years);
array_unshift($valid_years, 'all');

$add_success_details = $add_success_details ?? null; 
$edit_success_details = $edit_success_details ?? null; 
$delete_success_details = $delete_success_details ?? null; 
$subject_to_edit = $subject_to_edit ?? null;
$fetch_error = $fetch_error ?? null;
$operation_error = $_SESSION['operation_error'] ?? null;
unset($_SESSION['operation_error']); // Clear general operation errors

// Use GET parameters for filtering (Use 'year' for consistency)
$selected_year = $_GET['year'] ?? 'all'; 
// $search_term variable and logic has been removed

// For displaying to the filter component, 'all' should be 'all', otherwise it should be 'Grade X'
$selected_year_display = ($selected_year === 'all') ? 'all' : "Grade {$selected_year}"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subject Management</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
// Your existing Tailwind config
tailwind.config = {
    theme: {
        extend: {
            colors: {
                'sidebar-bg': '#1B3C53',
                'sidebar-text': '#E5E7EB',
                'page-bg': '#F8F9FB', 
                'primary-blue': '#3B82F6', 
                'primary-green': '#10B981',
                'friendly-blue': '#60A5FA',
                'error-red': '#EF4444',
                'neutral-light': '#E5E7EB',
            },
        }
    }
}
</script>
</head>
<body class="bg-page-bg flex h-screen overflow-hidden">

<?php 
include '../components/sidebar.php'; 
?>

<div class="flex-1 flex flex-col overflow-hidden md:ml-56">
    <main class="flex-1 overflow-x-hidden overflow-y-auto p-8">
        
        <header class="mb-10 pb-4 flex justify-between items-center border-b">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-900">Subject Management</h1>
            <p class="text-gray-500 mt-2">View, manage, and assign all subjects.</p>
        </div>
        
        <button id="openModalBtn" class="flex items-center space-x-2 bg-primary-green hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition duration-150">
            <i data-lucide="book-open-check" class="w-5 h-5"></i>
            <span>Add New Subject</span>
        </button>
    </header>
        <?php if ($fetch_error || $operation_error): ?>
            <div class="bg-red-100 border border-error-red text-error-red px-4 py-3 rounded-lg relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($fetch_error ?? $operation_error); ?></span>
            </div>
        <?php endif; ?>

        <div class="flex justify-end mb-6"> 
            <?php 
                // Define variables required by year_filter.php
                $selected_year = $selected_year_display; // Pass 'all' or 'Grade X'
                // $valid_years is already defined above
                include '../components/year_filter.php'; 
            ?>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                Subject List 
                <span id="subjectCountDisplay" class="text-base font-normal text-gray-500">(<?= count($subjects); ?>)</span>
            </h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Code</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year Level</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="subjectsTableBody">
                    <?php if (empty($subjects)): ?>
                        <tr id="noResultsRow">
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                No subjects found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subjects as $subject): ?>
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($subject['subject_code']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($subject['subject_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Grade <?= htmlspecialchars($subject['year_level']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button 
                                    class="text-primary-green hover:text-green-700 p-2 rounded-full transition duration-150 edit-subject-btn" 
                                    data-id="<?= htmlspecialchars($subject['id']); ?>"
                                    data-code="<?= htmlspecialchars($subject['subject_code']); ?>"
                                    data-name="<?= htmlspecialchars($subject['subject_name']); ?>"
                                    data-year="<?= htmlspecialchars($subject['year_level']); ?>"
                                    title="Edit Subject"
                                >
                                    <i data-lucide="pencil" class="w-5 h-5"></i>
                                </button>
                                <button 
                                    class="text-error-red hover:text-red-700 p-2 rounded-full transition duration-150 delete-subject-btn"
                                    data-id="<?= htmlspecialchars($subject['id']); ?>"
                                    data-name="<?= htmlspecialchars($subject['subject_name']); ?> (Grade <?= htmlspecialchars($subject['year_level']); ?>)"
                                    title="Delete Subject"
                                >
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
</div>

<?php 
include '../components/loading_overlay.php'; 
include '../components/add_subject_modal.php'; 
include '../components/edit_subject_modal.php'; 
include '../components/success_modal.php'; 
include '../components/delete_confirmation_modal.php'; 
?>

<form id="deleteConfirmationForm" method="POST" action="subjects.php" class="hidden">
    <input type="hidden" name="action" id="delete_action_input" value="">
    <input type="hidden" name="subject_id" id="delete_id_input" value="">
</form>

<?php
$add_success_json = json_encode($add_success_details);
$edit_success_json = json_encode($edit_success_details);
$delete_success_json = json_encode($delete_success_details);
$year_levels_for_modal = [7, 8, 9, 10, 11, 12];
$year_levels_json = json_encode($year_levels_for_modal);

echo "<script>const successDetails = {$add_success_json};</script>";
echo "<script>const editSuccessDetails = {$edit_success_json};</script>"; 
echo "<script>const deleteSuccessDetails = {$delete_success_json};</script>"; 
echo "<script>const yearLevels = {$year_levels_json};</script>"; 
?>

<script>
    // Overriding the function defined in year_filter.php
    function handleFilterSelect(year_display) {
        let year_value = 'all';
        if (year_display !== 'all') {
            // Extracts the number (e.g., '7') from the string 'Grade 7'
            year_value = year_display.replace('Grade ', '');
        }
        
        const overlay = document.getElementById('loadingOverlay');
        const loadingText = document.getElementById('loadingMessageText'); 

        if (overlay) {
            if (loadingText) {
                loadingText.textContent = 'Loading Subjects by Year...'; // Set a specific filter message
            }
            overlay.classList.remove('hidden');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
            }, 10);
        }
        
        // Perform the redirect/filter action. No search term is included.
        const redirectUrl = 'subjects.php?year=' + year_value;
        
        window.location.href = redirectUrl;
    }
</script>

<script src="../js/subject-manage.js"></script>
</body>
</html>