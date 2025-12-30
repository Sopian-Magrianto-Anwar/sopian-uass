<?php
// AMBIL DATA OTOMATIS DARI RAILWAY (Jangan diubah-ubah lagi)
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'uas_sopian';
$port = getenv('MYSQLPORT') ?: '3306';

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function base_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $baseUrl = $protocol . "://" . $host . ($scriptDir == '/' ? '' : $scriptDir);
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

function getCategoryStyle($input) {
    if (!$input) return 'background-color: #f3f4f6; color: #1f2937;';
    $seed = is_numeric($input) ? (int)$input : crc32($input);
    $hue = ($seed * 137.508) % 360;
    if ($hue < 0) $hue += 360;
    return "background-color: hsl($hue, 85%, 92%); color: hsl($hue, 85%, 25%);";
}
?>
