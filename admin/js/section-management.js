document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    lucide.createIcons();

    // --- Loading Overlay Fix/Cleanup (Hide it after page load) ---
    const overlay = document.getElementById('loadingOverlay');
    if (overlay && !overlay.classList.contains('hidden')) {
        overlay.classList.add('opacity-0');
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);
    }
    // --- END Loading Overlay Fix/Cleanup ---

    // --- Modal Elements ---
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

    // --- EDIT MODAL ELEMENTS ---
    const editModal = document.getElementById('editSectionModal');
    const editModalContent = document.getElementById('editModalContent');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const editForm = document.getElementById('editSectionForm');
    const editSectionIdInput = document.getElementById('edit_section_id');
    const editSectionNameInput = document.getElementById('edit_modal_section_name');
    const editTeacherNameInput = document.getElementById('edit_modal_teacher_name');
    const editYearRadios = document.getElementsByName('edit_section_year');
    const updateSectionBtn = document.getElementById('updateSectionBtn');
    const updateIcon = document.getElementById('updateIcon');
    const updateText = document.getElementById('updateText');
    const updateLoadingSpinner = document.getElementById('updateLoadingSpinner');

    // --- DELETE MODAL ELEMENTS ---
    const deleteModal = document.getElementById('deleteConfirmationModal');
    const deleteModalContent = document.getElementById('deleteModalContent');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteSectionNameSpan = document.getElementById('deleteSectionName');
    
    // --- LOADING/SUCCESS TEXT ELEMENTS ---
    const loadingMessageText = document.getElementById('loadingMessageText');
    const successModalDescription = document.getElementById('success-modal-description');


    // --- Generic Modal Functions ---
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
            // Check all modals before resetting overflow
            if(modal.classList.contains('hidden') && successModal.classList.contains('hidden') && editModal.classList.contains('hidden') && deleteModal.classList.contains('hidden')) {
                document.body.style.overflow = '';
            }
        }, 300); 
    };
    
    // --- Exposed Global Functions for Buttons in section_card.php ---
    
    // Function to trigger the PHP edit action (page reload)
    window.initiateEditAction = function(sectionId) {
        // Show loading state before redirect
        if (overlay) {
            if (loadingMessageText) {
                loadingMessageText.textContent = 'Fetching Section Details...'; 
            }
            overlay.classList.remove('hidden', 'opacity-0');
            setTimeout(() => { overlay.classList.add('opacity-100'); }, 10);
        }

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

    // Function to open the confirmation modal 
    window.confirmDeleteAction = function(sectionId, sectionName) {
        deleteSectionNameSpan.textContent = sectionName;
        confirmDeleteBtn.setAttribute('data-section-id', sectionId);
        openModal(deleteModal, deleteModalContent);
    };

    // Function to execute the delete after confirmation (shows overlay and reloads)
    window.executeDeleteAction = function(sectionId) {
        // 1. Hide the confirmation modal
        closeModal(deleteModal, deleteModalContent);

        // 2. Show the loading overlay 
        if (overlay) {
            if (loadingMessageText) {
                loadingMessageText.textContent = 'Deleting Section...'; 
            }
            
            overlay.classList.remove('hidden', 'opacity-0');
            setTimeout(() => {
                 overlay.classList.add('opacity-100');
            }, 10);
        }

        // 3. Create a temporary form to submit the delete action
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
    
    // --- EDIT MODAL POPULATION & TRIGGER (On Page Load) ---
    // Note: sectionToEdit variable is echoed in sections.php
    if (typeof sectionToEdit !== 'undefined' && sectionToEdit && sectionToEdit.id) {
        // 1. Populate the form fields
        editSectionIdInput.value = sectionToEdit.id;
        editSectionNameInput.value = sectionToEdit.name;
        editTeacherNameInput.value = sectionToEdit.teacher;

        // 2. Select the correct radio button for the year
        editYearRadios.forEach(radio => {
            if (radio.value === sectionToEdit.year) {
                radio.checked = true;
            } else {
                radio.checked = false;
            }
        });

        // 3. Open the edit modal
        openModal(editModal, editModalContent);
    }
    
    // --- Event Listeners ---
    
    // ADD Modal Listeners
    openBtn.addEventListener('click', () => openModal(modal, modalContent));
    closeBtn.addEventListener('click', () => closeModal(modal, modalContent));
    if (addSectionForm) {
        addSectionForm.addEventListener('submit', function(event) {
            // Show loading state when submitting the ADD form
            if (addSectionForm.checkValidity()) {
                saveIcon.classList.add('hidden');
                saveText.textContent = 'Saving...';
                loadingSpinner.classList.remove('hidden');
                saveSectionBtn.disabled = true; 
                saveSectionBtn.classList.remove('hover:bg-blue-700');
                saveSectionBtn.classList.add('opacity-70', 'cursor-not-allowed');
            }
        });
    }

    // EDIT Modal Listeners
    closeEditModalBtn.addEventListener('click', () => closeModal(editModal, editModalContent)); 
    if (editForm) {
        editForm.addEventListener('submit', function(event) {
            // Show loading state when submitting the EDIT form
            if (editForm.checkValidity()) {
                updateIcon.classList.add('hidden');
                updateText.textContent = 'Updating...';
                updateLoadingSpinner.classList.remove('hidden');
                updateSectionBtn.disabled = true; 
                updateSectionBtn.classList.remove('hover:bg-green-700');
                updateSectionBtn.classList.add('opacity-70', 'cursor-not-allowed');
            }
        });
    }
    
    // DELETE Modal Listeners
    cancelDeleteBtn.addEventListener('click', () => closeModal(deleteModal, deleteModalContent));
    confirmDeleteBtn.addEventListener('click', function() {
        const sectionId = this.getAttribute('data-section-id');
        if (sectionId) {
            executeDeleteAction(sectionId);
        }
    });

    // SUCCESS Modal Listener
    closeSuccessModalBtn.addEventListener('click', () => closeModal(successModal, successModalContent));


    // Modal click-off handlers (Clicking the backdrop closes the modal)
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
    
    // --- Add/Update/Delete Success Logic (On Page Load) ---
    let detailsToShow = null;
    let modalTitle = '';
    let modalDescription = ''; 
    
    // Check for ADD success 
    if (typeof successDetails !== 'undefined' && successDetails && successDetails.name) {
        detailsToShow = successDetails;
        modalTitle = 'Section Added Successfully!';
        modalDescription = 'The new section has been saved to the database.';
    } 
    // Check for EDIT success
    else if (typeof editSuccessDetails !== 'undefined' && editSuccessDetails && editSuccessDetails.name) {
        detailsToShow = editSuccessDetails;
        modalTitle = 'Section Updated Successfully!';
        modalDescription = 'The section details have been successfully updated.';
    }
    // Check for DELETE success
    else if (typeof deleteSuccessDetails !== 'undefined' && deleteSuccessDetails && deleteSuccessDetails.name) {
        detailsToShow = deleteSuccessDetails;
        modalTitle = 'Section Deleted Successfully!';
        modalDescription = 'The section was permanently removed from the system.';
    }


    if (detailsToShow) {
        // 1. Update the success modal's dynamic content
        document.getElementById('success-modal-title').textContent = modalTitle;
        
        if (successModalDescription) {
            successModalDescription.textContent = modalDescription;
        }

        document.getElementById('modalSectionName').textContent = detailsToShow.name;
        document.getElementById('modalSectionYear').textContent = detailsToShow.year;
        document.getElementById('modalTeacherName').textContent = detailsToShow.teacher;
        
        // 2. Open the success modal
        openModal(successModal, successModalContent);
    }
    
    // Initialize sidebar JS logic after all HTML is loaded (moved from sidebar.php)
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.querySelector('main');
    
    if (sidebar && toggleBtn && sidebarOverlay) {
        
        const openSidebar = () => {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        const closeSidebar = () => {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
            // Only restore scroll if no other modal is open
            if(modal.classList.contains('hidden') && successModal.classList.contains('hidden') && editModal.classList.contains('hidden') && deleteModal.classList.contains('hidden')) {
                 document.body.style.overflow = '';
            }
        };

        toggleBtn.addEventListener('click', () => {
            if (sidebar.classList.contains('-translate-x-full')) {
                openSidebar();
            } else {
                closeSidebar();
            }
        });

        sidebarOverlay.addEventListener('click', closeSidebar);

        const navLinks = sidebar.querySelectorAll('nav a');
        navLinks.forEach(link => {
            link.addEventListener('click', closeSidebar);
        });
        
        // Handle main content margin for smaller screens
        const setMainMargin = () => {
            if (window.innerWidth < 768) {
                 if(mainContent) mainContent.style.marginLeft = '4rem';
            } else {
                 if(mainContent) mainContent.style.marginLeft = ''; 
            }
        };

        setMainMargin();
        window.addEventListener('resize', setMainMargin);
    }

});