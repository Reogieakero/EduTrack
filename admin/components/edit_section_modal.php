<div id="editSectionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden" aria-modal="true" role="dialog" aria-labelledby="edit-modal-title">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform transition-all duration-300 scale-95 opacity-0" id="editModalContent">
        <div class="p-6">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center space-x-2" id="edit-modal-title">
                    <i data-lucide="pencil" class="w-6 h-6 text-primary-green"></i>
                    <span>Edit Section Details</span>
                </h3>
                <button id="closeEditModalBtn" class="text-gray-400 hover:text-gray-600 p-1 rounded-full transition duration-150">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <form method="POST" action="sections.php" class="space-y-5 pt-6" id="editSectionForm">
                <input type="hidden" name="action" value="update_section">
                <input type="hidden" name="section_id" id="edit_section_id">

                <div>
                    <label for="edit_modal_section_name" class="block text-sm font-medium text-gray-700 mb-2">Section Name</label>
                    <input type="text" id="edit_modal_section_name" name="edit_section_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., Beryl, Block 1A, Section 7-1">
                </div>

                <div class="pt-2">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Academic Year</label>
                    <div class="flex flex-wrap gap-3">
                        <?php 
                        $years = ['Year 7', 'Year 8', 'Year 9', 'Year 10', 'Year 11', 'Year 12'];
                        foreach ($years as $year):
                        ?>
                        <label class="inline-flex items-center">
                            <input type="radio" name="edit_section_year" value="<?php echo $year; ?>" required class="form-radio h-4 w-4 text-primary-green focus:ring-primary-green">
                            <span class="ml-2 text-gray-700 text-sm"><?php echo $year; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label for="edit_modal_teacher_name" class="block text-sm font-medium text-gray-700 mb-1">Assigned Teacher</label>
                    <input type="text" id="edit_modal_teacher_name" name="edit_teacher_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., Mr. Jonathan Doe">
                </div>

                <div class="pt-4 border-t flex justify-end">
                    <button type="submit" id="updateSectionBtn" class="w-full sm:w-auto bg-primary-green hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded-lg transition duration-150 shadow-md flex items-center justify-center space-x-2">
                        <i data-lucide="check" class="w-5 h-5" id="updateIcon"></i>
                        <span id="updateText">Update Section</span>
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