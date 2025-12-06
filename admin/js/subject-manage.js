document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    lucide.createIcons();

    // --- Common Modal Functions ---
    const subjectModal = document.getElementById('addSubjectModal');
    const subjectModalContent = document.getElementById('modalContent');
    const openSubjectModalBtn = document.getElementById('openModalBtn');
    const closeSubjectModalBtn = document.getElementById('closeModalBtn');
    
    const editSubjectModal = document.getElementById('editSubjectModal');
    const editSubjectModalContent = document.getElementById('editModalContent');
    const closeEditSubjectModalBtn = document.getElementById('closeEditModalBtn');

    const successModal = document.getElementById('successModal');
    const successModalContent = document.getElementById('successModalContent');
    const closeSuccessModalBtn = document = document.getElementById('closeSuccessModalBtn');
    
    const deleteConfirmationModal = document.getElementById('deleteConfirmationModal');
    const deleteConfirmationContent = document.getElementById('deleteConfirmationContent');
    const closeDeleteModalBtn = document.getElementById('closeDeleteModalBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    const loadingOverlay = document.getElementById('loadingOverlay');

    function showModal(modal, content) {
        modal.classList.remove('hidden', 'opacity-0');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 50);
    }

    function hideModal(modal, content) {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    function showSimpleModal(modal) {
        modal.classList.remove('hidden', 'opacity-0');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
        }, 50);
    }

    // --- Add Modal Logic ---
    openSubjectModalBtn?.addEventListener('click', () => showModal(subjectModal, subjectModalContent));
    closeSubjectModalBtn?.addEventListener('click', () => hideModal(subjectModal, subjectModalContent));
    
    const addSubjectForm = document.getElementById('addSubjectForm');
    const saveSubjectBtn = document.getElementById('saveSubjectBtn');
    const saveIcon = document.getElementById('saveIcon');
    const saveText = document.getElementById('saveText');
    const loadingSpinner = document.getElementById('loadingSpinner');

    addSubjectForm?.addEventListener('submit', function(event) {
        if (addSubjectForm.checkValidity()) {
            // Show loading state
            saveIcon.classList.add('hidden');
            saveText.textContent = 'Saving...';
            loadingSpinner.classList.remove('hidden');
            saveSubjectBtn.disabled = true;
            saveSubjectBtn.classList.add('opacity-70', 'cursor-not-allowed');
        }
        // Let the form submit normally
    });

    // --- Edit Modal Logic ---
    closeEditSubjectModalBtn?.addEventListener('click', () => hideModal(editSubjectModal, editSubjectModalContent));

    const editSubjectForm = document.getElementById('editSubjectForm');
    const updateSubjectBtn = document.getElementById('updateSubjectBtn');
    const updateIcon = document.getElementById('updateIcon');
    const updateText = document.getElementById('updateText');
    const updateLoadingSpinner = document.getElementById('updateLoadingSpinner');

    editSubjectForm?.addEventListener('submit', function(event) {
        if (editSubjectForm.checkValidity()) {
            // Show loading state
            updateIcon.classList.add('hidden');
            updateText.textContent = 'Updating...';
            updateLoadingSpinner.classList.remove('hidden');
            updateSubjectBtn.disabled = true;
            updateSubjectBtn.classList.add('opacity-70', 'cursor-not-allowed');
        }
        // Let the form submit normally
    });

    // Function to populate the Edit Modal
    function populateEditModal(id, code, name, year) {
        document.getElementById('edit_subject_id').value = id;
        document.getElementById('edit_subject_code').value = code;
        document.getElementById('edit_subject_name').value = name;
        
        // Populate the year level dropdown and select the current value
        const yearSelect = document.getElementById('edit_year_level');
        yearSelect.innerHTML = '<option value="">Select Year Level</option>';
        window.yearLevels.forEach(y => {
            const option = document.createElement('option');
            option.value = y;
            // CHANGED: From 'Grade ${y}' to 'Year ${y}'
            option.textContent = `Year ${y}`;
            if (y == year) { // Use == for comparison as year might be string
                option.selected = true;
            }
            yearSelect.appendChild(option);
        });

        showModal(editSubjectModal, editSubjectModalContent);
    }

    // Attach click listeners to all edit buttons
    document.querySelectorAll('.edit-subject-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const code = this.getAttribute('data-code');
            const name = this.getAttribute('data-name');
            const year = this.getAttribute('data-year');
            populateEditModal(id, code, name, year);
        });
    });


    // --- Delete Modal Logic ---
    closeDeleteModalBtn?.addEventListener('click', () => hideModal(deleteConfirmationModal, deleteConfirmationContent));

    // Attach click listeners to all delete buttons
    document.querySelectorAll('.delete-subject-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            // Set the ID and action for the hidden form
            document.getElementById('delete_id_input').value = id;
            document.getElementById('delete_action_input').value = 'delete_subject';
            
            // Update the modal message
            document.getElementById('deleteItemName').textContent = name;
            
            showModal(deleteConfirmationModal, deleteConfirmationContent);
        });
    });

    confirmDeleteBtn?.addEventListener('click', function() {
        hideModal(deleteConfirmationModal, deleteConfirmationContent);
        // Submit the hidden form
        document.getElementById('deleteConfirmationForm').submit();
        showSimpleModal(loadingOverlay);
    });


    // --- Success Modal Logic (Check on load) ---
    function checkSuccessMessages() {
        if (window.successDetails) {
            document.getElementById('successModalTitle').textContent = 'Subject Added!';
            // CHANGED: From ' (Grade ${window.successDetails.year})' to ' (Year ${window.successDetails.year})'
            document.getElementById('successModalMessage').innerHTML = `The subject <strong>${window.successDetails.name} (Year ${window.successDetails.year})</strong> has been successfully added.`;
            showModal(successModal, successModalContent);
        } else if (window.editSuccessDetails) {
            document.getElementById('successModalTitle').textContent = 'Subject Updated!';
            // CHANGED: From ' (Grade ${window.editSuccessDetails.year})' to ' (Year ${window.editSuccessDetails.year})'
            document.getElementById('successModalMessage').innerHTML = `The subject <strong>${window.editSuccessDetails.name} (Year ${window.editSuccessDetails.year})</strong> has been successfully updated.`;
            showModal(successModal, successModalContent);
        } else if (window.deleteSuccessDetails) {
            document.getElementById('successModalTitle').textContent = 'Subject Deleted!';
            document.getElementById('successModalMessage').innerHTML = `The subject <strong>${window.deleteSuccessDetails.name}</strong> has been successfully removed.`;
            showModal(successModal, successModalContent);
        }
    }
    
    checkSuccessMessages();
    closeSuccessModalBtn?.addEventListener('click', () => hideModal(successModal, successModalContent));

    // --- Loading Overlay (Hide when page is fully loaded) ---
    const overlay = document.getElementById('loadingOverlay');
    if (overlay && !overlay.classList.contains('hidden')) {
        overlay.classList.add('opacity-0');
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);
    }
});