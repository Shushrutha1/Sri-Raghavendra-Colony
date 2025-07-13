<?php
session_start();
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: index.php");
    exit;
}

include 'config.php';

// Fetch all receipts where status = 1
$sql = "SELECT id, plotno, plname, rno, rdate, month, year, amount FROM receipts WHERE status = 1 ORDER BY rno DESC";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row; // Add each row to the data array
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    "data" => $data // DataTables expects a "data" key
]);
exit;
