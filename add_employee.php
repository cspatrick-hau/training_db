<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

include("mysqlConnection.php");

if (isset($_GET['get_employee']) && isset($_GET['emp_id'])) {
    $emp_id = intval($_GET['emp_id']);
    $sql = "SELECT * FROM employees WHERE emp_id = $emp_id";
    $result = mysqli_query($connection, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $employee = mysqli_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'employee' => $employee]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
    }
    exit();
}

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $dept_query = "SELECT dept_id, dept_name FROM departments ORDER BY dept_name";
    $dept_result = mysqli_query($connection, $dept_query);
    
    $departments = [];
    while ($row = mysqli_fetch_assoc($dept_result)) {
        $departments[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['departments' => $departments]);
    exit();
}

// Handle EDIT employee
if (isset($_POST['edit_employee']) && isset($_POST['emp_id'])) {
    $emp_id = intval($_POST['emp_id']);
    $emp_name = mysqli_real_escape_string($connection, $_POST['emp_name']);
    $dept_id = intval($_POST['dept_id']);
    $salary = floatval($_POST['salary']);
    $is_active = intval($_POST['is_active']);
    
    // CHECK IF EMPLOYEE NAME ALREADY EXISTS (excluding current employee)
    $check_sql = "SELECT emp_id FROM employees WHERE emp_name = '$emp_name' AND emp_id != $emp_id";
    $check_result = mysqli_query($connection, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Another employee with this name already exists!']);
        exit();
    }

    $sql = "CALL sp_edit_employee($emp_id, '$emp_name', $dept_id, $salary, $is_active)";
    $result = mysqli_query($connection, $sql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode(['success' => $row['success'], 'message' => $row['message']]);
    } else {

        mysqli_query($connection, "UPDATE employees SET emp_name='$emp_name', dept_id=$dept_id, salary=$salary, is_active=$is_active WHERE emp_id=$emp_id");
        
        header('Content-Type: application/json');
        if (mysqli_affected_rows($connection) > 0) {
            echo json_encode(['success' => true, 'message' => 'Employee updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: '.mysqli_error($connection)]);
        }
    }
    exit();
}


if (isset($_POST['emp_name']) && !isset($_POST['edit_employee'])) {
    $emp_name  = mysqli_real_escape_string($connection, $_POST['emp_name']);
    $dept_id   = intval($_POST['dept_id']);
    $salary    = floatval($_POST['salary']);
    $is_active = intval($_POST['is_active']);

    // CHECK IF EMPLOYEE ALREADY EXISTS
    $check_sql = "SELECT emp_id FROM employees WHERE emp_name = '$emp_name'";
    $check_result = mysqli_query($connection, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        header('Content-Type: application/json');
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
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Employee added successfully!']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: '.mysqli_error($connection)]);
    }
    exit();
}


$edit_mode = isset($_GET['edit']) ? true : false;
$edit_emp_id = $edit_mode ? intval($_GET['edit']) : 0;


$dept_query = "SELECT * FROM departments";
$dept_result = mysqli_query($connection, $dept_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $edit_mode ? 'Edit Employee' : 'Add Employee'; ?></title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; font-family: Arial, sans-serif; background-color: #f0f2f5; }
        .container { background-color: #fff; padding: 30px 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); width: 400px; text-align: center; }
        h2 { margin-bottom: 25px; color: #333; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; text-align: left; }
        input, select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; box-sizing: border-box; }
        button.submit-btn { width: 100%; padding: 10px; background-color: #28a745; border: none; border-radius: 5px; color: white; font-size: 16px; cursor: pointer; }
        button.submit-btn:hover { background-color: #218838; }
        button.submit-btn.edit-mode { background-color: #007bff; }
        button.submit-btn.edit-mode:hover { background-color: #0056b3; }
        .back-btn { display: inline-block; margin-top: 15px; padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px; }
        .back-btn:hover { background-color: #5a6268; }
        .message { margin-bottom: 15px; font-weight: bold; }
        .loading { display: none; margin-bottom: 15px; color: #007bff; }
    </style>
</head>
<body>

<div class="container">
    <h2><?php echo $edit_mode ? 'Edit Employee' : 'Add Employee'; ?></h2>

    <div class="loading" id="loading">Loading employee data...</div>
    <div class="message" id="message"></div>

    <form id="addEmployeeForm">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="emp_id" id="emp_id" value="<?php echo $edit_emp_id; ?>">
            <input type="hidden" name="edit_employee" value="1">
        <?php endif; ?>

        <label>Employee Name:</label>
        <input 
            type="text" 
            name="emp_name"
            id="emp_name"
            pattern="[A-Za-z\s]+" 
            title="Only letters and spaces allowed"
            minlength="2"
            maxlength="100"
            required
        >

        <label>Department:</label>
        <select name="dept_id" id="dept_id" required>
            <option value="">--Select Department--</option>
            <?php
            if (mysqli_num_rows($dept_result) > 0) {
                while ($row = mysqli_fetch_assoc($dept_result)) {
                    echo "<option value='{$row['dept_id']}'>{$row['dept_name']}</option>";
                }
            } else {
                echo "<option value=''>No departments available</option>";
            }
            ?>
        </select>

        <label>Salary:</label>
        <input type="number" name="salary" id="salary" step="0.01" required>

        <label>Is Active?</label>
        <select name="is_active" id="is_active">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>

        <button type="submit" class="submit-btn <?php echo $edit_mode ? 'edit-mode' : ''; ?>">
            <?php echo $edit_mode ? 'Update Employee' : 'Save Employee'; ?>
        </button>
    </form>

    <a href="view_employees.php" class="back-btn">← Back</a>
</div>

<script>
    $(document).ready(function() {
        var editMode = <?php echo $edit_mode ? 'true' : 'false'; ?>;
        var empId = <?php echo $edit_emp_id; ?>;

        if (editMode && empId > 0) {
            $('#loading').show();
            
            $.ajax({
                url: '?get_employee=1&emp_id=' + empId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#loading').hide();
                    
                    if (data.success) {
                        var emp = data.employee;
                        $('#emp_name').val(emp.emp_name);
                        $('#dept_id').val(emp.dept_id);
                        $('#salary').val(emp.salary);
                        $('#is_active').val(emp.is_active);
                    } else {
                        $('#message').css('color', 'red').text(data.message);
                    }
                },
                error: function(err) {
                    $('#loading').hide();
                    $('#message').css('color', 'red').text('Error loading employee data');
                    console.error('Error:', err);
                }
            });
        }

        $('#addEmployeeForm').submit(function(e) {
            e.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: '',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(data) {
                    var messageDiv = $('#message');
                    if (data.success) {
                        messageDiv.css('color', 'green').text(data.message);
                        
                        if (!editMode) {
                            $('#addEmployeeForm')[0].reset();
                        } else {
                            setTimeout(function() {
                                window.location.href = 'view_employees.php';
                            }, 1500);
                        }
                    } else {
                        messageDiv.css('color', 'red').text(data.message);
                    }
                },
                error: function(err) {
                    $('#message').css('color', 'red').text('Error processing request');
                    console.error('Error:', err);
                }
            });
        });
    });
</script>

</body>
</html>