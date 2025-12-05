<div id="adminAuthModal" class="fixed inset-0 bg-gray-900 bg-opacity-70 z-50 hidden transition-opacity duration-300 opacity-0 flex items-center justify-center" aria-modal="true" role="dialog">
    <div id="adminAuthModalContent" class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6 transform transition-all duration-300 scale-95 opacity-0">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800 flex items-center space-x-2">
                <i data-lucide="shield-alert" class="w-6 h-6 text-red-500"></i>
                <span>Admin Required</span>
            </h3>
            <button id="closeAdminAuthModalBtn" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <p class="text-gray-600 mb-4 text-sm">
            Please enter the **Admin Password** to confirm the <span id="authActionText" class="font-semibold text-red-600"></span> action for section **<span id="authSectionName" class="font-semibold"></span>** (ID: <span id="authSectionId"></span>).
        </p>

        <form id="adminAuthForm" method="POST" action="sections.php">
            <input type="hidden" name="action" id="authHiddenAction">
            <input type="hidden" name="section_id" id="authHiddenSectionId">

            <div class="mb-4">
                <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">Admin Password:</label>
                <input type="password" id="admin_password" name="admin_password" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
            </div>
            
            <div id="authError" class="hidden p-2 mb-3 text-sm text-red-700 bg-red-100 rounded-lg" role="alert"></div>

            <button type="submit" id="adminAuthSubmitBtn" 
                    class="w-full flex items-center justify-center space-x-2 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-4 rounded-lg transition duration-150">
                <span id="authSubmitIcon"><i data-lucide="lock" class="w-5 h-5"></i></span>
                <span id="authSubmitText">Confirm Action</span>
                <svg id="authLoadingSpinner" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </form>
    </div>
</div>