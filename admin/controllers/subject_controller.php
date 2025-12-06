<?php

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Adjust path if needed
    header("Location: login.html");
    exit;
}

// IMPORTANT: This line includes your database.php, which MUST define the global $conn variable.
require_once __DIR__ . '/../../config/database.php'; 

// --- Session Message Handlers ---
$add_success_details = $_SESSION['add_subject_success'] ?? null;
unset($_SESSION['add_subject_success']);
$edit_success_details = $_SESSION['edit_subject_success'] ?? null;
unset($_SESSION['edit_subject_success']);
$delete_success_details = $_SESSION['delete_subject_success'] ?? null;
unset($_SESSION['delete_subject_success']);
$subject_to_edit = $_SESSION['subject_to_edit'] ?? null;
unset($_SESSION['subject_to_edit']);

$fetch_error = false;
$subjects = [];
$year_levels = [7, 8, 9, 10, 11, 12]; // Define available year levels

// --- Helper Functions ---

function log_error_and_set_session($error_message) {
    error_log($error_message);
    $_SESSION['operation_error'] = $error_message;
}

// MODIFIED: Function now only accepts the year filter
function fetch_subjects($conn, $selected_year) {
    global $fetch_error;
    $subjects_list = [];
    $where_clauses = [];
    $params = [];
    $types = '';

    // 1. Year Filter
    // Check if the year is a valid integer between 7 and 12
    if ($selected_year !== 'all' && is_numeric($selected_year) && in_array((int)$selected_year, [7, 8, 9, 10, 11, 12])) {
        $where_clauses[] = "year_level = ?";
        $params[] = (int)$selected_year;
        $types .= 'i';
    }
    // Search Filter logic removed

    $sql = "SELECT id, subject_code, subject_name, year_level FROM subjects";
    
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    $sql .= " ORDER BY year_level ASC, subject_name ASC";
    
    if (empty($params)) {
        // No parameters, execute as simple query
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $subjects_list[] = $row;
            }
            $result->free();
        } else {
            $fetch_error = "ERROR: Could not fetch subjects. " . $conn->error;
            log_error_and_set_session($fetch_error);
        }
    } else {
        // Use prepared statement
        if ($stmt = $conn->prepare($sql)) {
            // Dynamically bind parameters
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $subjects_list[] = $row;
                }
                $result->free();
            } else {
                $fetch_error = "ERROR: Could not execute prepared statement. " . $stmt->error;
                log_error_and_set_session($fetch_error);
            }
            $stmt->close();
        } else {
            $fetch_error = "ERROR: Could not prepare statement. " . $conn->error;
            log_error_and_set_session($fetch_error);
        }
    }

    return $subjects_list;
}

function add_new_subject($conn, $data) {
    $code = trim($data['subject_code'] ?? '');
    $name = trim($data['subject_name'] ?? '');
    $year = (int)($data['year_level'] ?? 0); 

    if (empty($code) || empty($name) || $year < 1) {
        log_error_and_set_session("Invalid input for adding subject.");
        return;
    }

    $sql_check = "SELECT id FROM subjects WHERE subject_code = ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("s", $code);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            log_error_and_set_session("Subject code '{$code}' already exists.");
            $stmt_check->close();
            return;
        }
        $stmt_check->close();
    }

    $sql = "INSERT INTO subjects (subject_code, subject_name, year_level) VALUES (?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssi", $code, $name, $year);
        if ($stmt->execute()) {
            $_SESSION['add_subject_success'] = [
                'name' => $name,
                'year' => $year
            ];
        } else {
            log_error_and_set_session("ERROR: Could not execute INSERT query. " . $stmt->error);
        }
        $stmt->close();
    } else {
        log_error_and_set_session("ERROR: Could not prepare INSERT statement. " . $conn->error);
    }
}

