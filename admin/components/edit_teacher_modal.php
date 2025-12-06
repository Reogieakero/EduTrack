<div id="editTeacherModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden" aria-modal="true" role="dialog" aria-labelledby="modal-title-edit">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform transition-all duration-300 scale-95 opacity-0" id="editTeacherModalContent">
        <div class="p-6">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center space-x-2" id="modal-title-edit">
                    <i data-lucide="square-pen" class="w-6 h-6 text-primary-blue"></i>
                    <span>Edit Teacher Details</span>
                </h3>
                <button id="closeEditModalBtn" type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded-full transition duration-150">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="pt-4">
                <div class="tab-content pt-6 space-y-5">
                    <form method="POST" action="teachers.php" class="space-y-5" id="editTeacherForm">
                        <input type="hidden" name="action" value="update_teacher">
                        <input type="hidden" name="teacher_id" id="edit_teacher_id">

                        <div>
                            <label for="edit_last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" name="edit_last_name" id="edit_last_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm">
                        </div>
                        <div>
                            <label for="edit_first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" name="edit_first_name" id="edit_first_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm">
                        </div>
                         <div>
                            <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="edit_email" id="edit_email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-blue focus:border-primary-blue transition duration-150 shadow-sm">
                        </div>

                        <div class="pt-4 border-t flex justify-end">
                            <button type="submit" id="updateTeacherBtn" class="w-full sm:w-auto bg-primary-blue hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-lg transition duration-150 shadow-md flex items-center justify-center space-x-2">
                                <i data-lucide="save" class="w-5 h-5"></i>
                                <span>Update Details</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>