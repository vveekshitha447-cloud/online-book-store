<?php
$sName   = "localhost";
$uName   = "root";
$pass    = "";
$db_name = "onlinebookstore _db";

try {
    $conn = new PDO(
        "mysql:host=$sName;dbname=$db_name;charset=utf8mb4",
        $uName,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // TRUE prepared statements = SQL injection protection
        ]
    );
} catch(PDOException $e) {
    // Never expose DB errors to users in production
    error_log("DB Connection failed: " . $e->getMessage());
    die("Something went wrong. Please try again later.");
}