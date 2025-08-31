<?php
include("../../DatabaseConn/conn.php");

if (isset($_POST['property_id']) && isset($_POST['status'])) {
    $property_id = intval($_POST['property_id']);
    $status = $_POST['status']; // 'approved' or 'rejected'

    if ($status !== 'approved' && $status !== 'rejected') {
        echo "Invalid status";
        exit;
    }

    $stmt = $conn->prepare("UPDATE properties SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $property_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Missing parameters";
}
?>
