<div id="addStudentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden" aria-modal="true" role="dialog" aria-labelledby="modal-title">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
        <div class="p-6">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center space-x-2" id="modal-title">
                    <i data-lucide="user-plus" class="w-6 h-6 text-primary-green"></i>
                    <span>Enroll New Student</span>
                </h3>
                <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600 p-1 rounded-full transition duration-150">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <form method="POST" action="students.php" class="space-y-5 pt-6" id="addStudentForm">
                <input type="hidden" name="action" value="add_student">

                <div>
                    <label for="modal_first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" id="modal_first_name" name="first_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-green focus:border-primary-green transition duration-150 shadow-sm" placeholder="e.g., Jane">
                </div>

                <div>
                    <label for="modal_last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" id="modal_last_name" name="last_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-green focus:border-primary-green transition duration-150 shadow-sm" placeholder="e.g., Doe">
                </div>

                <div>
                    <label for="modal_date_of_birth" class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input type="date" id="modal_date_of_birth" name="date_of_birth" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-green focus:border-primary-green transition duration-150 shadow-sm">
                </div>
                
                <div>
                    <label for="modal_section_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Section</label>
                    <select id="modal_section_id" name="section_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-green focus:border-primary-green transition duration-150 shadow-sm">
                        <option value="">-- Select a Section --</option>
                        <?php 
                        // Note: $sections_list is passed from students.php
                        if (isset($sections_list) && is_array($sections_list)):
                            foreach ($sections_list as $id => $section):
                        ?>
                        <option value="<?php echo $id; ?>">
                            <?php echo htmlspecialchars($section['year'] . ' - ' . $section['name'] . ' (Teacher: ' . $section['teacher'] . ')'); ?>
                        </option>
                        <?php 
                            endforeach; 
                        endif;
                        ?>
                    </select>
                </div>

                <div class="pt-4 border-t flex justify-end">
                    <button type="submit" id="saveStudentBtn" class="w-full sm:w-auto bg-primary-green hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded-lg transition duration-150 shadow-md flex items-center justify-center space-x-2">
                        <i data-lucide="save" class="w-5 h-5" id="saveIcon"></i>
                        <span id="saveText">Enroll Student</span>
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