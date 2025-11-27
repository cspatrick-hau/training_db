<?php
session_start();
include("mysqlConnection.php"); // your database connection

// Handle AJAX login
if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simple query without encryption for now
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        echo json_encode(['success' => true, 'message' => 'Login successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
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

        .login-container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            width: 350px;
            text-align: center;
        }

        h2 { margin-bottom: 25px; color: #333; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
        button, .signup-button { width: 90%; padding: 10px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-bottom: 10px; }
        button { background-color: #007bff; color: white; }
        button:hover { background-color: #0056b3; }
        .signup-button { background-color: #28a745; color: white; text-decoration: none; display: inline-block; line-height: normal; }
        .signup-button:hover { background-color: #218838; }
        .message { margin-bottom: 15px; font-weight: bold; }
        .footer { text-align: center; margin-top: 10px; font-size: 12px; color: #888; }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <div class="message" id="message"></div>

    <form id="loginForm">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <br>
    <a href="signup.php" class="signup-button">Create Account</a>

    <div class="footer">
        &copy; 2025 Training System
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault(); // stop page from reloading

    const formData = new FormData(this);

    fetch('', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            const msgDiv = document.getElementById('message');
            if (data.success) {
                msgDiv.style.color = 'green';
                msgDiv.textContent = data.message;
                // Redirect after 1 second
                setTimeout(() => { window.location.href = 'view_employees.php'; }, 1000);
            } else {
                msgDiv.style.color = 'red';
                msgDiv.textContent = data.message;
            }
        })
        .catch(err => console.error('Error:', err));
});
</script>

</body>
</html>
