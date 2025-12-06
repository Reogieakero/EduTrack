<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../pages/login.html");
    exit;
}


require_once '../../config/database.php'; 

$add_success_details = $_SESSION['add_teacher_success'] ?? null;
unset($_SESSION['add_teacher_success']);

$edit_success_details = $_SESSION['edit_teacher_success'] ?? null;
unset($_SESSION['edit_teacher_success']);

$delete_success_details = $_SESSION['delete_teacher_success'] ?? null;
unset($_SESSION['delete_teacher_success']);

$teacher_to_edit = $_SESSION['teacher_to_edit'] ?? null;
unset($_SESSION['teacher_to_edit']);

$assign_success_details = $_SESSION['assign_teacher_success'] ?? null;
unset($_SESSION['assign_teacher_success']);

$add_error = null;
$fetch_error = null;

unset($_SESSION['auth_error']);
unset($_SESSION['auth_action']);
unset($_SESSION['auth_section_id']);

$teachers = [];
$sections_list = [];

$sql_fetch_teachers = "
    SELECT t.id, t.last_name, t.first_name, t.email, s.name AS section_name, s.year AS section_year, s.id AS section_id
    FROM teachers t
    LEFT JOIN sections s ON t.assigned_section_id = s.id
    ORDER BY t.last_name ASC, t.first_name ASC
";

if ($stmt = $conn->prepare($sql_fetch_teachers)) {
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $teachers[] = $row;
        }
    } else {
        error_log("ERROR: Could not execute the teacher fetch statement. " . $stmt->error);
        $fetch_error = "Failed to load teacher data.";
    }
    $stmt->close();
} else {
    error_log("ERROR: Could not prepare the teacher fetch statement. " . $conn->error);
    $fetch_error = "Database error on teacher fetch setup.";
}


$sql_fetch_sections = "SELECT id, name, year, teacher FROM sections ORDER BY year ASC, name ASC";

