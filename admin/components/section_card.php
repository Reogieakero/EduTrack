<?php

if (!isset($section)) {
    return;
}

$student_count = count($section['students'] ?? []);
$teacher_display = htmlspecialchars($section['teacher'] ?? 'Unassigned');
$year_display = htmlspecialchars($section['year'] ?? 'N/A');

$created_at_timestamp = $section['created_at'] ?? null;
$created_at_display = 'N/A';

if ($created_at_timestamp) {
    try {
        $datetime = new DateTime($created_at_timestamp);
        $created_at_display = $datetime->format('F j, Y \a\t g:i A');
    } catch (Exception $e) {
        $created_at_display = "Error: " . $created_at_timestamp;
    }
}

?>
<div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 transition duration-300 hover:shadow-xl">
    <div class="flex justify-between items-start mb-4 pb-4 border-b">
        <div>
            <div class="flex items-center space-x-3">
                <h3 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($section['name']); ?></h3>
                
                <button 
                    onclick="initiateEditAction('<?php echo htmlspecialchars($section['id']); ?>')"
                    class="text-gray-400 hover:text-primary-blue p-1 rounded-full transition duration-150" 
                    title="Edit Section">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </button>
                
                <button 
                    onclick="confirmDeleteAction('<?php echo htmlspecialchars($section['id']); ?>', '<?php echo htmlspecialchars($section['name']); ?>')"
                    class="text-gray-400 hover:text-red-500 p-1 rounded-full transition duration-150" 
                    title="Delete Section">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
            
            <p class="text-sm text-gray-600 mt-1">
                <span class="font-bold text-primary-blue mr-4"><?php echo $year_display; ?></span>
                Teacher: <span class="font-medium text-primary-green"><?php echo $teacher_display; ?></span>
            </p>
            
            <p class="text-xs text-gray-400 mt-2 flex items-center space-x-1">
                <i data-lucide="clock" class="w-3 h-3 flex-shrink-0"></i>
                <span>Added At: <?php echo $created_at_display; ?></span>
            </p>
        </div>
        <div class="text-right p-3 bg-gray-50 rounded-lg border border-gray-200">
            <span class="text-3xl font-extrabold text-primary-blue"><?php echo $student_count; ?></span>
            <p class="text-sm text-gray-500 mt-0.5">Students</p>
        </div>
    </div>

    <div class="mt-4">
        <h4 class="text-lg font-semibold text-gray-700 mb-3 flex items-center space-x-2">
            <i data-lucide="users" class="w-5 h-5 text-gray-500"></i>
            <span>Students in Section:</span>
        </h4>
        
        <?php if ($student_count > 0): ?>
            <ul class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm text-gray-700 max-h-48 overflow-y-auto pr-2 custom-scroll">
                <?php foreach ($section['students'] as $student): ?>
                    <li class="flex items-center space-x-2 p-2 rounded-lg bg-gray-100/70 border border-gray-200">
                        <i data-lucide="user" class="w-4 h-4 text-gray-500 flex-shrink-0"></i>
                        <span class="truncate"><?php echo htmlspecialchars($student['name'] ?? 'Unknown'); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="p-4 bg-yellow-50 text-yellow-700 rounded-lg border border-yellow-200 flex items-center space-x-2">
                <i data-lucide="info" class="w-5 h-5 flex-shrink-0"></i>
                <p class="italic">No students are currently assigned to this section.</p>
            </div>
        <?php endif; ?>

        <button onclick="alert('In a real app, this would open a modal to manage students for this section (ID: <?php echo htmlspecialchars($section['id'] ?? 'N/A'); ?>)')" class="mt-5 text-sm font-medium flex items-center space-x-1 text-primary-blue hover:text-blue-700 transition duration-150">
            <i data-lucide="external-link" class="w-4 h-4"></i>
            <span>Manage Section Students</span>
        </button>
    </div>
</div>