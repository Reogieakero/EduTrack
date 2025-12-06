<div id="editStudentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden" aria-modal="true" role="dialog" aria-labelledby="edit-modal-title">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform transition-all duration-300 scale-95 opacity-0" id="editModalContent">
        <div class="p-6">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center space-x-2" id="edit-modal-title">
                    <i data-lucide="square-pen" class="w-6 h-6 text-primary-blue"></i>
                    <span>Edit Student Record</span>
                </h3>
                <button id="closeEditModalBtn" class="text-gray-400 hover:text-gray-600 p-1 rounded-full transition duration-150">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <form method="POST" action="students.php" class="space-y-5 pt-6" id="editStudentForm">
                <input type="hidden" name="action" value="edit_student">
                <input type="hidden" id="edit_student_id" name="student_id" required value=""> <div>
                    <label for="edit_first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" id="edit_first_name" name="first_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., Jane">
                </div>

                <div>
                    <label for="edit_last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" id="edit_last_name" name="last_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., Doe">
                </div>
                
                <div>
                    <label for="edit_middle_initial" class="block text-sm font-medium text-gray-700 mb-1">Middle Initial (Optional)</label>
                    <input type="text" id="edit_middle_initial" name="middle_initial" maxlength="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., A">
                </div>
                <div>
                    <label for="edit_date_of_birth" class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <div class="relative">
                        <input type="date" id="edit_date_of_birth" name="date_of_birth" required 
                               class="w-full pl-4 pr-10 py-2 border border-gray-300 bg-white rounded-lg shadow-md 
                                      focus:ring-primary-blue focus:border-primary-blue transition duration-150 
                                      custom-date-input">
                        
                        <i data-lucide="calendar-check" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign Section</label>
                    
                    <input type="hidden" id="edit_section_id" name="section_id" required value="">

                    <div id="edit-custom-section-select" class="relative">
                        <button type="button" 
                                id="editSectionSelectButton"
                                class="flex justify-between items-center w-full py-2 pl-4 pr-3 border border-gray-300 bg-white 
                                       rounded-lg shadow-md hover:border-primary-blue/50 
                                       text-gray-700 font-medium cursor-pointer 
                                       focus:outline-none focus:ring-2 focus:ring-primary-blue focus:border-primary-blue 
                                       transition duration-200"
                                aria-haspopup="listbox"
                                aria-expanded="false"
                                onclick="toggleDropdown(this, 'edit')"> 
                            
                            <span id="edit-selected-section-text" class="truncate text-sm text-gray-400">-- Select a Section --</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 ml-2 text-gray-500"></i>
                        </button>
                        
                        <ul id="edit-section-options-list" 
                            class="absolute z-10 w-full mt-2 bg-white border border-gray-200 
                                   rounded-lg shadow-xl focus:outline-none hidden max-h-60 overflow-y-auto" 
                            tabindex="-1" role="listbox" 
                            aria-labelledby="edit-selected-section-text">
                            
                            <li class="py-2 px-3 text-gray-400 text-sm" role="option" aria-disabled="true">-- Select a Section --</li>

                            <?php 
                            // Re-use $sections_list from students.php
                            if (isset($sections_list) && is_array($sections_list)):
                                foreach ($sections_list as $id => $section):
                                    $option_display_name = htmlspecialchars($section['year'] . ' - ' . $section['name'] . ' (Teacher: ' . $section['teacher'] . ')');
                            ?>
                            <li class="py-2 px-3 cursor-pointer text-gray-900 text-sm transition-colors duration-150 hover:bg-gray-100"
                                role="option" 
                                data-value="<?php echo $id; ?>"
                                data-display="<?php echo $option_display_name; ?>"
                                onclick="handleSectionSelect(this, 'edit')">
                                
                                <?php echo $option_display_name; ?>
                                
                                <i data-lucide="check" class="w-4 h-4 inline-block ml-2 align-middle hidden edit-section-check-icon"></i>
                            </li>
                            <?php 
                                endforeach; 
                            endif;
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="pt-4 border-t flex justify-end">
                    <button type="submit" id="updateStudentBtn" class="w-full sm:w-auto bg-primary-blue hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-lg transition duration-150 shadow-md flex items-center justify-center space-x-2">
                        <i data-lucide="save" class="w-5 h-5" id="updateIcon"></i>
                        <span id="updateText">Save Changes</span>
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

