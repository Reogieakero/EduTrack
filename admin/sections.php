<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

// NOTE: Ensure your database connection file is correctly configured and placed.
require_once '../config/database.php';

$add_success_details = null;
if (isset($_SESSION['add_success_details'])) {
    $add_success_details = $_SESSION['add_success_details'];
    unset($_SESSION['add_success_details']); 
} 

// --- EDIT DATA STORAGE ---
$section_to_edit = null;
if (isset($_SESSION['section_to_edit'])) {
    $section_to_edit = $_SESSION['section_to_edit'];
    unset($_SESSION['section_to_edit']);
}

// --- Session variable for successful UPDATE details ---
$edit_success_details = null; 
if (isset($_SESSION['edit_success_details'])) {
    $edit_success_details = $_SESSION['edit_success_details'];
    unset($_SESSION['edit_success_details']);
}
// --- Session variable for successful DELETE details ---
$delete_success_details = null; 
if (isset($_SESSION['delete_success_details'])) {
    $delete_success_details = $_SESSION['delete_success_details'];
    unset($_SESSION['delete_success_details']);
}
// --- END EDIT/DELETE DATA STORAGE ---

// --- REMOVE AUTH ERROR SESSION VARIABLES (Admin Auth Removed) ---
unset($_SESSION['auth_error']);
unset($_SESSION['auth_action']);
unset($_SESSION['auth_section_id']);
// --- END REMOVE AUTH ERROR SESSION VARIABLES ---


$sections = []; 
$add_error = false;
$fetch_error = false;

// --- YEAR FILTER LOGIC ---
$selected_year = $_GET['year'] ?? 'all'; 
$valid_years = ['all', 'Year 7', 'Year 8', 'Year 9', 'Year 10', 'Year 11', 'Year 12'];

// Sanitize the selected year
if (!in_array($selected_year, $valid_years)) {
    $selected_year = 'all'; 
}
// --- END YEAR FILTER LOGIC ---


// --- HANDLE POST REQUEST TO ADD A NEW SECTION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_section') {
    $new_section_name = trim($_POST['section_name'] ?? '');
    $new_teacher_name = trim($_POST['teacher_name'] ?? '');
    $new_section_year = trim($_POST['section_year'] ?? '');
    
    if (empty($new_section_name) || empty($new_teacher_name) || empty($new_section_year)) {
        $add_error = "Section Name, Assigned Teacher, and Academic Year are all required.";
    } else {
        // Assuming your 'sections' table has a `created_at` column with a default of CURRENT_TIMESTAMP
        $sql = "INSERT INTO sections (year, name, teacher) VALUES (?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $param_year, $param_name, $param_teacher);
            
            $param_year = $new_section_year;
            $param_name = $new_section_name;
            $param_teacher = $new_teacher_name;
            
            if ($stmt->execute()) {
                $_SESSION['add_success_details'] = [
                    'name' => $new_section_name,
                    'year' => $new_section_year,
                    'teacher' => $new_teacher_name
                ];
                header("Location: sections.php");
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
// --- END POST HANDLING (ADD SECTION) ---

// --- HANDLE POST REQUEST TO EDIT/DELETE/UPDATE A SECTION (Auth Removed) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['action']) && ($_POST['action'] === 'delete_section' || $_POST['action'] === 'edit_section' || $_POST['action'] === 'update_section'))) {
    
    $action_to_perform = $_POST['action'];
    $section_id = (int)($_POST['section_id'] ?? 0);
    
    // -----------------------------------------------------
    // 1. Execute Actions
    // -----------------------------------------------------
    if ($action_to_perform === 'delete_section' && $section_id > 0) {
        // 1. Fetch details before deletion for success message
        $sql_select = "SELECT year, name, teacher FROM sections WHERE id = ?";
        $deleted_details = null;

        if ($stmt_select = $conn->prepare($sql_select)) {
            $stmt_select->bind_param("i", $section_id);
            $stmt_select->execute();
            $result_select = $stmt_select->get_result();
            $deleted_details = $result_select->fetch_assoc();
            $stmt_select->close();
        }

        // 2. Execute DELETE Logic
        if ($deleted_details) {
            $sql_delete = "DELETE FROM sections WHERE id = ?";
            if ($stmt_delete = $conn->prepare($sql_delete)) {
                $stmt_delete->bind_param("i", $section_id);
                if ($stmt_delete->execute()) {
                    // Store structured data for the success modal
                    $_SESSION['delete_success_details'] = [
                        'name' => $deleted_details['name'],
                        'year' => $deleted_details['year'],
                        'teacher' => $deleted_details['teacher']
                    ];
                    header("Location: sections.php");
                    exit;
                } else {
                    $add_error = "ERROR: Could not delete section. " . $stmt_delete->error;
                }
                $stmt_delete->close();
            }
        } else {
             $add_error = "ERROR: Section not found for deletion.";
        }
    }
    
    if ($action_to_perform === 'edit_section' && $section_id > 0) {
        // --- Fetch data to populate the modal ---
        $sql = "SELECT id, year, name, teacher FROM sections WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $section_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                // Store the entire section data in a session variable
                $_SESSION['section_to_edit'] = $row;
            }
            $stmt->close();
        }
        // Redirect to clean the POST and trigger the modal via JS
        header("Location: sections.php");
        exit;
    }

    if ($action_to_perform === 'update_section' && $section_id > 0) {
        // --- Execute UPDATE Logic (triggered by the edit modal form) ---
        $updated_name = trim($_POST['edit_section_name'] ?? '');
        $updated_teacher = trim($_POST['edit_teacher_name'] ?? '');
        $updated_year = trim($_POST['edit_section_year'] ?? '');

        if (!empty($updated_name) && !empty($updated_teacher) && !empty($updated_year)) {
             $sql = "UPDATE sections SET year = ?, name = ?, teacher = ? WHERE id = ?";
             if ($stmt = $conn->prepare($sql)) {
                 $stmt->bind_param("sssi", $updated_year, $updated_name, $updated_teacher, $section_id);
                 if ($stmt->execute()) {
                     // Store structured data for the success modal
                     $_SESSION['edit_success_details'] = [
                         'name' => $updated_name,
                         'year' => $updated_year,
                         'teacher' => $updated_teacher
                     ];
                     header("Location: sections.php");
                     exit;
                 } else {
                     $add_error = "ERROR: Could not update section. " . $stmt->error;
                 }
                 $stmt->close();
             }
        } else {
            $add_error = "ERROR: All fields are required for section update.";
        }
    }
}
// --- END ADMIN ACTION HANDLING ---


