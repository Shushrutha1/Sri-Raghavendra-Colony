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
    $plotno = $_POST['plotno'] ?? '';
    $plname = $_POST['plname'] ?? '';
    $phoneno = $_POST['phoneno'] ?? '';
    $status = 1;

    // Sanitize and validate phone number
    $phoneno = preg_replace('/[^0-9+]/', '', $phoneno); // Remove unwanted characters
    if (!preg_match('/^\+?[0-9]{7,15}$/', $phoneno)) {
        echo "<script>alert('Invalid phone number format!');</script>";
        exit;
    }

    if ($id) {
        // Update Record
        $stmt = $conn->prepare("UPDATE plots SET plotno = ?, plname = ?, phoneno = ? WHERE id = ? AND status = ?");
        $stmt->bind_param('sssii', $plotno, $plname, $phoneno, $id, $status);
    } else {
        // Create Record
        $stmt = $conn->prepare("INSERT INTO plots (plotno, plname, phoneno, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $plotno, $plname, $phoneno, $status);
    }
    $stmt->execute();
} elseif (isset($_GET['delete'])) {
    // Handle Delete (Set status=0)
    $id = $_GET['delete'];
    $stmt = $conn->prepare("UPDATE plots SET status = 0 WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

// Fetch All Records
$result = $conn->query("SELECT * FROM plots WHERE status = 1 ORDER BY LENGTH(plotno), plotno");
$plots = $result->fetch_all(MYSQLI_ASSOC);
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
            <h1 class="page-title">Plot Details</h1>

            <!-- Form for Create/Update -->
            <div class="row">
                <div class="col-lg-12">
                  
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="id">

                        <div class="form-group col-lg-3">
                            <label>Plot Number:</label>
                            <input class="form-control" type="text" name="plotno" id="plotno" required>
                        </div>

                        <div class="form-group col-lg-3">
                            <label>Plot Name:</label>
                            <input class="form-control" type="text" name="plname" id="plname" required>
                        </div>

                        <div class="form-group col-lg-3">
                            <label>Phone Number:</label>
                            <input class="form-control" type="text" name="phoneno" id="phoneno" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>

            <hr>

            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

            <!-- Display Plots -->
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-title">Plots List</h2>
                    <table id="plotsTable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>SR.No</th>
                                <th>Plot Number</th>
                                <th>Plot Name</th>
                                <th>Phone Number</th>
                                <?php if ($role == 1): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($plots as $plot): 
                            ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= htmlspecialchars($plot['plotno']) ?></td>
                                    <td><?= htmlspecialchars($plot['plname']) ?></td>
                                    <td><?= htmlspecialchars($plot['phoneno']) ?></td>
                                     <?php if ($role == 1): ?>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editPlot(<?= htmlspecialchars(json_encode($plot)) ?>)">Edit</button>
                                        <a href="?delete=<?= $plot['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this plot?')">Delete</a>
                                    </td>
                                    <?php endif; ?>
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
function editPlot(plot) {
    document.getElementById('id').value = plot.id;
    document.getElementById('plotno').value = plot.plotno;
    document.getElementById('plname').value = plot.plname;
    document.getElementById('phoneno').value = plot.phoneno;
}
</script>


<!-- Include Required Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function () {
    $('#plotsTable').DataTable({
        dom: 'Bflrtip',
        buttons: [
            { extend: 'excelHtml5', text: 'Export to Excel', className: 'btn btn-success btn-sm' },
            { extend: 'csvHtml5', text: 'Export to CSV', className: 'btn btn-info btn-sm' },
            { extend: 'pdfHtml5', text: 'Export to PDF', className: 'btn btn-danger btn-sm' },
            { extend: 'print', text: 'Print', className: 'btn btn-primary btn-sm' }
        ],
        paging: true,
        searching: true,
        responsive: true,
    });
});
</script>
<?php
include 'footer.php';
?>