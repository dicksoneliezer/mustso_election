<?php
// hash_password.php - Run this once and delete it
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed password: " . $hashed;
echo "\n\nCopy this SQL:\n";
echo "UPDATE admin SET password = '$hashed' WHERE username = 'admin';";
?>