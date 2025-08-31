<?php
session_start();
include '../DatabaseConn/conn.php';

// Get search parameters
$searchTitle = isset($_GET['title']) ? trim($_GET['title']) : '';
$searchPrice = isset($_GET['price']) ? trim($_GET['price']) : '';
$searchLocation = isset($_GET['location']) ? trim($_GET['location']) : '';

$hasSearch = ($searchTitle !== '' || $searchPrice !== '' || $searchLocation !== '');

$properties = [];
if ($hasSearch) {
    // Get all approved properties
    $sql = "SELECT * FROM properties WHERE status='approved'";
    $result = $conn->query($sql);

    $allProperties = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $allProperties[] = $row;
        }
    }

    // Calculate relevance score
    $scoredProperties = [];
    foreach ($allProperties as $property) {
        $score = 0;
        $matchFound = false;

        // Title
        if ($searchTitle !== '') {
            $propertyTitle = strtolower($property['title'] ?? '');
            $searchTitleClean = strtolower($searchTitle);
            if (strpos($propertyTitle, $searchTitleClean) !== false) {
                $score += 5;
                $matchFound = true;
            }
            $titleWords = explode(' ', $searchTitleClean);
            foreach ($titleWords as $word) {
                if (strlen($word) > 2 && strpos($propertyTitle, $word) !== false) {
                    $score += 2;
                    $matchFound = true;
                }
            }
        }

        // Price
        if ($searchPrice !== '') {
            $propertyPrice = (int)($property['price'] ?? 0);
            $searchPriceInt = (int)$searchPrice;
            if ($searchPriceInt > 0) {
                if ($propertyPrice == $searchPriceInt) {
                    $score += 5;
                    $matchFound = true;
                } elseif (abs($propertyPrice - $searchPriceInt) <= ($searchPriceInt * 0.3)) {
                    $score += 3;
                    $matchFound = true;
                } elseif (abs($propertyPrice - $searchPriceInt) <= ($searchPriceInt * 0.7)) {
                    $score += 1;
                    $matchFound = true;
                } elseif (abs($propertyPrice - $searchPriceInt) <= ($searchPriceInt * 1.5)) {
                    $score += 0.5;
                    $matchFound = true;
                }
            }
        }

        // Location
        if ($searchLocation !== '') {
            $propertyLocation = strtolower($property['location'] ?? '');
            $searchLocationClean = strtolower($searchLocation);
            if ($propertyLocation === $searchLocationClean) {
                $score += 5;
                $matchFound = true;
            } elseif (strpos($propertyLocation, $searchLocationClean) !== false || strpos($searchLocationClean, $propertyLocation) !== false) {
                $score += 3;
                $matchFound = true;
            }
            $locationWords = explode(' ', $searchLocationClean);
            foreach ($locationWords as $word) {
                if (strlen($word) > 2 && strpos($propertyLocation, $word) !== false) {
                    $score += 1;
                    $matchFound = true;
                }
            }
        }

        if ($matchFound || $score > 0) {
            $scoredProperties[] = [
                'property' => $property,
                'score' => $score
            ];
        }
    }

    if (empty($scoredProperties)) {
        foreach ($allProperties as $property) {
            $scoredProperties[] = [
                'property' => $property,
                'score' => 0.1
            ];
        }
    }

    usort($scoredProperties, fn($a, $b) => $b['score'] <=> $a['score']);

    // ✅ Only take top 4 search results
    $properties = array_slice(array_map(fn($item) => $item['property'], $scoredProperties), 0, 4);

} else {
    $sql = "SELECT * FROM properties WHERE status='approved' ORDER BY id DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
    }
}

// ✅ Hot Deals
$hotDeals = [];
if (!$hasSearch) {
    $hotDealsQuery = "SELECT * FROM properties WHERE status = 'approved' ORDER BY price ASC LIMIT 4";
    $hotDealsResult = $conn->query($hotDealsQuery);
    if ($hotDealsResult && $hotDealsResult->num_rows > 0) {
        while ($row = $hotDealsResult->fetch_assoc()) {
            $hotDeals[] = $row;
        }
    }
}

