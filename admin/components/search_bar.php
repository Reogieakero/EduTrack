<?php
$current_search_term = $search_term ?? ''; 
$target_url = $target_url ?? basename($_SERVER['PHP_SELF']);
$placeholder_text = $placeholder_text ?? 'Search by student name...';
$input_id = $input_id ?? 'search-input';
$form_id = $form_id ?? 'searchForm';
?>

<form id="<?php echo htmlspecialchars($form_id); ?>" method="GET" action="<?php echo htmlspecialchars($target_url); ?>" class="flex-shrink-0 w-full sm:w-64">
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i data-lucide="search" class="w-5 h-5 text-gray-400"></i>
        </div>
        
        <input 
            type="search" 
            id="<?php echo htmlspecialchars($input_id); ?>"
            name="search" 
            value="<?php echo htmlspecialchars($current_search_term); ?>"
            class="block w-full rounded-lg border-0 py-2.5 pl-10 pr-4 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-blue sm:text-sm sm:leading-6 transition duration-150"
            placeholder="<?php echo htmlspecialchars($placeholder_text); ?>"
        >

        <?php if (isset($selected_section_id)): ?>
            <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($selected_section_id); ?>">
        <?php endif; ?>
    </div>
</form>