<?php
/*include 'config.php';

if (isset($_GET['plotno'])) {
    $plotno = $_GET['plotno'];

    // Query the database for the plot name
    $stmt = $conn->prepare("SELECT plname FROM plots WHERE plotno = ?");
    $stmt->bind_param('s', $plotno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'plname' => $row['plname']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Plot not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
*/



include 'config.php';

$plotno = isset($_GET['plotno']) ? $_GET['plotno'] : '';

if ($plotno) {
    $stmt = $conn->prepare("SELECT plname FROM plots WHERE PlotNo LIKE ? AND status = 1");
    $plotno = "%$plotno%"; // Add wildcards for partial matching
    $stmt->bind_param("s", $plotno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['success' => true, 'plname' => $row['plname']]);
    } else {
        echo json_encode(['success' => false]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false]);
}

?>
