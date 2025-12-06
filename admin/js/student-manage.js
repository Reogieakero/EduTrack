document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    const overlay = document.getElementById('loadingOverlay');
    const loadingMessageText = document.getElementById('loadingMessageText'); 
    
    if (overlay && !overlay.classList.contains('hidden')) {
        overlay.classList.add('opacity-0');
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);
    }

    const modal = document.getElementById('addStudentModal');
    const modalContent = document.getElementById('modalContent');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    
    const successModal = document.getElementById('successModal');
    const successModalContent = document.getElementById('successModalContent');
    const closeSuccessModalBtn = document.getElementById('closeSuccessModalBtn');

    const addStudentForm = document.getElementById('addStudentForm');
    const saveStudentBtn = document.getElementById('saveStudentBtn');
    const saveIcon = document.getElementById('saveIcon');
    const saveText = document.getElementById('saveText');
    const loadingSpinner = document.getElementById('loadingSpinner');

    const successModalDescription = document.getElementById('success-modal-description');
    
    const deleteModal = document.getElementById('deleteConfirmationModal');
    const deleteModalContent = document.getElementById('deleteModalContent');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteItemNameSpan = document.getElementById('deleteItemName');
    const deleteItemTypeSpan = document.getElementById('deleteItemType');
    
    const editModal = document.getElementById('editStudentModal');
    const editModalContent = document.getElementById('editModalContent');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    
    const editStudentForm = document.getElementById('editStudentForm');
    const updateStudentBtn = document.getElementById('updateStudentBtn');
    const updateIcon = document.getElementById('updateIcon');
    const updateText = document.getElementById('updateText');
    const updateLoadingSpinner = document.getElementById('updateLoadingSpinner');
    
    const searchInput = document.getElementById('search-input'); 
    const searchForm = document.getElementById('searchForm'); 
    let searchTimeout = null; 

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
            if(modal.classList.contains('hidden') && successModal.classList.contains('hidden') && deleteModal.classList.contains('hidden') && (!editModal || editModal.classList.contains('hidden'))) { 
                document.body.style.overflow = '';
            }
        }, 300); 
    };

    if (addStudentForm) {
        addStudentForm.addEventListener('submit', function(event) {
            if (addStudentForm.checkValidity()) {
                saveIcon.classList.add('hidden');
                saveText.textContent = 'Enrolling...';
                loadingSpinner.classList.remove('hidden');
                saveStudentBtn.disabled = true; 
                saveStudentBtn.classList.remove('hover:bg-green-700');
                saveStudentBtn.classList.add('opacity-70', 'cursor-not-allowed');
            }
        });
    }
    
    if (editStudentForm) {
        editStudentForm.addEventListener('submit', function(event) {
            if (editStudentForm.checkValidity()) {
                updateIcon.classList.add('hidden');
                updateText.textContent = 'Saving...';
                updateLoadingSpinner.classList.remove('hidden');
                updateStudentBtn.disabled = true; 
                updateStudentBtn.classList.remove('hover:bg-blue-700');
                updateStudentBtn.classList.add('opacity-70', 'cursor-not-allowed');
                
                const loadingOverlay = document.getElementById('loadingOverlay');
                if (loadingOverlay) {
                    if (loadingMessageText) {
                        loadingMessageText.textContent = 'Updating Student...';
                    }
                    loadingOverlay.classList.remove('hidden');
                    setTimeout(() => {
                        loadingOverlay.classList.remove('opacity-0');
                    }, 10);
                }
            }
        });
    }

    if (openBtn) openBtn.addEventListener('click', () => openModal(modal, modalContent));
    if (closeBtn) closeBtn.addEventListener('click', () => closeModal(modal, modalContent));
    if (closeSuccessModalBtn) closeSuccessModalBtn.addEventListener('click', () => closeModal(successModal, successModalContent));
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', () => closeModal(deleteModal, deleteModalContent)); 
    if (closeEditModalBtn) closeEditModalBtn.addEventListener('click', () => closeModal(editModal, editModalContent)); 

    if (modal) modal.addEventListener('click', (e) => {
        if (e.target === modal) { closeModal(modal, modalContent); }
    });
    if (successModal) successModal.addEventListener('click', (e) => {
        if (e.target === successModal) { closeModal(successModal, successModalContent); }
    });
    if (deleteModal) deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) { closeModal(deleteModal, deleteModalContent); }
    });
    if (editModal) editModal.addEventListener('click', (e) => { 
        if (e.target === editModal) { closeModal(editModal, editModalContent); }
    });
    
    if (searchInput && searchForm) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout); 
            searchTimeout = setTimeout(() => {
                const loadingOverlay = document.getElementById('loadingOverlay');
                if (loadingOverlay) {
                    if (loadingMessageText) {
                        loadingMessageText.textContent = 'Searching Students...';
                    }
                    loadingOverlay.classList.remove('hidden');
                    setTimeout(() => {
                        loadingOverlay.classList.remove('opacity-0');
                    }, 10);
                }
                searchForm.submit();
            }, 300);
        });
    }

    window.confirmDeleteAction = function(itemId, itemName) {
        const itemType = 'student'; 
        
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
            
            if (itemType === 'student') {
                const loadingOverlay = document.getElementById('loadingOverlay');
                if (loadingOverlay) {
                    if (loadingMessageText) {
                        loadingMessageText.textContent = 'Deleting Student...';
                    }
                    
                    loadingOverlay.classList.remove('hidden');
                    setTimeout(() => {
                        loadingOverlay.classList.remove('opacity-0');
                    }, 10);
                }
                
                closeModal(deleteModal, deleteModalContent); 

                const tempForm = document.createElement('form');
                tempForm.method = 'POST';
                tempForm.action = 'students.php'; 
                tempForm.innerHTML = `
                    <input type="hidden" name="action" value="delete_student">
                    <input type="hidden" name="student_id" value="${itemId}">
                `;
                
                document.body.appendChild(tempForm);
                tempForm.submit();
            } else {
                console.error("Item type mismatch in student-manage.js delete handler.");
            }
        });
    }

    window.initiateEditAction = function(studentId) {
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            if (loadingMessageText) {
                loadingMessageText.textContent = 'Preparing Edit Form...';
            }
            loadingOverlay.classList.remove('hidden');
            setTimeout(() => {
                loadingOverlay.classList.remove('opacity-0');
            }, 10);
        }
        
        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        tempForm.action = 'students.php'; 
        tempForm.innerHTML = `
            <input type="hidden" name="action" value="fetch_edit_data">
            <input type="hidden" name="student_id" value="${studentId}">
        `;
        
        document.body.appendChild(tempForm);
        tempForm.submit();
    };


    let detailsToShow = null;
    let modalTitle = '';
    let modalDescription = ''; 
    
    if (typeof successDetails !== 'undefined' && successDetails && successDetails.name) {
        detailsToShow = successDetails;
        modalTitle = 'Student Enrolled Successfully!';
        modalDescription = `${detailsToShow.name} was successfully enrolled into ${detailsToShow.section_year} - ${detailsToShow.section_name}.`;
    } 
    else if (typeof editSuccessDetails !== 'undefined' && editSuccessDetails && editSuccessDetails.name) {
        detailsToShow = editSuccessDetails;
        modalTitle = 'Student Updated Successfully!';
        modalDescription = `${detailsToShow.name}'s record was successfully updated.`;
    } 
    else if (typeof deleteSuccessDetails !== 'undefined' && deleteSuccessDetails && deleteSuccessDetails.name) {
        detailsToShow = deleteSuccessDetails;
        modalTitle = 'Student Deleted Successfully!';
        modalDescription = 'The student was permanently removed from the system.'; 
    }

    if (detailsToShow) {
        document.getElementById('success-modal-title').textContent = modalTitle;
        
        if (successModalDescription) {
            successModalDescription.textContent = modalDescription; 
        }
        
        if (document.getElementById('modalStudentName')) {
             document.getElementById('modalStudentName').textContent = detailsToShow.name || 'N/A';
        }
        if (document.getElementById('modalSectionYear')) {
             document.getElementById('modalSectionYear').textContent = detailsToShow.section_year || 'N/A';
        }
        if (document.getElementById('modalSectionName')) {
             document.getElementById('modalSectionName').textContent = detailsToShow.section_name || 'N/A';
        }
        if (document.getElementById('modalTeacherName')) {
             document.getElementById('modalTeacherName').textContent = detailsToShow.teacher_name || 'N/A';
        }
        
        if (addStudentForm) {
            addStudentForm.reset(); 
            if(saveIcon && saveText && loadingSpinner && saveStudentBtn) {
                saveIcon.classList.remove('hidden');
                saveText.textContent = 'Enroll Student';
                loadingSpinner.classList.add('hidden');
                saveStudentBtn.disabled = false;
                saveStudentBtn.classList.add('hover:bg-green-700');
                saveStudentBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        }

        openModal(successModal, successModalContent);
    }
    
    if (typeof studentToEdit !== 'undefined' && studentToEdit && studentToEdit.id) {
        setTimeout(() => {
            document.getElementById('edit_student_id').value = studentToEdit.id;
            document.getElementById('edit_first_name').value = studentToEdit.first_name;
            document.getElementById('edit_last_name').value = studentToEdit.last_name;
            document.getElementById('edit_middle_initial').value = studentToEdit.middle_initial || ''; 
            document.getElementById('edit_date_of_birth').value = studentToEdit.date_of_birth;
            
            const currentSectionId = studentToEdit.section_id.toString();
            
            const selectedOption = document.querySelector(`#edit-section-options-list li[data-value="${currentSectionId}"]`);
            
            if (selectedOption && typeof handleSectionSelect === 'function') {
                handleSectionSelect(selectedOption, 'edit');
            } else {
                 document.getElementById('edit_section_id').value = currentSectionId;
                 const sectionInfo = sectionsList[currentSectionId];
                 if (sectionInfo) {
                    const sectionDisplay = `${sectionInfo.year} - ${sectionInfo.name} (Teacher: ${sectionInfo.teacher})`;
                    document.getElementById('edit-selected-section-text').textContent = sectionDisplay;
                    document.getElementById('edit-selected-section-text').classList.remove('text-gray-400');
                    document.getElementById('edit-selected-section-text').classList.add('text-gray-900');
                 }
            }

            const loadingOverlay = document.getElementById('loadingOverlay');
            if (loadingOverlay) {
                loadingOverlay.classList.add('opacity-0');
                setTimeout(() => {
                    loadingOverlay.classList.add('hidden');
                }, 300);
            }
            
            openModal(editModal, editModalContent);
        }, 500); 
    }
});