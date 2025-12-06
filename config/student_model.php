<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date; 

require_once __DIR__ . '/database.php';

function check_for_duplicate_student($conn, $first_name, $last_name, $middle_initial) {
    
    $sql = "SELECT id FROM students WHERE 
            LOWER(first_name) = LOWER(?) AND 
            LOWER(last_name) = LOWER(?)";

    $params = [$first_name, $last_name];
    $types = 'ss';

    if (!empty($middle_initial)) {
        $sql .= " AND LOWER(middle_initial) = LOWER(?)";
        $params[] = $middle_initial;
        $types .= 's';
    } else {
        $sql .= " AND middle_initial IS NULL";
    }

    $is_duplicate = false;
    if ($stmt = $conn->prepare($sql)) {
        $bind_names = [$types];
        for ($i=0; $i<count($params); $i++) {
            $bind_names[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
        
        if ($stmt->execute()) {
            $stmt->store_result();
            $is_duplicate = $stmt->num_rows > 0;
            $stmt->close();
        } else {
            error_log("DB Execute Error (check_for_duplicate_student): " . $stmt->error);
            $stmt->close();
        }
    } else {
        error_log("DB Prepare Error (check_for_duplicate_student): " . $conn->error);
    }
    return $is_duplicate;
}

function generate_student_id($conn) {
    $current_year = date('Y');
    $search_pattern = $current_year . '-%';
    
    $sql = "SELECT MAX(CAST(SUBSTRING(id, 6) AS UNSIGNED)) as max_seq 
            FROM students 
            WHERE id LIKE ?";
            
    $max_seq = 0;
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $search_pattern);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $max_seq = (int)($row['max_seq'] ?? 0);
            }
        } else {
             error_log("DB Execute Error (generate_student_id): " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("DB Prepare Error (generate_student_id): " . $conn->error);
    }
    
    $next_seq = $max_seq + 1;
    
    if ($next_seq > 9999) {
        error_log("Student ID sequence overflow for year " . $current_year);
        return null; 
    }
    
    $formatted_seq = str_pad($next_seq, 4, '0', STR_PAD_LEFT);
    
    return $current_year . '-' . $formatted_seq;
}

function fetch_sections_list($conn) {
    $sections_list = []; 
    $sql = "SELECT id, year, name, teacher FROM sections ORDER BY year ASC, name ASC";
    if ($stmt = $conn->prepare($sql)) {
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $sections_list[$row['id']] = $row;
            }
        } else {
            error_log("DB Execute Error (fetch_sections_list): " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("DB Prepare Error (fetch_sections_list): " . $conn->error);
    }
    return $sections_list;
}

function fetch_students($conn, $selected_section_id, $search_term, &$fetch_error) {
    $students = [];
    $sql_fetch = "SELECT s.*, sec.year as section_year, sec.name as section_name, sec.teacher as teacher_name FROM students s JOIN sections sec ON s.section_id = sec.id";
    $where_clauses = [];
    $params = [];
    $types = '';

    if ($selected_section_id !== 'all' && is_numeric($selected_section_id)) {
        $where_clauses[] = "s.section_id = ?";
        $params[] = $selected_section_id;
        $types .= 'i';
    }

    if (!empty($search_term)) {
        $search_pattern = '%' . $search_term . '%';
        $where_clauses[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ? OR CONCAT(s.last_name, ' ', s.first_name) LIKE ? OR s.id LIKE ?)";
        $params[] = $search_pattern;
        $params[] = $search_pattern;
        $params[] = $search_pattern;
        $params[] = $search_pattern;
        $params[] = $search_pattern; 
        $types .= 'sssss'; 
    }

    if (!empty($where_clauses)) {
        $sql_fetch .= " WHERE " . implode(' AND ', $where_clauses);
    }

    $sql_fetch .= " ORDER BY sec.year ASC, sec.name ASC, s.last_name ASC, s.first_name ASC";


    if ($stmt = $conn->prepare($sql_fetch)) {
        if (!empty($params)) {
            $bind_names = [$types];
            for ($i=0; $i<count($params); $i++) {
                $bind_names[] = &$params[$i];
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_names);
        }
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $students[] = $row;
                }
            }
        } else {
            $fetch_error = "ERROR: Could not execute the student fetch statement. " . $stmt->error;
        }
        $stmt->close();
    } else {
        $fetch_error = "ERROR: Could not prepare the student fetch statement. " . $conn->error;
    }
    return $students;
}

