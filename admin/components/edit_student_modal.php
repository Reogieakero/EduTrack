<?php
// components/edit_student_modal.php
// Assumes $sections_list is available from students.php
?>

<div id="editStudentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden" aria-modal="true" role="dialog" aria-labelledby="edit-student-modal-title">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform transition-all duration-300 scale-95 opacity-0" id="editStudentModalContent">
        <div class="p-6">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center space-x-2" id="edit-student-modal-title">
                    <i data-lucide="square-pen" class="w-6 h-6 text-primary-blue"></i>
                    <span>Edit Student Details</span>
                </h3>
                <button id="closeEditStudentModalBtn" class="text-gray-400 hover:text-gray-600 p-1 rounded-full transition duration-150">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <form method="POST" action="students.php" class="space-y-5 pt-6" id="editStudentForm">
                <input type="hidden" name="action" value="update_student">
                <input type="hidden" name="student_id" id="edit_student_id">

                <div>
                    <label for="edit_modal_first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" id="edit_modal_first_name" name="first_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., Jane">
                </div>

                <div>
                    <label for="edit_modal_last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" id="edit_modal_last_name" name="last_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm" placeholder="e.g., Doe">
                </div>

                <div>
                    <label for="edit_modal_date_of_birth" class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <div class="relative">
                        <input type="date" id="edit_modal_date_of_birth" name="date_of_birth" required 
                               class="w-full pl-4 pr-10 py-2 border border-gray-300 bg-white rounded-lg shadow-md 
                                      focus:ring-primary-blue focus:border-primary-blue transition duration-150 
                                      custom-date-input">
                        
                        <i data-lucide="calendar-check" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign Section</label>
                    
                    <input type="hidden" id="edit_modal_section_id" name="section_id" required value="">

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
                                onclick="editToggleDropdown(this)">
                            
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
                            if (isset($sections_list) && is_array($sections_list)):
                                foreach ($sections_list as $id => $section):
                                    $option_display_name = htmlspecialchars($section['year'] . ' - ' . $section['name'] . ' (Teacher: ' . $section['teacher'] . ')');
                            ?>
                            <li class="py-2 px-3 cursor-pointer text-gray-900 text-sm transition-colors duration-150 hover:bg-gray-100"
                                role="option" 
                                data-value="<?php echo $id; ?>"
                                data-display="<?php echo $option_display_name; ?>"
                                onclick="editHandleSectionSelect(this)">
                                
                                <?php echo $option_display_name; ?>
                                
                                <i data-lucide="check" class="w-4 h-4 inline-block ml-2 align-middle hidden section-check-icon"></i>
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
                        <span id="updateText">Update Student</span>
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