<?php
session_start(); 
// If session variable is not set it will redirect to login page
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location:index.php");
  exit;
}
include 'config.php';
include 'header.php';
include 'sidemenu.php';

/* Get the last voucher number for auto-increment
$sqlv = "SELECT MAX(vno) AS max_vno FROM voucher";
$resultv = $conn->query($sqlv) or die("Query failed: " . $conn->error);
$rowv = $resultv->fetch_assoc();
$new_vno = $rowv['max_vno'] ? $rowv['max_vno'] + 1 : 1; */
// Initialize variables
/*$new_vno = 1;
$voucher = null;

// Fetch the next voucher number
$sqlv = "SELECT MAX(vno) AS max_vno FROM voucher";
$resultv = $conn->query($sqlv) or die("Query failed: " . $conn->error);
if ($rowv = $resultv->fetch_assoc()) {
    $new_vno = $rowv['max_vno'] ? $rowv['max_vno'] + 1 : 1;
}
*/
// Initialize variables
$id = $vno = $vtype = $employeetype = $paymode = $paydetails = $amount = $date = $month = $year = $status = "";
$edit_mode = false;

// Handle Create or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $vno = $_POST['vno'];
    $vtype = $_POST['vtype'];
    $employeetype = $_POST['employeetype'];
    $paymode = $_POST['paymode'];
    $paydetails = $_POST['paydetails'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $month = date('F', strtotime($date));
    $year = date('Y', strtotime($date));
    $status = $_POST['status'];

    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE voucher SET vno = ?, vtype = ?, employeetype = ?, paymode = ?, paydetails = ?, amount = ?, date = ?, month = ?, year = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssssdssssi", $vno, $vtype, $employeetype, $paymode, $paydetails, $amount, $date, $month, $year, $status, $id);
        $stmt->execute();
    } else {
        // Create
        $stmt = $conn->prepare("INSERT INTO voucher (vno, vtype, employeetype, paymode, paydetails, amount, date, month, year, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssdssss", $vno, $vtype, $employeetype, $paymode, $paydetails, $amount, $date, $month, $year, $status);
        $stmt->execute();
    }

    header("Location: voucher.php");
    exit();
}

// Handle Edit
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM voucher WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $voucher = $result->fetch_assoc();
        $vno = $voucher['vno'];
        $vtype = $voucher['vtype'];
        $employeetype = $voucher['employeetype'];
        $paymode = $voucher['paymode'];
        $paydetails = $voucher['paydetails'];
        $amount = $voucher['amount'];
        $date = $voucher['date'];
        $month = $voucher['month'];
        $year = $voucher['year'];
        $status = $voucher['status'];
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM voucher WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: voucher.php");
    exit();
}

// Fetch all records
$result = $conn->query("SELECT * FROM voucher ORDER BY id DESC");
$vouchers = [];
while ($row = $result->fetch_assoc()) {
    $vouchers[] = $row;
}
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<title>Voucher Management</title>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <h1 class="page-title">Vouchers</h1>

            <!-- Form for Create/Update -->
            <div class="row">
                <div class="col-lg-12">

           <form method="post">
        <input type="hidden" name="id" value="<?= $id ?>">
         <div class="form-group col-lg-3">
         
        <label>Voucher No: <input class="form-control"  type="text" name="vno" value="<?= $vno ?>" required></label><br>
         </div>
                        <div class="form-group col-lg-3">
        <label>Type: <input class="form-control"  type="text" name="vtype" value="<?= $vtype ?>" required></label><br>
         </div>
                        <div class="form-group col-lg-3">
        <label>Employee Type: <input class="form-control"  type="text" name="employeetype" value="<?= $employeetype ?>" required></label><br>
         </div>
                        <div class="form-group col-lg-3">
        <label>Pay Mode: <input class="form-control"  type="text" name="paymode" value="<?= $paymode ?>" required></label><br>
         </div>
                        <div class="form-group col-lg-3">
        <label>Pay Details: <textarea class="form-control"  name="paydetails"><?= $paydetails ?></textarea></label><br>
         </div>
                        <div class="form-group col-lg-3">
        <label>Amount: <input class="form-control"  type="number" name="amount" value="<?= $amount ?>" step="0.01" required></label><br>
         </div>
                        <div class="form-group col-lg-3">
        <label>Date: <input class="form-control"  type="date" name="date" value="<?= $date ?>" required></label><br>
         </div>
                        <div class="form-group col-lg-3">
        <label>Status: 
            <select class="form-control"  name="status">
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </label><br>
         </div>
                       
        <button class="btn btn-primary" type="submit"><?= $edit_mode ? 'Update' : 'Create' ?></button>
   
    </form>
</div></div>

            <!-- Voucher Table -->
            <h2>Existing Vouchers</h2>

            <!-- Table for Display -->
             <div class="row">
                <div class="col-lg-12">
    <table id="voucherdata" border="1" cellpadding="10" class="table table-bordered">
        <thead>
            <tr>
                <th>S.No</th>
                <th>VNo</th>
                <th>Type</th>
                <th>Employee Type</th>
                <th>Pay Mode</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php  $i = 1; foreach ($vouchers as $voucher): ?>
            <tr>
                 <td><?= $i ?></td>
                <td><?= $voucher['vno'] ?></td>
                <td><?= $voucher['vtype'] ?></td>
                <td><?= $voucher['employeetype'] ?></td>
                <td><?= $voucher['paymode'] ?></td>
                <td><?= $voucher['amount'] ?></td>
                <td><?= $voucher['date'] ?></td>
                <td><?= $voucher['status'] ?></td>
                <td>
                    <a class="btn btn-warning" href="?edit=<?= $voucher['id'] ?>">Edit</a>
                    <a class="btn btn-danger" href="?delete=<?= $voucher['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php $i++;  endforeach; ?>
        </tbody>
    </table>
       </div>
            </div>
        </div>
    </div>
</div>

<script>
   $('.edit-btn').click(function() {
    const id = $(this).data('id');
    $.ajax({
        url: 'fetch_voucher.php', // A separate PHP script to fetch voucher details by ID
        type: 'GET',
        data: { id: id },
        success: function(data) {
            const voucher = JSON.parse(data);
            $('input[name="id"]').val(voucher.id);
            $('input[name="vno"]').val(voucher.vno);
            $('select[name="vtype"]').val(voucher.vtype);
            $('input[name="employeetype"]').val(voucher.employeetype);
            $('select[name="paymode"]').val(voucher.paymode);
            $('input[name="amount"]').val(voucher.amount);
            $('input[name="date"]').val(voucher.date);
            $('select[name="month"]').val(voucher.month);
            $('input[name="year"]').val(voucher.year);
        }
    });
});

</script>
<script>
    $(document).ready(function() {
        $('#voucherdata').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });        

</script>
