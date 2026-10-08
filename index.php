<?php
session_start();
include("includes/config.php");

$message = "";

if(isset($_POST['login'])){
    $username = mysqli_real_escape_string($conn,$_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if($role=='admin' || $role=='teacher'){
        $table = 'teacher';
    } else {
        $table = 'student';
    }

    $res = mysqli_query($conn,"SELECT * FROM $table WHERE username='$username'");
    if(mysqli_num_rows($res)==1){
        $row = mysqli_fetch_assoc($res);
        if($password == $row['password']){
    // Store user details in session
    $_SESSION['user_id'] = $row['id'];   // <-- Add this line
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;

    if ($role == 'admin') {
        header("Location: admin/dashboard.php");
    }
    elseif ($role == 'teacher') {
        header("Location: teacher/dashboard.php");
    }
    else { // student
        header("Location: student/dashboard.php");
    }
    exit();
}

        } else {
            $message = "Invalid password!";
        }
    } else {
        $message = "";
    }

?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>

<style>
/* BODY */
*{box-sizing:border-box;margin:0;padding:0;font-family: "Segoe UI", Arial, sans-serif;}
body{
    background:#187bff;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    transition:0.3s;
}

/* SLIDE DOWN */
@keyframes slideDown {
    0% {opacity:0; transform:translateY(-60px);}
    60% {opacity:1; transform:translateY(10px);}
    100% {transform:translateY(0);}
}

/* CONTAINER */
.container{
    width:1000px;
    background:white;
    padding:80px 70px;
    border-radius:40px;
    box-shadow:0 15px 40px rgba(0,0,0,0.20);
    animation:slideDown 0.9s cubic-bezier(0.16, 1, 0.3, 1);
}

/* TITLE */
h2{
    text-align:center;
    margin-bottom:30px;
    font-size:32px;
    color:#333;
}

/* FLOATING LABEL GROUP */
.group{
    position:relative;
    margin:22px 0;
}
.group input, .group select{
    width:100%;
    padding:16px 14px;
    border-radius:10px;
    border:1px solid #bbb;
    background:transparent;
    font-size:17px;
    transition:0.3s;
}
.group input:focus, .group select:focus{
    border-color:#4CAF50;
    box-shadow:0 0 8px rgba(76,175,80,0.3);
}

/* FLOATED LABEL */
.group label{
    position:absolute;
    top:50%;
    left:16px;
    transform:translateY(-50%);
    color:#777;
    pointer-events:none;
    transition:0.3s;
    background:white;
    padding:0 6px;
}
.group input:focus + label,
.group input:not(:placeholder-shown) + label,
.group select:focus + label,
.group select option:checked + label{
    top:-8px;
    font-size:13px;
    color:#4CAF50;
}

/* PASSWORD TOGGLE */
.password-field{
    position:relative;
}
.toggle-btn{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    font-size:18px;
    color:#555;
    transition:0.3s;
}

/* SUBMIT BUTTON */
input[type=submit]{
    width:100%;
    background:#4CAF50;
    border:none;
    padding:16px;
    margin-top:20px;
    font-size:18px;
    color:white;
    border-radius:12px;
    cursor:pointer;
    transition:0.3s;
}
input[type=submit]:hover{
    background:#43a047;
    transform:scale(1.03);
    box-shadow:0 5px 12px rgba(0,0,0,0.2);
}

/* ERROR MESSAGE */
.message{
    text-align:center;
    color:#d32f2f;
    margin-bottom:18px;
    font-weight:bold;
}

/* DARK MODE */
.dark{background:#111;color:white;}
.dark .container{background:#222;}
.dark input, .dark select{background:#333;color:white;border:1px solid #444;}
.dark label{background:#222 !important;}

/* SMALL DARK MODE BUTTON */
.theme-toggle{
    position:absolute;
    top:20px;
    right:20px;
    padding:8px 12px;
    border:none;
    background:#333;
    color:white;
    font-size:14px;
    border-radius:8px;
    cursor:pointer;
    transition:0.3s;
}
.theme-toggle:hover{
    background:#555;
}
</style>

<script>
function togglePassword(){
    let pass=document.getElementById("password");
    let btn=document.getElementById("toggleBtn");
    if(pass.type==="password"){
        pass.type="text";
        btn.textContent="🙈";
    }else{
        pass.type="password";
        btn.textContent="👁";
    }
}
function toggleTheme(){
    document.body.classList.toggle("dark");
    localStorage.setItem("theme", document.body.classList.contains("dark")?"dark":"light");
}
window.onload=function(){
    if(localStorage.getItem("theme")==="dark")
        document.body.classList.add("dark");
}
</script>
</head>

<body>

<button class="theme-toggle" onclick="toggleTheme()">🌙</button>

<div class="container">
<h2> Welcome to Login page </h2>

<?php if($message) echo "<div class='message'>$message</div>"; ?>

<form method="POST">

<!-- USERNAME -->
<div class="group">
    <input type="text" name="username" required placeholder=" ">
    <label>Username</label>
</div>

<!-- PASSWORD -->
<div class="group password-field">
    <input type="password" name="password" id="password" required placeholder=" ">
    <label>Password</label>
    <span class="toggle-btn" id="toggleBtn" onclick="togglePassword()">👁</span>
</div>

<!-- ROLE -->
<div class="group">
    <select name="role" required>
        <option value="" disabled selected hidden></option>
        <option value="admin">Admin</option>
        <option value="teacher">Teacher</option>
        <option value="student">Student</option>
    </select>
    <label>Role</label>
</div>

<input type="submit" name="login" value="Login">

</form>
</div>

</body>
</html>
