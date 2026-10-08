<?php
// Change the password here to whatever you want
$plain_password = "admin123";  

// Generate bcrypt hash
$hash = password_hash($plain_password, PASSWORD_DEFAULT);

// Display the hash
echo "Password: $plain_password<br>";
echo "Hash: $hash";
echo "Teacher password hash: " . password_hash("teach123", PASSWORD_DEFAULT) . "<br>";
echo "Student password hash: " . password_hash("stud123", PASSWORD_DEFAULT) . "<br>";
?>
