<?php
if (!isset($current_user)) {
    $current_user = "Admin User"; 
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
#sidebar::-webkit-scrollbar {
    display: none;
}
#sidebar {
    -ms-overflow-style: none;  
    scrollbar-width: none;  
}
</style>

<div class="md:hidden flex items-start justify-center w-16 px-1 py-3 bg-sidebar-bg text-white shadow-md fixed top-0 left-0 z-30 h-full">
    <button id="sidebarToggle" class="p-2 rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white">
        <i data-lucide="menu" class="w-6 h-6"></i>
    </button>
</div>

<aside id="sidebar" class="fixed top-0 left-0 w-56 h-full bg-sidebar-bg text-sidebar-text shadow-xl flex flex-col z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300 overflow-y-auto">
    
    <div class="p-6 flex items-center space-x-2 border-b border-primary-hover/50 flex-shrink-0">
        <i data-lucide="graduation-cap" class="w-7 h-7 text-white"></i>
        <span class="text-xl font-bold font-droid text-white">EduTrack Admin</span>
    </div>
    
    <nav class="flex-grow p-4 space-y-1.5">
        <a href="../pages/dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg font-semibold transition duration-150 focus:outline-none focus:ring-2 focus:ring-white/50
            <?php echo ($current_page == 'dashboard.php') ? 'bg-white bg-opacity-20 text-white' : 'text-white hover:bg-primary-hover'; ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span>Dashboard</span>
        </a>

        <p class="pt-4 pb-1 px-3 text-xs uppercase tracking-widest text-gray-400 font-medium">User Management</p>

        <a href="../pages/sections.php" class="flex items-center space-x-3 p-3 rounded-lg transition duration-150 focus:outline-none focus:ring-2 focus:ring-white/50
            <?php echo ($current_page == 'sections.php') ? 'bg-white bg-opacity-20 text-white' : 'text-white hover:bg-primary-hover'; ?>">
            <i data-lucide="layers" class="w-5 h-5"></i>
            <span>Section</span>
        </a>

        <a href="../pages/students.php" class="flex items-center space-x-3 p-3 rounded-lg transition duration-150 focus:outline-none focus:ring-2 focus:ring-white/50
            <?php echo ($current_page == 'students.php') ? 'bg-white bg-opacity-20 text-white' : 'text-white hover:bg-primary-hover'; ?>">
            <i data-lucide="user-cog" class="w-5 h-5"></i>
            <span>Students</span>
        </a>

        <a href="parents.php" class="flex items-center space-x-3 p-3 rounded-lg transition duration-150 focus:outline-none focus:ring-2 focus:ring-white/50
            <?php echo ($current_page == 'parents.php') ? 'bg-white bg-opacity-20 text-white' : 'text-white hover:bg-primary-hover'; ?>">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span>Parents</span>
        </a>

        <a href="teachers.php" class="flex items-center space-x-3 p-3 rounded-lg transition duration-150 focus:outline-none focus:ring-2 focus:ring-white/50
            <?php echo ($current_page == 'teachers.php') ? 'bg-white bg-opacity-20 text-white' : 'text-white hover:bg-primary-hover'; ?>">
            <i data-lucide="graduation-cap" class="w-5 h-5"></i>
            <span>Teachers</span>
        </a>
    </nav>

    <div class="p-4 border-t border-primary-hover/50 flex-shrink-0">
        <div class="flex items-center space-x-3 p-3 text-sm text-gray-300">
            <i data-lucide="user-check" class="w-4 h-4"></i>
            <span>Logged in as: <strong><?php echo $current_user; ?></strong></span>
        </div>
        <a href="../processes/logout.php" class="w-full flex items-center justify-center space-x-3 p-3 mt-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition duration-150 focus:outline-none focus:ring-2 focus:ring-red-300">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-30 md:hidden"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('overlay');
    
    const mainContent = document.querySelector('main');
    if (mainContent) {
        if (window.innerWidth < 768) {
             mainContent.style.marginLeft = '4rem';
        }
        
        window.addEventListener('resize', () => {
            if (window.innerWidth < 768) {
                mainContent.style.marginLeft = '4rem';
            } else {
                mainContent.style.marginLeft = ''; 
            }
        });
    }
    
    const openSidebar = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    const closeSidebar = () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    };

    toggleBtn.addEventListener('click', () => {
        if (sidebar.classList.contains('-translate-x-full')) {
            openSidebar();
        } else {
            closeSidebar();
        }
    });

    overlay.addEventListener('click', closeSidebar);

    const navLinks = sidebar.querySelectorAll('nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) { 
                closeSidebar();
            }
        });
    });
});
</script>