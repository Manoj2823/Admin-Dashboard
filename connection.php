<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host     = "localhost";
$username = "root";
$password = "";
$database = "login_db";

$conn = new mysqli($host, $username, $password, $database, 3307);

if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;color:red;padding:20px;'>
        <strong>Connection Failed:</strong> " . $conn->connect_error . "
    </div>");
}

$conn->set_charset("utf8mb4");
?>
