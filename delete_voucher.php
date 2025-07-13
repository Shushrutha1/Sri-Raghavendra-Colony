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

// Check if 'id' is provided in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the voucher data based on the voucher ID
    $sql = "SELECT * FROM voucher WHERE id = '$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $voucher = $result->fetch_assoc();

        // Handle the deletion request
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Delete the voucher from the database
            // $delete_sql = "DELETE FROM voucher WHERE id = '$id'";
			$delete_sql = "UPDATE voucher SET status=0 WHERE id = '$id'";

            if ($conn->query($delete_sql) === TRUE) {
             echo '<script>alert("Voucher Edited successfully!"); window.location.href = "voucher-report.php";</script>'; 
            } else {
                echo "Error deleting voucher: " . $conn->error;
            }
        }
    } else {
             echo '<script>alert("Voucher Not Found!"); window.location.href = "voucher-report.php";</script>'; 

    }
} else {
     echo '<script>alert("Invalid Voucher Number!"); window.location.href = "vouchers.php";</script>'; 
    exit;
}
?>

<!-- Delete confirmation form -->
<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <h1 class="page-title">Delete Voucher</h1>

            <!-- Confirmation message -->
            <div class="row" style="background-color:#D7EEEB; padding: 15px;">
                <div class="col-lg-12">
                    <form method="post" action="delete_voucher.php?id=<?= $voucher['id'] ?>">

                        <p>Are you sure you want to delete the voucher with <strong style="color:#F00">Voucher Number:  <?= $voucher['vno'] ?>  <i style=" color:#00F" class="ion-help"></i></strong></p>

                        <button type="submit" class="btn btn-danger"><i class="ion-trash-a"></i> Delete Voucher</button>
                        <a href="voucher-report.php" class="btn btn-info">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
