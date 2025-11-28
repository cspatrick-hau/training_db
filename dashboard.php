<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

include("mysqlConnection.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:100vh;
            margin:0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f4f6f8, #e8f0fe);
        }
        .container {
            background:#fff;
            padding:50px 40px;
            border-radius:16px;
            box-shadow:0 20px 40px rgba(0,0,0,0.1);
            text-align:center;
            width:400px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .container:hover { transform: translateY(-3px); box-shadow:0 25px 50px rgba(0,0,0,0.15); }
        h2 { margin-bottom:30px; color:#007bff; }
        .button { display:flex; align-items:center; justify-content:center; width:250px; padding:15px; margin:10px auto; font-size:16px; border:none; border-radius:10px; cursor:pointer; color:white; font-weight:600; text-decoration:none; transition: all 0.3s ease; }
        .button i { margin-right:10px; font-size:18px; }
        .button:hover { transform: translateY(-3px) scale(1.05); box-shadow:0 10px 25px rgba(0,0,0,0.2); }
        .add-btn { background: linear-gradient(45deg,#28a745,#218838); }
        .delete-btn { background: linear-gradient(45deg,#dc3545,#c0392b); }
        .view-btn { background: linear-gradient(45deg,#007bff,#0056b3); }
        .logout-btn { display:flex; justify-content:center; margin:20px auto 0 auto; padding:12px 25px; background: linear-gradient(45deg,#6c757d,#5a6268); color:white; border-radius:10px; font-weight:600; text-decoration:none; transition: all 0.3s ease; }
        .logout-btn:hover { transform: translateY(-2px) scale(1.03); box-shadow:0 10px 20px rgba(0,0,0,0.25); }

        /* --- Popup Styles --- */
        .popup { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); justify-content:center; align-items:center; z-index:1000; }
        .popup-content { background:#fff; padding:50px; border-radius:16px; width:90%; max-width:1200px; position:relative; box-shadow:0 25px 50px rgba(0,0,0,0.3); animation: fadeIn 0.3s ease; overflow:auto; max-height:90%; }
        @keyframes fadeIn { from{opacity:0;transform:scale(0.95);} to{opacity:1;transform:scale(1);} }
        .close { position:absolute; top:12px; right:18px; font-size:28px; font-weight:bold; cursor:pointer; color:#555; }
        .close:hover { color:#000; transform:scale(1.2); }
        table { width:100%; border-collapse:collapse; margin-top:20px; font-size:14px; }
        th,td { padding:12px; text-align:left; border-bottom:1px solid #ddd; }
        th { background: linear-gradient(90deg,#007bff,#0056b3); color:white; }
        tr:hover { background:#f1f7ff; transform:scale(1.01); transition:0.2s; }
        input, select { width:100%; padding:12px; margin:8px 0; border-radius:8px; border:1px solid #ccc; box-sizing:border-box; font-size:14px; transition:border-color 0.3s, box-shadow 0.3s; }
        input:focus, select:focus { border-color:#007bff; box-shadow:0 0 8px rgba(0,123,255,0.3); outline:none; }
        .popup .submit-btn { padding:10px 18px; background: linear-gradient(45deg,#28a745,#218838); color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; margin-top:10px; transition: all 0.3s ease; width:100%; }
        .popup .submit-btn:hover { transform: translateY(-2px) scale(1.05); box-shadow:0 8px 15px rgba(0,0,0,0.2); }
        .popup .delete-btn { padding: 6px 12px; background: linear-gradient(45deg,#dc3545,#c82333); color:white; border:none; border-radius:5px; cursor:pointer; transition:0.2s; width:auto; margin-top:0; }
        .popup .delete-btn:hover { background: linear-gradient(45deg,#c82333,#bd2130); transform: translateY(-2px); box-shadow:0 4px 10px rgba(220, 53, 69, 0.4); }
        .message { margin-bottom:15px; font-weight:bold; }
        label { display:block; margin-bottom:5px; font-weight:bold; color:#555; text-align:left; }
    </style>
</head>
<body>

<div class="container">
    <h2>Dashboard</h2>
    <button class="button add-btn" id="addBtn"><i class="fas fa-user-plus"></i>Add Employee</button>
    <button class="button delete-btn" id="deleteBtn"><i class="fas fa-user-minus"></i>Delete Employee</button>
    <button class="button view-btn" id="viewBtn"><i class="fas fa-users"></i>View Employees</button>
    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<!-- View/Delete Popup -->
<div class="popup" id="popup">
    <div class="popup-content">
        <span class="close" id="closePopup">&times;</span>
        <div id="popupBody"></div>
    </div>
</div>

<!-- Add Employee Popup -->
<div class="popup" id="addEmployeePopup">
    <div class="popup-content">
        <span class="close" id="closeAddPopup">&times;</span>
        <div id="addEmployeeBody"></div>
    </div>
</div>

<script>
const popup = document.getElementById('popup');
const popupBody = document.getElementById('popupBody');
const closeBtn = document.getElementById('closePopup');

const addEmployeePopup = document.getElementById('addEmployeePopup');
const addEmployeeBody = document.getElementById('addEmployeeBody');
const closeAddBtn = document.getElementById('closeAddPopup');

// ---------------------- View/Delete Employees ----------------------
function openPopup(file) {
    fetch(file + '?ajax=1')
        .then(res => res.json())
        .then(data => {
            let html = '<h2>' + (file.includes('delete') ? 'Delete Employees' : 'View Employees') + '</h2>';
            html += '<table><tr><th>ID</th><th>Name</th><th>Department</th><th>Salary</th><th>Active</th>';
            if(file.includes('delete')) html += '<th>Action</th>';
            html += '</tr>';
            if(data.length === 0){
                html += '<tr><td colspan="' + (file.includes('delete') ? '6' : '5') + '" style="text-align:center;">No employees found</td></tr>';
            } else {
                data.forEach(emp => {
                    html += `<tr>
                        <td>${emp.emp_id}</td>
                        <td>${emp.emp_name}</td>
                        <td>${emp.dept_name || ''}</td>
                        <td>${emp.salary}</td>
                        <td>${emp.is_active==1?'Yes':'No'}</td>`;
                    if(file.includes('delete')){
                        html += `<td><button class="delete-btn" onclick="deleteEmployee(${emp.emp_id})">Delete</button></td>`;
                    }
                    html += '</tr>';
                });
            }
            html += '</table>';
            popupBody.innerHTML = html;
            popup.style.display = 'flex';
        })
        .catch(err => { popupBody.innerHTML = '<p style="color:red;">Error: '+err.message+'</p>'; popup.style.display='flex'; });
}

// ---------------------- Add Employee ----------------------
function openAddEmployeePopup() {
    fetch('add_employee.php?ajax=1')
        .then(res => res.json())
        .then(data => {
            let deptOptions = '<option value="">--Select Department--</option>';
            if(data.departments && data.departments.length > 0){
                data.departments.forEach(d => { deptOptions += `<option value="${d.dept_id}">${d.dept_name}</option>`; });
            } else { deptOptions += '<option value="">No departments</option>'; }

            const formHTML = `
                <h2>Add Employee</h2>
                <div class="message" id="addMessage"></div>
                <form id="addEmployeeForm">
                    <label>Employee Name:</label>
                    <input type="text" name="emp_name" required pattern="[A-Za-z\\s]+" title="Only letters and spaces allowed">

                    <label>Department:</label>
                    <select name="dept_id" required>${deptOptions}</select>

                    <label>Salary:</label>
                    <input type="number" name="salary" step="0.01" min="0" required>

                    <label>Is Active?</label>
                    <select name="is_active">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>

                    <button type="submit" class="submit-btn">Save Employee</button>
                </form>
            `;
            addEmployeeBody.innerHTML = formHTML;
            addEmployeePopup.style.display='flex';

            document.getElementById('addEmployeeForm').addEventListener('submit', function(e){
                e.preventDefault();
                const name = this.emp_name.value.trim();
                const salary = parseFloat(this.salary.value);
                const msgDiv = document.getElementById('addMessage');
                if(!/^[A-Za-z\s]+$/.test(name)){ msgDiv.textContent='Name must contain letters only'; msgDiv.style.color='red'; return; }
                if(salary<0){ msgDiv.textContent='Salary must be positive'; msgDiv.style.color='red'; return; }

                const formData = new FormData(this);
                fetch('add_employee.php',{method:'POST',body:formData})
                .then(res => res.json())
                .then(data => {
                    msgDiv.style.color = data.success?'green':'red';
                    msgDiv.textContent = data.message;
                    if(data.success) this.reset();
                })
                .catch(err => { msgDiv.style.color='red'; msgDiv.textContent='Error adding employee'; });
            });
        })
        .catch(err => { addEmployeeBody.innerHTML='<p style="color:red;">Error: '+err.message+'</p>'; addEmployeePopup.style.display='flex'; });
}

// ---------------------- Delete Employee ----------------------
function deleteEmployee(empId){
    if(!confirm('Are you sure?')) return;
    fetch('delete_employees.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'delete_id='+empId
    })
    .then(res=>res.json())
    .then(data=>{ if(data.success) openPopup('delete_employees.php'); else alert('Failed'); })
    .catch(err=>console.error(err));
}

// ---------------------- Close Popups ----------------------
closeBtn.addEventListener('click',()=>popup.style.display='none');
closeAddBtn.addEventListener('click',()=>addEmployeePopup.style.display='none');
popup.addEventListener('click', e=>{if(e.target===popup) popup.style.display='none';});
addEmployeePopup.addEventListener('click', e=>{if(e.target===addEmployeePopup) addEmployeePopup.style.display='none';});

// ---------------------- Button Listeners ----------------------
document.getElementById('addBtn').addEventListener('click', openAddEmployeePopup);
document.getElementById('deleteBtn').addEventListener('click', ()=>openPopup('delete_employees.php'));
document.getElementById('viewBtn').addEventListener('click', ()=>openPopup('view_employees.php'));
</script>

</body>
</html>
