<?php
// components/year_filter.php

// NOTE: This component assumes $selected_year and $valid_years 
// are defined in the parent file (sections.php)

// Ensure valid_years is available
if (!isset($valid_years)) {
    $valid_years = ['all', 'Year 7', 'Year 8', 'Year 9', 'Year 10', 'Year 11', 'Year 12'];
}
// Ensure selected_year is set
if (!isset($selected_year)) {
    $selected_year = 'all';
}

$display_name = ($selected_year === 'all') ? 'All Years' : $selected_year;
?>

<div class="flex items-center space-x-3 text-sm">
    <label for="custom-year-filter" class="text-gray-600 font-semibold whitespace-nowrap">
        <i data-lucide="calendar-check" class="w-4 h-4 inline-block mr-1 align-text-bottom text-primary-blue"></i>
        Filter by Year:
    </label>
    
    <div id="custom-year-filter" class="relative w-40">
        
        <button type="button" 
                class="flex justify-between items-center w-full py-2 pl-4 pr-3 border border-gray-300 bg-white 
                       rounded-lg shadow-md hover:border-primary-blue/50 
                       text-gray-700 font-medium cursor-pointer 
                       focus:outline-none focus:ring-2 focus:ring-primary-blue focus:border-primary-blue
                       transition duration-200"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-labelledby="custom-year-filter-label"
                onclick="toggleFilterDropdown(this)">
            
            <span id="selected-year-text" class="truncate text-sm"><?php echo htmlspecialchars($display_name); ?></span>
            <i data-lucide="chevron-down" class="w-4 h-4 ml-2 text-gray-500"></i>
        </button>
        
        <ul id="year-filter-options" 
            class="absolute z-10 w-full mt-2 bg-white border border-gray-200 
                   rounded-lg shadow-xl focus:outline-none hidden max-h-60 overflow-y-auto" 
            tabindex="-1" role="listbox" 
            aria-labelledby="selected-year-text">
            
            <?php foreach ($valid_years as $year): 
                $is_selected = ($selected_year === $year);
                $option_display_name = ($year === 'all') ? 'All Years' : $year;
            ?>
                <li class="py-2 px-3 cursor-pointer text-gray-900 text-sm transition-colors duration-150
                           <?php 
                           // CHANGED: Use neutral gray background
                           echo $is_selected ? 'bg-gray-200 text-gray-900 font-semibold' : 'hover:bg-gray-100'; 
                           ?>"
                    role="option" 
                    aria-selected="<?php echo $is_selected ? 'true' : 'false'; ?>"
                    onclick="handleFilterSelect('<?php echo htmlspecialchars($year); ?>')">
                    
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
    // --- Custom Filter Dropdown JS Logic for Years ---
    function toggleFilterDropdown(buttonElement) {
        const optionsList = buttonElement.nextElementSibling;
        const isHidden = optionsList.classList.contains('hidden');
        
        // Hide all other open dropdowns (if multiple filter components exist on the page)
        document.querySelectorAll('#year-filter-options').forEach(list => {
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

    function handleFilterSelect(year) {
        const overlay = document.getElementById('loadingOverlay');
        const loadingText = document.getElementById('loadingMessageText'); 

        if (overlay) {
            if (loadingText) {
                loadingText.textContent = 'Loading Sections by Year...'; // Set a more specific filter message
            }
            overlay.classList.remove('hidden');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
            }, 10);
        }
        
        // Perform the redirect/filter action
        window.location.href = 'sections.php?year=' + year;
    }

    // Close dropdown if user clicks outside
    document.addEventListener('click', function(event) {
        const customFilter = document.getElementById('custom-year-filter');
        if (customFilter && !customFilter.contains(event.target)) {
            const optionsList = document.getElementById('year-filter-options');
            if (optionsList && !optionsList.classList.contains('hidden')) {
                optionsList.classList.add('hidden');
                customFilter.querySelector('button').setAttribute('aria-expanded', 'false');
            }
        }
    });

</script>