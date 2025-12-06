<?php
?>

<div id="deleteConfirmationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden" aria-modal="true" role="dialog" aria-labelledby="delete-modal-title">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 transform transition-all duration-300 scale-95 opacity-0" id="deleteModalContent">
        <div class="p-6 text-center">
            <div class="flex flex-col items-center justify-center">
                <div class="p-3 bg-red-100 rounded-full mb-4">
                    <i data-lucide="alert-triangle" class="w-10 h-10 text-red-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2" id="delete-modal-title">Confirm Deletion</h3>
                <p class="text-gray-500 mb-5">
                    Are you sure you want to delete the <span id="deleteItemType" class="font-semibold text-red-600">item</span>: 
                    <span id="deleteItemName" class="font-semibold text-red-600"></span>?
                </p>
                <p class="text-sm text-gray-400">This action cannot be undone.</p>
            </div>
            
            <div class="flex justify-between space-x-3 mt-6">
                <button id="cancelDeleteBtn" class="w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2.5 px-4 rounded-lg transition duration-150">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" data-item-id="" data-item-type="" class="w-1/2 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-4 rounded-lg transition duration-150">
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>