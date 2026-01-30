<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "healthcare_db";
$port = 3308;

$conn = new mysqli($servername, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>