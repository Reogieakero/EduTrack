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

    const successModalDescription = document.getElementById('success-modal-description');

    const editModal = document.getElementById('editSectionModal');
    const editModalContent = document.getElementById('editModalContent');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const editForm = document.getElementById('editSectionForm');
    const editSectionIdInput = document.getElementById('edit_section_id');
    const editNameInput = document.getElementById('edit_modal_section_name');
    const editYearInput = document.getElementById('edit_modal_section_year');
    const editTeacherSelect = document.getElementById('edit_modal_teacher_name');
    
    const deleteModal = document.getElementById('deleteConfirmationModal');
    const deleteModalContent = document.getElementById('deleteModalContent');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteItemNameSpan = document.getElementById('deleteItemName');
    const deleteItemTypeSpan = document.getElementById('deleteItemType');


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

    const hideLoadingOverlay = () => {
        if (overlay) {
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
            if(modal.classList.contains('hidden') && successModal.classList.contains('hidden') && editModal.classList.contains('hidden') && deleteModal.classList.contains('hidden')) {
                document.body.style.overflow = '';
            }
        }
    };


    if (openBtn) openBtn.addEventListener('click', () => openModal(modal, modalContent));
    if (closeBtn) closeBtn.addEventListener('click', () => closeModal(modal, modalContent));
    if (closeSuccessModalBtn) closeSuccessModalBtn.addEventListener('click', () => closeModal(successModal, successModalContent));
    if (closeEditModalBtn) closeEditModalBtn.addEventListener('click', () => closeModal(editModal, editModalContent));
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', () => closeModal(deleteModal, deleteModalContent));


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

    window.initiateEditAction = function(sectionId, sectionDataString) {
        showLoadingOverlay('Loading section data...');
        
        let sectionData;
        try {
            sectionData = JSON.parse(sectionDataString);
        } catch (e) {
            console.error("Failed to parse section data JSON:", e);
            hideLoadingOverlay();
            return;
        }

        if (!sectionData || !sectionData.id) {
            console.error("Section data not found for ID: " + sectionId);
            hideLoadingOverlay();
            return;
        }

        setTimeout(() => {
            editSectionIdInput.value = sectionData.id;
            editNameInput.value = sectionData.name || '';

            const yearRadio = document.getElementById('edit_year_' + (sectionData.year || '').replace(' ', '_'));
            if (yearRadio) {
                yearRadio.checked = true;
            }

            if (editTeacherSelect) {
                editTeacherSelect.innerHTML = '';
                const currentTeacherName = sectionData.teacher || 'Unassigned';

                const unassignedOption = document.createElement('option');
                unassignedOption.value = 'Unassigned';
                unassignedOption.textContent = 'Unassigned (No Teacher)';
                editTeacherSelect.appendChild(unassignedOption);
                
                if (typeof allTeachers !== 'undefined' && Array.isArray(allTeachers)) {
                    allTeachers.forEach(teacher => {
                        const isUnassigned = parseInt(teacher.assigned_section_id) === 0;
                        const isCurrentTeacher = teacher.full_name === currentTeacherName;

                        if (isUnassigned || isCurrentTeacher) {
                            const option = document.createElement('option');
                            option.value = teacher.full_name;
                            option.textContent = teacher.full_name;
                            editTeacherSelect.appendChild(option);
                        }
                    });
                } else {
                    console.error("Teacher data (allTeachers) not found in scope.");
                }

                editTeacherSelect.value = currentTeacherName;
            }

            hideLoadingOverlay();
            openModal(editModal, editModalContent);
        }, 200); 
    };

    if (editForm) {
        editForm.addEventListener('submit', function() {
            showLoadingOverlay('Updating section...');
            
            const updateIcon = document.getElementById('updateIcon');
            const updateText = document.getElementById('updateText');
            const updateLoadingSpinner = document.getElementById('updateLoadingSpinner');
            const updateSectionBtn = document.getElementById('updateSectionBtn');

            if(updateIcon && updateText && updateLoadingSpinner && updateSectionBtn) {
                updateIcon.classList.add('hidden');
                updateText.textContent = 'Updating...';
                updateLoadingSpinner.classList.remove('hidden');
                updateSectionBtn.disabled = true;
                updateSectionBtn.classList.remove('hover:bg-green-700');
                updateSectionBtn.classList.add('opacity-70', 'cursor-not-allowed');
            }
        });
    }

    window.confirmDeleteAction = function(itemId, itemName, itemType) {
        deleteItemNameSpan.textContent = itemName;
        deleteItemTypeSpan.textContent = itemType; 
        
        confirmDeleteBtn.setAttribute('data-item-id', itemId);
        confirmDeleteBtn.setAttribute('data-item-type', itemType);

        openModal(deleteModal, deleteModalContent);
    };

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            const itemType = this.getAttribute('data-item-type');
            
            if (itemType === 'section') {
                closeModal(deleteModal, deleteModalContent); 
                
                showLoadingOverlay('Deleting section and associated data...'); 
                
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
            }
        });
    }


    let detailsToShow = null;
    let modalTitle = 'Success';
    let modalDescription = '';

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

        if (document.getElementById('modalSectionName')) {
             document.getElementById('modalSectionName').textContent = detailsToShow.name || 'N/A';
        }
        if (document.getElementById('modalSectionYear')) {
             document.getElementById('modalSectionYear').textContent = detailsToShow.year || 'N/A';
        }
        
        if (document.getElementById('modalSectionTeacher')) {
             const assignedTeacher = detailsToShow.teacher || 'Unassigned';
             document.getElementById('modalSectionTeacher').textContent = assignedTeacher;
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