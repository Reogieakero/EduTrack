<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

// Variable required by sidebar.php
$current_user = htmlspecialchars($_SESSION['username']); 
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
                        'app-bg': '#1B3C53', 
                        'primary': '#1B3C53', 
                        'primary-hover': '#153043',
                        'page-bg': '#F4F7FF',
                        'secondary-text': '#1F2937', 
                        'sidebar-bg': '#1B3C53',
                        'sidebar-text': '#E5E7EB', // Gray-200
                        'active-link': '#10B981', // Emerald-500
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        droid: ['Roboto', 'sans-serif'], 
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-page-bg min-h-screen flex">

    <?php 
    include 'sidebar.php'; 
    ?>

    <main class="flex-grow md:ml-64 p-8">
        
        <header class="mb-8 border-b pb-4">
            <h1 class="text-4xl font-semibold text-secondary-text">Admin Dashboard</h1>
            <p class="text-gray-500">Overview and management of the EduTrack system.</p>
        </header>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <p class="text-lg font-medium text-gray-800">🎉 Welcome back, <?php echo $current_user; ?>!</p>
            <p class="mt-2 text-gray-600">Use the sidebar to navigate the different management sections.</p>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-indigo-50 p-4 rounded-lg shadow-md border-l-4 border-indigo-500">
                    <p class="text-sm uppercase tracking-wider text-indigo-700">Total Students</p>
                    <p class="text-3xl font-bold text-indigo-900">1,250</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg shadow-md border-l-4 border-green-500">
                    <p class="text-sm uppercase tracking-wider text-green-700">Total Teachers</p>
                    <p class="text-3xl font-bold text-green-900">85</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg shadow-md border-l-4 border-yellow-500">
                    <p class="text-sm uppercase tracking-wider text-yellow-700">Active Parents</p>
                    <p class="text-3xl font-bold text-yellow-900">980</p>
                </div>
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