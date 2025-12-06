<?php
// This component assumes a $subject variable is available, e.g.:
// ['id' => 1, 'name' => 'English', 'year_level' => 7, 'quarter_semester' => 'Q1']

$quarter_label = $subject['year_level'] >= 11 ? 'Semester/Quarter' : 'Quarter';
$quarter_display = htmlspecialchars($subject['quarter_semester']);
$year_display = 'Year ' . htmlspecialchars($subject['year_level']);

// Use a different color for senior years (11 & 12)
$header_class = $subject['year_level'] >= 11 ? 'bg-primary-green/20' : 'bg-primary-blue/20'; 

?>

<div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 overflow-hidden">
    <div class="<?php echo $header_class; ?> p-4 border-b border-gray-200">
        <h3 class="text-lg font-bold text-gray-800 flex items-center space-x-2">
            <i data-lucide="book" class="w-5 h-5 text-gray-600"></i>
            <span><?php echo htmlspecialchars($subject['name']); ?></span>
        </h3>
    </div>
    <div class="p-4 space-y-3">
        <div class="flex items-center space-x-3">
            <i data-lucide="calendar" class="w-5 h-5 text-gray-500 flex-shrink-0"></i>
            <span class="text-gray-700 font-medium"><?php echo $year_display; ?></span>
        </div>
        <div class="flex items-center space-x-3">
            <i data-lucide="calendar-check" class="w-5 h-5 text-gray-500 flex-shrink-0"></i>
            <span class="text-gray-700">
                <?php echo $quarter_label . ': '; ?>
                <span class="font-semibold text-primary-blue"><?php echo $quarter_display; ?></span>
            </span>
        </div>
    </div>
    <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-2">
        <button onclick="window.initiateEditAction(<?php echo htmlspecialchars(json_encode($subject)); ?>)" class="text-primary-green hover:text-green-700 transition duration-150 p-1 rounded-full">
            <i data-lucide="pencil" class="w-5 h-5"></i>
        </button>
        <button onclick="window.initiateDeleteAction(<?php echo $subject['id']; ?>, '<?php echo htmlspecialchars($subject['name']); ?>', 'subject')" class="text-red-600 hover:text-red-700 transition duration-150 p-1 rounded-full">
            <i data-lucide="trash-2" class="w-5 h-5"></i>
        </button>
    </div>
</div>