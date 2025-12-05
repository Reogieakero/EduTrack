document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    const overlay = document.getElementById('loadingOverlay');
    if (overlay && !overlay.classList.contains('hidden')) {
        overlay.classList.add('opacity-0');
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);
    }

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

    const editModal = document.getElementById('editSectionModal');
    const editModalContent = document.getElementById('editModalContent');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const editForm = document.getElementById('editSectionForm');
    const editSectionIdInput = document.getElementById('edit_section_id');
    const editSectionNameInput = document.getElementById('edit_modal_section_name');
    const editTeacherNameInput = document.getElementById('edit_modal_teacher_name');
    const editYearRadios = document.getElementsByName('edit_section_year');

    const deleteModal = document.getElementById('deleteConfirmationModal');
    const deleteModalContent = document.getElementById('deleteModalContent');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteSectionNameSpan = document.getElementById('deleteSectionName');
    
    const loadingMessageText = document.getElementById('loadingMessageText');

    const successModalDescription = document.getElementById('success-modal-description');


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
            if(modal.classList.contains('hidden') && successModal.classList.contains('hidden') && editModal.classList.contains('hidden') && deleteModal.classList.contains('hidden')) {
                document.body.style.overflow = '';
            }
        }, 300); 
    };

    window.initiateEditAction = function(sectionId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'sections.php';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'edit_section'; 
        form.appendChild(actionInput);

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'section_id';
        idInput.value = sectionId;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
    };

    window.confirmDeleteAction = function(sectionId, sectionName) {
        deleteSectionNameSpan.textContent = sectionName;
        confirmDeleteBtn.setAttribute('data-section-id', sectionId);
        openModal(deleteModal, deleteModalContent);
    };

    window.executeDeleteAction = function(sectionId) {
        closeModal(deleteModal, deleteModalContent);

        if (overlay) {
            if (loadingMessageText) {
                loadingMessageText.textContent = 'Deleting Section...'; 
            }
            
            overlay.classList.remove('hidden', 'opacity-0');
            setTimeout(() => {
                 overlay.classList.add('opacity-100');
            }, 10);
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'sections.php';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_section';
        form.appendChild(actionInput);

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'section_id';
        idInput.value = sectionId;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
    };
    
    if (sectionToEdit) {
        editSectionIdInput.value = sectionToEdit.id;
        editSectionNameInput.value = sectionToEdit.name;
        editTeacherNameInput.value = sectionToEdit.teacher;

        editYearRadios.forEach(radio => {
            if (radio.value === sectionToEdit.year) {
                radio.checked = true;
            } else {
                radio.checked = false;
            }
        });

        openModal(editModal, editModalContent);
    }
    
    openBtn.addEventListener('click', () => openModal(modal, modalContent));
    closeBtn.addEventListener('click', () => closeModal(modal, modalContent));
    closeSuccessModalBtn.addEventListener('click', () => closeModal(successModal, successModalContent));
    closeEditModalBtn.addEventListener('click', () => closeModal(editModal, editModalContent)); 
    
    cancelDeleteBtn.addEventListener('click', () => closeModal(deleteModal, deleteModalContent));
    confirmDeleteBtn.addEventListener('click', function() {
        const sectionId = this.getAttribute('data-section-id');
        if (sectionId) {
            executeDeleteAction(sectionId);
        }
    });


    modal.addEventListener('click', (e) => {
        if (e.target === modal) { closeModal(modal, modalContent); }
    });
    successModal.addEventListener('click', (e) => {
        if (e.target === successModal) { closeModal(successModal, successModalContent); }
    });
    editModal.addEventListener('click', (e) => { 
        if (e.target === editModal) { closeModal(editModal, editModalContent); }
    });
    deleteModal.addEventListener('click', (e) => { 
        if (e.target === deleteModal) { closeModal(deleteModal, deleteModalContent); }
    });
    
    const style = document.createElement('style');
    style.innerHTML = `
    .custom-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: #f8f9fb;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #D1D5DB;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #9CA3AF;
    }
    `;
    document.head.appendChild(style);

    let detailsToShow = null;
    let modalTitle = '';
    let modalDescription = ''; 
    
    if (successDetails && successDetails.name) {
        detailsToShow = successDetails;
        modalTitle = 'Section Added Successfully!';
        modalDescription = 'The new section has been saved to the database.';
    } 
    else if (editSuccessDetails && editSuccessDetails.name) {
        detailsToShow = editSuccessDetails;
        modalTitle = 'Section Updated Successfully!';
        modalDescription = 'The section details have been successfully updated.';
    }
    else if (deleteSuccessDetails && deleteSuccessDetails.name) {
        detailsToShow = deleteSuccessDetails;
        modalTitle = 'Section Deleted Successfully!';
        modalDescription = 'The section was permanently removed from the system.'; 
    }


    if (detailsToShow) {
        document.getElementById('success-modal-title').textContent = modalTitle;
        
        if (successModalDescription) {
            successModalDescription.textContent = modalDescription; 
        }

        document.getElementById('modalSectionName').textContent = detailsToShow.name;
        document.getElementById('modalSectionYear').textContent = detailsToShow.year;
        document.getElementById('modalTeacherName').textContent = detailsToShow.teacher;
        
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