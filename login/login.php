<?php
include '../DatabaseConn/conn.php'; // Replace with actual DB connection file path

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $errors[] = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $full_name, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION["user_id"] = $user_id;
                $_SESSION["user_name"] = $full_name;
                header("Location: ../frontpage/frontpage.php");
                exit();
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No user found with that email.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RentEase Login</title>
    <link rel="stylesheet" href="login.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter&display=swap"
    />
  </head>
  <body>
    <div class="container">
      <div class="left">
        <h1>WELCOME TO RentEase</h1>
        <p>Your Ultimate renting partner</p>
        <img src="https://i.imgur.com/BGQ90YZ.png" alt="Promo Image" />
        <div class="links">
          <span>Terms and condition</span>
          <span>Privacy Policy</span>
          <span>FAQ'S</span>
        </div>
      </div>
      <div class="right">
        <h2>LOGIN</h2>
        <p class="register-link">
          New to the site?
          <a href="../registerpage/registerpage.php">Register</a>
        </p>

        <!-- Show errors -->
        <?php if (!empty($errors)): ?>
        <div style="color: red; background: #ffe0e0; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
          <?php foreach ($errors as $error) echo "• $error<br>"; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="form-group">
            <label>Email Address</label>
            <input
              type="text"
              name="email"
              placeholder="Enter your email"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            />
          </div>

          <div class="form-group">
            <label>Password</label>
            <input
              type="password"
              name="password"
              placeholder="Enter your password"
            />
          </div>

          <div class="forgot-password">Forgot Password?</div>

          <p class="privacy-note">
            We’ll text you to confirm your email. Standard message and data
            rates apply. <a href="#">Privacy Policy</a>
          </p>

          <button class="btn" type="submit">Continue →</button>

          <div class="divider">OR</div>

          <div style="text-align: center; font-size: 18px; margin-bottom: 10px">
            Continue With
          </div>

          <div class="social-buttons">
            <button>
              <img src="https://i.imgur.com/2y0cT6D.png" alt="Facebook" />
              Facebook
            </button>
            <button>
              <img src="https://i.imgur.com/3rqr9kR.png" alt="Google" />
              Google
            </button>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>