if ($stmt = $conn->prepare($sql_fetch_sections)) {
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $sections_list[] = $row;
        }
    } else {
        error_log("ERROR: Could not execute sections list fetch: " . $stmt->error);
    }
    $stmt->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    $redirect_url = basename($_SERVER['PHP_SELF']);

    $action_to_perform = $_POST['action'];
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);

    if ($action_to_perform === 'add_teacher') {
        $lastName = trim($_POST['last_name'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($lastName) || empty($firstName) || empty($email)) {
            $add_error = "All fields (First Name, Last Name, Email) are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             $add_error = "Invalid email format.";
        } else {
            $sql = "INSERT INTO teachers (last_name, first_name, email) VALUES (?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sss", $lastName, $firstName, $email);
                if ($stmt->execute()) {
                    $_SESSION['add_teacher_success'] = [
                        'name' => "$firstName $lastName",
                        'email' => $email
                    ];
                    header("Location: " . $redirect_url);
                    exit;
                } else {
                    if ($conn->errno == 1062) {
                        $add_error = "ERROR: A teacher with that email address already exists.";
                    } else {
                        $add_error = "ERROR: Could not add teacher. " . $stmt->error;
                    }
                }
                $stmt->close();
            } else {
                $add_error = "ERROR: Could not prepare insert statement. " . $conn->error;
            }
        }
    }

    if ($action_to_perform === 'edit_teacher' && $teacher_id > 0) {
        $sql = "SELECT id, last_name, first_name, email, assigned_section_id FROM teachers WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $teacher_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $_SESSION['teacher_to_edit'] = $row;
            }
            $stmt->close();
        }
        header("Location: " . $redirect_url);
        exit;
    }

    // Key Logic for Successful Edit
    if ($action_to_perform === 'update_teacher' && $teacher_id > 0) {
        $updatedLastName = trim($_POST['edit_last_name'] ?? '');
        $updatedFirstName = trim($_POST['edit_first_name'] ?? '');
        $updatedEmail = trim($_POST['edit_email'] ?? '');

        if (empty($updatedLastName) || empty($updatedFirstName) || empty($updatedEmail)) {
            $add_error = "ERROR: All fields are required for teacher update.";
        } elseif (!filter_var($updatedEmail, FILTER_VALIDATE_EMAIL)) {
             $add_error = "Invalid email format.";
        } else {
             $sql = "UPDATE teachers SET last_name = ?, first_name = ?, email = ? WHERE id = ?";
             if ($stmt = $conn->prepare($sql)) {
                 $stmt->bind_param("sssi", $updatedLastName, $updatedFirstName, $updatedEmail, $teacher_id);
                 if ($stmt->execute()) {
                     // *** This sets the success flag and data in the session ***
                     $_SESSION['edit_teacher_success'] = [
                         'name' => "$updatedFirstName $updatedLastName",
                         'email' => $updatedEmail
                     ];
                     header("Location: " . $redirect_url); // Redirects to trigger client-side script
                     exit;
                 } else {
                      if ($conn->errno == 1062) {
                         $add_error = "ERROR: A teacher with that email address already exists.";
                     } else {
                         $add_error = "ERROR: Could not update teacher. " . $stmt->error;
                     }
                 }
                 $stmt->close();
             }
        }
    }

    if ($action_to_perform === 'delete_teacher' && $teacher_id > 0) {
        $sql_select = "SELECT first_name, last_name, assigned_section_id FROM teachers WHERE id = ?";
        $deleted_details = null;

        if ($stmt_select = $conn->prepare($sql_select)) {
            $stmt_select->bind_param("i", $teacher_id);
            $stmt_select->execute();
            $result_select = $stmt_select->get_result();
            $deleted_details = $result_select->fetch_assoc();
            $stmt_select->close();
        }

        if ($deleted_details) {
            if ($deleted_details['assigned_section_id'] > 0) {
                $sql_unassign = "UPDATE sections SET teacher = NULL WHERE id = ?";
                if ($stmt_unassign = $conn->prepare($sql_unassign)) {
                    $stmt_unassign->bind_param("i", $deleted_details['assigned_section_id']);
                    $stmt_unassign->execute();
                    $stmt_unassign->close();
                }
            }

            $sql_delete = "DELETE FROM teachers WHERE id = ?";
            if ($stmt_delete = $conn->prepare($sql_delete)) {
                $stmt_delete->bind_param("i", $teacher_id);
                
                if ($stmt_delete->execute()) {
                    $_SESSION['delete_teacher_success'] = [
                        'name' => $deleted_details['first_name'] . ' ' . $deleted_details['last_name'],
                    ];
                    header("Location: " . $redirect_url);
                    exit;
                } else {
                    $add_error = "ERROR: Could not delete teacher. " . $stmt_delete->error;
                }
                $stmt_delete->close();
            } else {
                 $add_error = "ERROR: Could not prepare teacher delete statement. " . $conn->error;
            }
        } else {
             $add_error = "ERROR: Teacher not found for deletion.";
        }
    }
    
    if ($action_to_perform === 'assign_teacher' && $teacher_id > 0) {
        $section_id_to_assign = (int)($_POST['section_id'] ?? 0);
        
        $teacher_name = '';
        $section_name_year = '';
        $old_section_id = 0; // Initialize old section ID

        // 1. Get Teacher Name and current assigned section
        $sql_teacher_name = "SELECT first_name, last_name, assigned_section_id FROM teachers WHERE id = ?";
        if ($stmt = $conn->prepare($sql_teacher_name)) {
            $stmt->bind_param("i", $teacher_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $teacher_name = trim($row['first_name'] . ' ' . $row['last_name']);
                $old_section_id = $row['assigned_section_id'];
            }
            $stmt->close();
        }

        // 2. Get new Section Details (only needed for success message if assigning)
        if ($section_id_to_assign > 0) {
            $sql_section_details = "SELECT name, year FROM sections WHERE id = ?";
            if ($stmt = $conn->prepare($sql_section_details)) {
                $stmt->bind_param("i", $section_id_to_assign);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $section_name_year = trim($row['year'] . ' - ' . $row['name']);
                }
                $stmt->close();
            }
        }


        if (empty($teacher_name) || ($section_id_to_assign > 0 && empty($section_name_year))) {
            $add_error = "ERROR: Teacher or section details not found.";
        } else {
            
            // --- Assignment/Unassignment Logic ---

            if ($section_id_to_assign == 0) {
                // CASE 1: UNASSIGNMENT

                // 1. Clear the teacher name from the section record if they were assigned to one (using $old_section_id).
                if ($old_section_id > 0) {
                    // Update section table: set teacher to 'Unassigned'
                    $sql_clear_section = "UPDATE sections SET teacher = 'Unassigned' WHERE id = ?";
                    if ($stmt_clear_s = $conn->prepare($sql_clear_section)) {
                        $stmt_clear_s->bind_param("i", $old_section_id);
                        $stmt_clear_s->execute();
                        $stmt_clear_s->close();
                    }
                }
                
                // 2. Clear the teacher's assigned_section_id (set to 0).
                $sql_unassign_teacher = "UPDATE teachers SET assigned_section_id = 0 WHERE id = ?";
                if ($stmt_unassign_t = $conn->prepare($sql_unassign_teacher)) {
                    $stmt_unassign_t->bind_param("i", $teacher_id);
                    if ($stmt_unassign_t->execute()) {
                        $_SESSION['assign_teacher_success'] = [
                            'teacher' => $teacher_name,
                            'section' => 'Unassigned'
                        ];
                        header("Location: " . $redirect_url);
                        exit;
                    } else {
                        $add_error = "ERROR: Could not unassign teacher record. " . $stmt_unassign_t->error;
                    }
                    $stmt_unassign_t->close();
                } else {
                    $add_error = "ERROR: Could not prepare unassign teacher statement. " . $conn->error;
                }

            } else { 
                // CASE 2: ASSIGNMENT ($section_id_to_assign > 0)
                
                // 1. Clear the old section's teacher name if the teacher is moving (enforces teacher-to-one).
                if ($old_section_id > 0 && $old_section_id != $section_id_to_assign) {
                    $sql_unassign_old_section = "UPDATE sections SET teacher = 'Unassigned' WHERE id = ?";
                    if ($stmt_unassign = $conn->prepare($sql_unassign_old_section)) {
                        $stmt_unassign->bind_param("i", $old_section_id);
                        $stmt_unassign->execute();
                        $stmt_unassign->close();
                    }
                }

                // 2. CRUCIAL FOR UNIQUENESS: Clear section assignment from *any other* teacher currently holding the NEW section (enforces section-to-one).
                $sql_clear_other_teacher_assignment = "
                    UPDATE teachers 
                    SET assigned_section_id = 0 
                    WHERE assigned_section_id = ? AND id != ?
                ";
                if ($stmt_clear_other = $conn->prepare($sql_clear_other_teacher_assignment)) {
                    $stmt_clear_other->bind_param("ii", $section_id_to_assign, $teacher_id);
                    $stmt_clear_other->execute();
                    $stmt_clear_other->close();
                }
                
                // 3. Update the section record with the new teacher name.
                $sql_update_section = "UPDATE sections SET teacher = ? WHERE id = ?";
                if ($stmt = $conn->prepare($sql_update_section)) {
                    $stmt->bind_param("si", $teacher_name, $section_id_to_assign);
                    if ($stmt->execute()) {
                        // 4. Update the teacher's assigned section ID.
                        $sql_update_teacher = "UPDATE teachers SET assigned_section_id = ? WHERE id = ?";
                        if ($stmt2 = $conn->prepare($sql_update_teacher)) {
                            $stmt2->bind_param("ii", $section_id_to_assign, $teacher_id);
                            if ($stmt2->execute()) {
                                $_SESSION['assign_teacher_success'] = [
                                    'teacher' => $teacher_name,
                                    'section' => $section_name_year
                                ];
                                header("Location: " . $redirect_url);
                                exit;
                            } else {
                                $add_error = "ERROR: Could not update teacher assignment record. " . $stmt2->error;
                            }
                            $stmt2->close();
                        }
                    } else {
                        $add_error = "ERROR: Could not assign teacher to section. " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                     $add_error = "ERROR: Could not prepare assignment statement. " . $conn->error;
                }
            }
        }
    }
}

if (isset($conn)) {
    close_db_connection($conn);
}
?>