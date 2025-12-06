<div id="editSubjectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden" aria-modal="true" role="dialog" aria-labelledby="edit-modal-title">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform transition-all duration-300 scale-95 opacity-0" id="editModalContent">
        <div class="p-6">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center space-x-2" id="edit-modal-title">
                    <i data-lucide="pencil" class="w-6 h-6 text-primary-green"></i>
                    <span>Edit Subject Details</span>
                </h3>
                <button id="closeEditModalBtn" class="text-gray-400 hover:text-gray-600 p-1 rounded-full transition duration-150">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <form method="POST" action="subjects.php" class="space-y-5 pt-6" id="editSubjectForm">
                <input type="hidden" name="action" value="update_subject">
                <input type="hidden" name="subject_id" id="edit_subject_id">

                <div>
                    <label for="edit_subject_code" class="block text-sm font-medium text-gray-700 mb-2">Subject Code *</label>
                    <input type="text" id="edit_subject_code" name="edit_subject_code" required maxlength="10"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-green focus:border-primary-green transition duration-150 shadow-sm"
                           placeholder="e.g., MATH101">
                    <p class="mt-1 text-xs text-gray-500">Unique code for the subject (max 10 chars).</p>
                </div>

                <div>
                    <label for="edit_subject_name" class="block text-sm font-medium text-gray-700 mb-2">Subject Name *</label>
                    <input type="text" id="edit_subject_name" name="edit_subject_name" required maxlength="100"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-green focus:border-primary-green transition duration-150 shadow-sm"
                           placeholder="e.g., General Mathematics">
                </div>

                <div>
                    <label for="edit_year_level" class="block text-sm font-medium text-gray-700 mb-2">Year Level *</label>
                    <select id="edit_year_level" name="edit_year_level" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-green focus:border-primary-green transition duration-150 shadow-sm bg-white">
                        <option value="">Select Year Level</option>
                        </select>
                    <p class="mt-1 text-xs text-gray-500">The grade level for this subject.</p>
                </div>

                <div class="pt-4 border-t flex justify-end">
                    <button type="submit" id="updateSubjectBtn" class="w-full sm:w-auto bg-primary-green hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded-lg transition duration-150 shadow-md flex items-center justify-center space-x-2">
                        <i data-lucide="check" class="w-5 h-5" id="updateIcon"></i>
                        <span id="updateText">Update Subject</span>
                        <svg id="updateLoadingSpinner" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>