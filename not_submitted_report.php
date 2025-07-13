<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location:index.php");
    exit;
}

include 'config.php';  // Your database connection file
include 'header.php';   // Your page header
include 'sidemenu.php'; // Your sidebar menu

// Fetch the year from the form submission
$year = isset($_POST['year']) && is_numeric($_POST['year']) ? intval($_POST['year']) : '';    

// Initialize array to store payment status by plot number and month
$payments = [];

// Fetch all plot numbers
$plotsQuery = "SELECT plotno, plname FROM plots";
$plotsResult = $conn->query($plotsQuery);

if (!$plotsResult) {
    die("SQL Error: " . $conn->error);
}

$plots = [];
while ($row = $plotsResult->fetch_assoc()) {
    $plots[$row['plotno']] = $row['plname'];
}

// Only execute the query if a year is selected
if ($year) {
    $query = "
        SELECT 
            r.plotno,
            r.plname,
            r.month,
            r.year,
            r.status,
            r.amount,
            r.rno
        FROM receipts r
        WHERE r.year = $year
        ORDER BY r.plotno, r.month
    ";

    $result = $conn->query($query);

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    // Populate the $payments array with the result
    while ($row = $result->fetch_assoc()) {
        $payments[$row['plotno']]['plname'] = $row['plname'];
        $payments[$row['plotno']]['months'][$row['month']] = [
            'status' => $row['status'] == 1 ? $row['rno'] : '--',
            'amount' => $row['amount']
        ];
    }
}

$months = [
    'January', 'February', 'March', 'April', 'May', 'June', 
    'July', 'August', 'September', 'October', 'November', 'December'
];

?>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipts Payment Report</title>
    
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  
<div id="printableContent">
<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <?php
            // Initialize year variable
            $year = "";

            // Check if form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['year'])) {
                $year = htmlspecialchars($_POST['year']); // Get the year entered by the user
            }
            ?>

            <h1 class="page-title">
                Receipts Payment Report 
                <?php if (!empty($year)) echo " - $year"; ?>
            </h1>

            <!-- Year Form -->
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="noprint">
                <label for="year">Year:</label>
                <input type="number" name="year" id="year" required>
                <button type="submit" class="btn btn-primary">Submit</button> <br><br>
                 <button onclick="printPage();" class="btn btn-primary noprint">Print Report</button>
            </form>
            <br />
     
            <?php if ($year && count($plots) > 0): ?>
                <!-- Display Payment Status for Each Plot -->
                <table id="receiptsdata" class="table table-bordered" border="1">
                    <thead>
                        <tr>
                            <th>Plot No</th>
                            <th>Name</th>
                            <th>Year</th>
                            <?php foreach ($months as $month): ?>
                                <th><?php echo $month; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plots as $plotno => $plname): ?>
                            <tr>
                                <td><?= $plotno ?></td>
                                <td><?= $plname ?></td>
                                <td><?= $year ?></td>
                                <?php foreach ($months as $month): ?>
                                    <td>
                                        <?php 
                                            // If there's a payment for the current plot and month, show the rno (receipt number)
                                            if (isset($payments[$plotno]['months'][$month])) {
                                                echo $payments[$plotno]['months'][$month]['status'];
                                            } else {
                                                echo '--';
                                            }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($year): ?>
                <p>No data found for the selected year.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
<!-- Print Button -->
<div class="noprint" style="text-align: center; margin-top: 20px;">

</div>

<script>
$(document).ready(function () {
    $('#receiptsdata').DataTable({
        dom: 'Bflrtip',
        buttons: [
            'copy', 'csv', 'excelHtml5', 'pdfHtml5', 'print'
        ]
    });
});
</script>



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

<?php include 'footer.php'; ?>