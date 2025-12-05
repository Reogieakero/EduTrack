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

$sections = []; 
$add_error = false;
$fetch_error = false;

$selected_year = $_GET['year'] ?? 'all'; 

$mandatory_grades = ['all', '7', '8', '9', '10', '11', '12'];

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

$sql_fetch = "SELECT id, year, name, teacher FROM sections";
$where_clause = "";

if ($selected_year !== 'all') {
    $where_clause = " WHERE year = '" . $conn->real_escape_string($selected_year) . "'";
}

$sql_fetch .= $where_clause . " ORDER BY year DESC, name ASC";

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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center space-x-2 mb-4 sm:mb-0">
                <i data-lucide="layers" class="w-7 h-7 text-gray-600"></i>
                <span>Sections (<?php echo count($sections); ?>)</span>
            </h2>

            <div class="flex space-x-2 p-1 bg-gray-100 rounded-xl shadow-inner overflow-x-auto">
                
                <?php foreach ($mandatory_grades as $year_option): 
                    $is_selected = $selected_year === $year_option;
                    $display_text = $year_option === 'all' ? 'All Grades' : 'Grade ' . $year_option;
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
                        No sections found for Grade <?php echo htmlspecialchars($selected_year); ?>.
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

$success_json = json_encode($add_success_details);
echo "<script>const successDetails = {$success_json};</script>";
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    const modal = document.getElementById('addSectionModal');
    const modalContent = document.getElementById('modalContent');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    
    const successModal = document.getElementById('successModal');
    const successModalContent = document.getElementById('successModalContent');
    const closeSuccessModalBtn = document.getElementById('closeSuccessModalBtn');
    const modalSectionName = document.getElementById('modalSectionName');
    const modalSectionYear = document.getElementById('modalSectionYear');
    const modalTeacherName = document.getElementById('modalTeacherName');

    const addSectionForm = document.getElementById('addSectionForm');
    const saveSectionBtn = document.getElementById('saveSectionBtn');
    const saveIcon = document.getElementById('saveIcon');
    const saveText = document.getElementById('saveText');
    const loadingSpinner = document.getElementById('loadingSpinner');

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
            if(modal.classList.contains('hidden') && successModal.classList.contains('hidden')) {
                document.body.style.overflow = '';
            }
        }, 300); 
    };

    openBtn.addEventListener('click', () => openModal(modal, modalContent));
    closeBtn.addEventListener('click', () => closeModal(modal, modalContent));
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal(modal, modalContent);
        }
    });

    closeSuccessModalBtn.addEventListener('click', () => closeModal(successModal, successModalContent));
    successModal.addEventListener('click', (e) => {
        if (e.target === successModal) {
            closeModal(successModal, successModalContent);
        }
    });

    if (addSectionForm) {
        addSectionForm.addEventListener('submit', function(event) {
            if (addSectionForm.checkValidity()) {
                saveIcon.classList.add('hidden');
                saveText.textContent = 'Saving...';
                loadingSpinner.classList.remove('hidden');
                saveSectionBtn.disabled = true; 
                saveSectionBtn.classList.remove('hover:bg-blue-700');
                saveSectionBtn.classList.add('opacity-70', 'cursor-not-allowed');
            }
        });
    }

    if (successDetails) {
        modalSectionName.textContent = successDetails.name;
        modalSectionYear.textContent = successDetails.year;
        modalTeacherName.textContent = successDetails.teacher;
        openModal(successModal, successModalContent);
    }
    
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