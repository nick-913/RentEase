<?php
// Connect to database
include '../DatabaseConn/conn.php';

// Fetch all properties ordered by newest first
$sql = "SELECT * FROM properties ORDER BY id DESC"; // Change `id` if your PK column name is different
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RentEase</title>
  <link rel="stylesheet" href="frontpage.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  
  <style>
    /* Added styles for property listing cards */
    .property-listings {
      padding: 40px 20px;
      background-color: #f9f9f9;
    }

    .property-listings h2 {
      text-align: center;
      margin-bottom: 30px;
      font-weight: 600;
      font-family: 'Inter', sans-serif;
      color: #333;
    }

    .properties-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
    }

    .property-card {
      width: 280px;
      border: 1px solid #ddd;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
      background: white;
      transition: transform 0.2s ease-in-out;
      font-family: 'Inter', sans-serif;
    }

    .property-card:hover {
      transform: scale(1.05);
    }

    .property-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    .property-info {
      padding: 15px;
    }

    .property-info h3 {
      margin: 0 0 10px 0;
      font-size: 1.2rem;
      color: #333;
    }

    .property-info p {
      margin: 5px 0;
      font-size: 0.9rem;
      color: #555;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">RENT <span>EASE</span></div>
    <nav>
      <a href="#">WISHLIST <i class="fa-solid fa-heart"></i></a>
      <a href="/RentEase-main/login/login.php">LOGIN <i class="fa-solid fa-user"></i></a>
      <a href="/RentEase-main/addroom/addroom.php">ADD PROPERTY <i class="fa-solid fa-plus"></i></a>
      <button class="btn-outline">FIND ME ROOM</button>
      <button class="btn-orange" id="shiftBtn">
        <i class="fa-solid fa-truck"></i> SHIFT
      </button>
    </nav>
  </header>

  <section class="hero">
    <div class="search-bar">
      <input type="text" placeholder="Search By Title" />
      <input type="text" placeholder="Search For Location" />
      <select>
        <option>Property Type</option>
      </select>
      <select>
        <option>Select Budget</option>
      </select>
      <button class="btn-orange">Search</button>
    </div>
  </section>

  <section class="banner">
    <p>
      न त हामी Mero Bazaar हो, न त हामी Dalay Bhai, हो.<br />
      हामी त बस <span>rentease.com</span>.
    </p>
    <div class="post-banner" id="animated-text">
      POST YOUR RENT FOR FREE<br /><small>www.rentease.com</small>
    </div>
  </section>

  <!-- New Property Listings Section -->
  <section class="property-listings">
    <h2>Available Properties</h2>
    <div class="properties-container">
      <?php
      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $mainPhoto = '../uploads/' . htmlspecialchars($row['main_photo']);
          ?>
          <div class="property-card">
            <img src="<?= $mainPhoto ?>" alt="<?= htmlspecialchars($row['title']) ?>" />
            <div class="property-info">
              <h3><?= htmlspecialchars($row['title']) ?></h3>
              <p><strong>Type:</strong> <?= htmlspecialchars($row['property_type']) ?></p>
              <p><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
              <p><strong>Bedrooms:</strong> <?= (int)$row['bedrooms'] ?> | <strong>Bathrooms:</strong> <?= (int)$row['bathrooms'] ?></p>
              <p><strong>Status:</strong> <?= htmlspecialchars($row['status']) ?></p>
              <p><strong>Contact:</strong> <?= htmlspecialchars($row['contact_number']) ?></p>
            </div>
          </div>
          <?php
        }
      } else {
        echo "<p style='text-align:center;'>No properties found.</p>";
      }
      ?>
    </div>
  </section>

  <script src="frontpage.js"></script>
</body>
</html>
<?php
$conn->close();
?>
