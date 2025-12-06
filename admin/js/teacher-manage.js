// js/teacher-manage.js

// =================================================================
// START: Custom Dropdown Functions (MUST be exposed globally)
// =================================================================

/**
 * Toggles the visibility of the custom dropdown list.
 * @param {HTMLElement} buttonElement The button that was clicked.
 * @param {string} type The context ('assign' or 'edit').
 */
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
    
    // Close all other dropdowns in case multiple custom selects exist
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

/**
 * Handles the selection of an item in the custom dropdown.
 * @param {HTMLElement} listItem The list item that was clicked.
 * @param {string} type The context ('assign' or 'edit').
 */
function handleSectionSelect(listItem, type) {
    const sectionId = listItem.getAttribute('data-value');
    const sectionDisplay = listItem.getAttribute('data-display');

    // Determine IDs based on type
    const prefix = type === 'assign' ? 'assign' : 'edit';
    
    // REMOVED: const primaryColorClass = 'bg-primary-blue'; 
    const checkIconClass = type === 'assign' ? 'assign-section-check-icon' : 'edit-section-check-icon';
    
    // Select the correct DOM elements
    const sectionIdInput = document.getElementById(`${prefix}_section_id_input`);
    const buttonTextSpan = document.getElementById(`${prefix}-selected-section-text`);
    const optionsList = document.getElementById(`${prefix}-section-options-list`);
    const selectButton = document.getElementById(`${prefix}SectionSelectButton`);

    if (!sectionIdInput || !buttonTextSpan || !optionsList || !selectButton) return;

    // 1. Update Hidden Input and Display Button Text
    sectionIdInput.value = sectionId;
    buttonTextSpan.textContent = sectionDisplay;
    buttonTextSpan.classList.remove('text-gray-400');
    buttonTextSpan.classList.add('text-gray-900', 'font-medium');

    // 2. Reset highlights/checks on all list items
    optionsList.querySelectorAll('li').forEach(li => {
        // Reset color classes: Remove all custom background/text styles
        li.classList.remove('bg-primary-blue', 'bg-primary-green', 'text-white', 'font-semibold', 'bg-gray-100');
        
        // Restore default styles for unselected items
        li.classList.add('hover:bg-gray-100', 'text-gray-900');
        
        // Hide check icon
        const checkIcon = li.querySelector(`.${checkIconClass}`);
        if (checkIcon) {
            checkIcon.classList.add('hidden');
            // Ensure check icon color is the primary color (blue)
            checkIcon.classList.add('text-primary-blue');
        }
        
        // Restore subtext color to default (not white)
        const subtextSpan = li.querySelector('.text-xs');
        if (subtextSpan) subtextSpan.classList.remove('text-white');
    });

    // 3. Highlight the clicked item (Now using light gray background)
    listItem.classList.add('bg-gray-100', 'text-gray-900', 'font-semibold');
    listItem.classList.remove('hover:bg-gray-100'); // Remove hover so the selection persists
    
    // Ensure primary colors are removed in case they were cached
    listItem.classList.remove('bg-primary-blue', 'bg-primary-green', 'text-white');

    // Change subtext color on selection (Keep it dark gray/default when selection is light gray)
    const subtextSpanSelected = listItem.querySelector('.text-xs');
    if (subtextSpanSelected) subtextSpanSelected.classList.remove('text-white');

    // Show the check icon on the selected item
    const checkIcon = listItem.querySelector(`.${checkIconClass}`);
    if (checkIcon) checkIcon.classList.remove('hidden');
    
    // 4. Close Dropdown
    optionsList.classList.add('hidden');
    selectButton.setAttribute('aria-expanded', 'false');
}

window.toggleDropdown = toggleDropdown;
window.handleSectionSelect = handleSectionSelect;

// =================================================================
// END: Custom Dropdown Functions
// =================================================================


