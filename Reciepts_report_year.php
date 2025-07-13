<?php
session_start(); 

// Redirect to login if session is not active
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location:index.php");
    exit;
}

ob_start();

// Database connection
include 'config.php';
include 'header.php';
include 'sidemenu.php';

// Initialize variables
$receipts = [];
$selectedYear = '';

// Fetch unique years from the database for the dropdown
$years = [
    '' => 'Select-Year'
];

// Query to get distinct years from the receipts table
$yearQuery = "SELECT DISTINCT year FROM receipts WHERE status = 1 ORDER BY year DESC";
$result = $conn->query($yearQuery);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $years[$row['year']] = $row['year'];
    }
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch and sanitize selected year
    $selectedYear = isset($_POST['year']) ? trim($_POST['year']) : '';

    if ($selectedYear !== '') {
        // Query to fetch data based on the selected year
        $query = "SELECT * FROM receipts WHERE year = ? AND status = 1 ORDER BY rno";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $selectedYear);
    } else {
        // If no year is selected, fetch all records
        $query = "SELECT * FROM receipts WHERE status = 1 ORDER BY rno";
        $stmt = $conn->prepare($query);
    }

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    // Fetch the data into an array
    while ($row = $result->fetch_assoc()) {
        $receipts[] = $row;
    }

    $stmt->close();
}
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- CSS for DataTable -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- DataTables Buttons -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>

<!-- jsPDF Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- jsPDF html2pdf (for PDF export support) -->
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.pdf.min.js"></script>

<!-- JS for jQuery and DataTable -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<style>
@media print {
    .print-button {
        display: none;
    }
    .dataTables_info, .dataTables_length, .dt-buttons, .dataTables_filter, .dataTables_paginate {
        display: none;
    }
}
</style>

<div id="printableContent">
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <h1 class="page-title">
                    Receipt Report by Year 
                    <?php if (!empty($selectedYear)): ?>
                        - <?= htmlspecialchars($selectedYear) ?>
                    <?php endif; ?>
                </h1>
                <!-- Form for selecting year -->
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" class="noprint">
                    <!-- Dropdown for selecting year -->
                    <label for="year">Year:
                    <select class="form-control" name="year" id="year" required>
                        <?php foreach ($years as $yearValue => $yearName): ?>
                            <option value="<?= $yearValue ?>" <?= (isset($_POST['year']) && $_POST['year'] == $yearValue) ? 'selected' : '' ?>><?= $yearName ?></option>
                        <?php endforeach; ?>
                    </select>
                    </label>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>

                <br />
                <!-- Table for displaying report data -->
                <table id="receiptdata" border="1" cellpadding="10" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Receipt No</th>
                            <th>Plot Name</th>
                            <th>Receipt Date</th>
                            <th>Paid Month</th> <!-- Month column -->
                            <th>Paid Year</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        foreach ($receipts as $receipt): ?>
                            <tr>
                                <td><?= $i ?></td>
                                <td><?= $receipt['rno'] ?></td>
                                <td><?= $receipt['plname'] ?></td>
                                <td><?= date('d-m-Y', strtotime($receipt['rdate'])) ?></td>
                                <td><?= $receipt['month'] ?></td> <!-- Display Month -->
                                <td><?= $receipt['year'] ?></td>
                                <td><?= number_format($receipt['amount'], 2) ?></td>
                            </tr>                   
                        <?php $i++; endforeach; ?>
                    </tbody>                
                </table>
                <button class="print-button btn btn-primary" onclick="printPage()">Print Report</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#receiptdata').DataTable({
        dom: 'Bflrtip',
        buttons: [
            'copy', 'csv',
            {
                extend: 'excelHtml5',
                text: 'Excel',
                footer: false  // Disable footer for total amount
            }
        ]
    });
});

function printPage() {
    const content = document.getElementById('printableContent').cloneNode(true);
    const printButton = content.querySelector('.print-button');
    const infobtn = content.querySelector('.dataTables_info');
    const noprintbtn = content.querySelector('.noprint');
    const lenbtn = content.querySelector('.dataTables_length');
    const allbtn = content.querySelector('.dt-buttons');
    const filterbtn = content.querySelector('.dataTables_filter');
    const pagebtn = content.querySelector('.dataTables_paginate');

    if (printButton) {
        printButton.style.display = 'none';
        infobtn.style.display = 'none';
        noprintbtn.style.display = 'none';
        lenbtn.style.display = 'none';
        allbtn.style.display = 'none';
        filterbtn.style.display = 'none';
        pagebtn.style.display = 'none';
    }

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Print Report</title>
                <style>
                    @media print {
                        body {
                            font-family:"Times New Roman", Times, serif;
                        }
                    }
                </style>
            </head>
            <body>
                ${content.outerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
    printWindow.onafterprint = () => printWindow.close();
}
</script>