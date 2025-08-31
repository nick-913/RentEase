<?php
include '../includes/db.php';
$result = $conn->query("SELECT * FROM shift_requests");
?>
<h2>Shift Requests</h2>
<?php if ($result && $result->num_rows > 0): ?>
<table border="1" cellspacing="0" cellpadding="10">
  <tr>
    <th>ID</th>
    <th>Full Name</th>
    <th>Pickup</th>
    <th>Dropoff</th>
    <th>Phone</th>
    <th>Email</th>
    <th>Booking Type</th>
    <th>Schedule</th>
    <th>Message</th>
    <th>Created At</th>
    <th>Action</th>
  </tr>
  <?php while($row = $result->fetch_assoc()): ?>
  <tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['full_name'] ?></td>
    <td><?= $row['pickup_location'] ?></td>
    <td><?= $row['dropoff_location'] ?></td>
    <td><?= $row['phone'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['booking_type'] ?></td>
    <td><?= $row['schedule_date'] ?></td>
    <td><?= $row['message'] ?></td>
    <td><?= $row['created_at'] ?></td>
    <td>
      <form method="POST" action="ajax/update_shift_request_status.php" style="display:inline-block;">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input type="hidden" name="status" value="Accepted">
        <button type="submit" style="background-color: green; color: white;">Accept</button>
      </form>
      <form method="POST" action="ajax/update_shift_request_status.php" style="display:inline-block;">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input type="hidden" name="status" value="Rejected">
        <button type="submit" style="background-color: red; color: white;">Reject</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php else: ?>
  <p>No shift request found.</p>
<?php endif; ?>