function fetch_grades_by_student($conn, $student_ids) {
    $grades_by_student = [];
    if (empty($student_ids)) {
        return $grades_by_student;
    }

    $in_clause_placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    $sql_fetch_grades = "SELECT student_id, quarter, grade FROM grades WHERE student_id IN ($in_clause_placeholders)";
    $types_grades = str_repeat('s', count($student_ids)); 

    if ($stmt_grades = $conn->prepare($sql_fetch_grades)) {
        $bind_names = [$types_grades];
        foreach ($student_ids as &$id) {
            $bind_names[] = &$id;
        }
        call_user_func_array([$stmt_grades, 'bind_param'], $bind_names);

        if ($stmt_grades->execute()) {
            $result_grades = $stmt_grades->get_result();
            
            while ($row = $result_grades->fetch_assoc()) {
                $student_id = $row['student_id'];
                $quarter = strtoupper($row['quarter']); 

                if (!isset($grades_by_student[$student_id])) {
                    $grades_by_student[$student_id] = [
                        'Q1' => null, 'Q2' => null, 
                        'Q3' => null, 'Q4' => null
                    ];
                }
                
                if (in_array($quarter, ['Q1', 'Q2', 'Q3', 'Q4'])) {
                    $grades_by_student[$student_id][$quarter] = number_format($row['grade'], 0); 
                }
            }
        } else {
            error_log("Grade Fetch Error: " . $stmt_grades->error);
        }
        $stmt_grades->close();
    } else {
         error_log("Grade Prepare Error: " . $conn->error);
    }
    return $grades_by_student;
}

function add_new_student($conn, $sections_list, $post_data) {
    
    
    $first_name = trim($post_data['first_name'] ?? '');
    $last_name = trim($post_data['last_name'] ?? '');
    $middle_initial_raw = trim($post_data['middle_initial'] ?? '');
    $section_id = (int)($post_data['section_id'] ?? 0);
    $date_of_birth = trim($post_data['date_of_birth'] ?? '');
    $middle_initial = empty($middle_initial_raw) ? null : strtoupper(substr($middle_initial_raw, 0, 1));
    
    if (empty($first_name) || empty($last_name) || $section_id <= 0 || empty($date_of_birth)) {
        $_SESSION['add_error_details'] = "All required student fields are necessary.";
        return;
    } 
    
    if (check_for_duplicate_student($conn, $first_name, $last_name, $middle_initial)) {
        $full_name = $last_name . ', ' . $first_name . ($middle_initial ? ' ' . $middle_initial . '.' : '');
        $_SESSION['add_error_details'] = "ERROR: A student with the name {$full_name} already exists in the system.";
        return;
    }
    
    $new_id = generate_student_id($conn);
    if (is_null($new_id)) {
        $_SESSION['add_error_details'] = "ERROR: Could not generate a unique student ID. Sequence overflow or database error.";
        return;
    }
    
    $sql = "INSERT INTO students (id, first_name, last_name, middle_initial, section_id, date_of_birth, enrollment_date) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssis", $param_id, $param_first_name, $param_last_name, $param_middle_initial, $param_section_id, $param_dob);
        
        $param_id = $new_id;
        $param_first_name = $first_name;
        $param_last_name = $last_name;
        $param_middle_initial = $middle_initial;
        $param_section_id = $section_id;
        $param_dob = $date_of_birth;
        
        if ($stmt->execute()) {
            $section_info = $sections_list[$section_id] ?? ['name' => 'N/A', 'year' => 'N/A', 'teacher' => 'N/A'];
            $full_name_display = $last_name . ', ' . $first_name . ($middle_initial ? ' ' . $middle_initial . '.' : '');
            
            $_SESSION['add_success_details'] = [
                'name' => $full_name_display,
                'section_name' => $section_info['name'],
                'section_year' => $section_info['year'],
                'teacher_name' => $section_info['teacher']
            ];
        } else {
            $_SESSION['add_error_details'] = "ERROR: Could not execute the insert statement. " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['add_error_details'] = "ERROR: Could not prepare the insert statement. " . $conn->error;
    }
}

