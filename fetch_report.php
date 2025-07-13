<?php
session_start(); 
// If session variable is not set it will redirect to login page
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location:index.php");
  exit;
}
// Database connection (replace with your actual connection details)
include'config.php';

// Fetch data from voucher table based on month and year
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

$query = "SELECT * FROM voucher WHERE 1=1";

// Apply filter for month and year if provided
if ($month != '') {
    $query .= " AND month = '$month'";
}
if ($year != '') {
    $query .= " AND year = '$year'";
}

$result = $conn->query($query);

$data = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Return data as JSON
echo json_encode($data);

$conn->close();
?>
