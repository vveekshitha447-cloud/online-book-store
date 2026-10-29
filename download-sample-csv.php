<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="bulk-upload-sample.csv"');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, ['title', 'author_id', 'category_id', 'description']);

// Sample rows
fputcsv($output, ['Atomic Habits', '13', '5', 'A practical guide to building good habits by James Clear']);
fputcsv($output, ['The Kite Runner', '23', '7', 'A powerful story set in Afghanistan by Khaled Hosseini']);
fputcsv($output, ['Clean Code', '15', '2', 'Best practices for writing clean and maintainable code']);

fclose($output);
exit;
?>