<script>
    // Generalizing the dropdown functions for reusability between add and edit
    function toggleDropdown(buttonElement, type = 'add') {
        const listId = type === 'edit' ? 'edit-section-options-list' : 'section-options-list';
        const optionsList = document.getElementById(listId);
        const isHidden = optionsList.classList.contains('hidden');
        
        // Close all other dropdowns if necessary (optional)
        document.querySelectorAll('.relative > ul').forEach(list => {
            if (list !== optionsList) {
                list.classList.add('hidden');
                list.previousElementSibling.setAttribute('aria-expanded', 'false');
            }
        });

        if (isHidden) {
            optionsList.classList.remove('hidden');
            buttonElement.setAttribute('aria-expanded', 'true');
        } else {
            optionsList.classList.add('hidden');
            buttonElement.setAttribute('aria-expanded', 'false');
        }
    }

    function handleSectionSelect(listItem, type = 'add') {
        const sectionId = listItem.getAttribute('data-value');
        const sectionDisplay = listItem.getAttribute('data-display');

        const sectionIdInput = document.getElementById(type === 'edit' ? 'edit_section_id' : 'modal_section_id');
        const buttonTextSpan = document.getElementById(type === 'edit' ? 'edit-selected-section-text' : 'selected-section-text');
        const optionsList = document.getElementById(type === 'edit' ? 'edit-section-options-list' : 'section-options-list');
        const checkIconClass = type === 'edit' ? 'edit-section-check-icon' : 'section-check-icon';
        const selectButton = document.getElementById(type === 'edit' ? 'editSectionSelectButton' : 'sectionSelectButton');
        const primaryColorClass = type === 'edit' ? 'bg-primary-blue' : 'bg-primary-green';

        sectionIdInput.value = sectionId;
        buttonTextSpan.textContent = sectionDisplay;
        buttonTextSpan.classList.remove('text-gray-400');
        buttonTextSpan.classList.add('text-gray-900');

        optionsList.querySelectorAll('li').forEach(li => {
            li.classList.remove('bg-primary-blue', 'bg-primary-green', 'text-white', 'font-semibold');
            li.classList.add('hover:bg-gray-100');
            const checkIcon = li.querySelector(`.${checkIconClass}`);
            if (checkIcon) checkIcon.classList.add('hidden');
        });

        listItem.classList.add(primaryColorClass, 'text-white', 'font-semibold');
        listItem.classList.remove('hover:bg-gray-100');
        const checkIcon = listItem.querySelector(`.${checkIconClass}`);
        if (checkIcon) checkIcon.classList.remove('hidden');
        
        optionsList.classList.add('hidden');
        selectButton.setAttribute('aria-expanded', 'false');
    }

    // Update global click listener to handle both modals
    document.addEventListener('click', function(event) {
        const addCustomSelect = document.getElementById('custom-section-select');
        const editCustomSelect = document.getElementById('edit-custom-section-select');
        
        // Handle Add Modal dropdown
        if (addCustomSelect && !addCustomSelect.contains(event.target) && !document.getElementById('addStudentModal').classList.contains('hidden')) {
            const optionsList = document.getElementById('section-options-list');
            const selectButton = document.getElementById('sectionSelectButton');
            
            if (optionsList && !optionsList.classList.contains('hidden')) {
                optionsList.classList.add('hidden');
                selectButton.setAttribute('aria-expanded', 'false');
            }
        }

        // Handle Edit Modal dropdown
        if (editCustomSelect && !editCustomSelect.contains(event.target) && !document.getElementById('editStudentModal').classList.contains('hidden')) {
            const optionsList = document.getElementById('edit-section-options-list');
            const selectButton = document.getElementById('editSectionSelectButton');
            
            if (optionsList && !optionsList.classList.contains('hidden')) {
                optionsList.classList.add('hidden');
                selectButton.setAttribute('aria-expanded', 'false');
            }
        }
    });

</script>

<style>
/* CSS for date input styling (copied from add_student_modal.php or similar) */
.custom-date-input::-webkit-calendar-picker-indicator {
    opacity: 0;
    position: absolute; 
    width: 100%;
    height: 100%;
    cursor: pointer;
    left: 0;
    top: 0;
    right: -10px;
}
.custom-date-input {
    -moz-appearance: none; 
    appearance: none;
}
.custom-date-input {
    padding-right: 2.5rem; 
}
</style>