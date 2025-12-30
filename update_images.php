<?php
require 'config/database.php';

// Add column if not exists
$check = mysqli_query($conn, "SHOW COLUMNS FROM barang LIKE 'gambar'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "ALTER TABLE barang ADD COLUMN gambar VARCHAR(255) DEFAULT NULL");
    echo "Column 'gambar' added.<br>";
}

// Update images
mysqli_query($conn, "UPDATE barang SET gambar='laptop.png' WHERE nama_barang LIKE '%Laptop%'");
mysqli_query($conn, "UPDATE barang SET gambar='mouse.png' WHERE nama_barang LIKE '%Mouse%'");
// Use placeholders or mouse image for others temporarily if needed, or leave null to show default icon
// mysqli_query($conn, "UPDATE barang SET gambar='paper.png' WHERE nama_barang LIKE '%Kertas%'"); 

echo "Images updated successfully.";
?>
