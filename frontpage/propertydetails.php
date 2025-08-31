<?php
session_start();
include '../DatabaseConn/conn.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid property ID");
}

$property_id = (int) $_GET['id'];

// Fetch property and owner info
$stmt = $conn->prepare("SELECT p.*, u.full_name as owner_name, u.id as owner_id FROM properties p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
if (!$stmt) {
  die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  die("Property not found");
}

$property = $result->fetch_assoc();
$stmt->close();

// Fetch all other properties for Jaccard similarity
$all_properties = [];
$similarity_with = 0.0;

$similar_stmt = $conn->prepare("SELECT id, price, property_type, location FROM properties WHERE id != ?");
$similar_stmt->bind_param("i", $property_id);
$similar_stmt->execute();
$similar_result = $similar_stmt->get_result();

if ($similar_result->num_rows > 0) {
  while ($row = $similar_result->fetch_assoc()) {
    $all_properties[] = $row;
  }
}
$similar_stmt->close();

function jaccard_similarity($a, $b)
{
  $setA = [$a['price'], strtolower($a['property_type']), strtolower($a['location'])];
  $setB = [$b['price'], strtolower($b['property_type']), strtolower($b['location'])];

  $intersection = count(array_intersect($setA, $setB));
  $union = count(array_unique(array_merge($setA, $setB)));
  return $union ? $intersection / $union : 0;
}

foreach ($all_properties as $other) {
  $similarity = jaccard_similarity($property, $other);
  if ($similarity > $similarity_with) {
    $similarity_with = $similarity;
  }
}

// Remaining part of the script continues below...

// Handle "I am Interested" submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['interested'])) {
  if (!isset($_SESSION['user_id'])) {
    $message = "You must be logged in to express interest.";
  } else {
    $interested_user_id = $_SESSION['user_id'];
    $owner_id = $property['owner_id'];

    if ($interested_user_id == $owner_id) {
      $message = "You cannot express interest in your own property.";
    } else {
      $user_stmt = $conn->prepare("SELECT full_name, phone FROM users WHERE id = ?");
      $user_stmt->bind_param("i", $interested_user_id);
      $user_stmt->execute();
      $user_result = $user_stmt->get_result();
      $interested_user = $user_result->fetch_assoc();
      $user_stmt->close();

      $interested_user_name = htmlspecialchars($interested_user['full_name'] ?? 'Unknown', ENT_QUOTES);
      $interested_user_contact = htmlspecialchars($interested_user['phone'] ?? 'No contact', ENT_QUOTES);
      $property_title = htmlspecialchars($property['title'], ENT_QUOTES);

      $notify_message = "User {$interested_user_name} is interested in your property titled '{$property_title}'. Contact: {$interested_user_contact}.";

      $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
      $notif_stmt->bind_param("is", $owner_id, $notify_message);
      if ($notif_stmt->execute()) {
        $message = "Your interest has been sent to the property owner.";
      } else {
        $message = "Failed to send interest notification.";
      }
      $notif_stmt->close();
    }
  }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && !empty(trim($_POST['review']))) {
  if (isset($_SESSION['user_id'])) {
    $review_text = trim($_POST['review']);
    $user_id = $_SESSION['user_id'];
    $review_stmt = $conn->prepare("INSERT INTO reviews (property_id, user_id, review_text) VALUES (?, ?, ?)");
    $review_stmt->bind_param("iis", $property_id, $user_id, $review_text);
    if ($review_stmt->execute()) {
      $message = "Review submitted successfully.";
      echo "<meta http-equiv='refresh' content='1'>";
    } else {
      $message = "Failed to submit review.";
    }
    $review_stmt->close();
  }
}

