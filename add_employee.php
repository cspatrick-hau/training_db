<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

include("mysqlConnection.php");

// Handle AJAX form submission
if (isset($_POST['emp_name'])) {
    $emp_name  = $_POST['emp_name'];
    $dept_id   = $_POST['dept_id'];
    $salary    = $_POST['salary'];
    $is_active = $_POST['is_active'];

    $sql = "INSERT INTO employees (emp_name, dept_id, salary, is_active)
            VALUES ('$emp_name', '$dept_id', '$salary', '$is_active')";

    $result = mysqli_query($connection, $sql);
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Employee added successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: '.mysqli_error($connection)]);
    }
    exit();
}

// Fetch all departments
$dept_query = "SELECT * FROM departments";
$dept_result = mysqli_query($connection, $dept_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Employee (AJAX)</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
        }

        .container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            width: 400px;
            text-align: center;
        }

        h2 { margin-bottom: 25px; color: #333; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input, select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; box-sizing: border-box; }
        button.submit-btn { width: 100%; padding: 10px; background-color: #28a745; border: none; border-radius: 5px; color: white; font-size: 16px; cursor: pointer; }
        button.submit-btn:hover { background-color: #218838; }

        .back-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover { background-color: #5a6268; }

        .message {
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add Employee</h2>

    <div class="message" id="message"></div>

    <form id="addEmployeeForm">
        <label>Employee Name:</label>
        <input type="text" name="emp_name" required>

        <label>Department:</label>
        <select name="dept_id" required>
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
        <input type="number" name="salary" step="0.01" required>

        <label>Is Active?</label>
        <select name="is_active">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>

        <button type="submit" class="submit-btn">Save Employee</button>
    </form>

    <a href="dashboard.php" class="back-btn">← Back</a>
</div>

<script>
document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
    e.preventDefault(); // prevent form from submitting normally

    const formData = new FormData(this);

    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('message');
        if (data.success) {
            messageDiv.style.color = 'green';
            messageDiv.textContent = data.message;
            this.reset(); // clear the form
        } else {
            messageDiv.style.color = 'red';
            messageDiv.textContent = data.message;
        }
    })
    .catch(err => console.error('Error:', err));
});
</script>

</body>
</html>
