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
    
    // ADDED CONSTANT
    const modalDetailBlock = document.getElementById('modal-detail-block');

    const addStudentForm = document.getElementById('addStudentForm');
    const saveStudentBtn = document.getElementById('saveStudentBtn');
    const saveIcon = document.getElementById('saveIcon');
    const saveText = document.getElementById('saveText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    
    const bulkAddStudentForm = document.getElementById('bulkAddStudentForm');
    const saveBulkStudentBtn = document.getElementById('saveBulkStudentBtn');
    const saveBulkIcon = document.getElementById('saveBulkIcon');
    const saveBulkText = document.getElementById('saveBulkText');
    const loadingBulkSpinner = document.getElementById('loadingBulkSpinner');

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
    
    const tabs = document.querySelectorAll('.tab-button');
    const singleEnrollTab = document.getElementById('singleEnrollTab');
    const bulkEnrollTab = document.getElementById('bulkEnrollTab');
    const singleEnrollContent = document.getElementById('singleEnrollContent');
    const bulkEnrollContent = document.getElementById('bulkEnrollContent');
    
    const showTab = (tabName) => {
        tabs.forEach(tab => {
            tab.classList.remove('border-primary-green', 'text-primary-green', 'border-primary-blue', 'text-primary-blue');
            tab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        });
        
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        const activeTab = document.querySelector(`.tab-button[data-tab="${tabName}"]`);
        const activeContent = document.getElementById(`${tabName}EnrollContent`);
        
        if (activeTab) {
            const activeColorClass = tabName === 'single' ? 'border-primary-green text-primary-green' : 'border-primary-blue text-primary-blue';
            activeTab.classList.add(...activeColorClass.split(' '));
            activeTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        }
        if (activeContent) {
            activeContent.classList.remove('hidden');
        }
    };
    
    if (singleEnrollTab) singleEnrollTab.addEventListener('click', () => showTab('single'));
    if (bulkEnrollTab) bulkEnrollTab.addEventListener('click', () => showTab('bulk'));

    const openModal = (targetModal, targetContent) => {
        targetModal.classList.remove('hidden');
        setTimeout(() => {
            targetModal.classList.remove('opacity-0');
            targetContent.classList.remove('scale-95', 'opacity-0');
            targetContent.classList.add('scale-100', 'opacity-100');
            document.body.style.overflow = 'hidden'; 
            showTab('single'); 
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
    
    if (bulkAddStudentForm) {
        bulkAddStudentForm.addEventListener('submit', function(event) {
            if (bulkAddStudentForm.checkValidity()) {
                saveBulkIcon.classList.add('hidden');
                saveBulkText.textContent = 'Uploading...';
                loadingBulkSpinner.classList.remove('hidden');
                saveBulkStudentBtn.disabled = true; 
                saveBulkStudentBtn.classList.remove('hover:bg-blue-700');
                saveBulkStudentBtn.classList.add('opacity-70', 'cursor-not-allowed');
                
                const loadingOverlay = document.getElementById('loadingOverlay');
                if (loadingOverlay) {
                    if (loadingMessageText) {
                        loadingMessageText.textContent = 'Processing Bulk Enrollment...';
                    }
                    loadingOverlay.classList.remove('hidden');
                    setTimeout(() => {
                        loadingOverlay.classList.remove('opacity-0');
                    }, 10);
                }
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
    
    const errorList = document.getElementById('success-modal-errors');
    if (errorList) {
        errorList.innerHTML = '';
        errorList.classList.add('hidden');
    }
    
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
    else if (typeof bulkSuccessDetails !== 'undefined' && bulkSuccessDetails && (bulkSuccessDetails.added > 0 || bulkSuccessDetails.failed > 0)) {
        detailsToShow = bulkSuccessDetails;
        // User request: "Bulk uploaded successfully"
        modalTitle = 'Bulk Enrollment Complete!'; 
        modalDescription = `Successfully added ${detailsToShow.added} students. ${detailsToShow.failed} students failed to enroll.`;
        
        if (bulkSuccessDetails.failed > 0 && errorList) {
            errorList.classList.remove('hidden');
            detailsToShow.errors.slice(0, 5).forEach(error => { 
                const listItem = document.createElement('li');
                listItem.textContent = error;
                errorList.appendChild(listItem);
            });
            if (detailsToShow.errors.length > 5) {
                const moreItem = document.createElement('li');
                moreItem.textContent = `... and ${detailsToShow.errors.length - 5} more errors. Check server logs for full details.`;
                errorList.appendChild(moreItem);
            }
        }
    }


    if (detailsToShow) {
        document.getElementById('success-modal-title').textContent = modalTitle;
        
        if (successModalDescription) {
            successModalDescription.innerHTML = modalDescription; 
        }
        
        // NEW LOGIC TO HIDE/SHOW DETAILS BLOCK
        if (modalDetailBlock) {
            if (typeof bulkSuccessDetails !== 'undefined' && bulkSuccessDetails && (bulkSuccessDetails.added > 0 || bulkSuccessDetails.failed > 0)) {
                modalDetailBlock.classList.add('hidden');
            } else {
                modalDetailBlock.classList.remove('hidden');
            }
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
        
        if (bulkAddStudentForm) {
            bulkAddStudentForm.reset(); 
            if(saveBulkIcon && saveBulkText && loadingBulkSpinner && saveBulkStudentBtn) {
                saveBulkIcon.classList.remove('hidden');
                saveBulkText.textContent = 'Upload & Enroll Students';
                loadingBulkSpinner.classList.add('hidden');
                saveBulkStudentBtn.disabled = false;
                saveBulkStudentBtn.classList.add('hover:bg-blue-700');
                saveBulkStudentBtn.classList.remove('opacity-70', 'cursor-not-allowed');
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
            
            const selectedOption = document.querySelector(`#edit-section-options-list li[data-value=\"${currentSectionId}\"]`);
            
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


function toggleDropdown(buttonElement) {
    const isEditModal = buttonElement.id.includes('edit');
    const optionsListId = isEditModal ? 'edit-section-options-list' : 'section-options-list';
    const optionsList = document.getElementById(optionsListId);
    const isHidden = optionsList.classList.contains('hidden');
    
    if (isHidden) {
        document.querySelectorAll('ul[id$="-section-options-list"]').forEach(list => {
            if (list !== optionsList) {
                list.classList.add('hidden');
                const button = list.previousElementSibling;
                if (button) button.setAttribute('aria-expanded', 'false');
            }
        });

        optionsList.classList.remove('hidden');
        buttonElement.setAttribute('aria-expanded', 'true');
    } else {
        optionsList.classList.add('hidden');
        buttonElement.setAttribute('aria-expanded', 'false');
    }
}

function handleSectionSelect(listItem, modalType = 'add') {
    const sectionId = listItem.getAttribute('data-value');
    const sectionDisplay = listItem.getAttribute('data-display');
    
    const idPrefix = modalType === 'edit' ? 'edit_' : 'modal_';
    const buttonTextId = modalType === 'edit' ? 'edit-selected-section-text' : 'selected-section-text';
    const optionsListId = modalType === 'edit' ? 'edit-section-options-list' : 'section-options-list';
    
    document.getElementById(idPrefix + 'section_id').value = sectionId;
    
    const buttonTextSpan = document.getElementById(buttonTextId);
    buttonTextSpan.textContent = sectionDisplay;
    buttonTextSpan.classList.remove('text-gray-400');
    buttonTextSpan.classList.add('text-gray-900');

    const optionsList = document.getElementById(optionsListId);
    
    optionsList.querySelectorAll('li').forEach(li => {
        // Removed color classes: bg-primary-green, text-white
        li.classList.remove('bg-primary-green', 'bg-gray-200', 'text-white', 'font-semibold'); 
        li.classList.add('hover:bg-gray-100', 'text-gray-900'); // Ensure hover and default text color
        const checkIcon = li.querySelector('.section-check-icon');
        if (checkIcon) checkIcon.classList.add('hidden');
    });

    // Use neutral gray background for selection
    listItem.classList.add('bg-gray-200', 'font-semibold', 'text-gray-900'); 
    listItem.classList.remove('hover:bg-gray-100', 'bg-primary-green', 'text-white'); // Removed primary-green/text-white 
    const checkIcon = listItem.querySelector('.section-check-icon');
    if (checkIcon) checkIcon.classList.remove('hidden');
    
    const selectButton = optionsList.previousElementSibling;
    optionsList.classList.add('hidden');
    selectButton.setAttribute('aria-expanded', 'false');
}

document.addEventListener('click', function(event) {
    const addCustomSelect = document.getElementById('custom-section-select');
    const editCustomSelect = document.getElementById('edit-custom-section-select'); 
    const addStudentModal = document.getElementById('addStudentModal');
    const editStudentModal = document.getElementById('editStudentModal'); 

    if (addCustomSelect && !addCustomSelect.contains(event.target) && addStudentModal && !addStudentModal.classList.contains('hidden')) {
        const optionsList = document.getElementById('section-options-list');
        const selectButton = document.getElementById('sectionSelectButton');
        
        if (optionsList && !optionsList.classList.contains('hidden')) {
            optionsList.classList.add('hidden');
            selectButton.setAttribute('aria-expanded', 'false');
        }
    }
    
    if (editCustomSelect && !editCustomSelect.contains(event.target) && editStudentModal && !editStudentModal.classList.contains('hidden')) {
        const optionsList = document.getElementById('edit-section-options-list');
        const selectButton = document.getElementById('editSectionSelectButton');
        
        if (optionsList && !optionsList.classList.contains('hidden')) {
            optionsList.classList.add('hidden');
            selectButton.setAttribute('aria-expanded', 'false');
        }
    }
});