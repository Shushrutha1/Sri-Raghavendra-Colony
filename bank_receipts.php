<?php
session_start(); 
// If session variable is not set it will redirect to login page
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
$start_date = isset($_POST['start_date']) ? date('Y-m-d', strtotime($_POST['start_date'])) : '';
$end_date = isset($_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : '';
$opening_balance = 0;
$transactions = [];

if ($start_date && $end_date) {
    // Calculate opening balance (up to the entered start date)
    $opening_query = "
		SELECT 
		    (SELECT COALESCE(SUM(amount), 0) FROM receipts WHERE rdate < '$start_date' AND status = 1) AS total_receipts,
    		(SELECT COALESCE(SUM(amount), 0) FROM voucher WHERE date < '$start_date' AND status = 1) AS total_vouchers

    ";
    $opening_result = $conn->query($opening_query);
    if ($opening_result && $row = $opening_result->fetch_assoc()) {
        $opening_balance = $row['total_receipts'] - $row['total_vouchers'];
    }

    // Fetch transactions (grouped receipts and vouchers between the entered dates)
/*    $transactions_query = "
        SELECT 
            MIN(rno) AS receipt_start, 
            MAX(rno) AS receipt_end, 
            rdate AS tdate, 
            SUM(amount) AS receipt_amount, 
            '' AS voucher_type, 
            0 AS voucher_amount
        FROM receipts 
        WHERE rdate BETWEEN '$start_date' AND '$end_date' AND status = 1
        GROUP BY rdate
        UNION ALL
        SELECT 
            '' AS receipt_start, 
            '' AS receipt_end, 
            date AS tdate, 
            0 AS receipt_amount, 
            vtype AS voucher_type, 
            amount AS voucher_amount
        FROM voucher 
        WHERE date BETWEEN '$start_date' AND '$end_date' AND status = 1
        ORDER BY tdate
    ";*/
	
	   $transactions_query = "
   SELECT 
    MIN(rno) AS receipt_start, 
    MAX(rno) AS receipt_end, 
    rdate AS tdate, 
    SUM(amount) AS receipt_amount, 
    '' AS voucher_no, 
    '' AS voucher_type, 
    0 AS voucher_amount,
    1 AS order_type
FROM receipts 
WHERE rdate BETWEEN '$start_date' AND '$end_date' AND status = 1
GROUP BY rdate

UNION ALL

SELECT 
    '' AS receipt_start, 
    '' AS receipt_end, 
    date AS tdate, 
    0 AS receipt_amount, 
    vno AS voucher_no, 
    vtype AS voucher_type, 
    amount AS voucher_amount,
    2 AS order_type
FROM voucher 
WHERE date BETWEEN '$start_date' AND '$end_date' AND status = 1

ORDER BY tdate, voucher_no, order_type

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
<h2 class="page-title">
    Bank Balance Report
    <?php if (!empty($start_date) && !empty($end_date)): ?>
        - From <?= htmlspecialchars($start_date) ?> to <?= htmlspecialchars($end_date) ?>
    <?php endif; ?>
</h2>
<!-- Form for selecting date range -->
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="noprint">
    <label for="start_date">Start Date:
    <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required></label>
    <label for="end_date">End Date:
    <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required></label>
    <button type="submit" class="btn btn-primary">Generate Report</button>
</form>
<br />

            <?php if ($start_date && $end_date): ?>
                <h3>Opening Balance: <?php echo number_format($opening_balance, 2); ?></h3>
  <!-- Print Button -->
                    <button onclick="printPage();" class="btn btn-primary noprint">Print Report</button>

                <!-- Transactions Table -->
                <table class="table table-bordered" border="2">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Date</th>
                            <th>Description</th> <!-- Receipt or Voucher -->
                            <th>Voucher No.</th> <!-- Receipt Range -->
                            <th>Receipt No.</th> <!-- Voucher No -->
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
                            // Update balance after receipts and vouchers
                            $balance_amount += $transaction['receipt_amount'] - $transaction['voucher_amount'];
                        ?>
                            <tr>
                                <td><?php echo $serial_no++; ?></td>
                                <td><?php echo date('d-m-Y',strtotime(htmlspecialchars($transaction['tdate']))); ?></td>
                                
                                <!-- Description column: Receipts or Voucher -->
                                <td>
    <?php
        if ($transaction['receipt_amount'] > 0) {
            echo "Receipts"; // Show "Receipts" for rows with receipt amounts
        } elseif ($transaction['voucher_type']) {
            echo htmlspecialchars($transaction['voucher_type']); // Show voucher type with "- voucher"
        }
    ?>
</td>
<td>
<?php echo  htmlspecialchars($transaction['voucher_no']); // Show voucher type with "- voucher" ?>
</td>


                                <!-- Receipt Numbers: Show range for receipts -->
                                <td>
                                    <?php
                                        if ($transaction['receipt_start'] && $transaction['receipt_end']) {
                                            echo htmlspecialchars($transaction['receipt_start']) . ' - ' . htmlspecialchars($transaction['receipt_end']);
                                        }
                                    ?>
                                </td>

                                <!-- Receipt Amount -->
                                <td><?php echo $transaction['receipt_amount'] != 0 ? number_format($transaction['receipt_amount'], 2) : ''; ?></td>

                             

                                <!-- Voucher Amount -->
                                <td><?php echo $transaction['voucher_amount'] != 0 ? number_format($transaction['voucher_amount'], 2) : ''; ?></td>

                                <!-- Balance Amount -->
                                <td><?php echo number_format($balance_amount, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" style="text-align:right;">Final Balance:<?php echo number_format($balance_amount, 2); ?></th>
                           
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
    const content = document.getElementById('printableContent').cloneNode(true);
    const printButton = content.querySelector('.noprint');
    if (printButton) {
        printButton.style.display = 'none';
    }
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
    printWindow.print();
    printWindow.onafterprint = () => printWindow.close();
}
</script>
