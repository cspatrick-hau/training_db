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
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
            padding: 40px 50px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.25);
            text-align: center;
            width: 400px;
        }

        h2 {
            margin-bottom: 30px;
            color: #333;
        }

        .button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 250px;      /* set a smaller width than container */
            padding: 15px;
            margin: 0 auto 15px auto;  /* top 0, horizontal auto, bottom 15px */
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }


        .button i {
            margin-right: 10px;
            font-size: 18px;
        }

        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }

        .add-btn {
            background-color: #28a745;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        .view-btn {
            background-color: #007bff;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 150px;        /* smaller width */
            margin: 20px auto 0 auto; /* top 20px, horizontal auto, bottom 0 */
            padding: 12px;
            background-color: #6c757d;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }


        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Dashboard</h2>

    <a href="add_employee.php" class="button add-btn"><i class="fas fa-user-plus"></i>Add Employee</a>
    <a href="delete_employees.php" class="button delete-btn"><i class="fas fa-user-minus"></i>Delete Employee</a>
    <a href="view_employees.php" class="button view-btn"><i class="fas fa-users"></i>View Employees</a>

    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

</body>
</html>
