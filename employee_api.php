<?php
session_start();

$isLoginRequest = (isset($_POST['action']) && $_POST['action'] == 'login');

if (!$isLoginRequest && !isset($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

include("mysqlConnection.php"); 
header('Content-Type: application/json');

// --- LOGIN ---
if ($isLoginRequest) {
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $password = mysqli_real_escape_string($connection, $_POST['password']);

    // Only letters allowed
    if (!preg_match('/^[a-zA-Z]+$/', $username)) {
        echo json_encode(['success' => false, 'message' => 'Username must contain only letters']);
        exit();
    }

    $result = mysqli_query($connection, "CALL sp_login_user('$username', '$password')");

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: '.mysqli_error($connection)]);
        exit();
    }

    $row = mysqli_fetch_assoc($result);

    if ($row['success'] == 1) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
    }

    echo json_encode($row);

    mysqli_free_result($result);
    mysqli_next_result($connection);
    exit();
}

// --- FETCH EMPLOYEES ---
if (isset($_GET['action']) && $_GET['action'] == 'fetch_employees') {
    // Clear any previous results first
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    
    $result = mysqli_query($connection, "CALL sp_fetch_employees()");
    
    if (!$result) {
        echo json_encode(['error' => 'Query failed: ' . mysqli_error($connection)]);
        exit();
    }
    
    $employees = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    
    echo json_encode($employees);
    
    if ($result) {
        mysqli_free_result($result);
    }
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    exit();
}

// --- FETCH DEPARTMENTS ---
if (isset($_GET['action']) && $_GET['action'] == 'fetch_departments') {
    // Clear any previous results first
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    
    $result = mysqli_query($connection, "CALL sp_fetch_departments()");
    
    if (!$result) {
        echo json_encode(['error' => 'Query failed: ' . mysqli_error($connection)]);
        exit();
    }
    
    $departments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = $row;
    }
    
    echo json_encode($departments);
    
    if ($result) {
        mysqli_free_result($result);
    }
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    exit();
}

// --- FETCH SINGLE EMPLOYEE ---
if (isset($_GET['action']) && $_GET['action'] == 'fetch_single_employee' && isset($_GET['emp_id'])) {
    $emp_id = intval($_GET['emp_id']);
    
    // Clear any previous results first
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    
    $result = mysqli_query($connection, "CALL sp_fetch_single_employee($emp_id)");
    
    if (!$result) {
        http_response_code(500); 
        echo json_encode(['error' => 'Database query failed: ' . mysqli_error($connection)]);
        exit();
    }
    
    $employee = mysqli_fetch_assoc($result);
    
    echo json_encode($employee ? $employee : []);
    
    if ($result) {
        mysqli_free_result($result);
    }
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    exit(); 
}

// --- ADD EMPLOYEE ---
if (isset($_POST['action']) && $_POST['action'] == 'add_employee') {
    // Clear any previous results first
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    
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
    if ($dept_result) {
        mysqli_free_result($dept_result);
    }
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    
    $department = mysqli_real_escape_string($connection, $department);

    $result = mysqli_query($connection, "CALL AddEmployee('$emp_name', '$department', $salary, $dept_id, $is_active)");
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode(['success' => (bool)$row['success'], 'message' => $row['message']]);
        mysqli_free_result($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: '.mysqli_error($connection)]);
    }
    
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    exit();
}

// --- EDIT EMPLOYEE ---
if (isset($_POST['action']) && $_POST['action'] == 'edit_employee' && isset($_POST['emp_id'])) {
    // Clear any previous results first
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    
    $emp_id = intval($_POST['emp_id']);
    $emp_name = mysqli_real_escape_string($connection, $_POST['emp_name']);
    $dept_id = intval($_POST['dept_id']);
    $salary = floatval($_POST['salary']);
    $is_active = intval($_POST['is_active']);

    $result = mysqli_query($connection, "CALL sp_edit_employee($emp_id, '$emp_name', $dept_id, $salary, $is_active)");
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode(['success' => (bool)$row['success'], 'message' => $row['message']]);
        mysqli_free_result($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: '.mysqli_error($connection)]);
    }
    
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    exit();
}

// --- DELETE EMPLOYEE ---
if (isset($_POST['action']) && $_POST['action'] == 'delete_employee') {
    // Clear any previous results first
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    
    $emp_id = intval($_POST['delete_id']);
    
    $result = mysqli_query($connection, "CALL delete_employee_sp($emp_id)");
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode(['success' => (bool)$row['success'], 'message' => $row['message']]);
        mysqli_free_result($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: '.mysqli_error($connection)]);
    }
    
    while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
        if ($res = mysqli_store_result($connection)) {
            mysqli_free_result($res);
        }
    }
    exit();
}

// --- SET LEAVE DATE ---
if (isset($_POST['action']) && $_POST['action'] == 'set_leave_date') {
    if (isset($_POST['emp_id'], $_POST['leave_date'])) {
        $empId = intval($_POST['emp_id']);
        $leaveDate = mysqli_real_escape_string($connection, $_POST['leave_date']);
        
        // Validate date format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $leaveDate)) {
            echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD.']);
            exit();
        }
        
        $result = mysqli_query($connection, "CALL sp_set_employee_leave_date($empId, '$leaveDate')");
        
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $success = ($row['success_code'] == 1);
            
            $message = isset($row['message']) ? $row['message'] : ($success ? 'Leave date updated successfully.' : 'Failed to update leave date.');
            
            echo json_encode([
                'success' => $success, 
                'message' => $message
            ]);
            
            mysqli_free_result($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: '.mysqli_error($connection)]);
        }
        
        // Clear any remaining results
        while (mysqli_more_results($connection) && mysqli_next_result($connection)) {
            if ($res = mysqli_store_result($connection)) {
                mysqli_free_result($res);
            }
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing employee ID or leave date.']);
    }
    exit();
}

// --- FALLBACK FOR INVALID REQUEST ---
http_response_code(400);
echo json_encode(['error' => 'Invalid API request']);
?>