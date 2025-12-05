<?php
if (!isset($current_user)) {
    $current_user = "Admin User"; 
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="fixed top-0 left-0 w-64 h-full bg-sidebar-bg text-sidebar-text shadow-xl hidden md:flex flex-col z-10">
    
    <div class="p-6 flex items-center space-x-2 border-b border-primary-hover/50">
        <i data-lucide="graduation-cap" class="w-7 h-7 text-white"></i>
        <span class="text-xl font-bold font-droid text-white">EduTrack Admin</span>
    </div>
    
    <nav class="flex-grow p-4 space-y-2">
        <!-- Dashboard -->
        <a href="dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg font-semibold transition duration-150
            <?php echo ($current_page == 'dashboard.php') ? 'bg-white bg-opacity-20 text-white' : 'text-white hover:bg-primary-hover'; ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span>Dashboard</span>
        </a>

        <p class="pt-4 pb-2 px-3 text-xs uppercase tracking-widest text-gray-400 font-medium">User Management</p>

        <!-- Section -->
        <a href="sections.php" class="flex items-center space-x-3 p-3 rounded-lg transition duration-150
            <?php echo ($current_page == 'sections.php') ? 'bg-white bg-opacity-20 text-white' : 'text-white hover:bg-primary-hover'; ?>">
            <i data-lucide="layers" class="w-5 h-5"></i>
            <span>Section</span>
        </a>

        <!-- Students -->
        <a href="students.php" class="flex items-center space-x-3 p-3 rounded-lg transition duration-150
            <?php echo ($current_page == 'students.php') ? 'bg-white bg-opacity-20 text-white' : 'text-white hover:bg-primary-hover'; ?>">
            <i data-lucide="user-cog" class="w-5 h-5"></i>
            <span>Students</span>
        </a>

        <!-- Parents -->
        <a href="parents.php" class="flex items-center space-x-3 p-3 rounded-lg transition duration-150
            <?php echo ($current_page == 'parents.php') ? 'bg-white bg-opacity-20 text-white' : 'text-white hover:bg-primary-hover'; ?>">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span>Parents</span>
        </a>

        <!-- Teachers -->
        <a href="teachers.php" class="flex items-center space-x-3 p-3 rounded-lg transition duration-150
            <?php echo ($current_page == 'teachers.php') ? 'bg-white bg-opacity-20 text-white' : 'text-white hover:bg-primary-hover'; ?>">
            <i data-lucide="graduation-cap" class="w-5 h-5"></i>
            <span>Teachers</span>
        </a>
    </nav>

    <div class="p-4 border-t border-primary-hover/50">
        <div class="flex items-center space-x-3 p-3 text-sm text-gray-300">
            <i data-lucide="user-check" class="w-4 h-4"></i>
            <span>Logged in as: <strong><?php echo $current_user; ?></strong></span>
        </div>
        <a href="logout.php" class="w-full flex items-center justify-center space-x-3 p-3 mt-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition duration-150">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
