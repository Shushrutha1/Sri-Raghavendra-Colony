<?php
session_start();

// Ensure the session is valid and the user has admin access
if (
    !isset($_SESSION['username']) || 
    empty($_SESSION['username']) || 
    !isset($_SESSION['role']) || 
    $_SESSION['role'] != 1
) {
    echo '<script>alert("You do not have the rights to access this page");</script>';
    header("location:index.php");
    exit;
}



ob_start();

// Include necessary files
include 'config.php';
include 'header.php';
include 'sidemenu.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $status = 1; // 1 for active, 0 for inactive

    // Hash the password using bcrypt
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Prepare SQL query to insert the new user
    $query = "INSERT INTO admin (username, password, role, status) VALUES ('$username', '$hashed_password', '$role', '$status')";

    // Execute the query
    if ($conn->query($query) === TRUE) {
        echo '<script>alert("Successfully Created");</script>';
    } else {
        $error_message = "Error creating user: " . $conn->error;
    }
}
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <h1 class="page-title">Create New User</h1>

            <!-- User Creation Form -->
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" class="form-control">
                        <option value="2">User</option>
                        <option value="1">Admin</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Create User</button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
