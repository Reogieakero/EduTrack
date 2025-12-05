<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

require_once '../config/database.php';

// Check for and retrieve success details (for the pop-up modal)
$add_success_details = null;
if (isset($_SESSION['add_success_details'])) {
    $add_success_details = $_SESSION['add_success_details'];
    unset($_SESSION['add_success_details']); // Remove the session data after fetching it
} 

$current_user = htmlspecialchars($_SESSION['username'] ?? 'Admin User'); 

$sections = []; 
$add_error = false;
$fetch_error = false;

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
                // Store section details for success modal display after redirect
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

$sql_fetch = "SELECT id, year, name, teacher FROM sections ORDER BY year, name";
if ($result = $conn->query($sql_fetch)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sections[] = [
                'id' => $row['id'],
                'year' => $row['year'],
                'name' => $row['name'],
                'teacher' => $row['teacher'],
                'students' => [] 
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

    <div class="lg:col-span-3">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center space-x-2">
            <i data-lucide="layers" class="w-7 h-7 text-gray-600"></i>
            <span>All Sections (<?php echo count($sections); ?>)</span>
        </h2>

        <div class="space-y-6">
            <?php if (empty($sections)): ?>
                <div class="text-center p-12 bg-white rounded-xl shadow-lg border border-gray-200">
                    <i data-lucide="inbox" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800">No Sections Found</h3>
                    <p class="text-gray-500 mt-2">Click the "Add New Section" button above to create your first section.</p>
                    <?php if (!$fetch_error): ?>
                        <p class="text-sm text-primary-blue mt-4 italic">Sections will now be saved to your MySQL database.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($sections as $section): 
                    $student_count = count($section['students'] ?? []);
                    $teacher_display = htmlspecialchars($section['teacher'] ?? 'Unassigned');
                    $year_display = htmlspecialchars($section['year'] ?? 'N/A');
                ?>
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 transition duration-300 hover:shadow-xl">
                        <div class="flex justify-between items-start mb-4 pb-4 border-b">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($section['name']); ?></h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Year: <span class="font-bold text-primary-blue mr-4"><?php echo $year_display; ?></span>
                                    Teacher: <span class="font-medium text-primary-green"><?php echo $teacher_display; ?></span>
                                </p>
                            </div>
                            <div class="text-right p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="text-3xl font-extrabold text-primary-blue"><?php echo $student_count; ?></span>
                                <p class="text-sm text-gray-500 mt-0.5">Students</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h4 class="text-lg font-semibold text-gray-700 mb-3 flex items-center space-x-2">
                                <i data-lucide="users" class="w-5 h-5 text-gray-500"></i>
                                <span>Students in Section:</span>
                            </h4>
                            
                            <?php if ($student_count > 0): ?>
                                <ul class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm text-gray-700 max-h-48 overflow-y-auto pr-2 custom-scroll">
                                    <?php foreach ($section['students'] as $student): ?>
                                        <li class="flex items-center space-x-2 p-2 rounded-lg bg-gray-100/70 border border-gray-200">
                                            <i data-lucide="user" class="w-4 h-4 text-gray-500 flex-shrink-0"></i>
                                            <span class="truncate"><?php echo htmlspecialchars($student['name'] ?? 'Unknown'); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="p-4 bg-yellow-50 text-yellow-700 rounded-lg border border-yellow-200 flex items-center space-x-2">
                                    <i data-lucide="info" class="w-5 h-5 flex-shrink-0"></i>
                                    <p class="italic">No students are currently assigned to this section.</p>
                                </div>
                            <?php endif; ?>

                            <button onclick="alert('In a real app, this would open a modal to manage students for this section (ID: <?php echo htmlspecialchars($section['id'] ?? 'N/A'); ?>)')" class="mt-5 text-sm font-medium flex items-center space-x-1 text-primary-blue hover:text-blue-700 transition duration-150">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                                <span>Manage Section Details</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php 
// Include both modals
include 'components/add_section_modal.php'; 
include 'components/success_modal.php'; 

// Pass success data to JavaScript
$success_json = json_encode($add_success_details);
echo "<script>const successDetails = {$success_json};</script>";
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    // Section Creation Modal Elements
    const modal = document.getElementById('addSectionModal');
    const modalContent = document.getElementById('modalContent');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    
    // Success Modal Elements
    const successModal = document.getElementById('successModal');
    const successModalContent = document.getElementById('successModalContent');
    const closeSuccessModalBtn = document.getElementById('closeSuccessModalBtn');
    const modalSectionName = document.getElementById('modalSectionName');
    const modalSectionYear = document.getElementById('modalSectionYear');
    const modalTeacherName = document.getElementById('modalTeacherName');

    // Loading/Save Button Elements
    const addSectionForm = document.getElementById('addSectionForm');
    const saveSectionBtn = document.getElementById('saveSectionBtn');
    const saveIcon = document.getElementById('saveIcon');
    const saveText = document.getElementById('saveText');
    const loadingSpinner = document.getElementById('loadingSpinner');

    // Generic Modal Functions
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
            // Only unlock scroll if no other modals are open
            if(modal.classList.contains('hidden') && successModal.classList.contains('hidden')) {
                document.body.style.overflow = '';
            }
        }, 300); 
    };

    // Event Listeners for Section Creation Modal
    openBtn.addEventListener('click', () => openModal(modal, modalContent));
    closeBtn.addEventListener('click', () => closeModal(modal, modalContent));
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal(modal, modalContent);
        }
    });

    // Event Listeners for Success Modal
    closeSuccessModalBtn.addEventListener('click', () => closeModal(successModal, successModalContent));

    successModal.addEventListener('click', (e) => {
        if (e.target === successModal) {
            closeModal(successModal, successModalContent);
        }
    });

    // Loading Animation Handler (Triggered on form submit)
    if (addSectionForm) {
        addSectionForm.addEventListener('submit', function(event) {
            // Check if all required fields are filled before showing spinner
            if (addSectionForm.checkValidity()) {
                // Show loading state
                saveIcon.classList.add('hidden');
                saveText.textContent = 'Saving...';
                loadingSpinner.classList.remove('hidden');
                saveSectionBtn.disabled = true; // Prevent multiple submissions
                saveSectionBtn.classList.remove('hover:bg-blue-700');
                saveSectionBtn.classList.add('opacity-70', 'cursor-not-allowed');
            }
        });
    }

    // Display the success modal if successDetails are present (after redirect)
    if (successDetails) {
        modalSectionName.textContent = successDetails.name;
        modalSectionYear.textContent = successDetails.year;
        modalTeacherName.textContent = successDetails.teacher;
        openModal(successModal, successModalContent);
    }
    
    // Custom scrollbar styling
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