function handle_bulk_add_students($conn, $sections_list, $files_data) {
    
    if (!isset($files_data['student_file']) || $files_data['student_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['add_error_details'] = "ERROR: No file uploaded or upload error occurred.";
        return;
    }
    
    $file_tmp_path = $files_data['student_file']['tmp_name'];
    $file_extension = strtolower(pathinfo($files_data['student_file']['name'], PATHINFO_EXTENSION));

    $allowed_extensions = ['csv', 'xlsx', 'xls'];

    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['add_error_details'] = "ERROR: Only CSV, XLSX, and XLS files are supported for bulk upload.";
        return;
    }
    
    $students_added = 0;
    $students_failed = 0;
    $errors = [];
    
    try {
        $spreadsheet = IOFactory::load($file_tmp_path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(); 

        $row_count = 1;
        
        $sql_insert = "INSERT INTO students (id, first_name, last_name, middle_initial, section_id, date_of_birth, enrollment_date) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        if ($stmt_insert = $conn->prepare($sql_insert)) {
            
            foreach ($rows as $index => $data) {
                if ($index === 0) {
                    continue; 
                }
                
                $row_count++;
                
                $first_name = trim($data[0] ?? '');
                $last_name = trim($data[1] ?? '');
                $middle_initial_raw = trim($data[2] ?? '');
                $section_name_raw = trim($data[3] ?? '');
                $date_of_birth_raw = trim($data[4] ?? '');
                
                $middle_initial = empty($middle_initial_raw) ? null : strtoupper(substr($middle_initial_raw, 0, 1));
                
                $section_id = 0;
                foreach ($sections_list as $id => $section) {
                    $full_section_name = $section['year'] . ' - ' . $section['name'];
                    if (strcasecmp($full_section_name, $section_name_raw) === 0) {
                        $section_id = $id;
                        break;
                    }
                }
                
                $date_of_birth = null;
                if (!empty($date_of_birth_raw)) {
                    if (is_numeric($date_of_birth_raw) && $date_of_birth_raw > 1) {
                        try {
                            $date_of_birth = Date::excelToDateTimeObject($date_of_birth_raw)->format('Y-m-d');
                        } catch (\Exception $e) {
                            $date_of_birth = $date_of_birth_raw;
                        }
                    } else {
                        $date_of_birth = $date_of_birth_raw;
                    }
                }

                if (empty($first_name) || empty($last_name) || $section_id <= 0 || empty($date_of_birth)) {
                    $students_failed++;
                    $errors[] = "Row {$row_count} (Name: {$last_name}, {$first_name}): Missing or invalid data (First Name, Last Name, Section, or DOB).";
                    continue;
                }
                
                if (check_for_duplicate_student($conn, $first_name, $last_name, $middle_initial)) {
                    $students_failed++;
                    $errors[] = "Row {$row_count} (Name: {$last_name}, {$first_name}): Duplicate student found. Skipping insertion.";
                    continue;
                }
                
                $new_id = generate_student_id($conn);

                if (is_null($new_id)) {
                    $students_failed++;
                    $errors[] = "Row {$row_count} (Name: {$last_name}, {$first_name}): Could not generate unique student ID.";
                    continue;
                }
                
                $new_id_param = $new_id;
                $first_name_param = $first_name;
                $last_name_param = $last_name;
                $middle_initial_param = $middle_initial;
                $section_id_param = $section_id;
                $date_of_birth_param = $date_of_birth;

                $stmt_insert->bind_param("ssssis", $new_id_param, $first_name_param, $last_name_param, $middle_initial_param, $section_id_param, $date_of_birth_param);

                if ($stmt_insert->execute()) {
                    $students_added++;
                } else {
                    $students_failed++;
                    $errors[] = "Row {$row_count} (Name: {$last_name}, {$first_name}): Database insertion failed. " . $stmt_insert->error;
                }
            }
            $stmt_insert->close();
        } else {
            $_SESSION['add_error_details'] = "ERROR: Could not prepare the bulk insert statement. " . $conn->error;
        }
    } catch (\Exception $e) {
        $_SESSION['add_error_details'] = "ERROR: File processing failed. Ensure the file is a valid CSV or Excel format. Details: " . $e->getMessage();
    }

    if ($students_added > 0 || $students_failed > 0) {
        $_SESSION['bulk_success_details'] = [
            'added' => $students_added,
            'failed' => $students_failed,
            'errors' => $errors
        ];
    }

    if (!isset($_SESSION['add_error_details']) && $students_added === 0 && $students_failed === 0) {
        $_SESSION['add_error_details'] = "ERROR: The uploaded file contained no valid student data to process.";
    }
}

