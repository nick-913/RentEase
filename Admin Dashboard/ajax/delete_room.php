<?php
include '../includes/db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
  $id = intval($_POST['id']);
 if($conn->query("DELETE FROM properties WHERE id = $id")){
  header("Location: http://localhost/RentEase-main 2/RentEase-main/Admin%20Dashboard/dashboard.php?page=shift_request");
  exit();}else {
    echo "error";
  }
}
?>