// --- Jaccard Similarity ---
function calculateJaccardSimilarity($searchCriteria, $property) {
    $searchFeatures = [];
    $propertyFeatures = [];

    if (!empty($searchCriteria['title'])) {
        foreach (explode(' ', strtolower($searchCriteria['title'])) as $w) {
            if (strlen($w) > 2) $searchFeatures[] = 'title_'.$w;
        }
    }
    if (!empty($searchCriteria['location'])) {
        foreach (explode(' ', strtolower($searchCriteria['location'])) as $w) {
            if (strlen($w) > 2) $searchFeatures[] = 'location_'.$w;
        }
    }
    if (!empty($searchCriteria['price'])) {
        $searchFeatures[] = 'price_'.getPriceRange((int)$searchCriteria['price']);
    }

    if (!empty($property['title'])) {
        foreach (explode(' ', strtolower($property['title'])) as $w) {
            if (strlen($w) > 2) $propertyFeatures[] = 'title_'.$w;
        }
    }
    if (!empty($property['location'])) {
        foreach (explode(' ', strtolower($property['location'])) as $w) {
            if (strlen($w) > 2) $propertyFeatures[] = 'location_'.$w;
        }
    }
    if (!empty($property['price'])) {
        $propertyFeatures[] = 'price_'.getPriceRange((int)$property['price']);
    }
    if (!empty($property['property_type'])) {
        $propertyFeatures[] = 'type_'.strtolower($property['property_type']);
    }

    if (isset($property['bedrooms'])) $propertyFeatures[] = 'bedrooms_'.$property['bedrooms'];
    if (isset($property['bathrooms'])) $propertyFeatures[] = 'bathrooms_'.$property['bathrooms'];

    if (empty($searchFeatures) || empty($propertyFeatures)) return 0;

    $intersection = array_intersect($searchFeatures, $propertyFeatures);
    $union = array_unique(array_merge($searchFeatures, $propertyFeatures));
    return count($union) > 0 ? count($intersection) / count($union) : 0;
}
function getPriceRange($price) {
    if ($price < 5000) return 'very_low';
    if ($price < 10000) return 'low';
    if ($price < 20000) return 'medium';
    if ($price < 35000) return 'high';
    return 'very_high';
}

// ✅ Recommendations (max 3)
$recommendations = [];
if ($hasSearch) {
    $searchIds = array_column($properties, 'id');
    $allQuery = "SELECT * FROM properties WHERE status = 'approved'";
    $allResult = $conn->query($allQuery);

    $similarities = [];
    if ($allResult && $allResult->num_rows > 0) {
        while ($property = $allResult->fetch_assoc()) {
            if (isset($property['id']) && !in_array($property['id'], $searchIds)) {
                $sim = calculateJaccardSimilarity([
                    'title' => $searchTitle,
                    'price' => $searchPrice,
                    'location' => $searchLocation
                ], $property);
                if ($sim > 0.05) {
                    $similarities[] = ['similarity'=>$sim,'property'=>$property];
                }
            }
        }
    }
    if (empty($similarities)) {
        $allResult = $conn->query($allQuery);
        $fallbackCount = 0;
        if ($allResult) {
            while (($property = $allResult->fetch_assoc()) && $fallbackCount < 3) {
                if (isset($property['id']) && !in_array($property['id'], $searchIds)) {
                    $similarities[] = ['similarity'=>0.1,'property'=>$property];
                    $fallbackCount++;
                }
            }
        }
    }
    usort($similarities, fn($a,$b)=>$b['similarity']<=>$a['similarity']);
    $recommendations = array_slice($similarities, 0, 3);
}

