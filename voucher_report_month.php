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
$vouchers = [];
$selectedMonth = '';

// Month names array for the dropdown
$months = [
    '' => 'Select-Month', 
    'January' => 'January', 'February' => 'February', 'March' => 'March', 
    'April' => 'April', 'May' => 'May', 'June' => 'June', 
    'July' => 'July', 'August' => 'August', 'September' => 'September', 
    'October' => 'October', 'November' => 'November', 'December' => 'December'
];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch and sanitize selected month
    $selectedMonth = isset($_POST['month']) ? trim($_POST['month']) : '';

    if ($selectedMonth !== '') {
        // Debugging: Print the selected month value
        echo "Month selected: $selectedMonth<br>";

        // Query to fetch data based on the selected month (using prepared statement)
        $query = "SELECT * FROM voucher WHERE month = ? AND status = 1 ORDER BY vno";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $selectedMonth);
    } else {
        // If no month is selected, fetch all records
        $query = "SELECT * FROM voucher WHERE status = 1 ORDER BY vno";
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
        $vouchers[] = $row;
    }

    // Check if vouchers are fetched
    if (empty($vouchers)) {
        echo "<p style='color: red;'>No data found for the selected month.</p>";
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
}
</style>

<div id="printableContent">
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <h1 class="page-title">Voucher Report by Month</h1>
                <!-- Form for selecting month -->
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" class="noprint">
                    <!-- Dropdown for selecting month -->
                    <label for="month">Month:
                    <select class="form-control" name="month" id="month" required>
                        <?php foreach ($months as $monthValue => $monthName): ?>
                            <option value="<?= $monthValue ?>" <?= (isset($_POST['month']) && $_POST['month'] == $monthValue) ? 'selected' : '' ?>><?= $monthName ?></option>
                        <?php endforeach; ?>
                    </select>
                    </label>
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>

                <br />
                <!-- Table for displaying report data -->
                <table id="voucherdata" border="1" cellpadding="10" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>VNo</th>
                            <th>V-Type</th>
                            <th>Employee Type</th>
                            <th>Pay Mode</th>                        
                            <th>Paid_Date</th>
                            <th>Paid Month</th>
                            <th>Paid Year</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $total_amount = 0;
                        foreach ($vouchers as $voucher): ?>
                            <tr>
                                <td><?= $i ?></td>
                                <td><?= $voucher['vno'] ?></td>
                                <td><?= $voucher['vtype'] ?></td>
                                <td><?= $voucher['employeetype'] ?></td>
                                <td><?= $voucher['paymode'] ?></td>                       
                                <td><?= date('d-m-Y',strtotime($voucher['date'])) ?></td>
                                <td><?= $voucher['month'] ?></td> <!-- month is now stored as name -->
                                <td><?= $voucher['year'] ?></td>
                                <td><?= $voucher['amount'] ?></td>
                            </tr>                  
                        <?php 
                        $total_amount += (float)$voucher['amount'];
                        $i++; endforeach; ?>
                    </tbody>                
                    <td colspan="7"></td>
                    <td><strong><div style="color:#00F" align="right">Total : </div></strong></td>
                    <td><strong><span style="color:#00F" id="totalAmount"><?php echo number_format($total_amount, 2); ?></span></strong></td> 
                </table>
                <button class="print-button btn btn-primary" onclick="printPage()">Print Report</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    let totalAmount = <?php echo $total_amount; ?>; // Pass the total amount from PHP to JS

    $('#voucherdata').DataTable({
        dom: 'Bflrtip',
        buttons: [
            'copy', 'csv',
            {
                extend: 'excelHtml5',
                text: 'Excel',
                footer: true,
                customize: function (xlsx) {
                    const sheet = xlsx.xl.worksheets['sheet1.xml'];
                    const sheetData = $(sheet).find('sheetData');
                    const rows = sheetData.find('row');
                    const lastRowIndex = parseFloat(rows.last().attr('r'), 10);
                    const formattedTotalAmount = parseFloat(totalAmount).toFixed(2);
                    const totalAmountRow = `
                        <row r="${lastRowIndex + 1}">
                            <c t="inlineStr" r="H${lastRowIndex + 1}">
                                <is><t>Total:</t></is>
                            </c>
                            <c t="n" r="I${lastRowIndex + 1}">
                                <v>${formattedTotalAmount}</v>
                            </c>
                        </row>
                    `;
                    sheetData.append(totalAmountRow);
                }
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
