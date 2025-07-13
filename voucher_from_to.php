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

// Initialize $vouchers as an empty array
$vouchers = [];

// Get the voucher number from the form
$frvno = isset($_POST['frvno']) && is_numeric($_POST['frvno']) ? intval($_POST['frvno']) : '';
$tovno = isset($_POST['tovno']) && is_numeric($_POST['tovno']) ? intval($_POST['tovno']) : '';

// Debugging - Check if the voucher number is set
echo "Voucher Number: " . $frvno . "<br>";

if ($frvno) {
    // Fetch all records for the given voucher number
    $query = "SELECT * FROM voucher WHERE vno BETWEEN '$frvno' AND '$tovno' AND status=1 ORDER BY vno";

    // Debugging - Print the query
    echo "Query: " . $query . "<br>";

    $result = $conn->query($query);

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $vouchers[] = $row;
    }
} else {
    echo "Please enter a valid voucher number.<br>";
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>

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
            <h1 class="page-title">Voucher Report by Voucher Number</h1>
            <!-- Form for selecting voucher number -->
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" class="noprint">
                <!-- Only Voucher Number From Input -->
                <label for="vno">Voucher Number From:
                <input type="number" name="frvno" id="frvno" required></label>
                <!-- Only Voucher Number To Input -->
                <label for="vno">Voucher Number To:
                <input type="number" name="tovno" id="tovno" required></label>

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
                        <th>Date</th>
                        <th>Paid Month</th>
                        <th>Paid Year</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;  $total_amount=0; foreach ($vouchers as $voucher): ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td><?= $voucher['vno'] ?></td>
                        <td><?= $voucher['vtype'] ?></td>
                        <td><?= $voucher['employeetype'] ?></td>
                        <td><?= $voucher['paymode'] ?></td>                       
                        <td><?= date('d-m-Y',strtotime($voucher['date'])) ?></td>
                        <td><?= $voucher['month'] ?></td>
                        <td><?= $voucher['year'] ?></td>
                        <td><?= $voucher['amount'] ?></td>
                    </tr>                  
                  <?php 
                    $total_amount+= (float)$voucher['amount'];
                    $i++; endforeach; ?>
                </tbody>                
                <tr>
                    <td colspan="7"></td>
                    <td> </td>
                    <td><strong><div style="color:#00F" align="right">Total : <?php echo number_format($total_amount, 2); ?></div></strong></td>
                </tr>
            </table>
            
            <!-- Print Button -->
            <button class="print-button btn btn-primary" onclick="printPage()">Print Report</button>
        </div>
    </div>
</div>
</div>

<script>
// Function to print the page content
function printPage() {
    // Get the content to print
    const content = document.getElementById('printableContent').cloneNode(true);

    // Hide the print button in the cloned content
    const printButton = content.querySelector('.print-button');
    const infobtn = content.querySelector('.dataTables_info');
    const noprintbtn = content.querySelector('.noprint');
    const lenbtn = content.querySelector('.dataTables_length');
    const allbtn = content.querySelector('.dt-buttons');
    const filterbtn = content.querySelector('.dataTables_filter');
    const pagebtn = content.querySelector('.dataTables_paginate');

    // Hide unnecessary elements
    if (printButton) printButton.style.display = 'none';
    if (infobtn) infobtn.style.display = 'none';
    if (noprintbtn) noprintbtn.style.display = 'none';
    if (lenbtn) lenbtn.style.display = 'none';
    if (allbtn) allbtn.style.display = 'none';
    if (filterbtn) filterbtn.style.display = 'none';
    if (pagebtn) pagebtn.style.display = 'none';

    // Open a new print window
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Print Report</title>
                <style>
                    @media print {
                        body { font-family: "Times New Roman", Times, serif; }
                    }
                </style>
            </head>
            <body>
                ${content.outerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();

    // Trigger the print dialog
    printWindow.print();

    // Close the print window after printing
    printWindow.onafterprint = () => printWindow.close();
}
</script>

</body>
</html>
