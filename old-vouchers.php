<?php
session_start(); 
// If session variable is not set it will redirect to login page
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location:index.php");
  exit;
}
ob_start(); // Start output buffering
include 'config.php';
include 'header.php';
include 'sidemenu.php';

$is_edit = isset($_GET['id']) && !empty($_GET['id']);  // Check if it's an edit

// Fetch the latest vno
$sql = "SELECT MAX(vno) AS last_vno FROM voucher";
$result = $conn->query($sql);

$new_vno = 1; // Default starting value if no records exist
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $new_vno = $row['last_vno'] ? $row['last_vno'] + 1 : 1; // Increment the last vno
}


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
    /*$month = date('F', strtotime($date));
    $year = date('Y', strtotime($date));*/
	$month = $_POST['month'];
	$year = $_POST['year'];
    $status = 1;

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
	echo '<script>
    alert("New or Updated Data");
       window.location.href = "vouchers.php";    
</script>';
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
        $status = 1;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("UPDATE voucher SET status=0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
	echo '<script>
    alert("Deleted Data");    
        window.location.href = "vouchers.php";    
</script>';
exit();
}

// Fetch all records
$result = $conn->query("SELECT * FROM voucher where status=1 ORDER BY vno DESC");
$vouchers = [];
while ($row = $result->fetch_assoc()) {
    $vouchers[] = $row;
}
?>
<style>
        /* Custom Row Color */
        #voucherdata tbody tr:nth-child(even) {
            background-color: #f2f2f2; /* Light gray for even rows */
        }

        #voucherdata tbody tr:nth-child(odd) {
            background-color: #ffffff; /* White for odd rows */
        }

        /* Custom Row Hover Color */
        #voucherdata tbody tr:hover {
            background-color: #d9f7be; /* Light green on hover */
        }

        /* Custom Button Styles */
        .dt-buttons .btn {
            background-color: #007bff; /* Blue button */
            color: white;
            border: 1px solid #007bff;
            margin-right: 5px;
        }

        .dt-buttons .btn:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        .dt-buttons .btn-csv {
            background-color: #28a745; /* Green button for CSV */
        }

        .dt-buttons .btn-csv:hover {
            background-color: #218838; /* Darker green on hover */
        }

        .dt-buttons .btn-excel {
            background-color: #ffc107; /* Yellow button for Excel */
        }

        .dt-buttons .btn-excel:hover {
            background-color: #e0a800; /* Darker yellow on hover */
        }

        .dt-buttons .btn-pdf {
            background-color: #dc3545; /* Red button for PDF */
        }

        .dt-buttons .btn-pdf:hover {
            background-color: #c82333; /* Darker red on hover */
        }

        .dt-buttons .btn-print {
            background-color: #17a2b8; /* Cyan button for Print */
        }

        .dt-buttons .btn-print:hover {
            background-color: #138496; /* Darker cyan on hover */
        }
    </style>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- CSS for DataTable -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- JS for jQuery and DataTable -->
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
            <div class="row"  style="background-color:#D7EEEB">
                <div class="col-lg-12">

           <form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

    <div class="form-group col-lg-3">
        <label>Voucher No:</label>
<!--  <input class="form-control" type="text" name="vno" value="< ?= htmlspecialchars($vno) ?>" required> -->

