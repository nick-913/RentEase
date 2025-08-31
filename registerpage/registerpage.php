<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RentEase</title>
    <link rel="stylesheet" href="register.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
  </head>
  <body>
    <?php
    include '../DatabaseConn/conn.php';

    $errors = [];
    $success = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $full_name = trim($_POST['full_name']);
      $email = trim($_POST['email']);
      $phone = trim($_POST['phone']);
      $password = $_POST['password'];
      $confirm_password = $_POST['confirm_password'];

      // Validation
      if (empty($full_name)) $errors[] = "Full Name is required.";

      if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
      }

      if (empty($phone) || !preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Phone number must be exactly 10 digits.";
      }

      if (empty($password)) {
        $errors[] = "Password is required.";
      } elseif (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
        $errors[] = "Password must be at least 8 characters and include uppercase, lowercase, digit, and special character.";
      }

      if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
      }

      if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $phone, $hashed_password);

        if ($stmt->execute()) {
          header("Location: ../login/login.php");
          exit();
        } else {
          $errors[] = "Error: Email might already be registered.";
        }

        $stmt->close();
      }
    }
    ?>

    <div class="container">
      <div class="left">
        <h2>WELCOME TO RentEase</h2>
        <p>Your Ultimate renting partner</p>
        <img src="../frontpage/your-image.jpg" alt="Promo Image" />
        <div class="links">
          <span>◦ <a href="#">Terms and condition</a></span>
          <span>◦ <a href="#">Privacy Policy</a></span>
          <span>◦ <a href="#">FAQ'S</a></span>
        </div>
      </div>

      <div class="right">
        <h2>SIGNUP</h2>
        <p>Already a member? <a href="../login/login.php">Login</a></p>

        <?php if (!empty($errors)): ?>
        <div style="color: red; background: #ffe0e0; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
          <?php foreach ($errors as $error) echo "• $error<br>"; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
        <div style="color: green; background: #e0ffe0; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
          <?= $success ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
          <input type="text" name="full_name" placeholder="Your Full Name"
            value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" />
          <input type="email" name="email" placeholder="Your Email Address"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
          <input type="text" name="phone" placeholder="Your Phone Number"
            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" />
          <input type="password" name="password" placeholder="Password" />
          <input type="password" name="confirm_password" placeholder="Confirm Password" class="full-width" />
          <div class="privacy">
            We’ll text you to confirm your email. Standard message and data rates apply. <a href="#">Privacy Policy</a>
          </div>
          <button type="submit" class="btn">Continue &rarr;</button>
          <div class="or">OR</div>
        </form>
      </div>
    </div>
  </body>
</html>
