<?php
include 'db.php';
$msg = "";

if (isset($_POST['register'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$user'");
    if (mysqli_num_rows($check) > 0) {
        $msg = "Username already exists!";
    } else {
        mysqli_query($conn, "INSERT INTO users (username, password) VALUES ('$user', '$pass')");
        $msg = "Registration successful! Please Login now.";
    }
}

if (isset($_POST['login'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE username='$user'");
    if ($row = mysqli_fetch_assoc($res)) {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $msg = "Invalid Password!";
        }
    } else {
        $msg = "User not found! Please Sign Up first.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Vishvakarma Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #3E2723 0%, #5D4037 50%, #8D6E63 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .wood-card {
            background: #FFF8E7;
            border: 4px solid #D4A373;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            padding: 35px;
            width: 100%;
            max-width: 420px;
        }
        .btn-wood {
            background: linear-gradient(to right, #6D4C41, #4E342E);
            color: #FFD54F;
            font-weight: bold;
            border: none;
        }
        .btn-wood:hover { background: #3E2723; color: #FFF; }
        .toggle-link { color: #8D6E63; cursor: pointer; font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
<div class="wood-card text-center">
    <!-- Custom Wooden SVG Logo -->
    <svg width="90" height="90" viewBox="0 0 100 100" class="mb-2">
        <circle cx="50" cy="50" r="46" fill="#4E342E" stroke="#D4A373" stroke-width="4"/>
        <path d="M25 30 L50 75 L75 30" fill="none" stroke="#FFD54F" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M38 45 L62 45" stroke="#D4A373" stroke-width="5" stroke-linecap="round"/>
    </svg>
    <h3 class="fw-bold" style="color: #4E342E;">VISHVAKARMA FURNITURE</h3>
    <p class="text-muted small">Mistry Work, Billing & Measurement Portal</p>

    <?php if($msg != "") echo "<div class='alert alert-warning py-2'>$msg</div>"; ?>

    <!-- Login Form -->
    <form method="POST" id="loginForm">
        <h5 class="mb-3 text-start fw-bold text-secondary">Login</h5>
        <input type="text" name="username" class="form-control mb-3" placeholder="Enter Username" required>
        <input type="password" name="password" class="form-control mb-3" placeholder="Enter Password" required>
        <button type="submit" name="login" class="btn btn-wood w-100 py-2">LOGIN</button>
        <p class="mt-3 mb-0 small">Not registered yet? <span class="toggle-link" onclick="toggleForms()">Sign Up First</span></p>
    </form>

    <!-- Register Form (Hidden by default) -->
    <form method="POST" id="registerForm" style="display:none;">
        <h5 class="mb-3 text-start fw-bold text-secondary">New Registration (Sign Up)</h5>
        <input type="text" name="username" class="form-control mb-3" placeholder="Create Username" required>
        <input type="password" name="password" class="form-control mb-3" placeholder="Create Password" required>
        <button type="submit" name="register" class="btn btn-success w-100 py-2 fw-bold">SIGN UP</button>
        <p class="mt-3 mb-0 small">Already have an account? <span class="toggle-link" onclick="toggleForms()">Back to Login</span></p>
    </form>
</div>

<script>
function toggleForms() {
    var l = document.getElementById('loginForm');
    var r = document.getElementById('registerForm');
    l.style.display = l.style.display === 'none' ? 'block' : 'none';
    r.style.display = r.style.display === 'none' ? 'block' : 'none';
}
</script>
</body>
</html>