<input class="form-control" type="text" name="vno" value="<?php echo $edit_mode ? htmlspecialchars($vno) : htmlspecialchars($new_vno); ?>" required>

    </div>

    <div class="form-group col-lg-3">
        <label>Type:</label>
        <select class="form-control" name="vtype" required>
            <option value="">Select Voucher Type</option>
            <?php 
            $types = [
                'Electricity Bill' => 'Electricity Bill', 'Plumber Charges' => 'Plumber Charges', 'Worker Salary' => 'Worker Salary',
                'Library' => 'Library', 'Material' => 'Material', 'Sanitation Works' => 'Sanitation Works',
                'Labour Charges' => 'Labour Charges', 'Printing Charges' => 'Printing Charges', 'Others' => 'Others'
            ];
            foreach ($types as $key => $type): ?>
                <option value="<?= $key ?>" <?= (isset($voucher['vtype']) && $voucher['vtype'] == $type) ? 'selected' : '' ?>><?= $type ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group col-lg-3">
        <label>Employee Type:</label>
        <input class="form-control" type="text" name="employeetype" value="<?= htmlspecialchars($employeetype) ?>" required>
    </div>

    <div class="form-group col-lg-3">
        <label>Pay Mode:</label>
        <select class="form-control" name="paymode" required>
            <option value="Cash" <?= (isset($voucher['paymode']) && $voucher['paymode'] == 'Cash') ? 'selected' : '' ?>>Cash</option>
            <option value="Cheque" <?= (isset($voucher['paymode']) && $voucher['paymode'] == 'Cheque') ? 'selected' : '' ?>>Cheque</option>
            <option value="UPI" <?= (isset($voucher['paymode']) && $voucher['paymode'] == 'UPI') ? 'selected' : '' ?>>UPI Transfer</option>
        </select>
    </div>

    <div class="form-group col-lg-3">
        <label>Pay Details:</label>
        <input type="text" class="form-control" name="paydetails" value="<?= htmlspecialchars($paydetails) ?>"  />
    </div>

    <div class="form-group col-lg-3">
        <label>Amount:</label>
        <input class="form-control" type="number" name="amount" value="<?= htmlspecialchars($amount) ?>" step="0.01" required>
    </div>

    <div class="form-group col-lg-3">
        <label>Date:</label>
        <input class="form-control" type="date" name="date" value="<?= htmlspecialchars($date) ?>" required>
    </div>

    <div class="form-group col-lg-3">
        <label>Paid Month:</label>
        <?php
$months = [
    0 => 'Select-Month', 1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
    7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];
?>
<select name="month" class="form-control">
    <?php foreach ($months as $key => $month): ?>
        <option value="<?= $key ?>" <?= (isset($voucher['month']) && $voucher['month'] == $key) ? 'selected' : '' ?>><?= $month ?></option>
    <?php endforeach; ?>
</select>

    </div>

    <div class="form-group col-lg-3">
        <label>Paid Year:</label>
        <input class="form-control" type="number" name="year" value="<?= htmlspecialchars($voucher['year'] ?? date('Y')) ?>" min="1990" required>
    </div>

    <div class="form-group col-lg-12">
        <button class="btn btn-primary" type="submit"><?= $edit_mode ? 'Update' : 'Create' ?></button>
    </div>
</form>

</div></div>

            <!-- Voucher Table -->
            <h2>Existing Vouchers</h2>

            <!-- Table for Display -->
             <div class="row">
                <div class="col-lg-12">
   <!-- Table structure -->
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
            <th>Paid Month</th>
            <th>Paid Year</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; foreach ($vouchers as $voucher): ?>
        <tr>
            <td><?= $i ?></td>
            <td><?= $voucher['vno'] ?></td>
            <td><?= $voucher['vtype'] ?></td>
            <td><?= $voucher['employeetype'] ?></td>
            <td><?= $voucher['paymode'] ?></td>
            <td><?= $voucher['amount'] ?></td>
            <td><?= $voucher['date'] ?></td>
            <td><?= $voucher['month'] ?></td>
            <td><?= $voucher['year'] ?></td>
            <td>
                <a class="btn btn-warning" href="?edit=<?= $voucher['id'] ?>">Edit</a>
                <a class="btn btn-danger" href="?delete=<?= $voucher['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php $i++; endforeach; ?>
    </tbody>
</table>

<!-- DataTable Initialization Script -->
<script>
    $(document).ready(function() {
        // Initialize DataTable with export buttons
        $('#voucherdata').DataTable({
            dom: 'Bflrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    });
</script>
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

