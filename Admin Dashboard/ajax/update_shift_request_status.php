<?php
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'], $_POST['status'])) {
  $id = intval($_POST['id']);
  $status = $conn->real_escape_string($_POST['status']);

  // Set response message
  $message = ($status === 'Accepted') 
    ? 'Your shift request has been accepted. You will get a call soon.'
    : 'Your shift request has been rejected.';

  // Update the shift_requests table
  $stmt = $conn->prepare("UPDATE shift_requests SET status = ?, response_message = ? WHERE id = ?");
  if (!$stmt) {
    die("Prepare failed: " . $conn->error);
  }

  $stmt->bind_param("ssi", $status, $message, $id);

  if ($stmt->execute()) {
    $stmt->close();
    header("Location: http://localhost/RentEase-main/RentEase-main/Admin%20Dashboard/dashboard.php?page=shift_request");
    exit();
  } else {
    echo "Error updating request: " . $stmt->error;
  }
}
?>
