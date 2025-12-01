<?php
session_start();
if (isset($_SESSION['logged_in'])) {
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
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; text-align: left; }
        input { 
            width: 100%; 
            padding: 10px; 
            margin-bottom: 15px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            font-size: 14px;
            box-sizing: border-box;
        }
        button, .signup-button { 
            width: 100%; 
            padding: 10px; 
            border: none; 
            border-radius: 5px; 
            font-size: 16px; 
            cursor: pointer; 
            margin-bottom: 10px;
        }
        button { background-color: #007bff; color: white; }
        button:hover { background-color: #0056b3; }
        .signup-button { 
            background-color: #28a745; 
            color: white; 
            text-decoration: none; 
            display: block;
            line-height: 1.5;
        }
        .signup-button:hover { background-color: #218838; }
        .message { margin-bottom: 15px; font-weight: bold; min-height: 20px; }
        .footer { margin-top: 20px; font-size: 12px; color: #888; }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <div class="message" id="message"></div>

    <form id="loginForm">
        <label>Username:</label>
        <input type="text" name="username" id="username" required>

        <label>Password:</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Login</button>
    </form>

    <a href="signup.php" class="signup-button">Create Account</a>

    <div class="footer">&copy; 2025 Training System</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$('#loginForm').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: 'employee_api.php',
        type: 'POST',
        data: $(this).serialize() + '&action=login',
        dataType: 'json',
        success: function(data) {
            const msg = $('#message');
            if (data.success == 1) {
                msg.css('color', 'green').text(data.message);
                setTimeout(() => window.location.href = 'view_employees.php', 1000);
            } else {
                msg.css('color', 'red').text(data.message);
                $('#password').val('');
            }
        },
        error: function() {
            $('#message').css('color', 'red').text('Server error occurred.');
        }
    });
});
</script>

</body>
</html>