<?php

include 'config.php';

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt = $conn->prepare("SELECT * FROM voucher WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $voucher = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $voucher]);
    } else {
        echo json_encode(['success' => false]);
    }
}
exit;
?>