document.addEventListener('DOMContentLoaded', function() {
    
    // Ensure Lucide icons are rendered (necessary if any modal/component has icons)
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // --- DOM Elements (Must match IDs in the component files) ---
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
    
    // Assumed common component IDs
    const deleteModal = document.getElementById('deleteConfirmationModal'); 
    const deleteModalContent = document.getElementById('deleteModalContent'); 
    const successModal = document.getElementById('successModal');
    const loadingOverlay = document.getElementById('loadingOverlay'); 

    // --- Modal Animation Utility ---
    const animateModal = (modal, content, show) => {
        if (!modal || !content) return; // Safely exit if modal is null

        if (show) {
            modal.classList.remove('hidden');
            void content.offsetWidth; // Force reflow for transition
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

    // --- Success/Error Message Display ---
    const showSuccessModal = (title, message) => {
        if (!successModal) return;
        document.getElementById('successModalTitle').textContent = title;
        document.getElementById('successModalMessage').innerHTML = message;
        showSimpleModal(successModal);
    };
    
    // Success Modal Close Handler
    document.getElementById('closeSuccessModalBtn')?.addEventListener('click', () => hideSimpleModal(successModal));
    document.getElementById('okSuccessModalBtn')?.addEventListener('click', () => hideSimpleModal(successModal));

    // Display messages on page load
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
    
    // --- Add Modal Handlers ---
    openAddModalBtn?.addEventListener('click', () => {
        addTeacherForm?.reset();
        showModal(addModal, addModalContent);
    });
    closeAddModalBtn?.addEventListener('click', () => hideModal(addModal, addModalContent));
    
    addTeacherForm?.addEventListener('submit', () => {
        // Show loading spinner on the button (optional visual feedback)
        // Ensure you have these elements in add_teacher_modal.php if you use them
        // document.getElementById('saveTeacherIcon').classList.add('hidden');
        // document.getElementById('saveTeacherText').classList.add('hidden');
        // document.getElementById('loadingTeacherSpinner').classList.remove('hidden');

        // Show global overlay while processing
        hideModal(addModal, addModalContent);
        showSimpleModal(loadingOverlay);
    });


    // --- Edit Modal Handlers ---

    // 1. Initiate Edit (Exposed globally for row button)
    const initiateEditAction = (teacherId) => {
        // First, try to find the teacher data from the list passed by PHP
        const teacherData = (typeof teachersList !== 'undefined') ? teachersList.find(t => t.id == teacherId) : null;
        
        if (teacherData && editTeacherForm) {
            document.getElementById('edit_teacher_id').value = teacherData.id;
            document.getElementById('edit_last_name').value = teacherData.last_name;
            document.getElementById('edit_first_name').value = teacherData.first_name;
            document.getElementById('edit_email').value = teacherData.email;
            
            // Ensure the form action is correct for update
            editTeacherForm.querySelector('input[name="action"]').value = 'update_teacher';
            
            showModal(editModal, editModalContent);
        } else if (editTeacherForm) {
             // Fallback: If data is not available locally (i.e., filtered table), 
             // submit a POST request to the controller to fetch it and redirect back.
             document.getElementById('edit_teacher_id').value = teacherId;
             editTeacherForm.action = 'teachers.php';
             editTeacherForm.querySelector('input[name="action"]').value = 'edit_teacher'; 
             editTeacherForm.submit(); 
             
             showSimpleModal(loadingOverlay);
        }
    };
    window.initiateEditAction = initiateEditAction;

    // 2. Handle Edit form submission
    closeEditModalBtn?.addEventListener('click', () => hideModal(editModal, editModalContent));
    editTeacherForm?.addEventListener('submit', () => {
        // Ensure action is set to update before final submit
        editTeacherForm.querySelector('input[name="action"]').value = 'update_teacher';
        hideModal(editModal, editModalContent);
        showSimpleModal(loadingOverlay);
    });

    // 3. Trigger edit modal open if PHP redirects with data (for fallback method)
    if (typeof teacherToEdit !== 'undefined' && teacherToEdit && editModal) {
         document.getElementById('edit_teacher_id').value = teacherToEdit.id;
         document.getElementById('edit_last_name').value = teacherToEdit.last_name;
         document.getElementById('edit_first_name').value = teacherToEdit.first_name;
         document.getElementById('edit_email').value = teacherToEdit.email;
         showModal(editModal, editModalContent);
         
         // Clear session data state 
         history.replaceState(null, null, 'teachers.php'); 
    }
    
    // --- Delete Modal Handlers ---
    const confirmDeleteAction = (teacherId, teacherName) => {
        if (!deleteModal || !deleteModalContent) return;
        
        // Populating the hidden form fields
        document.getElementById('delete_id_input').value = teacherId;
        document.getElementById('delete_action_input').value = 'delete_teacher';
        
        // Populating the visible text in the modal
        document.getElementById('deleteItemName').textContent = teacherName;
        document.getElementById('deleteItemType').textContent = 'teacher'; // Assuming this ID exists in delete_confirmation_modal.php
        
        showModal(deleteModal, deleteModalContent);
    };
    window.confirmDeleteAction = confirmDeleteAction;

    // Corrected IDs for delete modal buttons from delete_confirmation_modal.php
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

    // --- Assignment Modal Handlers ---
    
    // Helper function to initialize the custom dropdown state
    const initializeCustomSectionSelect = (currentSectionId, type) => {
        const prefix = type === 'assign' ? 'assign' : 'edit';
        const sectionIdInput = document.getElementById(`${prefix}_section_id_input`);
        const buttonTextSpan = document.getElementById(`${prefix}-selected-section-text`);
        const optionsList = document.getElementById(`${prefix}-section-options-list`);
        
        if (!sectionIdInput || !buttonTextSpan || !optionsList) return;

        // Reset state
        sectionIdInput.value = '';
        buttonTextSpan.textContent = '-- Select a Section --';
        buttonTextSpan.classList.remove('text-gray-900', 'font-medium');
        buttonTextSpan.classList.add('text-gray-400');
        
        // Find the "Unassign" option to simulate a click if no section is selected
        const unassignOption = optionsList.querySelector(`li[data-value=""]`);

        if (currentSectionId && currentSectionId != 0) {
            // Find the corresponding list item and trigger selection logic
            const selectedItem = optionsList.querySelector(`li[data-value="${currentSectionId}"]`);
            if (selectedItem) {
                // Call the global selection function to correctly set the display and input value
                handleSectionSelect(selectedItem, type);
                return;
            }
        } 
        
        // If no section is assigned or the assigned section doesn't exist, select the unassign option
        if (unassignOption) {
            handleSectionSelect(unassignOption, type);
        }
    };
    

    const initiateAssignAction = (teacherId, teacherName, currentSectionId) => {
        if (!assignModal || !assignModalContent) return;

        document.getElementById('assign_teacher_id').value = teacherId;
        document.getElementById('assign_teacher_name').textContent = teacherName;
        
        // Initialize the custom dropdown to the current section (or unassigned)
        initializeCustomSectionSelect(currentSectionId, 'assign');
        
        showModal(assignModal, assignModalContent);
    };
    window.initiateAssignAction = initiateAssignAction;

    closeAssignModalBtn?.addEventListener('click', () => hideModal(assignModal, assignModalContent));
    teacherAssignmentForm?.addEventListener('submit', () => {
        hideModal(assignModal, assignModalContent);
        showSimpleModal(loadingOverlay);
    });

    // --- Global Click Listener for Dropdown Closure ---
    document.addEventListener('click', function(event) {
        const assignCustomSelect = document.getElementById('assign-custom-section-select');
        
        // Handle Assignment Modal dropdown
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