function update_existing_subject($conn, $data) {
    $id = (int)($data['subject_id'] ?? 0);
    $code = trim($data['edit_subject_code'] ?? '');
    $name = trim($data['edit_subject_name'] ?? '');
    $year = (int)($data['edit_year_level'] ?? 0);

    if ($id <= 0 || empty($code) || empty($name) || $year < 1) {
        log_error_and_set_session("Invalid input for updating subject.");
        return;
    }
    
    // Check for duplicate subject code, excluding the current subject being edited
    $sql_check = "SELECT id FROM subjects WHERE subject_code = ? AND id != ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("si", $code, $id);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            log_error_and_set_session("Subject code '{$code}' already exists for another subject.");
            $stmt_check->close();
            return;
        }
        $stmt_check->close();
    }

    $sql = "UPDATE subjects SET subject_code = ?, subject_name = ?, year_level = ? WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssii", $code, $name, $year, $id);
        if ($stmt->execute()) {
            $_SESSION['edit_subject_success'] = [
                'name' => $name,
                'year' => $year
            ];
        } else {
            log_error_and_set_session("ERROR: Could not execute UPDATE query. " . $stmt->error);
        }
        $stmt->close();
    } else {
        log_error_and_set_session("ERROR: Could not prepare UPDATE statement. " . $conn->error);
    }
}

function delete_existing_subject($conn, $subject_id) {
    $id = (int)$subject_id;
    
    if ($id <= 0) {
        log_error_and_set_session("Invalid subject ID for deletion.");
        return;
    }

    // Optional: Fetch name for success message
    $subject_name = '';
    $sql_name = "SELECT subject_name FROM subjects WHERE id = ?";
    if ($stmt_name = $conn->prepare($sql_name)) {
        $stmt_name->bind_param("i", $id);
        $stmt_name->execute();
        $stmt_name->bind_result($subject_name);
        $stmt_name->fetch();
        $stmt_name->close();
    }

    $sql = "DELETE FROM subjects WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['delete_subject_success'] = [
                'name' => $subject_name
            ];
        } else {
            log_error_and_set_session("ERROR: Could not execute DELETE query. " . $stmt->error);
        }
        $stmt->close();
    } else {
        log_error_and_set_session("ERROR: Could not prepare DELETE statement. " . $conn->error);
    }
}

function fetch_subject_for_edit($conn, $subject_id) {
    $id = (int)$subject_id;

    $sql = "SELECT id, subject_code, subject_name, year_level FROM subjects WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $_SESSION['subject_to_edit'] = $result->fetch_assoc();
        } else {
             log_error_and_set_session("Subject with ID {$id} not found.");
        }
        $stmt->close();
    } else {
        log_error_and_set_session("ERROR: Could not prepare fetch statement. " . $conn->error);
    }
}

// --- Main Controller Logic (Using global $conn) ---

// Get filter parameters from GET request
$selected_year = $_GET['year'] ?? 'all'; 
// $search_term = trim($_GET['search'] ?? ''); REMOVED


// Check if the global $conn variable (created by database.php) is valid
if (!isset($conn) || $conn->connect_error) {
    // If the database connection failed or $conn isn't set, set an error flag
    $fetch_error = "FATAL ERROR: Database connection failed.";
    log_error_and_set_session($fetch_error);
} else {
    // Connection is valid, proceed with POST handling
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        
        switch ($_POST['action']) {
            case 'add_subject':
                add_new_subject($conn, $_POST);
                break;
                
            case 'update_subject':
                update_existing_subject($conn, $_POST);
                break;
                
            case 'delete_subject':
                delete_existing_subject($conn, trim($_POST['subject_id'] ?? ''));
                break;
                
            case 'fetch_edit_data':
                fetch_subject_for_edit($conn, trim($_POST['subject_id'] ?? ''));
                // Note: fetch_edit_data doesn't redirect, the JS handles the response
                break;
        }
        
        // Redirect after POST to prevent form resubmission, except for AJAX fetch
        if ($_POST['action'] !== 'fetch_edit_data') {
            // Preserve current GET filter during redirect
            $redirect_query = http_build_query([
                'year' => $selected_year
                // 'search' parameter removed
            ]);
            header("Location: subjects.php?" . $redirect_query);
            exit;
        }
    }

    // Fetch the list of subjects for the main page view, applying the year filter
    $subjects = fetch_subjects($conn, $selected_year); // Only pass $selected_year

    // Close the connection using the function defined in your database.php
    close_db_connection($conn);
}
?>