function delete_existing_student($conn, $student_id) {
    
    $sql_select = "SELECT s.first_name, s.last_name, s.middle_initial, sec.year, sec.name as section_name, sec.teacher FROM students s JOIN sections sec ON s.section_id = sec.id WHERE s.id = ?";
    $deleted_details = null;

    if ($stmt_select = $conn->prepare($sql_select)) {
        $stmt_select->bind_param("s", $student_id); 
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        $deleted_details = $result_select->fetch_assoc();
        $stmt_select->close();
    }

    if ($deleted_details) {
        $sql_delete = "DELETE FROM students WHERE id = ?";
        if ($stmt_delete = $conn->prepare($sql_delete)) {
            $stmt_delete->bind_param("s", $student_id); 
            if ($stmt_delete->execute()) {
                
                $mi = $deleted_details['middle_initial'];
                $full_name_display = $deleted_details['last_name'] . ', ' . $deleted_details['first_name'] . ($mi ? ' ' . $mi . '.' : '');

                $_SESSION['delete_success_details'] = [
                    'name' => $full_name_display,
                    'section_name' => $deleted_details['section_name'],
                    'section_year' => $deleted_details['year'],
                    'teacher_name' => $deleted_details['teacher']
                ];
            } else {
                $_SESSION['add_error_details'] = "ERROR: Could not delete student. " . $stmt_delete->error;
            }
            $stmt_delete->close();
        } else {
            $_SESSION['add_error_details'] = "ERROR: Could not prepare delete statement. " . $conn->error;
        }
    } else {
        $_SESSION['add_error_details'] = "ERROR: Student not found for deletion.";
    }
}

function fetch_student_for_edit($conn, $student_id) {
    
    $sql_fetch_student = "SELECT id, first_name, last_name, middle_initial, section_id, date_of_birth FROM students WHERE id = ?";
    
    if ($stmt_fetch = $conn->prepare($sql_fetch_student)) {
        $stmt_fetch->bind_param("s", $student_id);
        if ($stmt_fetch->execute()) {
            $result_fetch = $stmt_fetch->get_result();
            $student_data = $result_fetch->fetch_assoc();
            $stmt_fetch->close();
            
            if ($student_data) {
                $_SESSION['student_to_edit'] = $student_data;
            } else {
                $_SESSION['add_error_details'] = "ERROR: Student not found for editing.";
            }
        } else {
            $_SESSION['add_error_details'] = "ERROR: Could not fetch student data for editing. " . $stmt_fetch->error;
        }
    } else {
        $_SESSION['add_error_details'] = "ERROR: Could not prepare fetch statement for editing. " . $conn->error;
    }
}

function update_existing_student($conn, $sections_list, $post_data) {

    $student_id = trim($post_data['student_id'] ?? ''); 
    $first_name = trim($post_data['first_name'] ?? '');
    $last_name = trim($post_data['last_name'] ?? '');
    $middle_initial_raw = trim($post_data['middle_initial'] ?? '');
    $section_id = (int)($post_data['section_id'] ?? 0);
    $date_of_birth = trim($post_data['date_of_birth'] ?? '');

    if (empty($student_id) || empty($first_name) || empty($last_name) || $section_id <= 0 || empty($date_of_birth)) {
        $_SESSION['add_error_details'] = "All required student fields and ID are required for update.";
        return;
    }
    
    $middle_initial = empty($middle_initial_raw) ? null : strtoupper(substr($middle_initial_raw, 0, 1));
    
    $sql = "UPDATE students SET first_name = ?, last_name = ?, middle_initial = ?, section_id = ?, date_of_birth = ? WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssis", $param_first_name, $param_last_name, $param_middle_initial, $param_section_id, $param_dob, $param_id);
        
        $param_first_name = $first_name;
        $param_last_name = $last_name;
        $param_middle_initial = $middle_initial;
        $param_section_id = $section_id;
        $param_dob = $date_of_birth;
        $param_id = $student_id;
        
        if ($stmt->execute()) {
            $section_info = $sections_list[$section_id] ?? ['name' => 'N/A', 'year' => 'N/A', 'teacher' => 'N/A'];
            
            $full_name_display = $last_name . ', ' . $first_name . ($middle_initial ? ' ' . $middle_initial . '.' : '');

            $_SESSION['edit_success_details'] = [
                'name' => $full_name_display,
                'section_name' => $section_info['name'],
                'section_year' => $section_info['year'],
                'teacher_name' => $section_info['teacher']
            ];
        } else {
            $_SESSION['add_error_details'] = "ERROR: Could not execute the update statement. " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['add_error_details'] = "ERROR: Could not prepare the update statement. " . $conn->error;
    }
}



?>