// Fetch photos
$photos = [];
if (!empty($property['main_photo'])) {
  $photos[] = $property['main_photo'];
}
if (!empty($property['additional_photos'])) {
  $additional_photos_arr = array_map('trim', explode(',', $property['additional_photos']));
  foreach ($additional_photos_arr as $photo) {
    if ($photo && !in_array($photo, $photos)) {
      $photos[] = $photo;
    }
  }
}
if (!empty($property['extra_file']) && !in_array($property['extra_file'], $photos)) {
  $photos[] = $property['extra_file'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($property['title']) ?> - RentEase</title>
  <link rel="stylesheet" href="frontpage.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    .property-details {
      max-width: 900px;
      margin: 30px auto;
      background: white;
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .gallery {
      position: relative;
      overflow: hidden;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .gallery img {
      width: 100%;
      display: none;
    }

    .gallery img.active {
      display: block;
    }

    .gallery-controls {
      position: absolute;
      top: 50%;
      width: 100%;
      display: flex;
      justify-content: space-between;
      transform: translateY(-50%);
      padding: 0 15px;
    }

    .gallery-controls button {
      background: rgba(0, 0, 0, 0.6);
      border: none;
      color: #fff;
      font-size: 20px;
      padding: 8px 14px;
      border-radius: 50%;
      cursor: pointer;
    }

    h1 {
      font-size: 1.8rem;
      margin-top: 20px;
      color: #333;
    }

    .description {
      margin: 15px 0;
      font-size: 1rem;
      color: #555;
      line-height: 1.6;
    }

    .info-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 20px;
    }

    .info-item {
      flex: 1 1 40%;
      font-size: 0.95rem;
      color: #333;
    }

    .owner-label {
      font-weight: bold;
      font-size: 1rem;
      margin-top: 10px;
      color: #222;
    }

    .btn-orange {
      display: block;
      background: orange;
      color: #fff;
      font-weight: bold;
      text-align: center;
      border: none;
      padding: 12px;
      border-radius: 6px;
      font-size: 1rem;
      margin-top: 30px;
      cursor: pointer;
      width: 100%;
    }

    .message {
      margin-top: 15px;
      padding: 10px;
      border-radius: 6px;
    }

    .success {
      background: #d4edda;
      color: #155724;
    }

    .error {
      background: #f8d7da;
      color: #721c24;
    }
  </style>
</head>

<body>
  <div class="property-details">
    <div class="gallery">
      <?php foreach ($photos as $index => $photo): ?>
        <img src="../uploads/<?= htmlspecialchars($photo) ?>" class="<?= $index === 0 ? 'active' : '' ?>"
          alt="Property photo <?= $index + 1 ?>">
      <?php endforeach; ?>
      <?php if (count($photos) > 1): ?>
        <div class="gallery-controls">
          <button id="prevBtn">&#10094;</button>
          <button id="nextBtn">&#10095;</button>
        </div>
      <?php endif; ?>
    </div>

    <h1><?= htmlspecialchars($property['title']) ?></h1>
    <div class="description"><?= nl2br(htmlspecialchars($property['description'])) ?></div>

    <div class="info-grid">
      <div class="info-item"><strong>Price:</strong> Rs. <?= number_format($property['price'], 0) ?></div>
      <div class="info-item"><strong>Type:</strong> <?= htmlspecialchars($property['property_type']) ?></div>
      <div class="info-item"><strong>Location:</strong> <?= htmlspecialchars($property['location']) ?></div>
      <div class="info-item"><strong>Bedrooms:</strong> <?= (int) $property['bedrooms'] ?></div>
      <div class="info-item"><strong>Bathrooms:</strong> <?= (int) $property['bathrooms'] ?></div>
      <div class="info-item"><strong>Living Room:</strong> <?= (int) $property['living_room'] ?></div>
      <div class="info-item"><strong>Contact:</strong> <?= htmlspecialchars($property['contact_number']) ?></div>
      <div class="info-item"><strong>Similarity Score:</strong> <?= number_format($similarity_with, 2) ?></div>
    </div>

    <div class="owner-label">Owner: <?= htmlspecialchars($property['owner_name']) ?></div>

    <?php if ($message): ?>
      <div
        class="message <?= strpos($message, 'Failed') === false && strpos($message, 'cannot') === false ? 'success' : 'error' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
      <form method="POST">
        <input type="hidden" name="interested" value="1">
        <button type="submit" class="btn-orange">I am Interested</button>
      </form>
    <?php else: ?>
      <p><a href="../login/login.php">Login</a> to express interest.</p>
    <?php endif; ?>

    <hr style="margin: 40px 0 20px;">
    <h2>Reviews</h2>

    <div style="margin-bottom: 30px;">
      <?php
      $review_stmt = $conn->prepare("SELECT r.review_text, r.created_at, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.property_id = ? ORDER BY r.created_at DESC");
      $review_stmt->bind_param("i", $property_id);
      $review_stmt->execute();
      $reviews_result = $review_stmt->get_result();
      if ($reviews_result->num_rows > 0):
        while ($review = $reviews_result->fetch_assoc()):
          ?>
          <div style="background: #f1f1f1; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
            <strong><?= htmlspecialchars($review['full_name']) ?></strong><br>
            <small><?= htmlspecialchars($review['created_at']) ?></small>
            <p style="margin-top: 8px;"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
          </div>
        <?php endwhile; else:
        echo "<p>No reviews yet.</p>"; endif;
      $review_stmt->close(); ?>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
      <form method="POST">
        <textarea name="review" rows="4"
          style="width:100%; padding:10px; font-size:1rem; border-radius:8px; border:1px solid #ccc;"
          placeholder="Write your review here..." required></textarea>
        <button type="submit" name="submit_review" class="btn-orange" style="margin-top: 10px;">Post Review</button>
      </form>
    <?php else: ?>
      <p><a href="../login/login.php">Login</a> to write a review.</p>
    <?php endif; ?>
  </div>

  <script>
    (() => {
      const photos = document.querySelectorAll('.gallery img');
      let currentIndex = 0;
      const showImage = (index) => {
        photos.forEach((img, i) => img.classList.toggle('active', i === index));
      };
      document.getElementById('prevBtn')?.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + photos.length) % photos.length;
        showImage(currentIndex);
      });
      document.getElementById('nextBtn')?.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % photos.length;
        showImage(currentIndex);
      });
    })();
  </script>
</body>

</html>
<?php $conn->close(); ?>