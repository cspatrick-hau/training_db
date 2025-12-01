<?php
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: view_employees.php");
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <div class="message" id="message"></div>

    <form id="loginForm">
        <label>Username:</label>
        <input type="text" name="username" id="username" required pattern="[a-zA-Z]+" title="Username must contain only letters">

        <label>Password:</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Login</button>
    </form>

    <br>
    <a href="signup.php" class="signup-button">Create Account</a>

    <div class="footer">
        &copy; 2025 Training System
    </div>
</div>

<script>
// Prevent non-letter input in real-time
$('#username').on('input', function() {
    this.value = this.value.replace(/[^a-zA-Z]/g, '');
});

// AJAX login via employee_api.php
$('#loginForm').submit(function(e) {
    e.preventDefault();
    var formData = $(this).serialize() + '&action=login';

    $.ajax({
        url: 'employee_api.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(data) {
            const msgDiv = $('#message');
            if (data.success == 1) {
                msgDiv.css('color', 'green').text(data.message);
                setTimeout(() => { window.location.href = 'view_employees.php'; }, 1000);
            } else {
                msgDiv.css('color', 'red').text(data.message);
                $('#password').val('');
            }
        },
        error: function(err) {
            console.error('Error:', err);
            $('#message').css('color', 'red').text('Server error occurred.');
        }
    });
});
</script>

</body>
</html>
