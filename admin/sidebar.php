<?php
// Note: $current_user must be defined in the calling script (dashboard.php)
if (!isset($current_user)) {
    // Fallback or error handling if not set
    $current_user = "Admin User"; 
}
?>
<aside class="fixed top-0 left-0 w-64 h-full bg-sidebar-bg text-sidebar-text shadow-xl hidden md:flex flex-col z-10">
    
    <div class="p-6 flex items-center space-x-2 border-b border-primary-hover/50">
        <i data-lucide="graduation-cap" class="w-7 h-7 text-white"></i>
        <span class="text-xl font-bold font-droid text-white">EduTrack Admin</span>
    </div>
    
    <nav class="flex-grow p-4 space-y-2">
        
        <a href="dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg text-white font-semibold bg-primary-hover transition duration-150">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span>Dashboard</span>
        </a>

        <p class="pt-4 pb-2 px-3 text-xs uppercase tracking-widest text-gray-400 font-medium">User Management</p>

        <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-primary-hover transition duration-150 group">
            <i data-lucide="layers" class="w-5 h-5 text-sidebar-text group-hover:text-active-link"></i>
            <span>Section</span>
        </a>

        <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-primary-hover transition duration-150 group">
            <i data-lucide="user-cog" class="w-5 h-5 text-sidebar-text group-hover:text-active-link"></i>
            <span>Students</span>
        </a>
        
        <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-primary-hover transition duration-150 group">
            <i data-lucide="users" class="w-5 h-5 text-sidebar-text group-hover:text-active-link"></i>
            <span>Parents</span>
        </a>

        <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-primary-hover transition duration-150 group">
            <i data-lucide="graduation-cap" class="w-5 h-5 text-sidebar-text group-hover:text-active-link"></i>
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