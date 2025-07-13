<?php
session_start(); 
// If session variable is not set it will redirect to login page
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location:index.php");
  exit;
}
// Include database connection
include 'config.php';
include 'header.php';
include 'sidemenu.php';

// Check if the 'id' is provided in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch voucher data based on the voucher id
    $sql = "SELECT * FROM voucher WHERE id = '$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $voucher = $result->fetch_assoc();
    } else {
        echo "Voucher not found.";
        exit;
    }
}

// Fetch vouchertype data
$vouchertypes = [];
$sqlvt = "SELECT id, vtype, status FROM vouchertype WHERE status = 1 order by vtype"; // Assuming status 1 means active
$resultvt = $conn->query($sqlvt);

if ($resultvt->num_rows > 0) {

    while ($rowvt = $resultvt->fetch_assoc()) {
        $vouchertypes[] = $rowvt;
    }
}
// Handle the form submission to update the voucher
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data and update the voucher record in the database
    $vtype = $_POST['vtype'];
    $employeetype = $_POST['employeetype'];
    $paymode = $_POST['paymode'];
    $paydetails = $_POST['paydetails'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    // Update voucher in the database
    $update_sql = "UPDATE voucher SET 
                    vtype = '$vtype',
                    employeetype = '$employeetype',
                    paymode = '$paymode',
                    paydetails = '$paydetails',
                    amount = '$amount',
                    date = '$date',
                    month = '$month',
                    year = '$year'
                    WHERE id = '$id'";

    if ($conn->query($update_sql) === TRUE) {
        echo '<script>alert("Voucher Edited successfully!"); window.location.href = "vouchers.php";</script>'; 
    } else {
        echo "Error updating voucher: " . $conn->error;
    }
}
?>

<!-- Your form and content here, similar to what was shown in the previous response -->


<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <h1 class="page-title">Edit Voucher</h1>

            <!-- Form for Edit Voucher -->
            <div class="row" style="background-color:#D7EEEB; padding: 15px;">
                <div class="col-lg-12">
                    <form method="post" action="edit_voucher.php?id=<?= $voucher['id'] ?>">

                        <div class="form-group col-lg-3">
                            <label for="vno">Voucher Number:</label>
                            <input class="form-control" type="text" name="vno" id="vno" value="<?= $voucher['vno'] ?>" readonly>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="vtype">Voucher Type:</label>
                            <select class="form-control" name="vtype" id="vtype" required>
                            <option value="">Select Voucher Type</option>
                            <?php foreach ($vouchertypes as $type): ?>
                                <option value="<?= $type['vtype'] ?>" <?= $voucher['vtype'] == $type['vtype'] ? 'selected' : '' ?>>
                                    <?= $type['vtype'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        </div>

                        <div class="form-group col-lg-3">
                            <label for="employeetype">Employee Type:</label>
                            <input class="form-control" type="text" name="employeetype" id="employeetype" value="<?= $voucher['employeetype'] ?>" required>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="paymode">Payment Mode:</label>
                             <select class="form-control" name="paymode" id="paymode" required>
                                <option value="">Select Pay Mode</option>
                                <option value="Cash" <?= $voucher['paymode'] == 'Cash' ? 'selected' : '' ?>>Cash</option>
                                <option value="Cheque" <?= $voucher['paymode'] == 'Cheque' ? 'selected' : '' ?>>Cheque</option>
                                <option value="Online" <?= $voucher['paymode'] == 'Online' ? 'selected' : '' ?>>Online</option>
                            </select>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="paydetails">Payment Details:</label>
                            <input class="form-control" type="text" name="paydetails" id="paydetails" value="<?= $voucher['paydetails'] ?>" required>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="amount">Amount:</label>
                            <input class="form-control" type="number" name="amount" id="amount" value="<?= $voucher['amount'] ?>" required>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="date">Date:</label>
                            <input class="form-control" type="date" name="date" id="date" value="<?= $voucher['date'] ?>" required>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="month">Month:</label>
                            <select class="form-control" name="month" id="month" required>
                                <option value="January" <?= $voucher['month'] == 'January' ? 'selected' : '' ?>>January</option>
                                <option value="February" <?= $voucher['month'] == 'February' ? 'selected' : '' ?>>February</option>
                                <option value="March" <?= $voucher['month'] == 'March' ? 'selected' : '' ?>>March</option>
                                <!-- Add more months as needed -->
                            </select>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="year">Year:</label>
                            <input class="form-control" type="text" name="year" id="year" value="<?= $voucher['year'] ?>" required>
                        </div>
                        <div class="form-group col-lg-3"><br>

                        <button type="submit" class="btn btn-primary">Edit Voucher</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'footer.php'; ?>
