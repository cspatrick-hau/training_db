<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    http_response_code(401);
    if (isset($_GET['ajax'])) {
        echo json_encode(['error' => 'Not logged in']);
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}

include("mysqlConnection.php");

// Handle AJAX request to get employees
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $sql = "SELECT e.emp_id, e.emp_name, d.dept_name, e.salary, e.is_active
            FROM employees e
            LEFT JOIN departments d ON e.dept_id = d.dept_id
            ORDER BY e.emp_id ASC";
    $result = mysqli_query($connection, $sql);

    $employees = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($employees);
    exit();
}

// Handle AJAX request to delete an employee
if (isset($_POST['delete_id'])) {
    $emp_id = intval($_POST['delete_id']);
    $delete_sql = "DELETE FROM employees WHERE emp_id=$emp_id";
    $success = mysqli_query($connection, $delete_sql);
    echo json_encode(['success' => $success]);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Employees (AJAX Single File)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            max-width: 900px;
            margin: auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        button.delete-btn {
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button.delete-btn:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .back-btn:hover {
            background-color: #5a6268;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Employees</h2>

    <table id="employeesTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Salary</th>
                <th>Active</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- AJAX data will populate here -->
        </tbody>
    </table>

    <!-- Back to Dashboard Button -->
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

<script>
    // Fetch and display employees
    function fetchEmployees() {
        fetch('?ajax=1')
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#employeesTable tbody');
                tbody.innerHTML = '';

                if (!Array.isArray(data) || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No employees found</td></tr>';
                    return;
                }

                data.forEach(emp => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${emp.emp_id}</td>
                        <td>${emp.emp_name}</td>
                        <td>${emp.dept_name ? emp.dept_name : ''}</td>
                        <td>${emp.salary}</td>
                        <td>${emp.is_active == 1 ? 'Yes' : 'No'}</td>
                        <td><button class="delete-btn" onclick="deleteEmployee(${emp.emp_id})">Delete</button></td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(err => console.error('Error fetching employees:', err));
    }

    // Delete employee via AJAX
    function deleteEmployee(empId) {
        if (!confirm("Are you sure you want to delete this employee?")) return;

        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'delete_id=' + empId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchEmployees(); // refresh table after deletion
            } else {
                alert('Failed to delete employee.');
            }
        })
        .catch(err => console.error('Error deleting employee:', err));
    }

    // Load employees on page load
    window.onload = fetchEmployees;
</script>

</body>
</html>
