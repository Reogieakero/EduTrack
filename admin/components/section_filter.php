<?php
// components/section_filter.php

// NOTE: This component assumes $selected_section_id and $sections_list
// are defined in the parent file (students.php)

// Ensure $sections_list is available and is an array
if (!isset($sections_list) || !is_array($sections_list)) {
    $sections_list = [];
}
// Ensure $selected_section_id is set
if (!isset($selected_section_id)) {
    $selected_section_id = 'all';
}

$display_name = 'All Sections';
if (isset($sections_list[$selected_section_id])) {
    $section = $sections_list[$selected_section_id];
    $display_name = $section['year'] . ' - ' . $section['name'];
}

// Add 'All Sections' as the first virtual option
$filter_options = ['all' => ['display_name' => 'All Sections', 'id' => 'all']];

foreach ($sections_list as $id => $section) {
    $filter_options[$id] = [
        'display_name' => htmlspecialchars($section['year'] . ' - ' . $section['name']),
        'id' => $id
    ];
}

?>

<div class="flex items-center space-x-3 text-sm">
    <label for="custom-section-filter" class="text-gray-600 font-semibold whitespace-nowrap hidden sm:block">
        <i data-lucide="layout-list" class="w-4 h-4 inline-block mr-1 align-text-bottom text-friendly-blue"></i>
        Filter by Section:
    </label>
    
    <div id="custom-section-filter" class="relative w-40 sm:w-56">
        
        <button type="button" 
                class="flex justify-between items-center w-full py-2 pl-4 pr-3 border border-gray-300 bg-white 
                       rounded-lg shadow-md hover:border-friendly-blue/50 
                       text-gray-700 font-medium cursor-pointer 
                       focus:outline-none focus:ring-2 focus:ring-friendly-blue focus:border-friendly-blue
                       transition duration-200"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-labelledby="custom-section-filter-label"
                onclick="toggleSectionDropdown(this)">
            
            <span id="selected-section-text" class="truncate text-sm"><?php echo htmlspecialchars($display_name); ?></span>
            <i data-lucide="chevron-down" class="w-4 h-4 ml-2 text-gray-500"></i>
        </button>
        
        <ul id="section-filter-options" 
            class="absolute z-10 w-full mt-2 bg-white border border-gray-200 
                   rounded-lg shadow-xl focus:outline-none hidden max-h-60 overflow-y-auto" 
            tabindex="-1" role="listbox" 
            aria-labelledby="selected-section-text">
            
            <?php foreach ($filter_options as $option): 
                $id = $option['id'];
                $option_display_name = $option['display_name'];
                $is_selected = ($selected_section_id == $id);
            ?>
                <li class="py-2 px-3 cursor-pointer text-gray-900 text-sm transition-colors duration-150
                           <?php echo $is_selected ? 'bg-friendly-blue text-white font-semibold' : 'hover:bg-gray-100'; ?>"
                    role="option" 
                    aria-selected="<?php echo $is_selected ? 'true' : 'false'; ?>"
                    onclick="handleSectionFilterSelect('<?php echo htmlspecialchars($id); ?>')">
                    
                    <?php echo $option_display_name; ?>
                    
                    <?php if ($is_selected): ?>
                        <i data-lucide="check" class="w-4 h-4 inline-block ml-2 align-middle"></i>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<script>
    // --- Custom Filter Dropdown JS Logic for Sections ---
    function toggleSectionDropdown(buttonElement) {
        const optionsList = buttonElement.nextElementSibling;
        const isHidden = optionsList.classList.contains('hidden');
        
        // Hide all other open dropdowns
        document.querySelectorAll('#section-filter-options').forEach(list => {
            if (list !== optionsList) {
                list.classList.add('hidden');
                list.previousElementSibling.setAttribute('aria-expanded', 'false');
            }
        });

        // Toggle visibility of the target dropdown
        if (isHidden) {
            optionsList.classList.remove('hidden');
            buttonElement.setAttribute('aria-expanded', 'true');
        } else {
            optionsList.classList.add('hidden');
            buttonElement.setAttribute('aria-expanded', 'false');
        }
    }

    function handleSectionFilterSelect(section_id) {
        // --- MODIFIED: Set loading message for filtering ---
        const overlay = document.getElementById('loadingOverlay');
        const loadingText = document.getElementById('loadingMessageText'); // Get the text element

        if (overlay) {
            if (loadingText) {
                loadingText.textContent = 'Loading Students...'; // Set the student filter message
            }
            overlay.classList.remove('hidden');
            // Give a moment for the browser to paint the overlay before redirecting
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
            }, 10);
        }
        // --- END MODIFIED ---
        
        // Perform the redirect/filter action
        window.location.href = 'students.php?section_id=' + section_id;
    }

    // Close dropdown if user clicks outside
    document.addEventListener('click', function(event) {
        const customFilter = document.getElementById('custom-section-filter');
        if (customFilter && !customFilter.contains(event.target)) {
            const optionsList = document.getElementById('section-filter-options');
            if (optionsList && !optionsList.classList.contains('hidden')) {
                optionsList.classList.add('hidden');
                customFilter.querySelector('button').setAttribute('aria-expanded', 'false');
            }
        }
    });

</script>