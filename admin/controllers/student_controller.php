<?php

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

require __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/student_model.php';


$add_success_details = $_SESSION['add_success_details'] ?? null;
unset($_SESSION['add_success_details']);
$edit_success_details = $_SESSION['edit_success_details'] ?? null; 
unset($_SESSION['edit_success_details']);
$delete_success_details = $_SESSION['delete_success_details'] ?? null; 
unset($_SESSION['delete_success_details']);
$bulk_success_details = $_SESSION['bulk_success_details'] ?? null;
unset($_SESSION['bulk_success_details']);
$add_error_details = $_SESSION['add_error_details'] ?? null; 
unset($_SESSION['add_error_details']);
$student_to_edit = $_SESSION['student_to_edit'] ?? null; 
unset($_SESSION['student_to_edit']);

$students = [];
$sections_list = []; 
$grades_by_student = []; 
$fetch_error = false;

$selected_section_id = $_GET['section_id'] ?? 'all';
$search_term = trim($_GET['search'] ?? '');



$sections_list = fetch_sections_list($conn);



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    switch ($_POST['action']) {
        case 'add_student':
            add_new_student($conn, $sections_list, $_POST);
            break;
            
        case 'bulk_add_students':
            handle_bulk_add_students($conn, $sections_list, $_FILES);
            break;
            
        case 'delete_student':
            delete_existing_student($conn, trim($_POST['student_id'] ?? ''));
            break;
            
        case 'fetch_edit_data':
            fetch_student_for_edit($conn, trim($_POST['student_id'] ?? ''));
            break;
            
        case 'edit_student':
             update_existing_student($conn, $sections_list, $_POST);
            break;
    }
    
    header("Location: students.php");
    exit;
}



$students = fetch_students($conn, $selected_section_id, $search_term, $fetch_error);

if (!empty($students)) {
    $student_ids = array_column($students, 'id');
    $grades_by_student = fetch_grades_by_student($conn, $student_ids);
}


if (isset($conn)) {
    $conn->close();
}

?>