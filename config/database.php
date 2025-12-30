<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'uas_sopian';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function base_url($path = '') {
    // Adjust this if your folder structure is different
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    // Assuming the folder name matches the path
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    // Clean up slashes
    $baseUrl = $protocol . "://" . $host . $scriptDir;
    return $path; 
}

// Generate HSL color based on input (ID or String) significantly reducing collisions
function getCategoryStyle($input) {
    if (!$input) return 'background-color: #f3f4f6; color: #1f2937;';
    
    // If input is numeric (ID), use it directly. If string, hash it.
    if (is_numeric($input)) {
        $seed = (int)$input;
    } else {
        $seed = crc32($input);
    }
    
    // Golden Angle approximation (137.508 degrees)
    // This distributes colors evenly around the wheel for sequential integers
    $hue = ($seed * 137.508) % 360;
    
    // Ensure positive
    if ($hue < 0) $hue += 360;
    
    $s = 85; // High saturation
    $l = 92; // High lightness for background
    $l_text = 25; // Dark text
    
    return "background-color: hsl($hue, $s%, $l%); color: hsl($hue, $s%, $l_text%);";
}
?>
