<?php
include("../../DatabaseConn/conn.php");

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);

    if ($action === "approve") {
        $stmt = $conn->prepare("UPDATE properties SET status='approved' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === "reject") {
        $stmt = $conn->prepare("UPDATE properties SET status='rejected' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to approvals page
header("Location: property_approvals.php");
exit;
?>
