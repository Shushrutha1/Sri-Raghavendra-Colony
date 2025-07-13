<?php
include 'config.php';

if (isset($_GET['plotno'])) {
    $plotno = $_GET['plotno'];

    // Fetch plot name (plname) and last recorded month/year
    $query = "SELECT p.plname, r.month, r.year FROM plots p 
              LEFT JOIN receipts r ON p.plotno = r.plotno 
              WHERE p.plotno = ? 
              ORDER BY r.year DESC, 
              FIELD(r.month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 
              'September', 'October', 'November', 'December') DESC 
              LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $plotno);
    $stmt->execute();
    $result = $stmt->get_result();
    $latest = $result->fetch_assoc();

    if ($latest) {
        // Convert month name to number
        $months = ["January" => 1, "February" => 2, "March" => 3, "April" => 4, "May" => 5, 
                   "June" => 6, "July" => 7, "August" => 8, "September" => 9, "October" => 10, 
                   "November" => 11, "December" => 12];

        $lastMonth = $latest['month'];
        $lastYear = $latest['year'];
        $plname = $latest['plname'];

        // Increment month by 1
        if ($lastMonth) {
            $newMonthNumber = $months[$lastMonth] + 1;
            $newYear = $lastYear;

            if ($newMonthNumber > 12) {
                $newMonthNumber = 1; // Reset to January
                $newYear += 1; // Increment year
            }

            // Convert number back to month name
            $newMonth = array_search($newMonthNumber, $months);
        } else {
            // Default if no previous entry found
            $newMonth = date('F');
            $newYear = date('Y');
        }

        echo json_encode(["success" => true, "plname" => $plname, "month" => $newMonth, "year" => $newYear]);
    } else {
        // If no record found
        echo json_encode(["success" => false]);
    }
} else {
    echo json_encode(["success" => false]);
}
?>
