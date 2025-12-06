<?php

?>

<div id="loadingOverlay" 
     class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 
            hidden transition-opacity duration-1000 opacity-0">
    <div class="flex flex-col items-center">
        <i data-lucide="loader-circle" class="w-12 h-12 text-white animate-spin"></i>
        <p id="loadingMessageText" class="mt-4 text-white text-lg font-semibold">Loading...</p> 
    </div>
</div>