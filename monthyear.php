<?php
session_start(); 
// If session variable is not set it will redirect to login page
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location:index.php");
  exit;
}
ob_start();
// Database connection
include 'config.php';
include 'header.php';
include 'sidemenu.php';

// Fetch data from voucher table based on month and year
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

$query = "SELECT * FROM voucher WHERE 1=1 AND status=1";

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

 <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Include DataTable CSS and jQuery -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <h1 class="page-title">Voucher Report by Month and Year</h1>

            <!-- Dropdowns for selecting month and year -->
            <label for="month">Month:</label>
            <select id="month">
                <option value="">All</option>
                <option value="01">January</option>
                <option value="02">February</option>
                <option value="03">March</option>
                <option value="04">April</option>
                <option value="05">May</option>
                <option value="06">June</option>
                <option value="07">July</option>
                <option value="08">August</option>
                <option value="09">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>

            <label for="year">Year:</label>
            <input type="number" min="1990" name="year" id="year" required>

            <!-- Submit Button -->
            <button id="submit-btn" class="btn btn-primary">Submit</button>

            <!-- Table for displaying report data -->
            <table id="voucher-report" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Voucher No</th>
                        <th>Voucher Type</th>
                        <th>Employee Type</th>
                        <th>Payment Mode</th>
                        <th>Payment Details</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Month</th>
                        <th>Year</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Initialize DataTable
    var table = $('#voucher-report').DataTable();

    // Function to load data based on selected filters
    function loadData() {
        var month = $('#month').val();
        var year = $('#year').val();

        $.ajax({
            url: 'voucher_report.php',  // Correct PHP script to process the request
            type: 'GET',
            data: {
                month: month,
                year: year
            },
            success: function(data) {
                // Clear the current table data
                table.clear();

                // Parse the returned JSON data and add it to the table
                var reportData = JSON.parse(data);
                reportData.forEach(function(item) {
                    table.row.add([
                        item.id,
                        item.vno,
                        item.vtype,
                        item.employeetype,
                        item.paymode,
                        item.paydetails,
                        item.amount,
                        item.date,
                        item.month,
                        item.year
                    ]).draw();
                });
            }
        });
    }

    // Load initial data without filters
    loadData();

    // Event listener for Submit button
    $('#submit-btn').on('click', function() {
        loadData();
    });
</script>
