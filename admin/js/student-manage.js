document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    const overlay = document.getElementById('loadingOverlay');
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
            if(modal.classList.contains('hidden') && successModal.classList.contains('hidden')) {
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

    if (openBtn) openBtn.addEventListener('click', () => openModal(modal, modalContent));
    if (closeBtn) closeBtn.addEventListener('click', () => closeModal(modal, modalContent));
    if (closeSuccessModalBtn) closeSuccessModalBtn.addEventListener('click', () => closeModal(successModal, successModalContent));

    if (modal) modal.addEventListener('click', (e) => {
        if (e.target === modal) { closeModal(modal, modalContent); }
    });
    if (successModal) successModal.addEventListener('click', (e) => {
        if (e.target === successModal) { closeModal(successModal, successModalContent); }
    });
    
    window.confirmDeleteAction = function(studentId, studentName) {
        alert("Delete feature placeholder for: " + studentName + " (ID: " + studentId + ")");
    };

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

    if (detailsToShow) {
        document.getElementById('success-modal-title').textContent = modalTitle;
        
        if (successModalDescription) {
            successModalDescription.textContent = modalDescription; 
        }
        
        document.getElementById('modalStudentName').textContent = detailsToShow.name || 'N/A';
        document.getElementById('modalSectionYear').textContent = detailsToShow.section_year || 'N/A';
        document.getElementById('modalSectionName').textContent = detailsToShow.section_name || 'N/A';
        document.getElementById('modalTeacherName').textContent = detailsToShow.teacher_name || 'N/A';
        
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