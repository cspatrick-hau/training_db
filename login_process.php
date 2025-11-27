<?php
session_start();
include("mysqlConnection.php");

$username = $_POST['username'];
$password = $_POST['password']; // plain text

// Check username and password
$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
$result = mysqli_query($connection, $sql);

if (!$result) {
    die("Query Error: " . mysqli_error($connection));
}

if (mysqli_num_rows($result) == 1) {
    $_SESSION['logged_in'] = true;
    header("Location: dashboard.php");
    exit();
} else {
    echo "Invalid username or password";
}
?>
