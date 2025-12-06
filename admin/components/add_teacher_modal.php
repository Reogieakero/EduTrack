<div id="addTeacherModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden" aria-modal="true" role="dialog" aria-labelledby="modal-title-teacher">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform transition-all duration-300 scale-95 opacity-0" id="teacherModalContent">
        <div class="p-6">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center space-x-2" id="modal-title-teacher">
                    <i data-lucide="user-plus" class="w-6 h-6 text-primary-blue"></i>
                    <span>Register New Teacher</span>
                </h3>
                <button id="closeAddModalBtn" type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded-full transition duration-150">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="pt-4">
                <div id="singleEnrollContent" class="tab-content pt-6 space-y-5">
                    <form method="POST" action="teachers.php" class="space-y-5" id="addTeacherForm">
                        <input type="hidden" name="action" value="add_teacher">

                        <div>
                            <label for="add_last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" id="add_last_name" name="last_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., Smith">
                        </div>

                        <div>
                            <label for="add_first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" id="add_first_name" name="first_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., Alan">
                        </div>
                        
                        <div>
                            <label for="add_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" id="add_email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., a.smith@school.edu">
                        </div>
                        
                        <div class="pt-4 border-t flex justify-end">
                            <button type="submit" id="saveTeacherBtn" class="w-full sm:w-auto bg-primary-blue hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-lg transition duration-150 shadow-md flex items-center justify-center space-x-2">
                                <i data-lucide="save" class="w-5 h-5" id="saveTeacherIcon"></i>
                                <span id="saveTeacherText">Register Teacher</span>
                                <svg id="loadingTeacherSpinner" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>