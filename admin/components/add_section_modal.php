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
            
            <form method="POST" action="sections.php" class="space-y-5 pt-6" id="addSectionForm">
                <input type="hidden" name="action" value="add_section">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                    <div class="flex flex-wrap gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <?php 
                        $academic_years = ['Year 7', 'Year 8', 'Year 9', 'Year 10', 'Year 11', 'Year 12'];
                        foreach ($academic_years as $year):
                        ?>
                        <label for="year_<?php echo str_replace(' ', '_', $year); ?>" class="flex items-center space-x-2 cursor-pointer p-2 rounded-lg transition duration-150 border border-transparent hover:border-primary-blue/50">
                            <input type="radio" id="year_<?php echo str_replace(' ', '_', $year); ?>" name="section_year" value="<?php echo htmlspecialchars($year); ?>" required class="form-radio text-primary-blue h-4 w-4 focus:ring-primary-blue">
                            <span class="text-sm font-medium text-gray-700"><?php echo $year; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label for="modal_section_name" class="block text-sm font-medium text-gray-700 mb-1">Section Name (e.g., Diamond, Sapphire)</label>
                    <input type="text" id="modal_section_name" name="section_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., Diamond">
                </div>
                
                <div>
                    <label for="modal_teacher_name" class="block text-sm font-medium text-gray-700 mb-1">Assigned Teacher</label>
                    <select id="modal_teacher_name" name="teacher_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm bg-white">
                        <option value="" disabled selected>Select a Teacher</option>
                        <option value="Unassigned">Unassigned (No Teacher)</option>
                        <?php 
                        // Assuming $all_teachers is available from sections.php
                        if (isset($all_teachers)):
                            foreach ($all_teachers as $teacher):
                                // Only show teachers who are currently unassigned (assigned_section_id is 0 or NULL)
                                if ((int)$teacher['assigned_section_id'] === 0):
                        ?>
                        <option value="<?php echo htmlspecialchars($teacher['full_name']); ?>">
                            <?php echo htmlspecialchars($teacher['full_name']); ?>
                        </option>
                        <?php 
                                endif;
                            endforeach; 
                        endif;
                        ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Only currently unassigned teachers are available for selection.</p>
                </div>
                <div class="pt-4 border-t flex justify-end">
                    <button type="submit" id="saveSectionBtn" class="w-full sm:w-auto bg-primary-blue hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-lg transition duration-150 shadow-md flex items-center justify-center space-x-2">
                        <i data-lucide="save" class="w-5 h-5" id="saveIcon"></i>
                        <span id="saveText">Save Section</span>
                        <svg id="loadingSpinner" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // This script should be added within sections.php document.addEventListener('DOMContentLoaded', ...) block
    document.addEventListener('DOMContentLoaded', function() {
        const addSectionForm = document.getElementById('addSectionForm');
        const saveSectionBtn = document.getElementById('saveSectionBtn');
        const saveIcon = document.getElementById('saveIcon');
        const saveText = document.getElementById('saveText');
        const loadingSpinner = document.getElementById('loadingSpinner');

        if (addSectionForm) {
            addSectionForm.addEventListener('submit', function(event) {
                // Check if all required fields are filled before showing spinner
                if (addSectionForm.checkValidity()) {
                    // Show loading state
                    saveIcon.classList.add('hidden');
                    saveText.textContent = 'Saving...';
                    loadingSpinner.classList.remove('hidden');
                    saveSectionBtn.disabled = true; // Prevent multiple submissions
                    saveSectionBtn.classList.remove('hover:bg-blue-700');
                    saveSectionBtn.classList.add('opacity-70', 'cursor-not-allowed');
                }
            });
        }
    });
</script>