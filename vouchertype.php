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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Create or Update
    $id = $_POST['id'] ?? null; // `id` is passed as a hidden field in the form
    $vtype = $_POST['vtype'] ?? '';
    $status = 1;

    if ($id) {
        // Update Record
        $stmt = $conn->prepare("UPDATE vouchertype SET vtype = ? WHERE id = ?");
        $stmt->bind_param('si', $vtype, $id);
    } else {
        // Create Record
        $stmt = $conn->prepare("INSERT INTO vouchertype (vtype, status) VALUES (?, ?)");
        $stmt->bind_param('si', $vtype, $status);
    }
    $stmt->execute();
} elseif (isset($_GET['delete'])) {
    // Handle Delete (Set status=0)
    $id = $_GET['delete'];
    $stmt = $conn->prepare("UPDATE vouchertype SET status = 0 WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

// Fetch All Records
$result = $conn->query("SELECT * FROM vouchertype WHERE status = 1");
$vouchertypes = $result->fetch_all(MYSQLI_ASSOC);
?>

<style>
body {
    background-color: #FFF;
}
table {
    border-collapse: collapse;
    width: 100%;
}
th, td {
    text-align: left;
    padding: 8px;
    color: #000;
}
tr:nth-child(even) { background-color: #f2f2f2; }
th {
    background-color: #04AA6D;
    color: white;
}
</style>

<div class="content-page">
    <!-- Start content -->
    <div class="content">
        <div class="container-fluid">
            <h1 class="page-title">Voucher Types</h1>

            <!-- Form for Create/Update -->
            <div class="row">
                <div class="col-lg-12">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="id">

                        <div class="form-group col-lg-3">
                            <label>Voucher Type:</label>
                            <input class="form-control" type="text" name="vtype" id="vtype" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>

            <hr>

            <!-- Display Voucher Types -->
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-title">Voucher Types List</h2>
                    <table id="vouchertypeTable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>SR.No</th>
                                <th>Voucher Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($vouchertypes as $vtype): 
                            ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= htmlspecialchars($vtype['vtype']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editVoucherType(<?= htmlspecialchars(json_encode($vtype)) ?>)">Edit</button>
                                        <a href="" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this voucher type?')">Delete</a>
                                    </td>
                                </tr>
                            <?php 
                            $i++; 
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editVoucherType(vtype) {
    document.getElementById('id').value = vtype.id;
    document.getElementById('vtype').value = vtype.vtype;
}
</script>

<?php
include 'footer.php';
?>
