<?php
include 'config.php';

// Fetch All Active Records
$result = $conn->query("SELECT id, plotno, plname, rno, rdate, month, year, amount FROM receipts WHERE status = 1 ORDER BY rno DESC");
$receipts = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($receipts);
?>
