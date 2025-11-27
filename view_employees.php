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

// Fetch employees (all or by department)
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $dept = isset($_GET['dept']) && $_GET['dept'] !== '' ? $_GET['dept'] : null;

    if ($dept) {
        $sql = "CALL GetEmployeesByDept('".$dept."')";
    } else {
        $sql = "SELECT e.emp_id, e.emp_name, d.dept_name, e.salary, e.is_active
                FROM employees e
                LEFT JOIN departments d ON e.dept_id = d.dept_id
                ORDER BY e.emp_id ASC";
    }

    $result = mysqli_query($connection, $sql);
    $employees = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }

    echo json_encode($employees);
    exit();
}

// Add employee via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $emp_name = $_POST['emp_name'];
    $dept_id  = intval($_POST['dept_id']);
    $salary   = floatval($_POST['salary']);
    $is_active = intval($_POST['is_active']);

    $sql = "CALL AddEmployee('$emp_name', (SELECT dept_name FROM departments WHERE dept_id=$dept_id), $salary, $dept_id)";
    $success = mysqli_query($connection, $sql);

    echo json_encode(['success' => $success, 'message' => $success ? 'Employee added!' : mysqli_error($connection)]);
    exit();
}

// Delete employee via AJAX
if (isset($_POST['delete_id'])) {
    $emp_id = intval($_POST['delete_id']);
    $sql = "CALL delete_employee_sp($emp_id)";
    $success = mysqli_query($connection, $sql);

    echo json_encode(['success' => $success]);
    exit();
}

