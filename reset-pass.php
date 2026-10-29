<?php
include "db_conn.php";
$hash = password_hash('123&45', PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = 1");
$stmt->execute([$hash]);
echo "Done! Now login with 123&45";