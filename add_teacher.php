<?php
session_start();
include "../includes/config.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($name == "" || $username == "" || $password == "") {
        $message = "All fields are required!";
    } else {
        // Check username exists
        $check = mysqli_query($conn, "SELECT id FROM teacher WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            $message = "Username already exists!";
        } else {
            $insert = mysqli_query($conn,
                "INSERT INTO teacher (name, username, password)
                 VALUES ('$name', '$username', '$password')");
            if ($insert) {
                $message = "Teacher added successfully!";
            } else {
                $message = "Database error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Teacher</title>
</head>
<body>
<h2>Add New Teacher</h2>

<p style="color:red;"><?php echo $message; ?></p>

<form method="post">
    <label>Name:</label><br>
    <input type="text" name="name"><br><br>

    <label>Username:</label><br>
    <input type="text" name="username"><br><br>

    <label>Password:</label><br>
    <input type="password" name="password"><br><br>

    <button type="submit">Add Teacher</button>
</form>

</body>
</html>
