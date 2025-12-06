<?php
?>

<div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden" aria-modal="true" role="dialog" aria-labelledby="success-modal-title">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 transform transition-all duration-300 scale-95 opacity-0" id="successModalContent">
        <div class="p-6 text-center">
            <div class="flex flex-col items-center justify-center">
                <div class="p-3 bg-primary-green/10 rounded-full mb-4">
                    <i data-lucide="check-circle" class="w-10 h-10 text-primary-green"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2" id="success-modal-title">Student Enrolled Successfully!</h3>
                <p class="text-gray-500 mb-5" id="success-modal-description">The student was successfully enrolled.</p>
            </div>

            <ul id="success-modal-errors" class="list-disc list-inside text-sm text-red-600 bg-red-50 p-3 rounded-lg border border-red-200 text-left mb-6 hidden">
                </ul>

            <button id="closeSuccessModalBtn" class="w-full bg-primary-blue hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition duration-150">
                Close
            </button>
        </div>
    </div>
</div>