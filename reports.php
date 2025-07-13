<?php
include 'config.php';
session_start(); 
// If session variable is not set it will redirect to login page
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location:index.php");
  exit;
}
//include 'config.php';
include 'header.php';
include 'sidemenu.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <h1 class="page-title">REPORTS</h1>
            
            <!-- Table with links for Receipts and Vouchers -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center" style="background-color:#FC3; color:#000">Receipts</th>
                            <th class="text-center">Vouchers</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <ul class="list-unstyled">
                                     <li><a href="Reciepts_report_year.php" class="btn btn-primary">Year-Wise Report</a></li><br />
                                    <li><a href="Reciepts_report_month.php" class="btn btn-pink">Month-Wise Report</a></li><br />
                                    <li><a href="Reciepts_report.php" class="btn btn-success">Year & Month-Wise Report</a></li><br />
                                    <li><a href="Reciepts_report_plno.php" class="btn btn-purple">Plot Number-Wise Report</a></li><br />
                                    <li><a href="Reciepts_report_plname.php" class="btn btn-inverse">Plot Name-Wise Report</a></li><br />
                                    <li><a href="Reciepts_report_rno.php" class="btn btn-danger">Receipts Number-Wise Report</a></li><br />
                                    <li><a href="Reciepts_from_to.php" class="btn btn-primary">Receipts From - To</a></li>
                                </ul>
                            </td>
                            <td>
                                <ul class="list-unstyled">
                                    <li><a href="voucher_report_year.php" class="btn btn-danger">Year-Wise Report</a></li><br />
                                    <li><a href="voucher_report_month.php" class="btn btn-inverse">Month-Wise Report</a></li><br />
                                    <li><a href="voucher_report.php" class="btn btn-purple">Year & Month-Wise Report</a></li><br />
                                    <li><a href="voucher_report_vno.php" class="btn btn-warning">Voucher Number-Wise Report</a></li><br />
                                    <li><a href="Voucher_type.php" class="btn btn-pink">Voucher Type Report</a></li><br />
                                    <li><a href="Voucher_from_to.php" class="btn btn-success">Voucher From - To</a></li>                                    
                                </ul>
                                
                                <hr />
                                <ul>
                                <li><a href="backup.php" class="btn btn-primary">Click Here to Dabase Backup</a></li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>



<style>
/* General Styling */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f7f6;
}

/* Title Styling */
.page-title {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
}

/* Table Styling */
.table-bordered {
    width: 100%;
    border: 1px solid #ddd;
    margin-bottom: 20px;
}

.table thead th {
    background-color: #3498db;
    color: white;
    font-size: 18px;
}

.table td, .table th {
    padding: 12px;
    text-align: left;
}

.table td a {
    color: #3498db;
    text-decoration: none;
    font-size: 16px;
}


/* Media Query for Responsiveness */
@media (max-width: 767px) {
    .table-responsive {
        margin-bottom: 20px;
    }

    .table {
        font-size: 14px;
    }

    .noprint {
        font-size: 14px;
        padding: 10px 20px;
    }
}
</style>

<?php include 'footer.php'; ?>
