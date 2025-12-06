<div id="teacherAssignmentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden" aria-modal="true" role="dialog" aria-labelledby="modal-title-assign">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform transition-all duration-300 scale-95 opacity-0" id="assignTeacherModalContent">
        <div class="p-6">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center space-x-2" id="modal-title-assign">
                    <i data-lucide="link" class="w-6 h-6 text-primary-green"></i>
                    <span>Assign Teacher to Section</span>
                </h3>
                <button id="closeAssignModalBtn" type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded-full transition duration-150">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="pt-4">
                <p class="text-sm text-gray-500 mb-5">
                    Link <strong id="assign_teacher_name" class="font-semibold text-gray-800"></strong> to an academic section. This will unassign them from any previous section.
                </p>
                <div class="tab-content pt-1 space-y-5">
                    <form method="POST" action="teachers.php" class="space-y-5" id="teacherAssignmentForm">
                        <input type="hidden" name="action" value="assign_teacher">
                        <input type="hidden" name="teacher_id" id="assign_teacher_id">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Section Assignment</label>
                            
                            <input type="hidden" id="assign_section_id_input" name="section_id" required value="">

                            <div id="assign-custom-section-select" class="relative">
                                <button type="button" 
                                        id="assignSectionSelectButton"
                                        class="flex justify-between items-center w-full py-2 pl-4 pr-3 border border-gray-300 bg-white 
                                                rounded-lg shadow-md hover:border-primary-blue/50 
                                                text-gray-700 font-medium cursor-pointer 
                                                focus:outline-none focus:ring-2 focus:ring-primary-blue focus:border-primary-blue 
                                                transition duration-200"
                                        aria-haspopup="listbox"
                                        aria-expanded="false"
                                        onclick="toggleDropdown(this, 'assign')"> 
                                    
                                    <span id="assign-selected-section-text" class="truncate text-sm text-gray-400">-- Select a Section --</span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 ml-2 text-gray-500"></i>
                                </button>
                                
                                <ul id="assign-section-options-list" 
                                    class="absolute z-10 w-full mt-2 bg-white border border-gray-200 
                                            rounded-lg shadow-xl focus:outline-none hidden max-h-60 overflow-y-auto" 
                                    tabindex="-1" role="listbox" 
                                    aria-labelledby="assign-selected-section-text">
                                    
                                    <li class="py-2 px-3 cursor-pointer text-gray-400 text-sm transition-colors duration-150 hover:bg-gray-100 flex justify-between items-center" 
                                        role="option" 
                                        data-value="" 
                                        data-display="-- Select a Section --" 
                                        data-subtext="(Unassign Teacher)"
                                        onclick="handleSectionSelect(this, 'assign')">
                                        
                                        <span>-- Select a Section --</span>
                                        <div class="text-xs text-gray-500 flex items-center">
                                            (Unassign Teacher)
                                            <i data-lucide="check" class="w-4 h-4 ml-2 align-middle hidden assign-section-check-icon text-primary-blue"></i>
                                        </div>
                                    </li>

                                    <?php 
                                    // Assumes $sections_list is available from the teacher_controller.php
                                    if (isset($sections_list) && is_array($sections_list)):
                                        foreach ($sections_list as $section): 
                                            // Determine current teacher's status for the section
                                            $teacher_status = $section['teacher'] ? ' (Teacher: ' . htmlspecialchars($section['teacher']) . ')' : ' (Unassigned)';
                                            $option_display_name = htmlspecialchars($section['year'] . ' - ' . $section['name']);
                                    ?>
                                    <li class="py-2 px-3 cursor-pointer text-gray-900 text-sm transition-colors duration-150 hover:bg-gray-100 flex justify-between items-center"
                                        role="option" 
                                        data-value="<?php echo htmlspecialchars($section['id']); ?>"
                                        data-display="<?php echo $option_display_name; ?>"
                                        data-subtext="<?php echo $teacher_status; ?>"
                                        onclick="handleSectionSelect(this, 'assign')">
                                        
                                        <span><?php echo $option_display_name; ?></span>
                                        <div class="text-xs text-gray-500 flex items-center">
                                            <?php echo $teacher_status; ?>
                                            <i data-lucide="check" class="w-4 h-4 ml-2 align-middle hidden assign-section-check-icon text-primary-blue"></i>
                                        </div>
                                    </li>
                                    <?php 
                                        endforeach; 
                                    endif;
                                    ?>
                                </ul>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Selecting an already assigned section will override the current teacher. Selecting "-- Select a Section --" will unassign the teacher.
                            </p>
                        </div>
                        
                        <div class="pt-4 border-t flex justify-end">
                            <button type="submit" id="saveAssignmentBtn" class="w-full sm:w-auto bg-primary-green hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded-lg transition duration-150 shadow-md flex items-center justify-center space-x-2">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                <span>Finalize Assignment</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>