<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

include("mysqlConnection.php");

$emp_name   = $_POST['emp_name'];
$salary     = $_POST['salary'];
$dept_id    = $_POST['dept_id'];
$is_active  = $_POST['is_active'];

$sql = "INSERT INTO employees (emp_name, salary, dept_id, is_active)
        VALUES ('$emp_name', '$salary', '$dept_id', '$is_active')";

$message = "";
if (mysqli_query($connection, $sql)) {
    $message = "Employee added successfully!";
} else {
    $message = "Error: " . mysqli_error($connection);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Save Employee</title>
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

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #555;
        }

        a.button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        a.button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Save Employee</h2>
    <p><?php echo $message; ?></p>
    <a href="add_employee.php" class="button">Add Another Employee</a>
</div>

</body>
</html>
