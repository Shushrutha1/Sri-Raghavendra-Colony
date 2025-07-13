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

// Fetch month and year from the form input
$month = isset($_POST['month']) && !empty($_POST['month']) ? $_POST['month'] : '';
$year = isset($_POST['year']) && !empty($_POST['year']) ? $_POST['year'] : '';

// Fetch all records based on the selected month and year
$query = "SELECT * FROM voucher WHERE month='$month' AND year='$year' AND status=1 ORDER BY vno";
$result = $conn->query($query);

if (!$result) {
    die("SQL Error: " . $conn->error);
}

$vouchers = [];
while ($row = $result->fetch_assoc()) {
    $vouchers[] = $row;
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>

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
                <h1 class="page-title">Voucher Report by Year and Month</h1>
                <!-- Form for selecting month and year -->
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" class="noprint">
                    <!-- Dropdown for selecting month -->
                    <label for="month">Month:</label>
                    <select name="month" id="month" required>
                        <?php
                        $monthNames = [
                            '' => 'Select-Month', 
                            'January' => 'January', 
                            'February' => 'February', 
                            'March' => 'March', 
                            'April' => 'April', 
                            'May' => 'May', 
                            'June' => 'June', 
                            'July' => 'July', 
                            'August' => 'August', 
                            'September' => 'September', 
                            'October' => 'October', 
                            'November' => 'November', 
                            'December' => 'December'
                        ];

                        foreach ($monthNames as $key => $month) {
                            echo "<option value='$key' " . ($key == $month ? "selected" : "") . ">$month</option>";
                        }
                        ?>
                    </select>
                    
                    <!-- Dropdown for selecting year -->
                    <label for="year">Year:</label>
                    <select name="year" id="year" required>
                        <option value="">Select Year</option>
                        <?php
                        // Get the available years from the database (or define a range)
                        $yearQuery = "SELECT DISTINCT year FROM voucher ORDER BY year DESC";
                        $yearResult = $conn->query($yearQuery);
                        while ($yearRow = $yearResult->fetch_assoc()) {
                            $yearVal = $yearRow['year'];
                            echo "<option value='$yearVal' " . ($yearVal == $year ? "selected" : "") . ">$yearVal</option>";
                        }
                        ?>
                    </select>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <br />

                <!-- Table for displaying voucher data -->
                <table id="voucherdata" border="1" cellpadding="10" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Voucher No</th>
                            <th>Voucher Type</th>
                            <th>Employee Type</th>
                            <th>Pay Mode</th>
                            <th>Pay Details</th>
                            <th>Amount</th>
                            <th>Date</th>
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
                                <td><?= $voucher['paydetails'] ?></td>
                                <td><?= $voucher['amount'] ?></td>
                                <td><?= date('d-m-Y',strtotime($voucher['date'])) ?></td>
                            </tr>                  
                        <?php 
                        $total_amount += (float)$voucher['amount'];
                        $i++; endforeach; ?>
                    </tbody>                
                    <td colspan="7"></td>
                    <td><strong><div style="color:#00F" align="right">Total : <?php echo number_format($total_amount, 2); ?></div></strong></td>
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
    if (printButton) {
        printButton.style.display = 'none';
        infobtn.style.display = 'none';
        noprintbtn.style.display = 'none';
        lenbtn.style.display = 'none';
        allbtn.style.display = 'none';
        filterbtn.style.display = 'none';
        pagebtn.style.display = 'none';
    }

    // Open a new print window
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

    // Trigger the print dialog
    printWindow.print();

    // Close the print window after printing
    printWindow.onafterprint = () => printWindow.close();
}
</script>

</body>
</html>
