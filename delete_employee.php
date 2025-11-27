<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

include("mysqlConnection.php");

if (isset($_GET['id'])) {
    $emp_id = $_GET['id'];

    // Delete employee
    $sql = "DELETE FROM employees WHERE emp_id = $emp_id";
    if (mysqli_query($connection, $sql)) {
        header("Location: view_employees.php");
        exit();
    } else {
        echo "Error deleting employee: " . mysqli_error($connection);
    }
} else {
    header("Location: view_employees.php");
    exit();
}
?>
