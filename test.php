<?php
include('config.php'); // Include the database configuration

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure username is set in session
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
   // echo 'Session username not set. Redirecting to index.php';
    header("location:index.php");
    exit;
} else {
    // Fetch username from session
    $username = $_SESSION['username'];

    // Prepare the query to check if the username exists in the database
    $query = "SELECT * FROM admin WHERE username = ?";

    if ($stmt = $conn->prepare($query)) {
        // Bind the session username to the query
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            // Store username and role in session if not already set
            if (!isset($_SESSION['role'])) {
                $_SESSION['role'] = $row['role']; // Set role in session if not set
            }

            // Check the session role
            if ($_SESSION['role'] == 1) {
                // User is an admin, proceed with admin logic
                echo '<script>alert("My role is: Admin");</script>';
                // Further admin actions
            } else {
                // User is not an admin, redirect to the receipts page
                echo '<script>alert("User is not an admin. Please login as Admin."); window.location.href = "receipts.php";</script>';
                exit;
            }
        } 
    } 
}
?>
