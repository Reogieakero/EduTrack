<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

$current_user = htmlspecialchars($_SESSION['username'] ?? 'Admin User'); 

$sections = []; 

$add_success = false;
$add_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_section') {
    $new_section_name = trim($_POST['section_name'] ?? '');
    $new_teacher_name = trim($_POST['teacher_name'] ?? '');
    
    if (!empty($new_section_name) && !empty($new_teacher_name)) {
        $add_success = "New section '{$new_section_name}' assigned to {$new_teacher_name} has been added (Non-Persistent Mock Success). Connect to a database to save data permanently.";
    } else {
        $add_error = "Both Section Name and Teacher Name are required.";
    }
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

    <?php if ($add_success): ?>
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center space-x-2 shadow-sm" role="alert">
            <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
            <span><?php echo $add_success; ?></span>
        </div>
    <?php endif; ?>

    <?php if ($add_error): ?>
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center space-x-2 shadow-sm" role="alert">
            <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0"></i>
            <span><?php echo $add_error; ?></span>
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
                    <p class="text-sm text-red-500 mt-4 italic">Note: Data is not persistent until a database connection is implemented.</p>
                </div>
            <?php else: ?>
                <?php foreach ($sections as $section): 
                    $section['teacher'] = $section['teacher'] ?? 'Unassigned';
                    $section['students'] = $section['students'] ?? [];
                ?>
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 transition duration-300 hover:shadow-xl">
                        <div class="flex justify-between items-start mb-4 pb-4 border-b">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($section['name']); ?></h3>
                                <p class="text-sm text-gray-600 mt-1">Teacher: <span class="font-medium text-primary-green"><?php echo htmlspecialchars($section['teacher']); ?></span></p>
                            </div>
                            <div class="text-right p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="text-3xl font-extrabold text-primary-blue"><?php echo count($section['students']); ?></span>
                                <p class="text-sm text-gray-500 mt-0.5">Students</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h4 class="text-lg font-semibold text-gray-700 mb-3 flex items-center space-x-2">
                                <i data-lucide="users" class="w-5 h-5 text-gray-500"></i>
                                <span>Students in Section:</span>
                            </h4>
                            
                            <?php if (!empty($section['students'])): ?>
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
include 'components/add_section_modal.php'; 
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    const modal = document.getElementById('addSectionModal');
    const modalContent = document.getElementById('modalContent');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');

    const openModal = () => {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
            document.body.style.overflow = 'hidden'; 
        }, 10);
    };

    const closeModal = () => {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        modal.classList.add('opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300); 
    };

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
    
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