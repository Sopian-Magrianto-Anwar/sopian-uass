<?php
require 'config/database.php';

// Add avatar column to users if not exists
$check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'avatar'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
    echo "Column 'avatar' added to users table.<br>";
}

echo "Database updated successfully.";
?>
