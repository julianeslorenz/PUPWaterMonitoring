<?php
$host = 'localhost'; // or your actual host
$db   = 'pup_water_monitoring'; // change to your database name
$user = 'root';       // change to your DB user
$pass = '';   // change to your DB password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
