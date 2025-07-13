<?php
session_start(); 
// If session variable is not set it will redirect to login page
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location:index.php");
  exit;
}
// Include database connection
include'config.php';
include'header.php';
include'sidemenu.php';

// Fetch voucher data
$vouchers = [];
$sql = "SELECT id, vno, vtype, employeetype, paymode, paydetails, amount, date, month, year, status FROM voucher where status=1 order by vno";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $vouchers[] = $row;
    }
}
?>

	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    
    
    <style>
        .buttons-copy { background-color: #4CAF50; color: white; }
        .buttons-csv { background-color: #2196F3; color: white; }
        .buttons-excel { background-color: #4CAF50; color: white; }
        .buttons-pdf { background-color: #F44336; color: white; }
        .buttons-print { background-color: #FF9800; color: white; }
    </style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <h1 class="page-title">Vouchers</h1>

            <!-- Form for Create/Update -->
            <div class="row">
            <div class="form-group col-lg-3">
            <a href="vouchers.php" class="btn btn-primary">Add New Voucher</a>
            </div>
                <div class="col-lg-12">
    
         <table id="voucherTable" class="table table-bordered table-striped"  style="background-color:#D7EEEB; padding: 15px;">
    <thead>
        <tr>
        <th>S.No</th>
            <th>V.No</th>
            <th>Voucher Type</th>
            <th>Employee Type</th>
            <th>Pay Mode</th>
            <th>Pay Details</th>
            <th>Amount</th>
            <th>Voucher_Date</th>
            <th>Month</th>
            <th>Year</th>
             <?php if ($role == 1): ?>
            <th>Edit</th>
            <th>Delete</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php $i=1; foreach ($vouchers as $voucher): ?>
            <tr>
            <td><?php echo $i; ?></td>
                <td><?= $voucher['vno'] ?></td>
                <td><?= $voucher['vtype'] ?></td>
                <td><?= $voucher['employeetype'] ?></td>
                <td><?= $voucher['paymode'] ?></td>
                <td><?= $voucher['paydetails'] ?></td>
                <td><?= number_format($voucher['amount'], 2) ?></td>
                <td><?= date('d-m-Y',strtotime($voucher['date'])) ?></td>
                <td><?= $voucher['month'] ?></td>
                <td><?= $voucher['year'] ?></td>
                 <?php if ($role == 1): ?>
                 <!-- Edit button with URL passing the voucher id -->
                <td><a href="edit_voucher.php?id=<?= $voucher['id'] ?>" class="btn btn-warning"><i class="ion-edit"></i></a></td>
                <!-- Delete button with URL passing the voucher id -->
                <td><a href="delete_voucher.php?id=<?= $voucher['id'] ?>" class="btn btn-danger"><i class="ion-trash-a"></i></a></td>
                <?php endif; ?>
           
            </tr>
        <?php $i++; endforeach; ?>
    </tbody>
</table>


        </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>


<script>
$(document).ready(function() {
    console.log("DataTable initialization started.");
    $('#voucherTable').DataTable({
          dom: 'Bflrtip',
          buttons: ['excelHtml5', 'csvHtml5', 'pdfHtml5', 'print'],
    });
    console.log("DataTable initialized.");
});
</script>
<script>
$(document).ready(function() {
    $('#voucherTable').DataTable();
});
</script>

   
<?php include'footer.php'; ?>