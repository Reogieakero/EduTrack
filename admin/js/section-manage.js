document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    const overlay = document.getElementById('loadingOverlay');
    if (overlay && !overlay.classList.contains('hidden')) {
        overlay.classList.add('opacity-0');
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);
    }

    // --- Add/Success Modals ---
    const modal = document.getElementById('addSectionModal');
    const modalContent = document.getElementById('modalContent');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    
    const successModal = document.getElementById('successModal');
    const successModalContent = document.getElementById('successModalContent');
    const closeSuccessModalBtn = document.getElementById('closeSuccessModalBtn');

    const addSectionForm = document.getElementById('addSectionForm');
    const saveSectionBtn = document.getElementById('saveSectionBtn');
    const saveIcon = document.getElementById('saveIcon');
    const saveText = document.getElementById('saveText');
    const loadingSpinner = document.getElementById('loadingSpinner');

    const successModalDescription = document.getElementById('success-modal-description');

    // --- Edit Modal ---
    const editModal = document.getElementById('editSectionModal');
    const editModalContent = document.getElementById('editModalContent');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const editForm = document.getElementById('editSectionForm');
    const editSectionIdInput = document.getElementById('edit_section_id');
    const editNameInput = document.getElementById('edit_modal_section_name');
    const editYearInput = document.getElementById('edit_modal_section_year');
    const editTeacherInput = document.getElementById('edit_modal_teacher_name');
    
    // --- Delete Modal (Generic) ---
    const deleteModal = document.getElementById('deleteConfirmationModal');
    const deleteModalContent = document.getElementById('deleteModalContent');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteItemNameSpan = document.getElementById('deleteItemName'); // Renamed to generic
    const deleteItemTypeSpan = document.getElementById('deleteItemType'); // Renamed to generic


    // --- Modal Helpers ---

    const openModal = (targetModal, targetContent) => {
        targetModal.classList.remove('hidden');
        setTimeout(() => {
            targetModal.classList.remove('opacity-0');
            targetContent.classList.remove('scale-95', 'opacity-0');
            targetContent.classList.add('scale-100', 'opacity-100');
            document.body.style.overflow = 'hidden'; 
        }, 10);
    };

    const closeModal = (targetModal, targetContent) => {
        targetContent.classList.remove('scale-100', 'opacity-100');
        targetContent.classList.add('scale-95', 'opacity-0');
        targetModal.classList.add('opacity-0');
        
        setTimeout(() => {
            targetModal.classList.add('hidden');
            // Check if all modals are closed before re-enabling scroll
            if(modal.classList.contains('hidden') && successModal.classList.contains('hidden') && editModal.classList.contains('hidden') && deleteModal.classList.contains('hidden')) {
                document.body.style.overflow = '';
            }
        }, 300); 
    };

    // --- New Loading Function to show overlay ---
    const showLoadingOverlay = (message = 'Processing...') => {
        if (overlay) {
            const messageElement = document.getElementById('loadingMessageText');
            if (messageElement) {
                messageElement.textContent = message;
            }
            overlay.classList.remove('hidden', 'opacity-0');
            setTimeout(() => {
                overlay.classList.add('opacity-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }
    };


    // --- Event Listeners ---
    if (openBtn) openBtn.addEventListener('click', () => openModal(modal, modalContent));
    if (closeBtn) closeBtn.addEventListener('click', () => closeModal(modal, modalContent));
    if (closeSuccessModalBtn) closeSuccessModalBtn.addEventListener('click', () => closeModal(successModal, successModalContent));
    if (closeEditModalBtn) closeEditModalBtn.addEventListener('click', () => closeModal(editModal, editModalContent));
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', () => closeModal(deleteModal, deleteModalContent));


    // Close modals when clicking outside
    [modal, successModal, editModal, deleteModal].forEach(m => {
        if (m) m.addEventListener('click', (e) => {
            if (e.target === m) {
                if(m.id === 'addSectionModal') closeModal(modal, modalContent);
                else if(m.id === 'successModal') closeModal(successModal, successModalContent);
                else if(m.id === 'editSectionModal') closeModal(editModal, editModalContent);
                else if(m.id === 'deleteConfirmationModal') closeModal(deleteModal, deleteModalContent);
            }
        });
    });

    // --- Add Form Submission ---
    if (addSectionForm) {
        addSectionForm.addEventListener('submit', function() {
            if(saveIcon && saveText && loadingSpinner && saveSectionBtn) {
                saveIcon.classList.add('hidden');
                saveText.textContent = 'Saving...';
                loadingSpinner.classList.remove('hidden');
                saveSectionBtn.disabled = true;
                saveSectionBtn.classList.remove('hover:bg-blue-700');
                saveSectionBtn.classList.add('opacity-70', 'cursor-not-allowed');
            }
        });
    }

    // --- Edit Action Handler (called from section card) ---
    window.initiateEditAction = function(sectionId, sectionData) {
        if (!sectionData) {
            console.error("Section data not found for ID: " + sectionId);
            return;
        }

        editSectionIdInput.value = sectionId;
        editNameInput.value = sectionData.name || '';
        editYearInput.value = sectionData.year || '';
        editTeacherInput.value = sectionData.teacher || '';

        openModal(editModal, editModalContent);
    };

    // --- Delete Confirmation (Generic Modal Opener) ---
    /**
     * Shows the generic delete confirmation modal with specific details.
     * @param {number|string} itemId The ID of the item (section) to delete.
     * @param {string} itemName The display name of the item.
     * @param {('section')} itemType The type of item being deleted (always 'section' here).
     */
    window.confirmDeleteAction = function(itemId, itemName, itemType) {
        deleteItemNameSpan.textContent = itemName;
        deleteItemTypeSpan.textContent = itemType; // Should be 'section'
        
        confirmDeleteBtn.setAttribute('data-item-id', itemId);
        confirmDeleteBtn.setAttribute('data-item-type', itemType);

        openModal(deleteModal, deleteModalContent);
    };

    // --- Generic Delete Confirmation POST Handler ---
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            const itemType = this.getAttribute('data-item-type');
            
            if (itemType === 'section') {
                // 1. Close the confirmation modal
                closeModal(deleteModal, deleteModalContent); 
                
                // 2. Show the loading overlay
                showLoadingOverlay('Deleting section and associated data...'); 
                
                // 3. Submit the form for section deletion
                const tempForm = document.createElement('form');
                tempForm.method = 'POST';
                tempForm.action = 'sections.php';
                tempForm.innerHTML = `
                    <input type="hidden" name="action" value="delete_section">
                    <input type="hidden" name="section_id" value="${itemId}">
                `;
                
                document.body.appendChild(tempForm);
                tempForm.submit();
            } else {
                console.error("Item type mismatch in section-manage.js delete handler.");
                // If student-manage.js is also present, it should handle 'student'
            }
        });
    }


    // --- Success Details Display Logic ---
    let detailsToShow = null;
    let modalTitle = 'Success';
    let modalDescription = '';

    // sectionsList is assumed to be defined globally in sections.php
    // sectionToEdit is assumed to be defined globally in sections.php

    if (typeof successDetails !== 'undefined' && successDetails && successDetails.name) {
        detailsToShow = successDetails;
        modalTitle = 'Section Added Successfully!';
        modalDescription = `${detailsToShow.name} (${detailsToShow.year}) was successfully created.`;
    }
    else if (typeof editSuccessDetails !== 'undefined' && editSuccessDetails && editSuccessDetails.name) {
        detailsToShow = editSuccessDetails;
        modalTitle = 'Section Updated Successfully!';
        modalDescription = 'The section details have been successfully updated.';
    }
    else if (typeof deleteSuccessDetails !== 'undefined' && deleteSuccessDetails && deleteSuccessDetails.name) {
        detailsToShow = deleteSuccessDetails;
        modalTitle = 'Section Deleted Successfully!';
        modalDescription = 'The section was permanently removed from the system.'; 
    }


    if (detailsToShow) {
        document.getElementById('success-modal-title').textContent = modalTitle;
        
        if (successModalDescription) {
            successModalDescription.textContent = modalDescription; 
        }

        // Assuming success_modal.php has these IDs
        if (document.getElementById('modalSectionName')) {
             document.getElementById('modalSectionName').textContent = detailsToShow.name || 'N/A';
        }
        if (document.getElementById('modalSectionYear')) {
             document.getElementById('modalSectionYear').textContent = detailsToShow.year || 'N/A';
        }
        if (document.getElementById('modalTeacherName')) {
             document.getElementById('modalTeacherName').textContent = detailsToShow.teacher || 'N/A';
        }
        
        if (addSectionForm) {
            addSectionForm.reset(); 

            if(saveIcon && saveText && loadingSpinner && saveSectionBtn) {
                saveIcon.classList.remove('hidden');
                saveText.textContent = 'Save Section';
                loadingSpinner.classList.add('hidden');
                saveSectionBtn.disabled = false;
                saveSectionBtn.classList.add('hover:bg-blue-700');
                saveSectionBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        }

        openModal(successModal, successModalContent);
    }
});