// --- FETCH ALL SECTIONS (LATEST FIRST) WITH FILTERING ---
$sql_fetch = "SELECT id, year, name, teacher, created_at FROM sections";
$where_clause = '';
$params = [];
$types = '';

if ($selected_year !== 'all') {
    $where_clause = " WHERE year = ?";
    $params[] = $selected_year;
    $types .= 's';
}

$sql_fetch .= $where_clause . " ORDER BY created_at DESC, year ASC, name ASC";


if ($stmt = $conn->prepare($sql_fetch)) {
    if (!empty($params)) {
        // Use call_user_func_array for dynamic parameter binding
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
                $sections[] = [
                    'id' => $row['id'],
                    'year' => $row['year'],
                    'name' => $row['name'],
                    'teacher' => $row['teacher'],
                    'created_at' => $row['created_at'], 
                    'students' => [] // Mocked student data
                ];
            }
        }
    } else {
        $fetch_error = "ERROR: Could not execute the fetch statement. " . $stmt->error;
    }
    $stmt->close();
} else {
    $fetch_error = "ERROR: Could not prepare the fetch statement. " . $conn->error;
}


// Check if $conn is still open before closing
if (isset($conn)) {
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Section Management</title>
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
// --- COMPONENT: Full-Page Loading Overlay ---
include 'components/loading_overlay.php'; 
?>

<?php 
// NOTE: These files must exist in their respective paths
include 'components/sidebar.php'; 
?>

<main class="flex-grow ml-16 md:ml-56 p-8">
    <header class="mb-10 pb-4 flex justify-between items-center border-b">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-900">Section Management</h1>
            <p class="text-gray-500 mt-2">View, add, and manage sections and associated students.</p>
        </div>
        
        <button id="openModalBtn" class="flex items-center space-x-2 bg-primary-blue hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition duration-150">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span>Add New Section</span>
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
                <i data-lucide="layers" class="w-7 h-7 text-gray-600"></i>
                <span>All Sections (<?php echo count($sections); ?>)</span>
            </h2>

            <?php include 'components/year_filter.php'; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($sections)): ?>
            <div class="lg:col-span-3 text-center p-12 bg-white rounded-xl shadow-lg border border-gray-200">
                <i data-lucide="inbox" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-800">No Sections Found</h3>
                <p class="text-gray-500 mt-2">
                    Click the "Add New Section" button above to create your first section.
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($sections as $section): 
                // Assuming 'components/section_card.php' exists
                include 'components/section_card.php'; 
            endforeach; ?>
        <?php endif; ?>
        </div>
    </div>
</main>

