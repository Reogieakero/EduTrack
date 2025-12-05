<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

require_once '../config/database.php';

$add_success_details = null;
if (isset($_SESSION['add_success_details'])) {
    $add_success_details = $_SESSION['add_success_details'];
    unset($_SESSION['add_success_details']); 
} 

// --- NEW EDIT DATA STORAGE ---
$section_to_edit = null;
if (isset($_SESSION['section_to_edit'])) {
    $section_to_edit = $_SESSION['section_to_edit'];
    unset($_SESSION['section_to_edit']);
}
// --- END NEW EDIT DATA STORAGE ---

$sections = []; 
$add_error = false;
$fetch_error = false;

// --- YEAR FILTER LOGIC ---
$selected_year = $_GET['year'] ?? 'all'; 
// --- END YEAR FILTER LOGIC ---


// --- HANDLE POST REQUEST TO ADD A NEW SECTION (Remains the same) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_section') {
    $new_section_name = trim($_POST['section_name'] ?? '');
    $new_teacher_name = trim($_POST['teacher_name'] ?? '');
    $new_section_year = trim($_POST['section_year'] ?? '');
    
    if (empty($new_section_name) || empty($new_teacher_name) || empty($new_section_year)) {
        $add_error = "Section Name, Assigned Teacher, and Academic Year are all required.";
    } else {
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

// --- HANDLE POST REQUEST TO EDIT/DELETE A SECTION (Admin Auth) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['action']) && ($_POST['action'] === 'delete_section' || $_POST['action'] === 'edit_section' || $_POST['action'] === 'update_section'))) {
    
    $action_to_perform = $_POST['action'];
    $section_id = (int)($_POST['section_id'] ?? 0);
    $admin_password_input = $_POST['admin_password'] ?? '';
    
    $ADMIN_PASS = "admin123"; // ⚠️ HARDCODED FOR DEMO ⚠️

    // -----------------------------------------------------
    // 1. Admin Password Check (only for delete/edit triggers)
    // -----------------------------------------------------
    if ($action_to_perform === 'delete_section' || $action_to_perform === 'edit_section') {
        if ($admin_password_input !== $ADMIN_PASS) {
            $_SESSION['auth_error'] = "Authentication failed. Incorrect Admin Password.";
            $_SESSION['auth_action'] = $action_to_perform; 
            $_SESSION['auth_section_id'] = $section_id;
            header("Location: sections.php");
            exit;
        }
    }

    // -----------------------------------------------------
    // 2. Execute Actions after successful auth
    // -----------------------------------------------------
    if ($action_to_perform === 'delete_section' && $section_id > 0) {
        // --- Execute DELETE Logic ---
        $sql = "DELETE FROM sections WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $section_id);
            if ($stmt->execute()) {
                $_SESSION['delete_success'] = "Section ID {$section_id} successfully deleted.";
                header("Location: sections.php");
                exit;
            } else {
                $add_error = "ERROR: Could not delete section. " . $stmt->error;
            }
            $stmt->close();
        }
    }
    
    if ($action_to_perform === 'edit_section' && $section_id > 0) {
        // --- AUTHENTICATION SUCCESS: Fetch data to populate the modal ---
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
                    $_SESSION['edit_success'] = "Section {$updated_name} (ID: {$section_id}) successfully updated.";
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


// --- FETCH ALL SECTIONS (WITH FILTER) ---
// 1. Fetch available years for the filter buttons
$sql_fetch = "SELECT DISTINCT year FROM sections ORDER BY year ASC";
$years = ['all']; 
if ($result_years = $conn->query($sql_fetch)) {
    while ($row = $result_years->fetch_assoc()) {
        $years[] = htmlspecialchars($row['year']);
    }
}

// 2. Fetch sections based on the selected year
$sql_fetch = "SELECT id, year, name, teacher, created_at FROM sections";
$where_clause = "";

if ($selected_year !== 'all') {
    $where_clause = " WHERE year = '" . $conn->real_escape_string($selected_year) . "'";
}

$sql_fetch .= $where_clause . " ORDER BY year ASC, name ASC";

if ($result = $conn->query($sql_fetch)) {
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
    $fetch_error = "ERROR: Could not retrieve sections from the database: " . $conn->error;
}

$conn->close();

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

    <?php if (isset($_SESSION['delete_success'])): ?>
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center space-x-2 shadow-sm" role="alert">
            <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
            <span><?php echo $_SESSION['delete_success']; ?></span>
        </div>
    <?php unset($_SESSION['delete_success']); endif; ?>
    
    <?php if (isset($_SESSION['edit_success'])): ?>
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center space-x-2 shadow-sm" role="alert">
            <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
            <span><?php echo $_SESSION['edit_success']; ?></span>
        </div>
    <?php unset($_SESSION['edit_success']); endif; ?>


    <div class="lg:col-span-3">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center space-x-2 mb-4 sm:mb-0">
                <i data-lucide="layers" class="w-7 h-7 text-gray-600"></i>
                <span>All Sections (<?php echo count($sections); ?>)</span>
            </h2>

            <div class="flex space-x-2 p-1 bg-gray-100 rounded-xl shadow-inner overflow-x-auto">
                <?php 
                $year_tabs = array_unique($years); 
                ?>
                
                <?php foreach ($year_tabs as $year_option): 
                    $is_selected = $selected_year === $year_option;
                    $display_text = $year_option === 'all' ? 'All Years' : $year_option;
                    $class = $is_selected 
                        ? 'bg-white text-primary-blue font-bold shadow-md' 
                        : 'text-gray-600 hover:bg-gray-200';
                ?>
                    <a href="sections.php?year=<?php echo $year_option; ?>" 
                        class="px-4 py-2 text-sm rounded-lg transition duration-150 whitespace-nowrap <?php echo $class; ?>">
                        <?php echo $display_text; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($sections)): ?>
            <div class="lg:col-span-3 text-center p-12 bg-white rounded-xl shadow-lg border border-gray-200">
                <i data-lucide="inbox" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-800">No Sections Found</h3>
                <p class="text-gray-500 mt-2">
                    <?php if ($selected_year !== 'all'): ?>
                        No sections found for academic year **<?php echo htmlspecialchars($selected_year); ?>**.
                    <?php else: ?>
                        Click the "Add New Section" button above to create your first section.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($sections as $section): 
                include 'components/section_card.php'; 
            endforeach; ?>
        <?php endif; ?>
        </div>
    </div>
