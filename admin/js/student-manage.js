document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    const overlay = document.getElementById('loadingOverlay');
    const loadingMessageText = document.getElementById('loadingMessageText'); // Get the message element
    
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
    
    // --- Delete Modal (Generic) ---
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
            if(modal.classList.contains('hidden') && successModal.classList.contains('hidden') && deleteModal.classList.contains('hidden')) {
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

    // --- Event Listeners ---
    if (openBtn) openBtn.addEventListener('click', () => openModal(modal, modalContent));
    if (closeBtn) closeBtn.addEventListener('click', () => closeModal(modal, modalContent));
    if (closeSuccessModalBtn) closeSuccessModalBtn.addEventListener('click', () => closeModal(successModal, successModalContent));
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', () => closeModal(deleteModal, deleteModalContent)); 


    if (modal) modal.addEventListener('click', (e) => {
        if (e.target === modal) { closeModal(modal, modalContent); }
    });
    if (successModal) successModal.addEventListener('click', (e) => {
        if (e.target === successModal) { closeModal(successModal, successModalContent); }
    });
    if (deleteModal) deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) { closeModal(deleteModal, deleteModalContent); }
    });
    
    // --- Delete Confirmation (Generic Modal Opener) ---
    window.confirmDeleteAction = function(itemId, itemName) {
        // Hardcode itemType as 'student' since the PHP call only passes two args
        const itemType = 'student'; 
        
        deleteItemNameSpan.textContent = itemName;
        deleteItemTypeSpan.textContent = itemType; 
        
        confirmDeleteBtn.setAttribute('data-item-id', itemId);
        confirmDeleteBtn.setAttribute('data-item-type', itemType);

        openModal(deleteModal, deleteModalContent);
    };

    // --- Generic Delete Confirmation POST Handler ---
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            const itemType = this.getAttribute('data-item-type');
            
            if (itemType === 'student') {
                // Show the loading overlay before submitting the form
                const loadingOverlay = document.getElementById('loadingOverlay');
                if (loadingOverlay) {
                    // Set the specific message for deletion
                    if (loadingMessageText) {
                        loadingMessageText.textContent = 'Deleting Student...';
                    }
                    
                    loadingOverlay.classList.remove('hidden');
                    // Use setTimeout to ensure the transition is triggered
                    setTimeout(() => {
                        loadingOverlay.classList.remove('opacity-0');
                    }, 10);
                }
                
                // Close the confirmation modal
                closeModal(deleteModal, deleteModalContent); 

                // Submit the form for student deletion
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
        alert("Edit feature placeholder for student ID: " + studentId);
    };


    let detailsToShow = null;
    let modalTitle = '';
    let modalDescription = ''; 
    
    if (typeof successDetails !== 'undefined' && successDetails && successDetails.name) {
        detailsToShow = successDetails;
        modalTitle = 'Student Enrolled Successfully!';
        modalDescription = `${detailsToShow.name} was successfully enrolled into ${detailsToShow.section_year} - ${detailsToShow.section_name}.`;
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
});