// ✅ Notifications (kept same)
$notifications = [];
if (isset($_SESSION['user_name'])) {
    $username = $_SESSION['user_name'];

    $generalNotifications = [];
    $stmt1 = $conn->prepare("SELECT id, message, is_read, created_at 
    FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC LIMIT 10");
    if ($stmt1) {
        $stmt1->bind_param("i", $_SESSION['user_id']);
        $stmt1->execute();
        $res1 = $stmt1->get_result();
        if ($res1) {
            $generalNotifications = $res1->fetch_all(MYSQLI_ASSOC);
        }
        $stmt1->close();
    }

    $shiftNotifications = [];
    $stmt2 = $conn->prepare("SELECT id, response_message AS message, 0 AS is_read, created_at FROM shift_requests WHERE full_name = ? AND response_message IS NOT NULL ORDER BY created_at DESC LIMIT 10");
    if ($stmt2) {
        $stmt2->bind_param("s", $username);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        if ($res2) {
            $shiftNotifications = $res2->fetch_all(MYSQLI_ASSOC);
        }
        $stmt2->close();
    }

    $notifications = array_merge($generalNotifications, $shiftNotifications);
    usort($notifications, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
    $notifications = array_slice($notifications, 0, 10);
}

$unreadCount = 0;
foreach ($notifications as $notif) {
    if (empty($notif['is_read']) || $notif['is_read'] == 0) {
        $unreadCount++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>RentEase</title>
<link rel="stylesheet" href="frontpage.css" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
.property-listings{padding:40px 20px;background:#f9f9f9}
.property-listings h2{text-align:center;margin-bottom:30px;font-weight:600;font-family:'Inter';color:#333}
.properties-container{display:flex;flex-wrap:wrap;justify-content:center;gap:20px}
.property-card{width:280px;border:1px solid #ddd;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1);background:#fff;transition:transform .2s;font-family:'Inter'}
.property-card:hover{transform:scale(1.05)}
.property-card img{width:100%;height:180px;object-fit:cover}
.property-info{padding:15px}
.property-info h3{margin:0 0 10px;font-size:1.2rem;color:#333}
.property-info p{margin:5px 0;font-size:.9rem;color:#555}
.user-dropdown{position:relative;display:inline-block}
.user-name{background:none;border:none;font-weight:bold;color:#333;cursor:pointer}
.dropdown-menu{display:none;position:absolute;background:#fff;min-width:100px;box-shadow:0 4px 8px rgba(0,0,0,.1);z-index:999;right:0}
.dropdown-menu a{padding:10px 15px;display:block;color:#000;text-decoration:none}
.dropdown-menu a:hover{background:#f2f2f2}
.user-dropdown:hover .dropdown-menu{display:block}
.search-bar{text-align:center;padding:30px;background:#fff;margin:20px 0;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.1)}
.search-bar form{display:flex;gap:15px;flex-wrap:wrap;justify-content:center;align-items:center;max-width:900px;margin:0 auto}
.search-bar input{padding:12px 15px;font-size:14px;min-width:200px;border:2px solid #ddd;border-radius:8px;transition:border-color .3s}
.search-bar input:focus{border-color:#ff6b35;outline:none}
.search-bar input::placeholder{font-size:12px;color:#999}
.btn-orange{background:#ff6b35;color:#fff;border:none;padding:12px 25px;font-weight:bold;border-radius:8px;cursor:pointer;transition:background .3s}
.btn-orange:hover{background:#e55a2b}
.notification-dropdown{position:relative;display:inline-block;margin-left:20px}
#notifBtn{background:none;border:none;cursor:pointer;position:relative;font-size:18px;color:#333}
#notifBtn span{color:#fff;background:red;font-size:12px;padding:2px 6px;border-radius:50%;position:absolute;top:-5px;right:-10px}
#notifMenu{display:none;position:absolute;right:0;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.2);width:320px;max-height:400px;overflow-y:auto;z-index:999;border-radius:5px}
#notifMenu div{padding:10px;border-bottom:1px solid #eee;font-size:14px;color:#333}
#notifMenu div.unread{background:#e6f7ff}
#notifMenu small{color:#666;display:block;margin-bottom:5px;font-size:12px}
.search-results-header{background:#f8f9fa;padding:15px;text-align:center;margin-bottom:20px;border-radius:8px}
.similarity-score{color:#007bff;font-weight:bold;font-size:0.85em}
.clear-search{background:#6c757d;color:#fff;border:none;padding:8px 15px;font-size:12px;border-radius:5px;cursor:pointer;margin-left:10px}
.clear-search:hover{background:#5a6268}
.recommendations-debug{background:#fff3cd;padding:10px;margin:10px 0;border-radius:5px;font-size:12px;color:#856404}
@media (max-width: 768px) {
  .search-bar form{flex-direction:column;}
  .search-bar input{width:100%;min-width:auto;}
}
</style>
</head>
<body>
<header>
  <div class="logo">RENT <span>EASE</span></div>
  <nav>
    <a href="../addroom/addroom.php">ADD PROPERTY <i class="fa-solid fa-plus"></i></a>
    <a href="../Admin Dashboard/adminlogin.php">LOGIN AS ADMIN <i class="fa-solid fa-user-tie"></i></a>
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="user-dropdown">
      <button class="user-name"><?=htmlspecialchars($_SESSION['user_name'] ?? '')?> <i class="fa fa-caret-down"></i></button>
      <div class="dropdown-menu"><a href="logout.php">Logout</a></div>
    </div>
    <div class="notification-dropdown">
      <button id="notifBtn" title="Notifications" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-bell"></i>
        <?php $uc=0; foreach($notifications as$nf){if(empty($nf['is_read'])||$nf['is_read']==0)$uc++;} if($uc>0)echo"<span>$uc</span>"; ?>
      </button>
      <div id="notifMenu" role="menu" aria-label="Notifications">
        <?php if(empty($notifications)): ?>
          <div style="padding:10px;">No notifications</div>
        <?php else: foreach($notifications as $nf): ?>
          <div class="<?= (empty($nf['is_read'])||$nf['is_read']==0)?'unread':'' ?>">
            <small><?=date('d M Y, H:i', strtotime($nf['created_at']))?></small>
            <div><?=htmlspecialchars($nf['message'])?></div>
          </div>
        <?php endforeach; endif;?>
      </div>
    </div>
    <?php else: ?>
    <a href="../login/login.php">LOGIN <i class="fa-solid fa-user"></i></a>
    <?php endif;?>
    <button class="btn-orange" id="shiftBtn"><i class="fa-solid fa-truck"></i> SHIFT</button>
  </nav>
</header>

<section class="hero">
  <div class="search-bar">
    <form method="GET">
      <input type="text" name="title" placeholder="Search by property title (e.g., '1BK', 'room', 'flat')..." value="<?=htmlspecialchars($searchTitle)?>" />
      <input type="number" name="price" placeholder="Enter price (Rs.)" min="0" value="<?=htmlspecialchars($searchPrice)?>" />
      <input type="text" name="location" placeholder="Enter location (e.g., 'Thimi', 'Pepsi')..." value="<?=htmlspecialchars($searchLocation)?>" />
      <button type="submit" class="btn-orange"><i class="fa fa-search"></i> Search</button>
      <?php if($hasSearch): ?>
        <a href="?" class="clear-search"><i class="fa fa-times"></i> Clear</a>
      <?php endif; ?>
    </form>
  </div>
</section>

<?php if($hasSearch): ?>
  <!-- SEARCH RESULTS -->
  <section class="property-listings">
    <div class="search-results-header">
      <h2><i class="fa fa-search"></i> Search Results</h2>
      <p><?= count($properties) ?> properties found matching your search criteria</p>
      <?php if($searchTitle): ?><p><strong>Title:</strong> "<?= htmlspecialchars($searchTitle) ?>"</p><?php endif; ?>
      <?php if($searchPrice): ?><p><strong>Price Range:</strong> Around Rs. <?= number_format($searchPrice) ?></p><?php endif; ?>
      <?php if($searchLocation): ?><p><strong>Location:</strong> "<?= htmlspecialchars($searchLocation) ?>"</p><?php endif; ?>
    </div>
    <div class="properties-container">
      <?php if(!empty($properties)): 
        foreach($properties as $index => $row): ?>
      <a href="propertydetails.php?id=<?= $row['id'] ?? '' ?>" style="text-decoration:none;color:inherit">
        <div class="property-card">
          <img src="../uploads/<?= htmlspecialchars($row['main_photo'] ?? '') ?>" alt="<?= htmlspecialchars($row['title'] ?? '') ?>" />
          <div class="property-info">
            <h3><?= htmlspecialchars($row['title'] ?? 'No Title') ?></h3>
            <p><strong>Price:</strong> Rs. <?= number_format($row['price'] ?? 0, 0) ?></p>
            <p><strong>Type:</strong> <?= htmlspecialchars($row['property_type'] ?? 'Not Specified') ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($row['location'] ?? 'Not Specified') ?></p>
            <p><strong>Owner:</strong> <?= htmlspecialchars($row['owner_name'] ?? 'Not Provided') ?></p>
          </div>
        </div>
      </a>
      <?php endforeach; else: ?>
      <div style="text-align:center;padding:40px;">
        <i class="fa fa-search" style="font-size:48px;color:#ccc;margin-bottom:20px;"></i>
        <h3>No properties found</h3>
        <p>Try adjusting your search criteria or browse all available properties.</p>
        <a href="?" class="btn-orange" style="display:inline-block;margin-top:15px;text-decoration:none;">View All Properties</a>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- DEBUG INFO -->
  <?php if(!empty($_GET['debug'])): ?>
  <div class="recommendations-debug">
    <strong>Debug Info:</strong><br>
    Search performed: <?= $hasSearch ? 'Yes' : 'No' ?><br>
    Properties found: <?= count($properties) ?><br>
    Recommendations found: <?= count($recommendations) ?><br>
    Search IDs excluded: <?= implode(', ', array_column($properties, 'id')) ?><br>
  </div>
  <?php endif; ?>

  <!-- RECOMMENDED PROPERTIES -->
  <?php if(!empty($recommendations)): ?>
  <section class="property-listings">
    <h2><i class="fa fa-star"></i> Similar Properties You Might Like </h2>
    <div class="properties-container">
      <?php foreach($recommendations as $rec): 
        if (isset($rec['property']) && is_array($rec['property'])):
          $row = $rec['property']; ?>
      <a href="propertydetails.php?id=<?= $row['id'] ?? '' ?>" style="text-decoration:none;color:inherit">
        <div class="property-card">
          <img src="../uploads/<?= htmlspecialchars($row['main_photo'] ?? '') ?>" alt="<?= htmlspecialchars($row['title'] ?? '') ?>" />
          <div class="property-info">
            <h3><?= htmlspecialchars($row['title'] ?? 'No Title') ?></h3>
            <p><strong>Price:</strong> Rs. <?= number_format($row['price'] ?? 0, 0) ?></p>
            <p><strong>Type:</strong> <?= htmlspecialchars($row['property_type'] ?? 'Not Specified') ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($row['location'] ?? 'Not Specified') ?></p>
            <p><strong>Owner:</strong> <?= htmlspecialchars($row['owner_name'] ?? 'Not Provided') ?></p>
            <p class="similarity-score"><i class="fa fa-chart-line"></i> <?= round(($rec['similarity'] ?? 0) * 100, 1) ?>% match</p>
          </div>
        </div>
      </a>
      <?php endif; endforeach; ?>
    </div>
  </section>
  <?php else: ?>
  <!-- Show message when no recommendations found -->
  <?php if($hasSearch): ?>
  <section class="property-listings">
    <div style="text-align:center;padding:40px;">
      <i class="fa fa-lightbulb" style="font-size:48px;color:#ccc;margin-bottom:20px;"></i>
      <h3>No Similar Properties Found</h3>
      <p>We couldn't find properties similar to your search. Try browsing all available properties instead.</p>
      <a href="?" class="btn-orange" style="display:inline-block;margin-top:15px;text-decoration:none;">View All Properties</a>
    </div>
  </section>
  <?php endif; ?>
  <?php endif; ?>

<?php else: ?>
  <!-- HOT DEALS -->
  <?php if(!empty($hotDeals)): ?>
  <section class="property-listings">
    <h2><i class="fa fa-fire"></i> Hot Deals</h2>
    <div class="properties-container">
      <?php foreach($hotDeals as $deal): ?>
      <a href="propertydetails.php?id=<?= $deal['id'] ?? '' ?>" style="text-decoration:none;color:inherit">
        <div class="property-card">
          <img src="../uploads/<?= htmlspecialchars($deal['main_photo'] ?? '') ?>" alt="<?= htmlspecialchars($deal['title'] ?? '') ?>" />
          <div class="property-info">
            <h3><?= htmlspecialchars($deal['title'] ?? 'No Title') ?></h3>
            <p><strong>Price:</strong> Rs. <?= number_format($deal['price'] ?? 0, 0) ?></p>
            <p><strong>Type:</strong> <?= htmlspecialchars($deal['property_type'] ?? 'Not Specified') ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($deal['location'] ?? 'Not Specified') ?></p>
            <p><strong>Owner:</strong> <?= htmlspecialchars($deal['owner_name'] ?? 'Not Provided') ?></p>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- AVAILABLE PROPERTIES -->
  <section class="property-listings">
    <h2><i class="fa fa-home"></i> Available Properties</h2>
    <?php if(!empty($properties)): ?>
    <div class="properties-container" id="availableProperties">
      <?php foreach(array_slice($properties, 0, 8) as $row): ?>
      <a href="propertydetails.php?id=<?= $row['id'] ?? '' ?>" style="text-decoration:none;color:inherit">
        <div class="property-card">
          <img src="../uploads/<?= htmlspecialchars($row['main_photo'] ?? '') ?>" alt="<?= htmlspecialchars($row['title'] ?? '') ?>" />
          <div class="property-info">
            <h3><?= htmlspecialchars($row['title'] ?? 'No Title') ?></h3>
            <p><strong>Price:</strong> Rs. <?= number_format($row['price'] ?? 0, 0) ?></p>
            <p><strong>Type:</strong> <?= htmlspecialchars($row['property_type'] ?? 'Not Specified') ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($row['location'] ?? 'Not Specified') ?></p>
            <p><strong>Owner:</strong> <?= htmlspecialchars($row['owner_name'] ?? 'Not Provided') ?></p>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php if(count($properties) > 8): ?>
      <div style="text-align:center;margin-top:20px;">
        <button class="btn-orange" onclick="loadAllProperties()">View All (<?= count($properties) ?> properties)</button>
      </div>
    <?php endif; ?>
    <?php else: ?>
      <p style="text-align:center;padding:40px;">No properties available at the moment.</p>
    <?php endif; ?>
  </section>
<?php endif; ?>

<script src="frontpage.js"></script>
<script>
// Notification functionality
const notifBtn = document.getElementById('notifBtn');
const notifMenu = document.getElementById('notifMenu');

if (notifBtn && notifMenu) {
  notifBtn.addEventListener('click', () => {
    if (notifMenu.style.display === 'block') {
      notifMenu.style.display = 'none';
      notifBtn.setAttribute('aria-expanded', 'false');
    } else {
      notifMenu.style.display = 'block';
      notifBtn.setAttribute('aria-expanded', 'true');
    }
  });

  document.addEventListener('click', e => {
    if (!notifBtn.contains(e.target) && !notifMenu.contains(e.target)) {
      notifMenu.style.display = 'none';
      notifBtn.setAttribute('aria-expanded', 'false');
    }
  });
}

// Load all properties function
function loadAllProperties(){
  const all = <?=json_encode($properties)?>;
  const container = document.getElementById("availableProperties");
  
  if (container && all) {
    container.innerHTML = "";
    
    all.forEach(property => {
      const link = document.createElement("a");
      link.href = `propertydetails.php?id=${property.id || ''}`;
      link.style.textDecoration = "none";
      link.style.color = "inherit";
      
      const card = document.createElement("div");
      card.className = "property-card";
      card.innerHTML = `
        <img src="../uploads/${property.main_photo || ''}" alt="${property.title || ''}" />
        <div class="property-info">
          <h3>${property.title || 'No Title'}</h3>
          <p><strong>Price:</strong> Rs. ${Number(property.price || 0).toLocaleString()}</p>
          <p><strong>Type:</strong> ${property.property_type || 'Not Specified'}</p>
          <p><strong>Location:</strong> ${property.location || 'Not Specified'}</p>
          <p><strong>Owner:</strong> ${property.owner_name || 'Not Provided'}</p>
        </div>`;
      
      link.appendChild(card);
      container.appendChild(link);
    });
    
    // Hide the "View All" button
    if (event && event.target) {
      event.target.style.display = "none";
    }
  }
}
</script>
</body>
</html>
<?php $conn->close(); ?>