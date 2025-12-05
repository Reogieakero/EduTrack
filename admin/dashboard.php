<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

$current_user = htmlspecialchars($_SESSION['username']); 

$total_sections = 15;
$total_students = 1250;
$total_parents = 400;
$total_teachers = 85;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                'sidebar-bg': '#1B3C53',
                'sidebar-text': '#E5E7EB',
                'page-bg': '#F4F7FF',
                'friendly-blue': '#6CB4EE',
                'friendly-green': '#7DD97D',
                'friendly-yellow': '#FFD77A',
                'friendly-purple': '#B78DEE',
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
        }
    }
}
</script>
</head>
<body class="bg-page-bg min-h-screen flex">

<?php include 'components/sidebar.php'; ?>

<main class="flex-grow ml-16 md:ml-56 p-8">
    <header class="mb-8 border-b pb-4">
        <h1 class="text-4xl font-semibold text-gray-800">Admin Dashboard</h1>
        <p class="text-gray-500 mt-1">Overview and management of the EduTrack system.</p>
    </header>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="relative overflow-hidden rounded-2xl shadow-lg p-6 bg-gradient-to-r from-white to-friendly-blue transition-transform duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Sections</p>
                    <p class="text-3xl font-bold mt-2 text-gray-900"><?php echo $total_sections; ?></p>
                </div>
                <div class="bg-blue-200 p-3 rounded-full">
                    <i data-lucide="layers" class="w-6 h-6 text-blue-700"></i>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-600 flex items-center">Total Sections</p>
        </div>

        <div class="relative overflow-hidden rounded-2xl shadow-lg p-6 bg-gradient-to-r from-white to-friendly-green transition-transform duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Students</p>
                    <p class="text-3xl font-bold mt-2 text-gray-900"><?php echo $total_students; ?></p>
                </div>
                <div class="bg-green-200 p-3 rounded-full">
                    <i data-lucide="user-cog" class="w-6 h-6 text-green-700"></i>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-600 flex items-center">Total Students</p>
        </div>

        <div class="relative overflow-hidden rounded-2xl shadow-lg p-6 bg-gradient-to-r from-white to-friendly-yellow transition-transform duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Parents</p>
                    <p class="text-3xl font-bold mt-2 text-gray-900"><?php echo $total_parents; ?></p>
                </div>
                <div class="bg-yellow-200 p-3 rounded-full">
                    <i data-lucide="users" class="w-6 h-6 text-yellow-700"></i>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-600 flex items-center">Total Parents</p>
        </div>

        <div class="relative overflow-hidden rounded-2xl shadow-lg p-6 bg-gradient-to-r from-white to-friendly-purple transition-transform duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Teachers</p>
                    <p class="text-3xl font-bold mt-2 text-gray-900"><?php echo $total_teachers; ?></p>
                </div>
                <div class="bg-purple-200 p-3 rounded-full">
                    <i data-lucide="graduation-cap" class="w-6 h-6 text-purple-700"></i>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-600 flex items-center">Total Teachers</p>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
});
</script>
</body>
</html>