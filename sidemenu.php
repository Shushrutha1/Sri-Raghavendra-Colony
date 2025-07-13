<?php
session_start();

// Assuming you have the database connection in $conn

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT username, role FROM admin WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $role);
    $stmt->fetch();
    $stmt->close();
}?>

<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
    <?php echo htmlspecialchars($username); ?> <span class="caret"></span>
</a>

<!-- ========== Left Sidebar Start ========== -->
            <div class="left side-menu">
                <div class="sidebar-inner slimscrollleft">
                    <div class="user-details">
                        <div class="pull-left">
                        <?php if ($role == 1): ?>
                            <img src="images/Meenaiah.jpg" alt="" class="thumb-md img-circle"> 
                            <?php elseif ($role == 2): ?>
                            <img src="images/users/avatar-1.jpg" alt="" class="thumb-md img-circle"> 
                            <?php endif; ?>
                            
                        </div>
                        <div class="user-info">
                            <div class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"> <?php echo htmlspecialchars($username); ?> 
</a>
<!--                                 <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)"><i class="md md-face-unlock"></i> Profile<div class="ripple-wrapper"></div></a></li>
                                    <li><a href="javascript:void(0)"><i class="md md-settings"></i> Settings</a></li>
                                    <li><a href="javascript:void(0)"><i class="md md-lock"></i> Lock screen</a></li>
                                    <li><a href="javascript:void(0)"><i class="md md-settings-power"></i> Logout</a></li>
                                </ul>-->
                            </div>
                            <!-- Displaying role conditionally -->
<?php if ($role == 1): ?>
    <span class="badge badge-success">Admin</span>
<?php elseif ($role == 2): ?>
    <span class="badge badge-warning">Moderator</span>
<?php else: ?>
    <span class="badge badge-secondary">User</span>
<?php endif; ?>
                           <!--  <p class="text-muted m-0">Administrator</p> -->
                        </div>
                    </div>
                    <!--- Divider -->
                    <div id="sidebar-menu">
                        <ul>
                            <li>
                                <a href="plotCreate.php" ><i class="ion-map "></i><span> Plot Creation </span></a>
                            </li>
                            
                            <li>
                                <a href="vouchers.php" ><i class="ion-compose"></i><span> New Voucher </span></a>
                            </li>
                              <li>
                                <a href="receipts.php"> <i class=" md-receipt"></i><span> Receipt</span></a>
                            </li>
                            <?php if ($role == 1): ?>
                             <li>
                                <a href="vouchertype.php" ><i class="ion-person-add "></i><span> Voucher Type </span></a>
                            </li>
                            
                             
                           
                             <li>
                                <a href="reports.php"> <i class="ion-document-text "></i><span> Reports</span></a>
                            </li>
                            <li>
                                <a href="bank_report.php"> <i class=" md-account-balance"></i><span> Bank Balance Report</span></a>
                            </li>
                            <li>
                                <a href="bank_receipts.php"> <i class=" md-account-balance"></i><span> Bank Balance Bunch</span></a>
                            </li>
                            <li>
                                <a href="not_submitted_report.php"> <i class=" ion-pie-graph"></i><span> Yearly Report List</span></a>
                            </li>
                            <?php endif; ?>
                            <hr />
                            <li>
                                <a href="logout.php"> <i class="ion-power"></i><span> Logout</span></a>
                            </li>

                         
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <!-- Left Sidebar End --> 
            
              <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->                      
            