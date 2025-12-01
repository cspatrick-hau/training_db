<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Employees</title>
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

        .action-buttons {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .add-btn {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .add-btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
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

        button.edit-btn {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-left: 5px;
        }

        button.edit-btn:hover {
            background-color: #0056b3;
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            animation: slideDown 0.3s;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h3 {
            margin: 0;
            color: #333;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close:hover,
        .close:focus {
            color: #000;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.3);
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn-submit {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }

        .btn-cancel {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Employees</h2>

    <div class="action-buttons">
        <button class="add-btn" onclick="openAddModal()">+ Add Employee</button>
    </div>

    <table id="employeesTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Salary</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>

    <a href="logout.php" class="back-btn">Logout</a>
</div>


<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h3 id="modalTitle">Add New Employee</h3>
        </div>
        <form id="addEmployeeForm">
            <input type="hidden" id="employeeIdField" name="emp_id" value="">
            <input type="hidden" id="formAction" name="action" value="add_employee">
            
            <div class="form-group">
                <label for="emp_name">Employee Name:</label>
                <input type="text" id="emp_name" name="emp_name" 
                       pattern="[A-Za-z\s]+" 
                       title="Only letters and spaces allowed"
                       minlength="2"
                       maxlength="100"
                       required>
            </div>
            <div class="form-group">
                <label for="dept_id">Department:</label>
                <select id="dept_id" name="dept_id" required>
                    <option value="">Select Department</option>
                </select>
            </div>
            <div class="form-group">
                <label for="salary">Salary:</label>
                <input type="number" id="salary" name="salary" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="is_active">Active Status:</label>
                <select id="is_active" name="is_active" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-submit" id="addEditButton">Add Employee</button>
            </div>
        </form>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        // Load employees when page loads
        fetchEmployees();

        // Handle Add/Edit Employee Form Submission
        $('#addEmployeeForm').submit(function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            
            $.ajax({
                url: 'employee_api.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        closeAddModal();
                        fetchEmployees(); // Refresh table!
                        alert(data.message || 'Employee processed successfully!');
                    } else {
                        alert(data.message || 'Failed to process employee.');
                    }
                },
                error: function(err) {
                    console.error('Error processing employee:', err);
                    alert('Error processing employee');
                }
            });
        });

        // Close modal when clicking outside
        $(window).click(function(event) {
            if ($(event.target).is('#addModal')) {
                closeAddModal();
            }
        });
    });

    // Fetch and display employees
    function fetchEmployees() {

        // Show loading message
        var tbody = $('#employeesTable tbody');
        tbody.html('<tr><td colspan="6" style="text-align:center;">⏳ Loading employees...</td></tr>');
        
        $.ajax({
            url: 'employee_api.php?action=fetch_employees',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var tbody = $('#employeesTable tbody');
                tbody.empty();

                if (!Array.isArray(data) || data.length === 0) {
                    tbody.html('<tr><td colspan="6" style="text-align:center;">No employees found</td></tr>');
                    return;
                }

                $.each(data, function(index, emp) {
                    var row = '<tr>' +
                        '<td>' + emp.emp_id + '</td>' +
                        '<td>' + emp.emp_name + '</td>' +
                        '<td>' + (emp.dept_name ? emp.dept_name : '') + '</td>' +
                        '<td>' + emp.salary + '</td>' +
                        '<td>' + (emp.is_active == 1 ? 'Yes' : 'No') + '</td>' +
                        '<td>' +
                            '<button class="edit-btn" onclick="editEmployee(' + emp.emp_id + ')">Edit</button> ' +
                            '<button class="delete-btn" onclick="deleteEmployee(' + emp.emp_id + ')">Delete</button>' +
                        '</td>' +
                        '</tr>';
                    tbody.append(row);
                });
            },
            error: function(err) {
                console.error('Error fetching employees:', err);
            }
        });
    }

    // Fetch departments for dropdown
    function fetchDepartments() {
        $.ajax({
            url: 'employee_api.php?action=fetch_departments',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var deptSelect = $('#dept_id');
                deptSelect.html('<option value="">Select Department</option>');
                
                $.each(data, function(index, dept) {
                    deptSelect.append('<option value="' + dept.dept_id + '">' + dept.dept_name + '</option>');
                });
            },
            error: function(err) {
                console.error('Error fetching departments:', err);
            }
        });
    }

    // Open Add Modal
    function openAddModal() {
        $('#modalTitle').text('Add New Employee');
        $('#addEditButton').text('Add Employee');
        $('#formAction').val('add_employee');
        $('#employeeIdField').val('');
        $('#addEmployeeForm')[0].reset();
        fetchDepartments();
        $('#addModal').show();
    }

    // Close Modal
    function closeAddModal() {
        $('#addModal').hide();
        $('#addEmployeeForm')[0].reset();
        $('#formAction').val('add_employee');
        $('#employeeIdField').val('');
    }

    // Delete employee
    function deleteEmployee(empId) {
        if (!confirm("Are you sure you want to delete this employee?")) return;

        $.ajax({
            url: 'employee_api.php',
            type: 'POST',
            data: { 
                action: 'delete_employee', 
                delete_id: empId 
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    fetchEmployees(); // Refresh table!
                    alert(data.message || 'Employee deleted successfully!');
                } else {
                    alert(data.message || 'Failed to delete employee.');
                }
            },
            error: function(err) {
                console.error('Error deleting employee:', err);
            }
        });
    }

    // Edit Employee - Opens modal with employee data
    function editEmployee(empId) {
        $.ajax({
            url: 'employee_api.php?action=fetch_single_employee&emp_id=' + empId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data && data.emp_id) { 
                    // Set modal for Editing
                    $('#modalTitle').text('Edit Employee');
                    $('#addEditButton').text('Update Employee');
                    $('#formAction').val('edit_employee');
                    
                    // Populate fields
                    $('#employeeIdField').val(data.emp_id);
                    $('#emp_name').val(data.emp_name);
                    $('#salary').val(parseFloat(data.salary).toFixed(2));
                    $('#is_active').val(data.is_active);

                    // Load Departments, then set the department ID
                    $.ajax({
                        url: 'employee_api.php?action=fetch_departments',
                        type: 'GET',
                        dataType: 'json',
                        success: function(deptData) {
                            var deptSelect = $('#dept_id');
                            deptSelect.html('<option value="">Select Department</option>');
                            
                            $.each(deptData, function(index, dept) {
                                deptSelect.append('<option value="' + dept.dept_id + '">' + dept.dept_name + '</option>');
                            });
                            
                            // Set the employee's department
                            $('#dept_id').val(data.dept_id);
                            
                            // Show the modal
                            $('#addModal').show();
                        }
                    });

                } else {
                    alert('Employee not found or invalid data returned.');
                }
            },
            error: function(err) {
                console.error('Error fetching employee data:', err);
                alert('Could not load employee details for editing.');
            }
        });
    }
</script>

</body>
</html>