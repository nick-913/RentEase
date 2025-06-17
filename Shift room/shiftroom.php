<?php
include '../DatabaseConn/conn.php'; // DB connection

$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $pickup = trim($_POST['pickup']);
    $dropoff = trim($_POST['dropoff']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $when = $_POST['when'];
    $schedule_date = $_POST['schedule_date'];
    $message = trim($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO shift_requests 
        (full_name, pickup_location, dropoff_location, phone, email, booking_type, schedule_date, message) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $full_name, $pickup, $dropoff, $phone, $email, $when, $schedule_date, $message);

    if ($stmt->execute()) {
        $success = "Your quote has been registered successfully!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shift Home - Quote Form</title>
    <link rel="stylesheet" href="shiftroom.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <style>
      .success {
        background: #d4edda;
        color: #155724;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
      }
    </style>
  </head>
  <body>
    <header>
      <div class="logo">RENT <span>EASE</span></div>
    </header>
    <section class="hero">
      <h1>Shift Room</h1>
    </section>

    <div class="form-container">
      <h2>Please fill all the details</h2>

      <?php if ($success): ?>
      <div class="success"><?= $success ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Pick up Location</label>
            <input type="text" name="pickup" value="<?= htmlspecialchars($_POST['pickup'] ?? '') ?>" required />
          </div>
          <div class="form-group">
            <label>Drop off Location</label>
            <input type="text" name="dropoff" value="<?= htmlspecialchars($_POST['dropoff'] ?? '') ?>" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>When</label>
            <select name="when" required>
              <option <?= (($_POST['when'] ?? '') == 'Instant Booking') ? 'selected' : '' ?>>Instant Booking</option>
              <option <?= (($_POST['when'] ?? '') == 'Schedule for Later') ? 'selected' : '' ?>>Schedule for Later</option>
            </select>
          </div>
          <div class="form-group">
            <label>Schedule Date</label>
            <input type="date" name="schedule_date" value="<?= htmlspecialchars($_POST['schedule_date'] ?? '') ?>" />
          </div>
        </div>

        <div class="form-group">
          <label>Message</label>
          <textarea name="message"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </div>

        <button class="submit-btn" type="submit">Ask For Quote</button>
      </form>
    </div>
  </body>
</html>
