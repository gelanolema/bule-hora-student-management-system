<?php
session_start();
include("../includes/config.php");

if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    header("Location: ../index.php");
    exit();
}

$message = "";

/* ---------------- ADD TEACHER ---------------- */
if(isset($_POST['add_teacher'])){
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $username = mysqli_real_escape_string($conn,$_POST['username']);
    $password = $_POST['password'];

    $insert = mysqli_query($conn,
        "INSERT INTO teacher(name,username,password,role) VALUES('$name','$username','$password','teacher')");
    $message = $insert ? "Teacher added successfully!" : "Error: ".mysqli_error($conn);
}

/* ---------------- DELETE TEACHER ---------------- */
if(isset($_POST['delete_teacher'])){
    $id = intval($_POST['teacher_id']);
    mysqli_query($conn,"DELETE FROM teacher WHERE id=$id");
    $message = "Teacher deleted!";
}

/* ---------------- ADD STUDENT ---------------- */
if(isset($_POST['add_student'])){
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $username = mysqli_real_escape_string($conn,$_POST['username']);
    $password = $_POST['password'];

    $insert = mysqli_query($conn,
        "INSERT INTO student(name,username,password) VALUES('$name','$username','$password')");
    $message = $insert ? "Student added successfully!" : "Error: ".mysqli_error($conn);
}

