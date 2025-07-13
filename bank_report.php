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

// Initialize variables
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$opening_balance = 0;
$transactions = [];

if ($start_date && $end_date) {
    // Calculate opening balance (up to the entered start date)
    $opening_query = "
        SELECT 
            (SELECT COALESCE(SUM(amount), 0) FROM receipts WHERE rdate <= '$start_date' AND status = 1) AS total_receipts,
            (SELECT COALESCE(SUM(amount), 0) FROM voucher WHERE date <= '$start_date' AND status = 1) AS total_vouchers";
    $opening_result = $conn->query($opening_query);
    if ($opening_result && $row = $opening_result->fetch_assoc()) {
        $opening_balance = $row['total_receipts'] - $row['total_vouchers'];
    }

    // Fetch transactions (receipts and vouchers between the entered dates)
    $transactions_query = "
        SELECT rdate AS tdate, amount AS receipt_amount, 0 AS voucher_amount
        FROM receipts 
        WHERE rdate BETWEEN '$start_date' AND '$end_date' AND status = 1
        UNION ALL
        SELECT date AS tdate, 0 AS receipt_amount, amount AS voucher_amount
        FROM voucher 
        WHERE date BETWEEN '$start_date' AND '$end_date' AND status = 1
        ORDER BY tdate
    ";
    $transactions_result = $conn->query($transactions_query);
    if ($transactions_result) {
        while ($row = $transactions_result->fetch_assoc()) {
            $transactions[] = $row;
        }
    }
}
?>
<div id="printableContent">
<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <h1 class="page-title">Bank Balance Report</h1>
            <!-- Form for selecting date range -->
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="noprint">
                <label for="start_date">Start Date:
                <input type="date" name="start_date" id="start_date" required></label>
                <label for="end_date">End Date:
                <input type="date" name="end_date" id="end_date" required></label>
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </form>
            <br />

            <?php if ($start_date && $end_date): ?>
                <h3>Opening Balance: <?php echo number_format($opening_balance, 2); ?></h3>

                <!-- Print Button -->
                <button onclick="printPage();" class="btn btn-primary noprint">Print Report</button>
                <br /><br />

                <!-- Transactions Table -->
                <table class="table table-bordered" border="2">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Date</th>
                            <th>Receipt Amount</th>
                            <th>Voucher Amount</th>
                            <th>Balance Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $serial_no = 1;
                        $balance_amount = $opening_balance;

                        foreach ($transactions as $transaction):
                            $balance_amount += $transaction['receipt_amount'] - $transaction['voucher_amount'];
                        ?>
                            <tr>
                                <td><?php echo $serial_no++; ?></td>
                                <td><?php echo date('d-m-Y',strtotime($transaction['tdate'])); ?></td>
                                <td><?php
                                if(number_format($transaction['receipt_amount'], 2)!=0){
                                    echo number_format($transaction['receipt_amount'], 2); 
                                }
                                else{
                                    echo '';
                                }?>
                                </td>
                                <td><?php 
                                if(number_format($transaction['voucher_amount'], 2)!=0)
                                {
                                    echo number_format($transaction['voucher_amount'], 2); 
                                }
                                else
                                {
                                    echo '';
                                }
                                ?></td>
                                <td><?php echo number_format($balance_amount, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" style="text-align:right;">Final Balance:</th>
                            <th><?php echo number_format($balance_amount, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p>Please enter a valid date range to generate the report.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<script>
function printPage() {
    // Get the content to print
    const content = document.getElementById('printableContent').cloneNode(true);

    // Hide the print button and form from the cloned content
    const printButton = content.querySelector('.noprint');
    if (printButton) {
        printButton.style.display = 'none';
    }

    // Open a new print window
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Print Report</title>
                <style>
                    body {
                        font-family: "Times New Roman", Times, serif;
                    }
                    @media print {
                        .noprint {
                            display: none;
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
