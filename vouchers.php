<?php
session_start(); 
// If session variable is not set it will redirect to login page*/
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location:index.php");
  exit;
}
// Database connection
include'config.php';
include'header.php';
include'sidemenu.php';


// Fetch the latest vno
$sql = "SELECT MAX(vno) AS last_vno FROM voucher where status=1";
$result = $conn->query($sql);

$new_vno = 1; // Default starting value if no records exist
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $new_vno = $row['last_vno'] ? $row['last_vno'] + 1 : 1; // Increment the last vno
}


// Fetch vouchertype data
$vouchertypes = [];
$sql = "SELECT id, vtype, status FROM vouchertype WHERE status = 1 order by vtype"; // Assuming status 1 means active
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $vouchertypes[] = $row;
    }
}

// Insert voucher data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vno = $_POST['vno'];
    $vtype = $_POST['vtype'];
    $employeetype = $_POST['employeetype'];
    $paymode = $_POST['paymode'];
    $paydetails = $_POST['paydetails'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $status = 1;

    $sql = "INSERT INTO voucher (vno, vtype, employeetype, paymode, paydetails, amount, date, month, year, status) 
            VALUES ('$vno', '$vtype', '$employeetype', '$paymode', '$paydetails', '$amount', '$date', '$month', '$year', '$status')";
    
    if ($conn->query($sql) === TRUE) {
        echo '<script>alert("Voucher added successfully!"); window.location.href = "vouchers.php";</script>'; 
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

//// voucher list min 10
// Fetch voucher data
$voucherlist = [];
$vsql = "SELECT id, vno, vtype, employeetype, paymode, paydetails, amount, date, month, year, status FROM voucher where status=1 order by vno DESC";
$vresult = $conn->query($vsql);

if ($vresult->num_rows > 0) {
    while ($vrow = $vresult->fetch_assoc()) {
        $voucherlist[] = $vrow;
    }
}





// Close connection
$conn->close();
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">


    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { background-color: #FCF; padding: 15px; }
    </style>
<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <h1 class="page-title">Vouchers</h1>

            <!-- Form for Create/Update -->
            <div class="row" style="background-color:#D7EEEB; padding: 15px;">
                <div class="col-lg-12">
                    <h2>Voucher Management</h2>
                    <form method="post" action="">
                        <div class="row">
                            <!-- Voucher Number -->
                            <div class="form-group col-lg-1">
                                <label for="vno">V.No:</label>
                                <input class="form-control" type="text" name="vno" id="vno" value="<?php echo htmlspecialchars($new_vno); ?>" readonly >
                            </div>

                            <!-- Voucher Type -->
                            <div class="form-group col-lg-3">
                                <label for="vtype">Voucher Type:</label>
                                <select class="form-control" name="vtype" id="vtype" required>
                                    <option value="">Select Voucher Type</option>
                                    <?php foreach ($vouchertypes as $type): ?>
                                        <option value="<?= $type['vtype'] ?>"><?= $type['vtype'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Employee Type -->
                            <div class="form-group col-lg-3">
                                <label for="employeetype">Employee Type:</label>
                                <input class="form-control" type="text" name="employeetype" id="employeetype" required>
                            </div>

                            <!-- Payment Mode -->
                            <div class="form-group col-lg-2">
                                <label for="paymode">Payment Mode:</label>
                                <select class="form-control" name="paymode" id="paymode" required>
                                    <option value="">Select Pay Mode</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Online">Online</option>
                                </select>
                            </div>
                        
                            <!-- Payment Details -->
                            <div class="form-group col-lg-3">
                                <label for="paydetails">Payment Details:</label>
                                <input class="form-control" type="text" name="paydetails" id="paydetails" placeholder="Select payment mode first" required>
                            </div>
</div>

                        <div class="row">
                            <!-- Amount -->
                            <div class="form-group col-lg-3">
                                <label for="amount">Amount:</label>
                                <input class="form-control" type="number" name="amount" min="0.00" step="0.01" id="amount" required>

                            </div>

                            <!-- Date -->
                            <div class="form-group col-lg-3">
                                <label for="date">Date:</label>
                                
                                <input class="form-control" type="date" name="date" id="date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                   
                            <!-- Month -->
                            <div class="form-group col-lg-3">
                                <label for="month">Month:</label>
                                <?php
                                $months = [
                                    '' => 'Select-Month', 'January' => 'January', 'February' => 'February', 'March' => 'March',
                                    'April' => 'April', 'May' => 'May', 'June' => 'June', 'July' => 'July', 'August' => 'August',
                                    'September' => 'September', 'October' => 'October', 'November' => 'November', 'December' => 'December'
                                ];
                                ?>
                                <select class="form-control" name="month" id="month" required>
                                    <?php foreach ($months as $key => $month): ?>
                                        <option value="<?= $key ?>" <?= (isset($voucher['month']) && $voucher['month'] == $key) ? 'selected' : '' ?>><?= $month ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                       
                            <!-- Year -->
                            <div class="form-group col-lg-3">
                                <label for="year">Year:</label>
                                <input class="form-control" type="text" name="year" id="year" value="<?php echo date('Y'); ?>"  required>
                            </div>
                             </div>

                        <div class="row">
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            
                             <div class="form-group col-lg-3">
                                 <button type="submit" class="btn btn-primary">Save Voucher</button>
                            
                            </div>
                             <div class="form-group col-lg-3">

                                <a href="voucher-report.php" class="btn btn-warning">List of Voucher</a>
                            </div>
                            
                        </div>
                    </form>                    
                    
                </div>
            </div>
            
        </div>
        <br />
    <div class="col-lg-12">
    
        
<table id="voucherTable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>S.No</th>
            <th>V.No</th>
            <th>Voucher Type</th>
            <th>Employee Type</th>
            <th>Pay Mode</th>
            <th>Pay Details</th>
            <th>Amount</th>
            <th>Voucher Date</th>
            <th>Month</th>
            <th>Year</th>
            <?php if ($role == 1): ?>
            <th>Edit</th>
            <th>Delete</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php $i=1; foreach ($voucherlist as $voucher): ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td><?= $voucher['vno'] ?></td>
                <td><?= $voucher['vtype'] ?></td>
                <td><?= $voucher['employeetype'] ?></td>
                <td><?= $voucher['paymode'] ?></td>
                <td><?= $voucher['paydetails'] ?></td>
                <td><?= number_format($voucher['amount'], 2) ?></td>
                <td><?= date('d-m-Y', strtotime($voucher['date'])) ?></td>
                <td><?= $voucher['month'] ?></td>
                <td><?= $voucher['year'] ?></td>
                <?php if ($role == 1): ?>
                <td><a href="edit_voucher.php?id=<?= $voucher['id'] ?>" class="btn btn-warning"><i class="ion-edit"></i></a></td>
                <td><a href="delete_voucher.php?id=<?= $voucher['id'] ?>" class="btn btn-danger"><i class="ion-trash-a"></i></a></td>
                <?php endif; ?>
            </tr>
        <?php $i++; endforeach; ?>
    </tbody>
</table>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<!-- Buttons extension -->
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script>
    $(document).ready(function () {
    if (!$.fn.DataTable.isDataTable("#voucherTable")) { // Prevent reinitialization error
        $('#voucherTable').DataTable({
            dom: 'Bflrtip',
            buttons: [
                'copy', 'csv', 'excel',
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':visible' // Only export visible columns
                    },
                    customize: function (doc) {
						 //doc.content[1].table.widths = ['5%', '10%', '15%', '15%', '10%', '10%', '10%', '10%', '10%', '5%']; 
                       //doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                        doc.defaultStyle.fontSize = 9;
                        doc.styles.tableHeader.fontSize = 10;
                        doc.styles.tableHeader.fillColor = '#343a40';
                        doc.styles.tableHeader.color = 'white';
                    }
                },
                'print'
            ],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            order: [[0, 'asc']]
        });
    }
});

</script>

<!-- Include DataTables CSS and JS 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
-->




        </div>    
    </div>
    
</div>



<script>
        // Function to update placeholder based on selected payment mode
        function updatePlaceholder() {
            const paymode = document.getElementById("paymode").value;
            const paydetails = document.getElementById("paydetails");

            if (paymode === "Cash") {
                paydetails.placeholder = "Enter cash details";
            } else if (paymode === "Cheque") {
                paydetails.placeholder = "Enter cheque number";
            } else if (paymode === "Online") {
                paydetails.placeholder = "Enter UTR No of GPay, PhonePe, Bhim, UPI, etc.";
            } else {
                paydetails.placeholder = "Select payment mode first";
            }
        }

        // Attach event listener to the paymode dropdown
        document.getElementById("paymode").addEventListener("change", updatePlaceholder);
    </script>
    
    <?php
include 'footer.php';
?>

	
    
    