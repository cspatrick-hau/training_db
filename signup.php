<?php
session_start();
include("mysqlConnection.php");

// Handle AJAX signup
if (isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);

    // Simple response array
    $response = ['success' => false, 'message' => ''];

    // Basic validation
    if ($password !== $confirm) {
        $response['message'] = "Passwords do not match!";
    } else {
        // Check if username exists
        $check = mysqli_query($connection, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            $response['message'] = "Username already taken!";
        } else {
            // Insert user (plain password for now)
            $insert = mysqli_query($connection, "INSERT INTO users (username, password) VALUES ('$username', '$password')");
            if ($insert) {
                $response['success'] = true;
                $response['message'] = "Account created successfully! You can now <a href='login.php'>Login</a>.";
            } else {
                $response['message'] = "Error: " . mysqli_error($connection);
            }
        }
    }

    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
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
            width: 350px;
            text-align: center;
        }

        h2 { margin-bottom: 25px; color: #333; }

        input[type="text"], input[type="password"] {
            width: 100%; padding: 10px; margin-bottom: 15px;
            border: 1px solid #ccc; border-radius: 5px; font-size: 14px; box-sizing: border-box;
        }

        button { width: 100%; padding: 10px; background-color: #28a745; border: none; border-radius: 5px; color: white; font-size: 16px; cursor: pointer; }
        button:hover { background-color: #218838; }

        .message { margin-bottom: 15px; font-weight: bold; color: #dc3545; }
        .footer { margin-top: 15px; font-size: 12px; color: #888; }
        .footer a { color: #007bff; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h2>Sign Up</h2>

    <div class="message" id="message"></div>

    <form id="signupForm">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Create Account</button>
    </form>

    <div class="footer">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

<script>
document.getElementById('signupForm').addEventListener('submit', function(e) {
    e.preventDefault(); // stop page from reloading

    const formData = new FormData(this);

    fetch('', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            const msgDiv = document.getElementById('message');
            if (data.success) {
                msgDiv.style.color = 'green';
                msgDiv.innerHTML = data.message;
                this.reset(); // clear the form
            } else {
                msgDiv.style.color = '#dc3545';
                msgDiv.textContent = data.message;
            }
        })
        .catch(err => console.error('Error:', err));
});
</script>

</body>
</html>
