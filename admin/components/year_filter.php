<?php

if (!isset($valid_years)) {
    $valid_years = ['all', 'Year 7', 'Year 8', 'Year 9', 'Year 10', 'Year 11', 'Year 12'];
}
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
                       text-gray-800 font-medium transition duration-150 focus:outline-none focus:ring-2 focus:ring-primary-blue"
                aria-expanded="false"
                aria-haspopup="listbox"
                onclick="toggleYearDropdown(this)">
            
            <span class="truncate"><?php echo $display_name; ?></span>
            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-500 transition duration-150"></i>
        </button>

        <ul id="year-filter-options" 
            class="absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-lg shadow-xl py-1 hidden 
                   max-h-60 overflow-y-auto"
            role="listbox">
            
            <?php foreach ($valid_years as $year): 
                $isActive = ($year === $selected_year);
                $link_text = ($year === 'all') ? 'All Years' : $year;
            ?>
                <li role="option" 
                    aria-selected="<?php echo $isActive ? 'true' : 'false'; ?>"
                    class="cursor-pointer px-4 py-2 text-gray-700 hover:bg-primary-blue/10 transition duration-100 <?php echo $isActive ? 'bg-primary-blue/10 font-bold text-primary-blue' : ''; ?>"
                    onclick="handleFilterSelect('<?php echo urlencode($year); ?>')">
                    <?php echo $link_text; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<script>
    function toggleYearDropdown(buttonElement) {
        const optionsList = document.getElementById('year-filter-options');
        const isHidden = optionsList.classList.contains('hidden');
        
        document.querySelectorAll('#year-filter-options').forEach(list => {
            if (list !== optionsList) {
                list.classList.add('hidden');
                list.previousElementSibling.setAttribute('aria-expanded', 'false');
            }
        });

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
                loadingText.textContent = 'Loading Sections...'; 
            }
            overlay.classList.remove('hidden', 'opacity-0'); 
            setTimeout(() => {
                window.location.href = 'sections.php?year=' + year;
            }, 50); 
        } else {
            window.location.href = 'sections.php?year=' + year;
        }
    }

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