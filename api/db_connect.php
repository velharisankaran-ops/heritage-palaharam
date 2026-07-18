<?php
// Hostinger Database Configuration
$servername = "localhost"; // Usually localhost on Hostinger
$username = "YOUR_DB_USERNAME"; // Replace with your MySQL username
$password = "YOUR_DB_PASSWORD"; // Replace with your MySQL password
$dbname = "YOUR_DB_NAME";       // Replace with your MySQL database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