/* ---------------- DELETE STUDENT ---------------- */
if(isset($_POST['delete_student'])){
    $id = intval($_POST['student_id']);
    mysqli_query($conn,"DELETE FROM student WHERE id=$id");
    $message = "Student deleted!";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<style>
/* ---------- GENERAL ---------- */
body {
    font-family: Arial, sans-serif;
    margin:0;
    padding:0;
    background: #f0f2f5;
    transition:0.3s;
}

/* ---------- NAVBAR ---------- */
.nav {
    background: #4CAF50;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.nav h1 {
    color: white;
    font-size:28px;
    margin:0;
    text-align:center;
}
.nav .profile {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
}
.nav .profile img {
    width:60px;
    height:60px;
    border-radius:50%;
    border:3px solid #fff;
    object-fit: cover;
    box-shadow: 0 3px 12px rgba(0,0,0,0.3);
    transition:0.3s;
}
.nav .profile img:hover {
    transform: scale(1.1);
}
.nav .actions {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    gap:10px;
}
.nav .actions button {
    background: transparent;
    border: none;
    font-size:22px;
    cursor:pointer;
    color:white;
}
.nav .actions a {
    text-decoration:none;
    color:white;
    background:#2e7d32;
    padding:8px 14px;
    border-radius:8px;
}
.nav .actions a:hover, .nav .actions button:hover {opacity:0.8;}

/* ---------- MESSAGE ---------- */
.message {
    text-align:center;
    color:#0a7a20;
    font-size:18px;
    font-weight:bold;
    margin:15px;
}

/* ---------- TABS ---------- */
.tabs {
    display:flex;
    justify-content:center;
    gap:15px;
    margin:20px 0;
}
.tabs button {
    padding:14px 20px;
    font-size:16px;
    background:#3a3a3a;
    color:white;
    border:none;
    border-radius:12px;
    cursor:pointer;
    transition:0.3s;
}
.tabs button.active {background:#4CAF50;}
.tabs button:hover {opacity:0.85;}

/* ---------- SECTIONS ---------- */
.section {
    display:none;
    max-width:1100px;
    margin:0 auto 40px auto;
    display:flex;
    gap:20px;
    flex-wrap:wrap;
}
.card-form, .card-table {
    background:white;
    border-radius:12px;
    box-shadow:0 6px 20px rgba(0,0,0,0.08);
    padding:20px;
}
.card-form {flex:1 1 280px;}
.card-table {flex:3 1 700px; overflow-x:auto;}
.card-table table {width:100%; border-collapse: collapse;}
.card-table th, .card-table td {
    padding:12px; border:1px solid #ddd; text-align:center;
}
.card-table th {background:#4CAF50; color:white;}
.card-table tr:hover {background:#f1f7f5;}

/* ---------- FORM ---------- */
input, select {width:100%; padding:12px; margin:8px 0; border-radius:8px; border:1px solid #ccc;}
input:focus, select:focus {border-color:#4CAF50; outline:none;}
input[type=submit], td button {
    background:#4CAF50;
    color:white;
    border:none;
    border-radius:8px;
    padding:8px 16px;
    cursor:pointer;
    transition:0.3s;
}
input[type=submit]:hover, td button:hover {opacity:0.9; transform:scale(1.05);}

/* ---------- FOOTER ---------- */
.footer {
    background:#4CAF50;
    color:white;
    text-align:center;
    padding:20px 10px;
    font-size:16px;
    border-radius:12px 12px 0 0;
}

/* ---------- DARK MODE ---------- */
.dark {background:#121212; color:#eee;}
.dark .nav {background:#1b5e20;}
.dark .actions a, .dark .actions button {color:white;}
.dark .card-form, .dark .card-table {background:#1e1e1e;}
.dark table th {background:#333;}
.dark table td {color:white;}
.dark input, .dark select {background:#333; color:white; border:1px solid #555;}
</style>

<script>
function openTab(id){
    let sec=document.getElementsByClassName("section");
    let btn=document.getElementsByClassName("tabBtn");
    for(let i=0;i<sec.length;i++){
        sec[i].style.display="none";
        btn[i].classList.remove("active");
    }
    document.getElementById(id).style.display="flex";
    event.currentTarget.classList.add("active");
}
function toggleTheme(){
    document.body.classList.toggle("dark");
    localStorage.setItem("theme", document.body.classList.contains("dark")?"dark":"light");
}
window.onload=function(){
    if(localStorage.getItem("theme")==="dark") document.body.classList.add("dark");
}
</script>
</head>

<body>

<div class="nav">
    <div class="profile">
        <img src="../uploads/default_admin.png" alt="Admin Profile">
    </div>
    <h1>Admin Dashboard</h1>
    <div class="actions">
        <button onclick="toggleTheme()">🌙</button>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<?php if($message) echo "<div class='message'>$message</div>"; ?>

<div class="tabs">
    <button class="tabBtn active" onclick="openTab('teachers')">Manage Teachers</button>
    <button class="tabBtn" onclick="openTab('students')">Manage Students</button>
</div>

<!-- TEACHERS -->
<div id="teachers" class="section" style="display:flex;">
    <div class="card-form">
        <h2>Add Teacher</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" name="add_teacher" value="Add Teacher">
        </form>
    </div>
    <div class="card-table">
        <h2>Teachers List</h2>
        <table>
            <tr><th>ID</th><th>Name</th><th>Username</th><th>Action</th></tr>
            <?php
            $teachers = mysqli_query($conn,"SELECT * FROM teacher WHERE role='teacher'");
            while($t=mysqli_fetch_assoc($teachers)){
            echo "<tr>
            <td>{$t['id']}</td>
            <td>{$t['name']}</td>
            <td>{$t['username']}</td>
            <td>
            <form method='POST'>
            <input type='hidden' name='teacher_id' value='{$t['id']}'>
            <input type='submit' name='delete_teacher' value='Delete'>
            </form>
            </td>
            </tr>";
            }
            ?>
        </table>
    </div>
</div>

<!-- STUDENTS -->
 
<div id="students" class="section">
    <div class="card-form">
        <h2>Add Student</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" name="add_student" value="Add Student">
        </form>
    </div>
    <div class="card-table">
        <h2>Students List</h2>
        <table>
            <tr><th>ID</th><th>Name</th><th>Username</th><th>Action</th></tr>
            <?php
            $students = mysqli_query($conn,"SELECT * FROM student");
            while($s=mysqli_fetch_assoc($students)){
            echo "<tr>
            <td>{$s['id']}</td>
            <td>{$s['name']}</td>
            <td>{$s['username']}</td>
            <td>
            <form method='POST'>
            <input type='hidden' name='student_id' value='{$s['id']}'>
            <input type='submit' name='delete_student' value='Delete'>
            </form>
            </td>
            </tr>";
            }
            ?>
        </table>
    </div>
</div>

<div class="footer">
My School Management System © <?php echo date("Y"); ?> | Designed for easy management
</div>

</body>
</html>
