<?php
session_start();
include('../config/db_connect.php');

// ✅ If user is already logged in, redirect them to the dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header("Location: ../dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' AND status='active' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['department_code'] = $user['department_code'];
            header("Location: ../dashboard.php");
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "User not found or inactive.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESRMS Login</title>

    <!-- Google Fonts for Better Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJzQ6n4u6m5H30KZJzK5S5kNDkDk6kU6DX5p5NKlm1dMw5vc4fGgQ4Z7dWg9" crossorigin="anonymous">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: #333;
            line-height: 1.3;
        }
        .form-control {
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 1rem;
            margin-bottom: 1.25rem;
            padding: 1rem;
            letter-spacing: 0.5px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            font-size: 1.125rem;
            padding: 0.75rem;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            text-align: center;
            margin-top: 1rem;
            font-weight: 500;
            line-height: 1.5;
        }
        label {
            font-weight: 600;
            font-size: 1rem;
            color: #333;
            margin-bottom: 0.5rem;
            display: block;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Login to Your Account</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gyb5eClb55sU5vY5vQkQ4bQ7Fi6qxKJ6yQ4e4VHK9XW7VJ7z0X" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0R8ZtA7Gv2Z5LZ9F4xzkfVfjlP6K8zS6v9ZrWZdfxJeypqbk" crossorigin="anonymous"></script>

</body>
</html>
