<div id="addSectionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden" aria-modal="true" role="dialog" aria-labelledby="modal-title">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
        <div class="p-6">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center space-x-2" id="modal-title">
                    <i data-lucide="plus-circle" class="w-6 h-6 text-primary-blue"></i>
                    <span>Create New Section</span>
                </h3>
                <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600 p-1 rounded-full transition duration-150">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <form method="POST" action="sections.php" class="space-y-5 pt-6">
                <input type="hidden" name="action" value="add_section">
                <div>
                    <label for="modal_section_name" class="block text-sm font-medium text-gray-700 mb-1">Section Name</label>
                    <input type="text" id="modal_section_name" name="section_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., Grade 9 - Ruby">
                </div>
                <div>
                    <label for="modal_teacher_name" class="block text-sm font-medium text-gray-700 mb-1">Assigned Teacher</label>
                    <input type="text" id="modal_teacher_name" name="teacher_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., Mr. Jonathan Doe">
                </div>

                <div class="pt-4 border-t flex justify-end">
                    <button type="submit" class="w-full sm:w-auto bg-primary-blue hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-lg transition duration-150 shadow-md flex items-center justify-center space-x-2">
                        <i data-lucide="save" class="w-5 h-5"></i>
                        <span>Save Section</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>