// Fetch all departments for dropdown
$dept_result = mysqli_query($connection, "SELECT * FROM departments");
$departments = [];
while ($row = mysqli_fetch_assoc($dept_result)) {
    $departments[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employees</title>
    <style>
/* General body */
body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    background: linear-gradient(120deg, #f4f6f8, #e8f0fe);
    padding: 30px;
    color: #333;
}

/* Main container card */
.container { 
    background:#fff; 
    padding:40px 35px; 
    border-radius:16px; 
    max-width:950px; 
    margin:auto; 
    box-shadow:0 20px 40px rgba(0,0,0,0.1); 
    text-align:center; 
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.container:hover {
    transform: translateY(-3px);
    box-shadow:0 25px 50px rgba(0,0,0,0.15);
}

/* Header */
h2 { 
    margin-bottom:25px; 
    color:#007bff; 
    font-weight:600;
    letter-spacing: 0.5px;
}

/* Table styling */
table { 
    width:100%; 
    border-collapse:collapse; 
    margin-top:20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border-radius:12px;
    overflow:hidden;
}

th, td { 
    padding:14px 15px; 
    border: none; 
    text-align:left; 
}

th { 
    background: linear-gradient(90deg, #007bff, #0056b3); 
    color:#fff; 
    font-weight:600;
}

tr:nth-child(even){ 
    background:#f9f9f9; 
}

tr:hover {
    background: #f1f7ff;
    transform: scale(1.01);
    transition: all 0.2s ease;
}

/* Buttons */
.btn { 
    padding:10px 20px; 
    margin:5px; 
    cursor:pointer; 
    border:none; 
    border-radius:8px; 
    color:white; 
    font-weight:600;
    transition: all 0.3s ease;
    box-shadow:0 4px 15px rgba(0,0,0,0.2);
}

/* Gradient Add button */
.add-btn {
    background: linear-gradient(45deg, #28a745, #218838);
}
.add-btn:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow:0 8px 20px rgba(0,0,0,0.3);
}

/* Gradient Delete toggle button */
.delete-toggle-btn {
    background: linear-gradient(45deg, #e74c3c, #c0392b);
    font-size:14px;
}
.delete-toggle-btn:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow:0 8px 20px rgba(0,0,0,0.3);
}

/* Minimal delete button beside name */
button.delete-btn {
    background:#e74c3c;
    padding:4px 10px;
    font-size:12px;
    border-radius:6px;
    font-weight:500;
    box-shadow:none;
    transition: all 0.2s ease;
}
button.delete-btn:hover {
    background:#c0392b;
    transform: translateY(-1px) scale(1.05);
}

/* Popup overlay with blur */
.popup { 
    display:none; 
    position:fixed; 
    top:0; left:0; 
    width:100%; height:100%; 
    backdrop-filter: blur(5px);
    background: rgba(0,0,0,0.4); 
    justify-content:center; 
    align-items:center; 
    z-index:1000;
}

/* Popup content */
.popup-content { 
    background:#fff; 
    padding:40px 30px; 
    border-radius:16px; 
    width:450px; 
    max-width:90%; 
    position:relative; 
    box-shadow: 0 25px 50px rgba(0,0,0,0.3); 
    animation: popupFade 0.3s ease;
}

/* Popup animation */
@keyframes popupFade {
    from { opacity:0; transform:scale(0.9); }
    to { opacity:1; transform:scale(1); }
}

/* Close button */
.close {
    position:absolute; 
    top:12px; 
    right:18px; 
    cursor:pointer; 
    font-weight:bold; 
    font-size:24px; 
    color:#555;
    transition: color 0.2s, transform 0.2s;
}
.close:hover { 
    color:#000; 
    transform: scale(1.2);
}

/* Inputs & selects */
.popup-content input, 
.popup-content select { 
    width:100%; 
    padding:12px; 
    margin:10px 0; 
    border-radius:8px; 
    border:1px solid #ccc; 
    box-sizing:border-box;
    font-size:14px;
    transition:border-color 0.3s, box-shadow 0.3s;
}
.popup-content input:focus, 
.popup-content select:focus {
    border-color:#007bff;
    box-shadow:0 0 8px rgba(0,123,255,0.3);
    outline:none;
}

/* Filter dropdown */
#deptFilter { 
    margin-bottom:20px;
    padding:10px 12px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:14px;
}

/* Back / Logout button */
.logout-btn {
    display:flex;
    justify-content:center;
    margin:20px auto 0 auto;
    padding:12px 25px;
    background: linear-gradient(45deg, #6c757d, #5a6268);
    color:white;
    text-decoration:none;
    border-radius:10px;
    font-weight:600;
    transition: all 0.3s ease;
}
.logout-btn:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow:0 10px 20px rgba(0,0,0,0.25);
}

    </style>
</head>
<body>

<div class="container">
    <h2>Employees</h2>

    <select id="deptFilter">
        <option value="">All Departments</option>
        <?php foreach($departments as $d) echo "<option value='{$d['dept_name']}'>{$d['dept_name']}</option>"; ?>
    </select>

    <div>
        <button class="btn add-btn" id="addBtn">Add</button>
        <button class="btn delete-toggle-btn" id="toggleDeleteBtn">Delete</button>
    </div>

    <table id="employeesTable">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Department</th><th>Salary</th><th>Active</th><th class="delete-col" style="display:none;">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<!-- Popup Add Employee -->
<div class="popup" id="addPopup">
    <div class="popup-content">
        <span class="close" id="closePopup">&times;</span>
        <h3>Add Employee</h3>
        <div id="message"></div>
        <form id="addForm">
            <input type="text" name="emp_name" placeholder="Employee Name" required>
            <select name="dept_id" required>
                <option value="">Select Department</option>
                <?php foreach($departments as $d) echo "<option value='{$d['dept_id']}'>{$d['dept_name']}</option>"; ?>
            </select>
            <input type="number" name="salary" step="0.01" placeholder="Salary" required>
            <select name="is_active">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            <button type="submit" class="btn add-btn">Save</button>
        </form>
    </div>
</div>

<script>
let deleteMode = false;

function fetchEmployees(dept='') {
    let url = '?ajax=1';
    if(dept) url += '&dept=' + encodeURIComponent(dept);

    fetch(url)
    .then(res=>res.json())
    .then(data=>{
        const tbody = document.querySelector('#employeesTable tbody');
        tbody.innerHTML = '';
        if(!Array.isArray(data) || data.length===0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No employees found</td></tr>';
            return;
        }
        data.forEach(emp=>{
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${emp.emp_id}</td>
                <td>${emp.emp_name}</td>
                <td>${emp.dept_name ? emp.dept_name : emp.department}</td>
                <td>${emp.salary}</td>
                <td>${emp.is_active==1?'Yes':'No'}</td>
                <td class="delete-col" style="display:${deleteMode?'table-cell':'none'};">
                    <button class="delete-btn" onclick="deleteEmployee(${emp.emp_id})">Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    });
}

function deleteEmployee(id){
    if(!confirm('Delete this employee?')) return;
    fetch('', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'delete_id='+id})
    .then(res=>res.json())
    .then(data=>{
        if(data.success) fetchEmployees(document.getElementById('deptFilter').value);
        else alert('Delete failed');
    });
}

// Toggle delete buttons
document.getElementById('toggleDeleteBtn').addEventListener('click', ()=>{
    deleteMode = !deleteMode;
    document.querySelectorAll('.delete-col').forEach(td=>{
        td.style.display = deleteMode?'table-cell':'none';
    });
});

// Department filter
document.getElementById('deptFilter').addEventListener('change', function(){
    fetchEmployees(this.value);
});

// Add popup logic
const popup = document.getElementById('addPopup');
document.getElementById('addBtn').addEventListener('click', ()=> popup.style.display='flex');
document.getElementById('closePopup').addEventListener('click', ()=> popup.style.display='none');

// Add employee via AJAX
document.getElementById('addForm').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action','add');
    fetch('', {method:'POST', body:formData})
    .then(res=>res.json())
    .then(data=>{
        const msg = document.getElementById('message');
        if(data.success){
            msg.style.color='green';
            msg.textContent=data.message;
            this.reset();
            fetchEmployees(document.getElementById('deptFilter').value);
            popup.style.display='none';
        } else {
            msg.style.color='red';
            msg.textContent=data.message;
        }
    });
});

window.onload = ()=>fetchEmployees();
</script>

</body>
</html>
