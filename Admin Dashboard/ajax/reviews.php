<?php
include '../includes/db.php';
$result = $conn->query("SELECT * FROM reviews");
?>
<h2>All Reviews</h2>

<?php if ($result->num_rows > 0): ?>
<table border="1" cellspacing="0" cellpadding="10">
  <tr>
    <th>ID</th>
    <th>Property ID</th>
    <th>User ID</th>
    <th>Review Text</th>
    <th>Created At</th>
    <th>Action</th>
  </tr>
  <?php while($row = $result->fetch_assoc()): ?>
  <tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['property_id'] ?></td>
    <td><?= $row['user_id'] ?></td>
    <td><?= $row['review_text'] ?></td>
    <td><?= $row['created_at'] ?></td>
    <td>
      <form method="POST" action="ajax/delete_review.php" onsubmit="return confirm('Are you sure you want to delete this review?')">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <button type="submit">Delete</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php else: ?>
  <p>No reviews found.</p>
<?php endif; ?>
