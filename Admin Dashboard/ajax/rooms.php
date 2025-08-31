<?php
include '../includes/db.php';
$result = $conn->query("SELECT * FROM properties");
?>

<h2>Listed Rooms</h2>

<table border="1" cellspacing="0" cellpadding="10">
  <tr>
    <th>User ID</th>
    <th>ID</th>
    <th>Title</th>
    <th>Main Photo</th>
    <th>Additional Photos</th>
    <th>Property Type</th>
    <th>Location</th>
    <th>Bedrooms</th>
    <th>Bathrooms</th>
    <th>Kitchen</th>
    <th>Living Room</th>
    <th>Status</th>
    <th>Contact Number</th>
    <th>Property Location</th>
    <th>Description</th>
    <th>Facilities</th>
    <th>Extra File</th>
    <th>Created At</th>
    <th>Price</th>
    <th>Action</th>
  </tr>

  <?php while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row['user_id'] ?></td>
      <td><?= $row['id'] ?></td>
      <td><?= $row['title'] ?></td>

      <!-- Main Photo -->
      <td>
        <?php if (!empty($row['main_photo'])): ?>
          <img src="../uploads/<?= $row['main_photo'] ?>" alt="Main Photo" width="100" height="100">
        <?php else: ?>
          No Image
        <?php endif; ?>
      </td>

      <!-- Additional Photos -->
      <td>
        <?php
          $photos = explode(',', $row['additional_photos']);
          foreach ($photos as $photo) {
            if (!empty($photo)) {
              echo "<img src='../uploads/$photo' width='50' height='50' style='margin:2px'>";
            }
          }
        ?>
      </td>

      <td><?= $row['property_type'] ?></td>
      <td><?= $row['location'] ?></td>
      <td><?= $row['bedrooms'] ?></td>
      <td><?= $row['bathrooms'] ?></td>
      <td><?= $row['kitchen'] ?></td>
      <td><?= $row['living_room'] ?></td>
      <td><?= $row['status'] ?></td>
      <td><?= $row['contact_number'] ?></td>
      <td><?= $row['property_location'] ?></td>
      <td><?= $row['description'] ?></td>
      <td><?= $row['facilities'] ?></td>
      <td>
        <?php if (!empty($row['extra_file'])): ?>
          <a href="../uploads/<?= $row['extra_file'] ?>" target="_blank">View File</a>
        <?php else: ?>
          N/A
        <?php endif; ?>
      </td>
      <td><?= $row['created_at'] ?></td>
      <td>Rs. <?= $row['price'] ?></td>
      <td>
        <form method="POST" action="ajax/delete_room.php" onsubmit="return confirm('Delete room?')">
          <input type="hidden" name="id" value="<?= $row['id'] ?>">
          <button type="submit">Delete</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
</table>