</main>

<?php 
include 'components/add_section_modal.php'; 
include 'components/success_modal.php'; 
include 'components/admin_auth_modal.php'; 
// --- NEW EDIT MODAL INCLUDE ---
include 'components/edit_section_modal.php'; 


$success_json = json_encode($add_success_details);
$edit_data_json = json_encode($section_to_edit); // Pass fetched data to JS
echo "<script>const successDetails = {$success_json};</script>";
echo "<script>const sectionToEdit = {$edit_data_json};</script>";
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    // --- Modal Elements (Add/Success) ---
    const modal = document.getElementById('addSectionModal');
    const modalContent = document.getElementById('modalContent');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    
    const successModal = document.getElementById('successModal');
    const successModalContent = document.getElementById('successModalContent');
    const closeSuccessModalBtn = document.getElementById('closeSuccessModalBtn');

    // ... (rest of the add/success elements)

    const addSectionForm = document.getElementById('addSectionForm');
    const saveSectionBtn = document.getElementById('saveSectionBtn');
    const saveIcon = document.getElementById('saveIcon');
    const saveText = document.getElementById('saveText');
    const loadingSpinner = document.getElementById('loadingSpinner');

    // --- ADMIN MODAL ELEMENTS ---
    const adminAuthModal = document.getElementById('adminAuthModal');
    const adminAuthModalContent = document.getElementById('adminAuthModalContent');
    const closeAdminAuthModalBtn = document.getElementById('closeAdminAuthModalBtn');
    const adminAuthForm = document.getElementById('adminAuthForm');
    const authActionText = document.getElementById('authActionText');
    const authSectionName = document.getElementById('authSectionName');
    const authSectionId = document.getElementById('authSectionId');
    const authHiddenAction = document.getElementById('authHiddenAction');
    const authHiddenSectionId = document.getElementById('authHiddenSectionId');
    const authError = document.getElementById('authError');
    const adminPasswordInput = document.getElementById('admin_password');

    // --- NEW EDIT MODAL ELEMENTS ---
    const editModal = document.getElementById('editSectionModal');
    const editModalContent = document.getElementById('editModalContent');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const editForm = document.getElementById('editSectionForm');
    const editSectionIdInput = document.getElementById('edit_section_id');
    const editSectionNameInput = document.getElementById('edit_modal_section_name');
    const editTeacherNameInput = document.getElementById('edit_modal_teacher_name');
    const editYearRadios = document.getElementsByName('edit_section_year');


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
            if(modal.classList.contains('hidden') && successModal.classList.contains('hidden') && adminAuthModal.classList.contains('hidden') && editModal.classList.contains('hidden')) {
                document.body.style.overflow = '';
            }
        }, 300); 
    };

    // --- ADMIN AUTH LOGIC ---
    window.confirmAdminAction = function(action, sectionId, sectionName) {
        // Set the text and hidden fields
        authActionText.textContent = action.toUpperCase();
        authSectionName.textContent = sectionName;
        authSectionId.textContent = sectionId;
        authHiddenAction.value = action + '_section';
        authHiddenSectionId.value = sectionId;
        
        // Clear previous error/input
        authError.classList.add('hidden');
        adminPasswordInput.value = '';

        // Open the modal
        openModal(adminAuthModal, adminAuthModalContent);
    };
    
    // Check for authentication error session variable on load and display it
    <?php if (isset($_SESSION['auth_error'])): ?>
        const authErrorMsg = "<?php echo $_SESSION['auth_error']; ?>";
        const authAction = "<?php echo $_SESSION['auth_action']; ?>";
        const authSectionID = "<?php echo $_SESSION['auth_section_id']; ?>";

        authError.textContent = authErrorMsg;
        authError.classList.remove('hidden');
        
        // Re-open the modal with the error context
        authActionText.textContent = authAction.replace('_section', '').toUpperCase();
        authSectionId.textContent = authSectionID;
        authHiddenAction.value = authAction;
        authHiddenSectionId.value = authSectionID;
        
        // Note: Section name cannot be reliably retrieved after redirect, so it's omitted here.
        
        openModal(adminAuthModal, adminAuthModalContent);

        // Clear session variables after displaying (done in PHP above, but good practice to know)
    <?php endif; ?>

    // --- NEW: EDIT MODAL POPULATION & TRIGGER ---
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
    closeAdminAuthModalBtn.addEventListener('click', () => closeModal(adminAuthModal, adminAuthModalContent));
    closeEditModalBtn.addEventListener('click', () => closeModal(editModal, editModalContent)); // New listener

    // Modal click-off handlers
    modal.addEventListener('click', (e) => {
        if (e.target === modal) { closeModal(modal, modalContent); }
    });
    successModal.addEventListener('click', (e) => {
        if (e.target === successModal) { closeModal(successModal, successModalContent); }
    });
    adminAuthModal.addEventListener('click', (e) => {
        if (e.target === adminAuthModal) { closeModal(adminAuthModal, adminAuthModalContent); }
    });
    editModal.addEventListener('click', (e) => { // New listener
        if (e.target === editModal) { closeModal(editModal, editModalContent); }
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
});
</script>
</body>
</html>