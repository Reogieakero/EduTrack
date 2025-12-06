function toggleDropdown(buttonElement, type) {
    let listId = '';
    if (type === 'assign') {
        listId = 'assign-section-options-list';
    } else if (type === 'edit') {
        listId = 'edit-section-options-list';
    } else {
        return;
    }
    
    const optionsList = document.getElementById(listId);
    if (!optionsList) return;

    const isHidden = optionsList.classList.contains('hidden');
    
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

function handleSectionSelect(listItem, type) {
    const sectionId = listItem.getAttribute('data-value');
    const sectionDisplay = listItem.getAttribute('data-display');

    const prefix = type === 'assign' ? 'assign' : 'edit';
    
    const checkIconClass = type === 'assign' ? 'assign-section-check-icon' : 'edit-section-check-icon';
    
    const sectionIdInput = document.getElementById(`${prefix}_section_id_input`);
    const buttonTextSpan = document.getElementById(`${prefix}-selected-section-text`);
    const optionsList = document.getElementById(`${prefix}-section-options-list`);
    const selectButton = document.getElementById(`${prefix}SectionSelectButton`);

    if (!sectionIdInput || !buttonTextSpan || !optionsList || !selectButton) return;

    sectionIdInput.value = sectionId;
    buttonTextSpan.textContent = sectionDisplay;
    buttonTextSpan.classList.remove('text-gray-400');
    buttonTextSpan.classList.add('text-gray-900', 'font-medium');

    optionsList.querySelectorAll('li').forEach(li => {
        li.classList.remove('bg-primary-blue', 'bg-primary-green', 'text-white', 'font-semibold', 'bg-gray-100');
        
        li.classList.add('hover:bg-gray-100', 'text-gray-900');
        
        const checkIcon = li.querySelector(`.${checkIconClass}`);
        if (checkIcon) {
            checkIcon.classList.add('hidden');
            checkIcon.classList.add('text-primary-blue');
        }
        
        const subtextSpan = li.querySelector('.text-xs');
        if (subtextSpan) subtextSpan.classList.remove('text-white');
    });

    listItem.classList.add('bg-gray-100', 'text-gray-900', 'font-semibold');
    listItem.classList.remove('hover:bg-gray-100');
    
    listItem.classList.remove('bg-primary-blue', 'bg-primary-green', 'text-white');

    const subtextSpanSelected = listItem.querySelector('.text-xs');
    if (subtextSpanSelected) subtextSpanSelected.classList.remove('text-white');

    const checkIcon = listItem.querySelector(`.${checkIconClass}`);
    if (checkIcon) checkIcon.classList.remove('hidden');
    
    optionsList.classList.add('hidden');
    selectButton.setAttribute('aria-expanded', 'false');
}

window.toggleDropdown = toggleDropdown;
window.handleSectionSelect = handleSectionSelect;

document.addEventListener('DOMContentLoaded', function() {
    
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const addModal = document.getElementById('addTeacherModal');
    const addModalContent = document.getElementById('teacherModalContent');
    const openAddModalBtn = document.getElementById('openAddModalBtn');
    const closeAddModalBtn = document.getElementById('closeAddModalBtn');
    const addTeacherForm = document.getElementById('addTeacherForm');
    
    const editModal = document.getElementById('editTeacherModal');
    const editModalContent = document.getElementById('editTeacherModalContent');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const editTeacherForm = document.getElementById('editTeacherForm');

    const assignModal = document.getElementById('teacherAssignmentModal');
    const assignModalContent = document.getElementById('assignTeacherModalContent');
    const closeAssignModalBtn = document.getElementById('closeAssignModalBtn');
    const teacherAssignmentForm = document.getElementById('teacherAssignmentForm');
    
    const deleteModal = document.getElementById('deleteConfirmationModal'); 
    const deleteModalContent = document.getElementById('deleteModalContent'); 
    const successModal = document.getElementById('successModal');
    const loadingOverlay = document.getElementById('loadingOverlay'); 

    const animateModal = (modal, content, show) => {
        if (!modal || !content) return; 

        if (show) {
            modal.classList.remove('hidden');
            void content.offsetWidth;
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        } else {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            
            content.addEventListener('transitionend', function handler() {
                modal.classList.add('hidden');
                content.removeEventListener('transitionend', handler);
            }, { once: true });
        }
    };

    const showModal = (modal, content) => animateModal(modal, content, true);
    const hideModal = (modal, content) => animateModal(modal, content, false);
    
    const showSimpleModal = (modal) => modal?.classList.remove('hidden');
    const hideSimpleModal = (modal) => modal?.classList.add('hidden');

    const showSuccessModal = (title, message) => {
        if (!successModal) return;
        
        // CRITICAL: Ensure modal content is visible when triggered on page load
        const titleElement = document.getElementById('success-modal-title');
        const messageElement = document.getElementById('success-modal-description');
        const contentElement = document.getElementById('successModalContent'); 
        
        if (titleElement) {
            titleElement.textContent = title;
        }
        if (messageElement) {
            messageElement.innerHTML = message;
        }
        
        document.getElementById('success-modal-errors')?.classList.add('hidden');
        messageElement?.classList.remove('hidden');
        
        if (contentElement) {
            contentElement.classList.remove('scale-95', 'opacity-0');
            contentElement.classList.add('scale-100', 'opacity-100');
        }
        
        showSimpleModal(successModal); 
    };
    
    document.getElementById('closeSuccessModalBtn')?.addEventListener('click', () => hideSimpleModal(successModal));
    document.getElementById('okSuccessModalBtn')?.addEventListener('click', () => hideSimpleModal(successModal)); 

    // --- Success Triggers ---
    if (typeof successDetails !== 'undefined' && successDetails) {
        showSuccessModal('Teacher Registered Successfully!', 
            `<p><strong>${successDetails.name}</strong> has been successfully registered.</p>
             <p class="mt-1 text-xs text-gray-400">Email: ${successDetails.email}</p>`
        );
        history.replaceState(null, null, 'teachers.php'); 
    }
     if (typeof editSuccessDetails !== 'undefined' && editSuccessDetails) {
        showSuccessModal('Teacher Updated Successfully!', 
            `<p><strong>${editSuccessDetails.name}</strong>'s details have been updated.</p>
             <p class="mt-1 text-xs text-gray-400">Email: ${editSuccessDetails.email}</p>`
        );
        history.replaceState(null, null, 'teachers.php'); 
    }
    if (typeof deleteSuccessDetails !== 'undefined' && deleteSuccessDetails) {
        showSuccessModal('Teacher Deleted Successfully!', 
            `<p>The record for <strong>${deleteSuccessDetails.name}</strong> has been removed.</p>`
        );
        history.replaceState(null, null, 'teachers.php'); 
    }
    if (typeof assignSuccessDetails !== 'undefined' && assignSuccessDetails) {
        showSuccessModal('Section Assigned Successfully!', 
            `<p><strong>${assignSuccessDetails.teacher}</strong> is now assigned to <strong>${assignSuccessDetails.section}</strong>.</p>`
        );
        history.replaceState(null, null, 'teachers.php'); 
    }
    
    // --- Add Teacher Logic ---
    openAddModalBtn?.addEventListener('click', () => {
        addTeacherForm?.reset();
        showModal(addModal, addModalContent);
    });
    closeAddModalBtn?.addEventListener('click', () => hideModal(addModal, addModalContent));
    
    addTeacherForm?.addEventListener('submit', () => {
       
        hideModal(addModal, addModalContent);
        showSimpleModal(loadingOverlay);
    });


    // --- Edit Teacher Logic ---
    const initiateEditAction = (teacherId) => {
        
        // 1. Show loading overlay immediately as requested
        showSimpleModal(loadingOverlay); 

        // Check if teacher data is locally available
        const teacherData = (typeof teachersList !== 'undefined') ? teachersList.find(t => t.id == teacherId) : null;
        
        if (teacherData && editTeacherForm) {
            // SCENARIO 1: Data is local. Open modal instantly.
            document.getElementById('edit_teacher_id').value = teacherData.id;
            document.getElementById('edit_last_name').value = teacherData.last_name;
            document.getElementById('edit_first_name').value = teacherData.first_name;
            document.getElementById('edit_email').value = teacherData.email;
            
            editTeacherForm.querySelector('input[name="action"]').value = 'update_teacher';
            
            showModal(editModal, editModalContent);
            
            // 2. Hide loading overlay after the edit modal is displayed
            // A short delay (100ms) can ensure the modal transition starts before hiding the overlay, reducing flash.
            setTimeout(() => {
                hideSimpleModal(loadingOverlay);
            }, 100);

        } else if (editTeacherForm) {
             // SCENARIO 2: Data is NOT local. Send request to fetch (page reload).
             document.getElementById('edit_teacher_id').value = teacherId;
             editTeacherForm.action = 'teachers.php';
             editTeacherForm.querySelector('input[name="action"]').value = 'edit_teacher'; 
             editTeacherForm.submit(); 
             
             // Loading overlay remains visible until the page reloads.
        }
    };
    window.initiateEditAction = initiateEditAction;

    closeEditModalBtn?.addEventListener('click', () => hideModal(editModal, editModalContent));
    
    // --- Edit Form Submission ---
    editTeacherForm?.addEventListener('submit', () => {
        editTeacherForm.querySelector('input[name="action"]').value = 'update_teacher';
        hideModal(editModal, editModalContent);
        // Show overlay before form submission and page redirect
        showSimpleModal(loadingOverlay);
    });

    if (typeof teacherToEdit !== 'undefined' && teacherToEdit && editModal) {
         document.getElementById('edit_teacher_id').value = teacherToEdit.id;
         document.getElementById('edit_last_name').value = teacherToEdit.last_name;
         document.getElementById('edit_first_name').value = teacherToEdit.first_name;
         document.getElementById('edit_email').value = teacherToEdit.email;
         showModal(editModal, editModalContent);
         
         history.replaceState(null, null, 'teachers.php'); 
    }
    
    // --- Delete Confirmation Logic ---
    const confirmDeleteAction = (teacherId, teacherName) => {
        if (!deleteModal || !deleteModalContent) return;
        
        document.getElementById('delete_id_input').value = teacherId;
        document.getElementById('delete_action_input').value = 'delete_teacher';
        
        document.getElementById('deleteItemName').textContent = teacherName;
        document.getElementById('deleteItemType').textContent = 'teacher'; 
        showModal(deleteModal, deleteModalContent);
    };
    window.confirmDeleteAction = confirmDeleteAction;

    document.getElementById('cancelDeleteBtn')?.addEventListener('click', () => hideModal(deleteModal, deleteModalContent));
    
    document.getElementById('confirmDeleteBtn')?.addEventListener('click', () => {
        const deleteForm = document.getElementById('deleteConfirmationForm');
        if (deleteForm) {
            deleteForm.action = 'teachers.php'; 
            deleteForm.submit();
            hideModal(deleteModal, deleteModalContent);
            showSimpleModal(loadingOverlay);
        }
    });

    
    // --- Assignment Logic ---
    const initializeCustomSectionSelect = (currentSectionId, type) => {
        const prefix = type === 'assign' ? 'assign' : 'edit';
        const sectionIdInput = document.getElementById(`${prefix}_section_id_input`);
        const buttonTextSpan = document.getElementById(`${prefix}-selected-section-text`);
        const optionsList = document.getElementById(`${prefix}-section-options-list`);
        
        if (!sectionIdInput || !buttonTextSpan || !optionsList) return;

        sectionIdInput.value = '';
        buttonTextSpan.textContent = '-- Select a Section --';
        buttonTextSpan.classList.remove('text-gray-900', 'font-medium');
        buttonTextSpan.classList.add('text-gray-400');
        
        const unassignOption = optionsList.querySelector(`li[data-value=""]`);

        if (currentSectionId && currentSectionId != 0) {
            const selectedItem = optionsList.querySelector(`li[data-value="${currentSectionId}"]`);
            if (selectedItem) {
                handleSectionSelect(selectedItem, type);
                return;
            }
        } 
        
        if (unassignOption) {
            handleSectionSelect(unassignOption, type);
        }
    };
    

    const initiateAssignAction = (teacherId, teacherName, currentSectionId) => {
        if (!assignModal || !assignModalContent) return;

        document.getElementById('assign_teacher_id').value = teacherId;
        document.getElementById('assign_teacher_name').textContent = teacherName;
        
        initializeCustomSectionSelect(currentSectionId, 'assign');
        
        showModal(assignModal, assignModalContent);
    };
    window.initiateAssignAction = initiateAssignAction;

    closeAssignModalBtn?.addEventListener('click', () => hideModal(assignModal, assignModalContent));
    teacherAssignmentForm?.addEventListener('submit', () => {
        hideModal(assignModal, assignModalContent);
        showSimpleModal(loadingOverlay);
    });

    document.addEventListener('click', function(event) {
        const assignCustomSelect = document.getElementById('assign-custom-section-select');
        
        if (assignCustomSelect && !assignCustomSelect.contains(event.target) && !document.getElementById('teacherAssignmentModal').classList.contains('hidden')) {
            const optionsList = document.getElementById('assign-section-options-list');
            const selectButton = document.getElementById('assignSectionSelectButton');
            
            if (optionsList && !optionsList.classList.contains('hidden')) {
                optionsList.classList.add('hidden');
                selectButton.setAttribute('aria-expanded', 'false');
            }
        }
    });
});