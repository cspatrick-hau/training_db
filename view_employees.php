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
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
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

        /* jQuery Validation Error Styles */
        label.error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
            font-weight: normal;
        }

        input.error,
        select.error {
            border-color: #dc3545 !important;
            box-shadow: 0 0 5px rgba(220, 53, 69, 0.3) !important;
        }

        input.valid,
        select.valid {
            border-color: #28a745 !important;
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.3) !important;
        }

        /* Select2 Custom Styling */
        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
            padding-left: 10px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-dropdown {
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff;
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

<!-- Add/Edit Modal -->
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
                <input type="text" id="emp_name" name="emp_name">
            </div>
            <div class="form-group">
                <label for="dept_id">Department:</label>
                <select id="dept_id" name="dept_id">
                    <option value="">Select Department</option>
                </select>
            </div>
            <div class="form-group">
                <label for="salary">Salary:</label>
                <input type="number" id="salary" name="salary" step="0.01">
            </div>
            <div class="form-group">
                <label for="is_active">Active Status:</label>
                <select id="is_active" name="is_active">
                    <option value="">Select Status</option>
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

<div id="leaveDateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" onclick="closeLeaveDateModal()">&times;</span>
            <h3 id="leaveModalTitle">Set Leave Date for Employee</h3>
        </div>
        <form id="setLeaveDateForm">
            <input type="hidden" id="leaveEmployeeIdField" name="emp_id">
            <input type="hidden" name="action" value="set_leave_date">
            
            <div class="form-group">
                <label for="leave_date">Date of Leave:</label>
                <input type="text" id="leave_date" name="leave_date" placeholder="Select a date">
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeLeaveDateModal()">Cancel</button>
                <button type="submit" class="btn-submit">Set Date</button>
            </div>
        </form>
    </div>
</div>

<!-- jQuery FIRST -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- jQuery Validation Plugin -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    $(document).ready(function() {
        // Initialize jQuery Validation
        $("#addEmployeeForm").validate({
            rules: {
                emp_name: {
                    required: true,
                    minlength: 2,
                    maxlength: 100,
                    lettersonly: true
                },
                dept_id: {
                    required: true
                },
                salary: {
                    required: true,
                    number: true,
                    min: 0.01
                },
                is_active: {
                    required: true
                }
            },
            messages: {
                emp_name: {
                    required: "Please enter employee name",
                    minlength: "Name must be at least 2 characters long",
                    maxlength: "Name cannot exceed 100 characters",
                    lettersonly: "Only letters and spaces are allowed"
                },
                dept_id: {
                    required: "Please select a department"
                },
                salary: {
                    required: "Please enter salary",
                    number: "Please enter a valid number",
                    min: "Salary must be greater than 0"
                },
                is_active: {
                    required: "Please select active status"
                }
            },
            errorElement: "label",
            errorClass: "error",
            validClass: "valid",
            errorPlacement: function(error, element) {
                error.insertAfter(element);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass(errorClass).removeClass(validClass);
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass(errorClass).addClass(validClass);
            },
            submitHandler: function(form) {
                var formData = $(form).serialize();
                
                $.ajax({
                    url: 'employee_api.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            closeAddModal();
                            fetchEmployees();
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
            }
        });

        // Custom validation method for letters and spaces only
        $.validator.addMethod("lettersonly", function(value, element) {
            return this.optional(element) || /^[A-Za-z\s]+$/.test(value);
        }, "Only letters and spaces are allowed");

        // Load employees when page loads
        fetchEmployees();

        // Close modal when clicking outside
        $(window).click(function(event) {
            if ($(event.target).is('#addModal')) {
                closeAddModal();
            }
        });
    });

    // Fetch and display employees
function fetchEmployees() {
    $.ajax({
        url: 'employee_api.php?action=fetch_employees',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('Employees data received:', data); // Debug log
            
            // Destroy DataTable if it exists
            if ($.fn.DataTable.isDataTable('#employeesTable')) {
                $('#employeesTable').DataTable().destroy();
            }
            
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
                    '<td>' + parseFloat(emp.salary).toFixed(2) + '</td>' +
                    '<td>' + (emp.is_active == 1 ? 'Yes' : 'No') + '</td>' +
                    '<td>' +
                        '<button class="edit-btn" onclick="editEmployee(' + emp.emp_id + ')">Edit</button> ' +
                        '<button class="delete-btn" onclick="deleteEmployee(' + emp.emp_id + ')">Delete</button> ' +
                        '<button class="add-btn" style="background-color: #ffc107; margin-left: 5px;" onclick="openLeaveDateModal(' + emp.emp_id + ', \'' + emp.emp_name + '\')">Set Leave</button>' +
                    '</td>' +
                    '</tr>';
                tbody.append(row);
            });
            
            // Reinitialize DataTable
            $('#employeesTable').DataTable({
                pageLength: 10,
                responsive: true,
                language: {
                    emptyTable: "No employees found"
                }
            });
            
            console.log('Table populated successfully'); // Debug log
        },
        error: function(xhr, status, error) {
            console.error('Error fetching employees:', xhr.responseText);
            
            // Destroy DataTable if it exists
            if ($.fn.DataTable.isDataTable('#employeesTable')) {
                $('#employeesTable').DataTable().destroy();
            }
            
            $('#employeesTable tbody').html('<tr><td colspan="6" style="text-align:center; color: #dc3545;">❌ Error loading employees</td></tr>');
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

                // 🎉 Initialize Select2 after populating options
                $('#dept_id').select2({
                    placeholder: " Search and select department...",
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#addModal')
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
        
        // Reset validation
        var validator = $("#addEmployeeForm").validate();
        validator.resetForm();
        $('#addEmployeeForm').find('.error').removeClass('error');
        $('#addEmployeeForm').find('.valid').removeClass('valid');
        
        fetchDepartments();
        $('#addModal').show();
    }

    // Close Modal
    function closeAddModal() {
        $('#addModal').hide();
        $('#addEmployeeForm')[0].reset();
        $('#formAction').val('add_employee');
        $('#employeeIdField').val('');
        
        // Reset validation
        var validator = $("#addEmployeeForm").validate();
        validator.resetForm();
        $('#addEmployeeForm').find('.error').removeClass('error');
        $('#addEmployeeForm').find('.valid').removeClass('valid');

        // 🎉 Destroy Select2 when closing modal
        if ($('#dept_id').hasClass("select2-hidden-accessible")) {
            $('#dept_id').select2('destroy');
        }
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
                    fetchEmployees();
                    alert(data.message || 'Employee deleted successfully!');
                } else {
                    alert(data.message || 'Failed to delete employee.');
                }
            },
            error: function(err) {
                console.error('Error deleting employee:', err);
                alert('Error deleting employee');
            }
        });
    }

    function testSP() {
    $.ajax({
        url: 'employee_api.php?action=fetch_employees',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('Success! Data received:', data);
            alert('SP Works! Check console for data');
        },
        error: function(xhr, status, error) {
            console.error('Error Response:', xhr.responseText);
            console.error('Status:', status);
            console.error('Error:', error);
            alert('Error: ' + xhr.responseText);
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
                    $('#modalTitle').text('Edit Employee');
                    $('#addEditButton').text('Update Employee');
                    $('#formAction').val('edit_employee');
                    
                    $('#employeeIdField').val(data.emp_id);
                    $('#emp_name').val(data.emp_name);
                    $('#salary').val(parseFloat(data.salary).toFixed(2));
                    $('#is_active').val(data.is_active);

                    // Reset validation before editing
                    var validator = $("#addEmployeeForm").validate();
                    validator.resetForm();
                    $('#addEmployeeForm').find('.error').removeClass('error');
                    $('#addEmployeeForm').find('.valid').removeClass('valid');

                    // Load Departments
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
                            
                            // 🎉 Initialize Select2
                            $('#dept_id').select2({
                                placeholder: "🔍 Search and select department...",
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('#addModal')
                            });

                            // Set the employee's department AFTER Select2 initialization
                            $('#dept_id').val(data.dept_id).trigger('change');
                            
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

    // Global variable to hold the Flatpickr instance for the leave date
    var leaveFlatpickr;

    // Open Leave Date Modal
    function openLeaveDateModal(empId, empName) {
        $('#leaveEmployeeIdField').val(empId);
        $('#leaveModalTitle').text('Set Leave Date for ' + empName);
        
        // Initialize Flatpickr if it hasn't been already
        if (!leaveFlatpickr) {
            leaveFlatpickr = flatpickr("#leave_date", {
                dateFormat: "Y-m-d", // MySQL date format
                minDate: "today",     // Cannot select dates in the past
                defaultDate: "today"
            });
        }
        
        // Reset the form and clear previous date
        $('#setLeaveDateForm')[0].reset();
        leaveFlatpickr.clear(); 
        
        // Reset validation (if you add validation to this form later)
        // var validator = $("#setLeaveDateForm").validate();
        // validator.resetForm();

        $('#leaveDateModal').show();
    }

    // Close Leave Date Modal
    function closeLeaveDateModal() {
        $('#leaveDateModal').hide();
    }

    // Handle form submission for setting the leave date
    $("#setLeaveDateForm").submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        // Basic validation check
        if (!$('#leave_date').val()) {
            alert('Please select a leave date.');
            return;
        }

        $.ajax({
            url: 'employee_api.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    closeLeaveDateModal();
                    fetchEmployees();
                    alert(data.message || 'Leave date set successfully!');
                } else {
                    alert(data.message || 'Failed to set leave date.');
                }
            },
            error: function(err) {
                console.error('Error setting leave date:', err);
                alert('Error setting leave date');
            }
        });
    });
</script>

</body>
</html>