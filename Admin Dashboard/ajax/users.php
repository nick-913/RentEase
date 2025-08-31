<?php
include '../includes/db.php';
$result = $conn->query("SELECT * FROM users");
?>
<h2>Registered Users</h2>

<?php if ($result->num_rows > 0): ?>
<table border="1" cellspacing="0" cellpadding="10">
  <tr>
    <th>ID</th>
    <th>Full Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Password</th>
    <th>Created At</th>
    <th>Action</th>
  </tr>
  <?php while($row = $result->fetch_assoc()): ?>
  <tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['full_name'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['phone'] ?></td>
    <td><?= $row['password'] ?></td>
    <td><?= $row['created_at'] ?></td>
    <td>
      <form method="POST" action="ajax/delete_user.php" onsubmit="return confirm('Are you sure?')">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <button type="submit">Delete</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php else: ?>
  <p>No registered users found.</p>
<?php endif; ?>