<?php 
// NOTE: These files must exist in their respective paths
include 'components/add_section_modal.php'; 
include 'components/success_modal.php'; 
include 'components/edit_section_modal.php'; 
include 'components/delete_confirmation_modal.php'; 


$success_json = json_encode($add_success_details);
$edit_success_json = json_encode($edit_success_details); 
$delete_success_json = json_encode($delete_success_details); 
$edit_data_json = json_encode($section_to_edit); 

echo "<script>const successDetails = {$success_json};</script>";
echo "<script>const editSuccessDetails = {$edit_success_json};</script>"; 
echo "<script>const deleteSuccessDetails = {$delete_success_json};</script>"; 
echo "<script>const sectionToEdit = {$edit_data_json};</script>";
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    // --- Loading Overlay Fix/Cleanup ---
    const overlay = document.getElementById('loadingOverlay');
    if (overlay && !overlay.classList.contains('hidden')) {
        overlay.classList.add('opacity-0');
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);
    }
    // --- END Loading Overlay Fix/Cleanup ---

    // --- Modal Elements ---
    const modal = document.getElementById('addSectionModal');
    const modalContent = document.getElementById('modalContent');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    
    const successModal = document.getElementById('successModal');
    const successModalContent = document.getElementById('successModalContent');
    const closeSuccessModalBtn = document.getElementById('closeSuccessModalBtn');

    const addSectionForm = document.getElementById('addSectionForm');
    const saveSectionBtn = document.getElementById('saveSectionBtn');
    const saveIcon = document.getElementById('saveIcon');
    const saveText = document.getElementById('saveText');
    const loadingSpinner = document.getElementById('loadingSpinner');

    // --- EDIT MODAL ELEMENTS ---
    const editModal = document.getElementById('editSectionModal');
    const editModalContent = document.getElementById('editModalContent');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const editForm = document.getElementById('editSectionForm');
    const editSectionIdInput = document.getElementById('edit_section_id');
    const editSectionNameInput = document.getElementById('edit_modal_section_name');
    const editTeacherNameInput = document.getElementById('edit_modal_teacher_name');
    const editYearRadios = document.getElementsByName('edit_section_year');

    // --- DELETE MODAL ELEMENTS ---
    const deleteModal = document.getElementById('deleteConfirmationModal');
    const deleteModalContent = document.getElementById('deleteModalContent');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteSectionNameSpan = document.getElementById('deleteSectionName');
    
    // --- LOADING TEXT ELEMENT ---
    const loadingMessageText = document.getElementById('loadingMessageText');

    // --- SUCCESS DESCRIPTION ELEMENT (NEW) ---
    const successModalDescription = document.getElementById('success-modal-description');


    // --- Generic Modal Functions ---
    const openModal = (targetModal, targetContent) => {
        targetModal.classList.remove('hidden');
        setTimeout(() => {
            targetModal.classList.remove('opacity-0');
            targetContent.classList.remove('scale-95', 'opacity-0');
            targetContent.classList.add('scale-100', 'opacity-100');
            document.body.style.overflow = 'hidden'; 
        }, 10);
    };

    const closeModal = (targetModal, targetContent) => {
        targetContent.classList.remove('scale-100', 'opacity-100');
        targetContent.classList.add('scale-95', 'opacity-0');
        targetModal.classList.add('opacity-0');
        
        setTimeout(() => {
            targetModal.classList.add('hidden');
            // Check all modals before resetting overflow (UPDATED CHECK)
            if(modal.classList.contains('hidden') && successModal.classList.contains('hidden') && editModal.classList.contains('hidden') && deleteModal.classList.contains('hidden')) {
                document.body.style.overflow = '';
            }
        }, 300); 
    };

    // Function to trigger the PHP edit action 
    window.initiateEditAction = function(sectionId) {
        // ... (Code to submit edit action)
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'sections.php';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'edit_section'; 
        form.appendChild(actionInput);

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'section_id';
        idInput.value = sectionId;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
    };

    // Function to open the confirmation modal 
    window.confirmDeleteAction = function(sectionId, sectionName) {
        deleteSectionNameSpan.textContent = sectionName;
        confirmDeleteBtn.setAttribute('data-section-id', sectionId);
        openModal(deleteModal, deleteModalContent);
    };

    // Function to execute the delete after confirmation (shows overlay)
    window.executeDeleteAction = function(sectionId) {
        // 1. Hide the confirmation modal
        closeModal(deleteModal, deleteModalContent);

        // 2. Show the loading overlay 
        if (overlay) {
            // Set the message specifically for deletion
            if (loadingMessageText) {
                loadingMessageText.textContent = 'Deleting Section...'; 
            }
            
            overlay.classList.remove('hidden', 'opacity-0');
            setTimeout(() => {
                 overlay.classList.add('opacity-100');
            }, 10);
        }

        // 3. Create a temporary form to submit the delete action
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'sections.php';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_section';
        form.appendChild(actionInput);

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'section_id';
        idInput.value = sectionId;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
    };
    
    // --- EDIT MODAL POPULATION & TRIGGER ---
    if (sectionToEdit) {
        // 1. Populate the form fields
        editSectionIdInput.value = sectionToEdit.id;
        editSectionNameInput.value = sectionToEdit.name;
        editTeacherNameInput.value = sectionToEdit.teacher;

        // 2. Select the correct radio button for the year
        editYearRadios.forEach(radio => {
            if (radio.value === sectionToEdit.year) {
                radio.checked = true;
            } else {
                radio.checked = false;
            }
        });

        // 3. Open the edit modal
        openModal(editModal, editModalContent);
    }
    
    // --- Event Listeners ---
    openBtn.addEventListener('click', () => openModal(modal, modalContent));
    closeBtn.addEventListener('click', () => closeModal(modal, modalContent));
    closeSuccessModalBtn.addEventListener('click', () => closeModal(successModal, successModalContent));
    closeEditModalBtn.addEventListener('click', () => closeModal(editModal, editModalContent)); 
    
    // Delete Modal Listeners
    cancelDeleteBtn.addEventListener('click', () => closeModal(deleteModal, deleteModalContent));
    confirmDeleteBtn.addEventListener('click', function() {
        const sectionId = this.getAttribute('data-section-id');
        if (sectionId) {
            executeDeleteAction(sectionId);
        }
    });


    // Modal click-off handlers
    modal.addEventListener('click', (e) => {
        if (e.target === modal) { closeModal(modal, modalContent); }
    });
    successModal.addEventListener('click', (e) => {
        if (e.target === successModal) { closeModal(successModal, successModalContent); }
    });
    editModal.addEventListener('click', (e) => { 
        if (e.target === editModal) { closeModal(editModal, editModalContent); }
    });
    deleteModal.addEventListener('click', (e) => { 
        if (e.target === deleteModal) { closeModal(deleteModal, deleteModalContent); }
    });
    
    // Custom scrollbar CSS injection for better UI
    const style = document.createElement('style');
    style.innerHTML = `
    .custom-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: #f8f9fb;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #D1D5DB;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #9CA3AF;
    }
    `;
    document.head.appendChild(style);

    // --- Add/Update/Delete Success Logic (MODIFIED to handle dynamic descriptions) ---
    let detailsToShow = null;
    let modalTitle = '';
    let modalDescription = ''; 
    
    // Check for ADD success 
    if (successDetails && successDetails.name) {
        detailsToShow = successDetails;
        modalTitle = 'Section Added Successfully!';
        modalDescription = 'The new section has been saved to the database.';
    } 
    // Check for EDIT success
    else if (editSuccessDetails && editSuccessDetails.name) {
        detailsToShow = editSuccessDetails;
        modalTitle = 'Section Updated Successfully!';
        modalDescription = 'The section details have been successfully updated.';
    }
    // Check for DELETE success
    else if (deleteSuccessDetails && deleteSuccessDetails.name) {
        detailsToShow = deleteSuccessDetails;
        modalTitle = 'Section Deleted Successfully!';
        modalDescription = 'The section was permanently removed from the system.'; // Correct delete message
    }


    if (detailsToShow) {
        // 1. Update the success modal's dynamic content
        document.getElementById('success-modal-title').textContent = modalTitle;
        
        if (successModalDescription) {
            successModalDescription.textContent = modalDescription; // Set the correct description text
        }

        document.getElementById('modalSectionName').textContent = detailsToShow.name;
        document.getElementById('modalSectionYear').textContent = detailsToShow.year;
        document.getElementById('modalTeacherName').textContent = detailsToShow.teacher;
        
        // 2. Reset the form and button/spinner state for the ADD modal only
        if (addSectionForm) {
            addSectionForm.reset(); 

            // Reset loading state for the button in the 'add section' modal
            if(saveIcon && saveText && loadingSpinner && saveSectionBtn) {
                saveIcon.classList.remove('hidden');
                saveText.textContent = 'Save Section';
                loadingSpinner.classList.add('hidden');
                saveSectionBtn.disabled = false;
                saveSectionBtn.classList.add('hover:bg-blue-700');
                saveSectionBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        }

        openModal(successModal, successModalContent);
    }
});
</script>
</body>
</html>