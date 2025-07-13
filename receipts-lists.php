<?php
session_start();

// Redirect to login if the session variable is not set
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: index.php");
    exit;
}

include 'config.php';
include 'header.php';
include 'sidemenu.php';

?>

<!-- DataTables and related CSS and JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <h1 class="page-title">Receipts Lists</h1>

            <hr>

            <!-- Display Receipts -->
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-title">Receipts List &nbsp;&nbsp; <a href="receipts.php" class="btn btn-primary">New Receipt</a></h2>

                    <br />
                    <table id="receiptsTable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>SR.No</th>
                                <th>Plot No</th>
                                <th>Plot Name</th>
                                <th>Receipt No</th>
                                <th>Receipt Date</th>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Amount</th>
                                <?php if ($role == 1): ?>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <script>
                        $(document).ready(function () {
                            $('#receiptsTable').DataTable({
                                ajax: {
                                    url: 'fetch_receipts.php',
                                    dataSrc: ''
                                },
                                columns: [
                                    { data: 'id' },
                                    { data: 'plotno' },
                                    { data: 'plname' },
                                    { data: 'rno' },
                                    { data: 'rdate', render: function(data) {
                                        return data ? new Date(data).toLocaleDateString() : '--';
                                    }},
                                    { data: 'month' },
                                    { data: 'year' },
                                    { data: 'amount' },
                                    <?php if ($role == 1): ?>
                                        { data: null, render: function(data) {
                                            return `
                                                <button class="btn btn-sm btn-warning" onclick="editReceipt(${data.id})">Edit</button>
                                                <a href="?delete=${data.id}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this receipt?')">Delete</a>
                                            `;
                                        }}
                                    <?php endif; ?>
                                ],
                                dom: 'Bflrtip',
                                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                                pageLength: 10 // Limit data displayed per page
                            });
                        });

                        function editReceipt(id) {
                            // Handle edit functionality
                            alert('Edit ' + id);
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
