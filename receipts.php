<?php
session_start();
// Redirect to login if not authenticated
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: index.php");
    exit;
}

include 'config.php';
include 'header.php';
include 'sidemenu.php';

$is_edit = isset($_GET['id']) && !empty($_GET['id']);  // Check if it's an edit

// Fetch the latest receipt number
$sql = "SELECT MAX(rno) AS last_rno FROM receipts WHERE status=1";
$result = $conn->query($sql);
$new_rno = $result->num_rows > 0 ? $result->fetch_assoc()['last_rno'] + 1 : 1;

// Handle Create/Update/Delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $plotno = $_POST['plotno'] ?? '';
    $plname = $_POST['plname'] ?? '';
    $rno = $_POST['rno'] ?? '';
    $rdate = $_POST['rdate'] ?? '';
    $month = $_POST['month'] ?? '';
    $year = $_POST['year'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $status = 1;

    if ($id) {
        $stmt = $conn->prepare("UPDATE receipts SET plotno = ?, plname = ?, rno = ?, rdate = ?, month = ?, year = ?, amount = ? WHERE id = ?");
        $stmt->bind_param('ssssssii', $plotno, $plname, $rno, $rdate, $month, $year, $amount, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO receipts (plotno, plname, rno, rdate, month, year, amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssii', $plotno, $plname, $rno, $rdate, $month, $year, $amount, $status);
    }
    $stmt->execute();
    echo '<script>alert("Saved successfully!"); window.location.href = "receipts.php";</script>';
    exit;
} elseif (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("UPDATE receipts SET status = 0 WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo '<script>alert("Deleted successfully!"); window.location.href = "receipts.php";</script>';
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipts Management</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
    </style>
</head>
<body>
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <h1 class="page-title">Receipts</h1>
                <!-- Form for Create/Update -->
                <div class="row">
                    <div class="col-lg-12">
                       <form method="POST" action="">
                        <input type="hidden" name="id" id="id">
						<div class="form-group col-lg-3">
                            <label>Receipt No:</label>
                            <!-- <input class="form-control" type="text" name="rno" id="rno" required> -->
   <input class="form-control" type="text" name="rno" id="rno" value="<?php echo htmlspecialchars($new_rno); ?>" required readonly="readonly">
                        </div>
                       <!--  <div class="form-group col-lg-3">
                            <label>Plot No:</label>
                            <input class="form-control" type="text" name="plotno" id="plotno" oninput="fetchPlotDetails()">
                            

                        </div> -->
                        
                        <div class="form-group col-lg-3">
    <label>Plot No:</label>
    <select class="form-control" name="plotno" id="plotno" onChange="fetchPlotDetails()">
        <option value="">Select Plot No</option>
        <?php
        $query = "SELECT plotno FROM plots WHERE status = 1 ORDER BY LENGTH(plotno), plotno"; // Adjust table name accordingly
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($row['plotno']) . '">' . htmlspecialchars($row['plotno']) . '</option>';
        }
        ?>
    </select>
</div>

                        <div class="form-group col-lg-3">
                            <label>Plot Name:</label>
                            <input class="form-control" type="text" name="plname" id="plname" readonly>
                            
                        </div>
                        
                        <div class="form-group col-lg-3">
    <label>Receipt Date:</label>
    <input class="form-control" type="date" name="rdate" id="rdate" value="<?php echo date('Y-m-d'); ?>" required>
</div>
                        <div class="form-group col-lg-3">
                            <label>Month:</label>
                            <select class="form-control" name="month" id="month">
                                <option value="January">January</option>
                                <option value="February">February</option>
                                <option value="March">March</option>
                                <option value="April">April</option>
                                <option value="May">May</option>
                                <option value="June">June</option>
                                <option value="July">July</option>
                                <option value="August">August</option>
                                <option value="September">September</option>
                                <option value="October">October</option>
                                <option value="November">November</option>
                                <option value="December">December</option>
                            </select>
                        </div>
                        <div class="form-group col-lg-3">
                            <label>Year:</label>
                            <input class="form-control" type="number" min="1990" name="year" id="year" value="<?php echo date('Y'); ?>" required>
                        </div>
                       <div class="form-group col-lg-3">
    <label>Amount:</label>
    <input class="form-control" type="number" min="0" name="amount" id="amount" value="500" required>
</div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                    </div>
                </div>
                <hr>
                <h1>Receipts Management</h1>
                <table id="receiptsTable" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Sl.No</th>
                            <th>Plot No</th>
                            <th>Plot Name</th>
                            <th>Receipt No</th>
                            <th>Receipt Date</th>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>


<script>

/*
let debounceTimeout;

function fetchPlotDetails() {
    clearTimeout(debounceTimeout); // Clear any previous timeout
    debounceTimeout = setTimeout(() => {
        const plotnoInput = document.getElementById('plotno');
        const plotno = plotnoInput.value.trim(); // Trim spaces to avoid unnecessary issues

        if (plotno) {
            // Use the existing get_plot_name.php to fetch the plot name
            fetch(`get_plot_name.php?plotno=${plotno}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('plname').value = data.plname;
                    } else {
                        document.getElementById('plname').value = '';
                        alert('Plot not found or inactive!');
                        plotnoInput.value = '';
                        plotnoInput.focus();
                    }
                })
                .catch(error => {
                    console.error('Error fetching plot details:', error);
                    alert('An error occurred while fetching plot details.');
                    plotnoInput.focus();
                });
        }
    }, 300); // Delay of 300ms after the last input
}

*/

function fetchPlotDetails() {
    const plotno = document.getElementById('plotno').value;

    if (plotno) {
        fetch(`get_plot_name.php?plotno=${plotno}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('plname').value = data.plname;
                } else {
                    document.getElementById('plname').value = '';
                    alert('Plot not found or inactive!');
                }
            })
            .catch(error => {
                console.error('Error fetching plot details:', error);
                alert('An error occurred while fetching plot details.');
            });
    } else {
        document.getElementById('plname').value = '';
    }
	
	 if (plotno) {
            fetch(`get_plot_details.php?plotno=${plotno}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('plname').value = data.plname;
                        document.getElementById('month').value = data.month;
                        document.getElementById('year').value = data.year;
                    } else {
                        document.getElementById('plname').value = '';
                        document.getElementById('month').value = new Date().toLocaleString('default', { month: 'long' });
                        document.getElementById('year').value = new Date().getFullYear();
                        alert('Plot not found or inactive!');
                    }
                })
                .catch(error => {
                    console.error('Error fetching plot details:', error);
                    alert('An error occurred while fetching plot details.');
                });
        } else {
            document.getElementById('plname').value = '';
            document.getElementById('month').value = new Date().toLocaleString('default', { month: 'long' });
            document.getElementById('year').value = new Date().getFullYear();
        }
	
	
	
}

</script>

<script>
let lastFocusData = null; // Store the last successful plot name for reuse
// Add event listener for the "focus" event to reuse last focus data
document.getElementById('plotno').addEventListener('focus', () => {
    const plnameInput = document.getElementById('plname');

    if (lastFocusData) {
        plnameInput.value = lastFocusData; // Reuse the last fetched plot name
    }
});


</script>
    <script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#receiptsTable').DataTable({
            ajax: {
                url: 'receipts_api.php', // API endpoint to fetch JSON data
                dataSrc: 'data'
            },
            columns: [
                { 
                    data: null, 
                    render: function (data, type, row, meta) {
                        return meta.row + 1; // Add serial number
                    } 
                },
                { data: 'plotno' },
                { data: 'plname' },
                { data: 'rno' },
                { data: 'rdate' },
                { data: 'month' },
                { data: 'year' },
                { data: 'amount' },
                {
                    data: null,
                    render: function (data) {
                        return `
                            <button class="btn btn-primary edit-btn" data-receipt='${JSON.stringify(data)}'>Edit</button>
                            <button class="btn btn-danger delete-btn" data-id="${data.id}">Delete</button>
                        `;
                    }
                }
            ]
        });

        // Handle Edit Button Click
        $('#receiptsTable').on('click', '.edit-btn', function () {
            const receipt = $(this).data('receipt');
            editReceipt(receipt);
        });

        // Handle Delete Button Click
        $('#receiptsTable').on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            deleteReceipt(id);
        });
    });

    function editReceipt(receipt) {
        $('#id').val(receipt.id);
        $('#plotno').val(receipt.plotno);
        $('#plname').val(receipt.plname);
        $('#rno').val(receipt.rno);
        $('#rdate').val(receipt.rdate);
        $('#month').val(receipt.month);
        $('#year').val(receipt.year);
        $('#amount').val(receipt.amount);
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    }

   /* function deleteReceipt(id) {
        if (confirm('Are you sure?')) {
            $.get(`receipts.php?delete=${id}`, function () {
                alert('Deleted successfully!');
                $('#receiptsTable').DataTable().ajax.reload();
            });
        }
    }*/
</script>
<script>
        function editReceipt(receipt) {
            $('#id').val(receipt.id);
            $('#plotno').val(receipt.plotno);
            $('#plname').val(receipt.plname);
            $('#rno').val(receipt.rno);
            $('#rdate').val(receipt.rdate);
            $('#month').val(receipt.month);
            $('#year').val(receipt.year);
            $('#amount').val(receipt.amount);
            $('html, body').animate({ scrollTop: 0 }, 'slow');
        }

        function deleteReceipt(id) {
            if (confirm('Are you sure?')) {
                $.get(`receipts.php?delete=${id}`, function () {
                    alert('Deleted successfully!');
                    $('#receiptsTable').DataTable().ajax.reload();
                });
            }
        }
    </script>
    
    

</body>
</html>
