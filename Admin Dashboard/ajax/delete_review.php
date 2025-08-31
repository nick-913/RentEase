<?php
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
  $id = intval($_POST['id']);
  
  if ($conn->query("DELETE FROM reviews WHERE id = $id") === TRUE) {
    header("Location: http://localhost/RentEase-main 2/RentEase-main/Admin%20Dashboard/dashboard.php?page=review");
    exit();
  } else {
    echo "error";
  }
}
?>
