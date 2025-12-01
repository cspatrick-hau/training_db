<?php
// Start session and check authentication
session_start();
if (!isset($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

include("mysqlConnection.php"); 

header('Content-Type: application/json');

// --- API Endpoints ---

if (isset($_GET['action']) && $_GET['action'] == 'fetch_employees') {
    $result = mysqli_query($connection, "CALL sp_fetch_employees()");
    
    $employees = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    
    echo json_encode($employees);
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'fetch_departments') {
    $result = mysqli_query($connection, "CALL sp_fetch_departments()");
    
    $departments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = $row;
    }
    
    echo json_encode($departments);
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'fetch_single_employee' && isset($_GET['emp_id'])) {
    $emp_id = intval($_GET['emp_id']);
    
    $result = mysqli_query($connection, "CALL sp_fetch_single_employee($emp_id)");
    
    if (!$result) {
        http_response_code(500); 
        echo json_encode(['error' => 'Database query failed']);
        exit();
    }
    
    $employee = mysqli_fetch_assoc($result);
    
    echo json_encode($employee ? $employee : []);
    exit(); 
}

if (isset($_POST['action']) && $_POST['action'] == 'add_employee') {
    $emp_name = mysqli_real_escape_string($connection, $_POST['emp_name']);
    $dept_id = intval($_POST['dept_id']);
    $salary = floatval($_POST['salary']);
    $is_active = intval($_POST['is_active']);

    // Get department name
    $dept_result = mysqli_query($connection, "CALL sp_fetch_departments()");
    $department = '';
    while ($dept_row = mysqli_fetch_assoc($dept_result)) {
        if ($dept_row['dept_id'] == $dept_id) {
            $department = $dept_row['dept_name'];
            break;
        }
    }
    mysqli_free_result($dept_result);
    mysqli_next_result($connection); // Clear result set
    
    $department = mysqli_real_escape_string($connection, $department);

    $result = mysqli_query($connection, "CALL AddEmployee('$emp_name', '$department', $salary, $dept_id, $is_active)");
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode(['success' => (bool)$row['success'], 'message' => $row['message']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: '.mysqli_error($connection)]);
    }
    exit();
}

if (isset($_POST['action']) && $_POST['action'] == 'edit_employee' && isset($_POST['emp_id'])) {
    $emp_id = intval($_POST['emp_id']);
    $emp_name = mysqli_real_escape_string($connection, $_POST['emp_name']);
    $dept_id = intval($_POST['dept_id']);
    $salary = floatval($_POST['salary']);
    $is_active = intval($_POST['is_active']);

    $result = mysqli_query($connection, "CALL sp_edit_employee($emp_id, '$emp_name', $dept_id, $salary, $is_active)");
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode(['success' => (bool)$row['success'], 'message' => $row['message']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: '.mysqli_error($connection)]);
    }
    exit();
}

if (isset($_POST['action']) && $_POST['action'] == 'delete_employee') {
    $emp_id = intval($_POST['delete_id']);
    
    $result = mysqli_query($connection, "CALL delete_employee_sp($emp_id)");
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode(['success' => (bool)$row['success'], 'message' => $row['message']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: '.mysqli_error($connection)]);
    }
    exit();
}

// Fallback for invalid request
http_response_code(400);
echo json_encode(['error' => 'Invalid API request']);
?>