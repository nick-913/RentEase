<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include '../DatabaseConn/conn.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../login/login.php");
  exit();
}

$errors = [];
// Initialize variables for sticky form
$title = $price = $property_type = $location = "";
$bedrooms = $bathrooms = $living_room = "";
$kitchen = $contact_number = $property_location = $description = "";
$facilities = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $user_id = $_SESSION['user_id'];

  // Sanitize inputs
  $title = trim($_POST['title']);
  $price = intval($_POST['price']);
  $property_type = trim($_POST['property_type']);
  $location = trim($_POST['location']);
  $bedrooms = trim($_POST['bedrooms']);
  $bathrooms = trim($_POST['bathrooms']);
  $kitchen = trim($_POST['kitchen']);
  $living_room = trim($_POST['living_room']);
  $contact_number = trim($_POST['contact_number']);
  $property_location = trim($_POST['property_location']);
  $description = trim($_POST['description']);
  $facilities = isset($_POST['facilities']) ? $_POST['facilities'] : [];
  $facilities_str = implode(',', $facilities);

  // Validation
  if (!preg_match('/^(98|97)[0-9]{8}$/', $contact_number)) {
    $errors[] = "Contact number must be exactly 10 digits and start with 98 or 97.";
  }

  if ($price <= 0) {
    $errors[] = "Price must be a positive number.";
  }

  if ($bedrooms === '' || !is_numeric($bedrooms) || intval($bedrooms) < 0) {
    $errors[] = "Bedrooms must be a non-negative number.";
  } else {
    $bedrooms = intval($bedrooms);
  }

  if ($bathrooms === '' || !is_numeric($bathrooms) || intval($bathrooms) < 0) {
    $errors[] = "Bathrooms must be a non-negative number.";
  } else {
    $bathrooms = intval($bathrooms);
  }

  if ($living_room === '' || !is_numeric($living_room) || intval($living_room) < 0) {
    $errors[] = "Living room count must be a non-negative number.";
  } else {
    $living_room = intval($living_room);
  }

  // Proceed only if no errors
  if (empty($errors)) {
    // Upload directory
    $uploadDir = "../uploads/";
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    // Main photo
    $main_photo_name = '';
    if (isset($_FILES['main_photo']) && $_FILES['main_photo']['error'] == 0) {
      $main_photo_name = uniqid() . '_' . basename($_FILES['main_photo']['name']);
      move_uploaded_file($_FILES['main_photo']['tmp_name'], $uploadDir . $main_photo_name);
    } else {
      $errors[] = "Main photo upload error.";
    }

    // Additional photos
    $additional_photos_names = [];
    if (!empty($_FILES['additional_photos']['name'][0])) {
      foreach ($_FILES['additional_photos']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['additional_photos']['error'][$key] == 0) {
          $filename = uniqid() . '_' . basename($_FILES['additional_photos']['name'][$key]);
          move_uploaded_file($tmp_name, $uploadDir . $filename);
          $additional_photos_names[] = $filename;
        }
      }
    }
    $additional_photos_str = implode(',', $additional_photos_names);

    // Extra file
    $extra_file_name = '';
    if (isset($_FILES['extra_file']) && $_FILES['extra_file']['error'] == 0) {
      $extra_file_name = uniqid() . '_' . basename($_FILES['extra_file']['name']);
      move_uploaded_file($_FILES['extra_file']['tmp_name'], $uploadDir . $extra_file_name);
    }

    // Document file (required)
    $document_file_name = '';
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == 0) {
      $document_file_name = uniqid() . '_' . basename($_FILES['document_file']['name']);
      move_uploaded_file($_FILES['document_file']['tmp_name'], $uploadDir . $document_file_name);
    } else {
      $errors[] = "Document upload is required.";
    }

    // Insert into DB if no errors
    if (empty($errors)) {
      $stmt = $conn->prepare("INSERT INTO properties 
        (user_id, title, price, main_photo, additional_photos, property_type, location, bedrooms, bathrooms, kitchen, living_room, contact_number, property_location, description, facilities, extra_file, document_file, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

      if (!$stmt) {
        $errors[] = "SQL Error: " . $conn->error;
      } else {
        $stmt->bind_param(
          "isissssiiisisssss",
          $user_id,
          $title,
          $price,
          $main_photo_name,
          $additional_photos_str,
          $property_type,
          $location,
          $bedrooms,
          $bathrooms,
          $kitchen,
          $living_room,
          $contact_number,
          $property_location,
          $description,
          $facilities_str,
          $extra_file_name,
          $document_file_name
        );

        if ($stmt->execute()) {
          $stmt->close();
          $conn->close();
          header("Location: ../frontpage/frontpage.php");
          exit();
        } else {
          $errors[] = "Error inserting data: " . $stmt->error;
          $stmt->close();
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kotha Bhada - Add Residential Property</title>
  <link rel="stylesheet" href="addroom.css" />
</head>

<body>
  <div class="container">
    <div class="main-content">
      <div class="logo" onclick="goToHomepage()"><span>Rent</span> Ease</div>
      <h1>Add Property</h1>

      <?php if (!empty($errors)): ?>
        <div style="color: red; margin-bottom: 20px;">
          <?php foreach ($errors as $error): ?>
            <p>❌ <?php echo htmlspecialchars($error); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <div class="section">
          <h2>Basic Details</h2>
          <label>Title
            <input type="text" name="title" required value="<?php echo htmlspecialchars($title); ?>" />
          </label>
          <label>Main Photo <input type="file" name="main_photo" accept="image/*" required /></label>
          <label>Additional Photos <input type="file" name="additional_photos[]" multiple accept="image/*" /></label>
          <label>Type
            <input type="text" name="property_type" required value="<?php echo htmlspecialchars($property_type); ?>" />
          </label>
          <label>Location
            <input type="text" name="location" required value="<?php echo htmlspecialchars($location); ?>" />
          </label>
        </div>

        <div class="section">
          <h2>Amenities</h2>
          <label>Bedroom
            <input type="number" name="bedrooms" min="0" required value="<?php echo htmlspecialchars($bedrooms); ?>" />
          </label>
          <label>Bathroom
            <input type="number" name="bathrooms" min="0" required value="<?php echo htmlspecialchars($bathrooms); ?>" />
          </label>
          <label>Kitchen
            <select name="kitchen" required>
              <option value="">Select Option</option>
              <option value="Yes" <?php if ($kitchen === "Yes") echo "selected"; ?>>Yes</option>
              <option value="No" <?php if ($kitchen === "No") echo "selected"; ?>>No</option>
            </select>
          </label>
          <label>Living Room
            <input type="number" name="living_room" min="0" required value="<?php echo htmlspecialchars($living_room); ?>" />
          </label>
        </div>

        <div class="section">
          <h2>More Details</h2>
          <label>Contact Number
            <input type="text" name="contact_number" maxlength="10" pattern="^(98|97)[0-9]{8}$"
              title="Must start with 98 or 97 and have 10 digits" required
              oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10)"
              value="<?php echo htmlspecialchars($contact_number); ?>">
          </label>
          <label>Property Location
            <input type="text" name="property_location" required value="<?php echo htmlspecialchars($property_location); ?>" />
          </label>
          <label>Price (Rs.)
            <input type="number" name="price" min="1" required value="<?php echo htmlspecialchars($price); ?>" />
          </label>
          <label>Description
            <textarea name="description" rows="5" required><?php echo htmlspecialchars($description); ?></textarea>
          </label>
          <label>House Document (Required for Verification)
            <input type="file" name="document_file" accept=".pdf,image/*" required />
          </label>
          <label>Facilities Nearby:</label>
          <div class="facilities">
            <?php
            $facility_options = [
              "Bus", "Swimming Pool", "Party Club", "Hospital", "School",
              "Internet", "Supermarket", "Petrol Pump", "Temple"
            ];
            foreach ($facility_options as $facility) {
              $checked = in_array($facility, $facilities) ? "checked" : "";
              echo "<label><input type='checkbox' name='facilities[]' value='" . htmlspecialchars($facility) . "' $checked /> $facility</label>";
            }
            ?>
          </div>
          <label>Extra File (Optional) <input type="file" name="extra_file" /></label>
        </div>

        <button type="submit" class="submit-btn">SUBMIT INFORMATION</button>
      </form>
    </div>
  </div>

  <script>
    function goToHomepage() {
      window.location.href = "../frontpage/frontpage.php";
    }
  </script>
</body>
</html>
