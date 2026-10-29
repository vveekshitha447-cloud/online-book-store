<?php
function get_reads_per_book($conn) {
    $sql = "SELECT b.title, COUNT(r.id) AS total_reads
            FROM book_reads r
            JOIN books b ON b.id = r.book_id
            GROUP BY r.book_id
            ORDER BY total_reads DESC
            LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_reads_per_day($conn) {
    $sql = "SELECT DATE(read_at) AS day, COUNT(*) AS total
            FROM book_reads
            WHERE read_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(read_at)
            ORDER BY day ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_top_readers($conn) {
    $sql = "SELECT u.email, COUNT(r.id) AS total_reads
            FROM book_reads r
            JOIN admin u ON u.id = r.user_id
            GROUP BY r.user_id
            ORDER BY total_reads DESC
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}