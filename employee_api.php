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
    $sql = "SELECT e.emp_id, e.emp_name, d.dept_name, e.dept_id, e.salary, e.is_active
            FROM employees e
            LEFT JOIN departments d ON e.dept_id = d.dept_id
            ORDER BY e.emp_id ASC";
    $result = mysqli_query($connection, $sql);

    $employees = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    
    echo json_encode($employees);
    exit();
}


if (isset($_GET['action']) && $_GET['action'] == 'fetch_departments') {
    $sql = "SELECT dept_id, dept_name FROM departments ORDER BY dept_name ASC";
    $result = mysqli_query($connection, $sql);
    
    $departments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = $row;
    }
    
    echo json_encode($departments);
    exit();
}


if (isset($_GET['action']) && $_GET['action'] == 'fetch_single_employee' && isset($_GET['emp_id'])) {
    $emp_id = intval($_GET['emp_id']);
    
    $sql = "SELECT emp_id, emp_name, dept_id, salary, is_active 
            FROM employees 
            WHERE emp_id = $emp_id";
    
    $result = mysqli_query($connection, $sql);
    
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
    
    // CHECK IF EMPLOYEE ALREADY EXISTS
    $check_sql = "SELECT emp_id FROM employees WHERE emp_name = '$emp_name'";
    $check_result = mysqli_query($connection, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Employee with this name already exists!']);
        exit();
    }
    

    $dept_result = mysqli_query($connection, "SELECT dept_name FROM departments WHERE dept_id = $dept_id");
    $dept_row = mysqli_fetch_assoc($dept_result);
    $department = isset($dept_row['dept_name']) ? mysqli_real_escape_string($connection, $dept_row['dept_name']) : '';


    $sql = "CALL AddEmployee('$emp_name', '$department', $salary, $dept_id)";
    $result = mysqli_query($connection, $sql);
    
    if ($result) {
        $last_id = mysqli_insert_id($connection);
        if ($is_active != 1) {
            mysqli_query($connection, "UPDATE employees SET is_active = $is_active WHERE emp_id = $last_id");
        }
        echo json_encode(['success' => true, 'message' => 'Employee added successfully!']);
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
    
    // CHECK IF EMPLOYEE NAME ALREADY EXISTS (excluding current employee)
    $check_sql = "SELECT emp_id FROM employees WHERE emp_name = '$emp_name' AND emp_id != $emp_id";
    $check_result = mysqli_query($connection, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Another employee with this name already exists!']);
        exit();
    }
    
    $sql = "CALL sp_edit_employee($emp_id, '$emp_name', $dept_id, $salary, $is_active)";
    $result = mysqli_query($connection, $sql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode(['success' => $row['success'], 'message' => $row['message']]);
    } else {

        mysqli_query($connection, "UPDATE employees SET emp_name='$emp_name', dept_id=$dept_id, salary=$salary, is_active=$is_active WHERE emp_id=$emp_id");
        
        if (mysqli_affected_rows($connection) > 0 || mysqli_errno($connection) == 0) {
            echo json_encode(['success' => true, 'message' => 'Employee updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: '.mysqli_error($connection)]);
        }
    }
    exit();
}

if (isset($_POST['action']) && $_POST['action'] == 'delete_employee') {
    $emp_id = intval($_POST['delete_id']);
    
    $delete_sql = "DELETE FROM employees WHERE emp_id=$emp_id";
    $success = mysqli_query($connection, $delete_sql);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Employee deleted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: '.mysqli_error($connection)]);
    }
    exit();
}

// Fallback for invalid request
http_response_code(400);
echo json_encode(['error' => 'Invalid API request']);

?>
