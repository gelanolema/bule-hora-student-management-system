<?php
session_start();
include("includes/config.php"); // DB connection

// If already logged in, redirect to dashboard
if(isset($_SESSION['username']) && isset($_SESSION['role'])){
    if($_SESSION['role']=='admin') header("Location: admin/dashboard.php");
    elseif($_SESSION['role']=='teacher') header("Location: teacher/dashboard.php");
    elseif($_SESSION['role']=='student') header("Location: student/dashboard.php");
    exit();
}

$error = "";

if(isset($_POST['login'])){
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $role     = mysqli_real_escape_string($conn, trim($_POST['role']));

    // Plain password check
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password' AND role='$role'";
    $result = mysqli_query($conn, $query);

    if($result && mysqli_num_rows($result) == 1){
        $row = mysqli_fetch_assoc($result);

        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        if($row['role']=='admin') header("Location: admin/dashboard.php");
        elseif($row['role']=='teacher') header("Location: teacher/dashboard.php");
        elseif($row['role']=='student') header("Location: student/dashboard.php");

        exit();
    } else {
        $error = "Invalid username, password, or role!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Student Marks System</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin:0; padding:0;
        height:100vh;
        display:flex;
        justify-content:center;
        align-items:center;
        background: linear-gradient(-45deg,#31088e,#5d18c7,#2b0d5b,#1b0033);
        background-size:400% 400%;
        animation: gradientBG 15s ease infinite;
        transition: background 0.5s, color 0.5s;
    }
    @keyframes gradientBG {
        0% {background-position:0% 50%;}
        50% {background-position:100% 50%;}
        100% {background-position:0% 50%;}
    }

    .login-container {
        width: 400px;
        background: rgba(255,255,255,0.1);
        padding: 50px 40px;
        border-radius: 25px;
        box-shadow:0 15px 40px rgba(0,0,0,0.4);
        backdrop-filter: blur(15px);
        text-align:center;
        color: #fff;
        position:relative;
        transition: background 0.5s, color 0.5s;
    }

    h2 { margin-bottom:30px; color:#00eaff; }

    input, select {
        width:100%;
        padding:14px;
        margin-bottom:20px;
        border-radius:12px;
        border:none;
        background: rgba(255,255,255,0.2);
        color:#fff;
        font-size:16px;
    }

    input:focus, select:focus { outline:none; background: rgba(255,255,255,0.35); }

    input[type="submit"] {
        background: linear-gradient(90deg,#00eaff,#00b5c7);
        font-weight:bold;
        border:none;
        border-radius:14px;
        padding:14px;
        cursor:pointer;
        transition:0.3s;
    }

    input[type="submit"]:hover { background: linear-gradient(90deg,#00b5c7,#0093a0); color:#000; }

    .error { color:#ff6b6b; margin-bottom:20px; font-weight:bold; }

    .password-wrapper { position:relative; }
    .password-wrapper input { padding-right:45px; }
    .password-wrapper .toggle-pass {
        position:absolute; right:12px; top:50%; transform:translateY(-50%);
        cursor:pointer; font-size:18px; user-select:none; color:#fff;
    }

    button#themeToggle {
        position:absolute; top:10px; right:10px;
        padding:8px 12px; border:none; border-radius:8px;
        cursor:pointer; background:#00eaff; color:#000; font-weight:bold;
    }

    body.dark { background:#121212; color:#eee; }
    body.dark .login-container { background:#1e1e1e; color:#eee; }
    body.dark input, body.dark select { background:#2b2b2b; color:#eee; }

    @media(max-width:420px){ .login-container { width:90%; padding:40px 20px; } }
</style>
</head>
<body>
<button id="themeToggle">Toggle Dark/Light</button>
<div class="login-container">
    <h2>Student Marks System</h2>
    <?php if($error) echo "<div class='error'>$error</div>"; ?>
    <form method="post" action="">
        <input type="text" name="username" placeholder="Username" required>
        <div class="password-wrapper">
            <input type="password" id="password" name="password" placeholder="Password" required>
            <span class="toggle-pass" id="togglePass">&#128065;</span>
        </div>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="admin">Admin</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
        </select>
        <input type="submit" name="login" value="Login">
    </form>
</div>

<script>
const passwordInput = document.getElementById('password');
const togglePass = document.getElementById('togglePass');
togglePass.addEventListener('click', function(){
    passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
});

const themeToggle = document.getElementById('themeToggle');
themeToggle.addEventListener('click', function(){
    document.body.classList.toggle('dark');
    localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
});

// Load saved theme
if(localStorage.getItem('theme')==='dark') document.body.classList.add('dark');
</script>
</body>
</html>
