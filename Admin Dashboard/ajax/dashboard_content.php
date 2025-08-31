<?php
include("../../DatabaseConn/conn.php");

// Helper function to count rows safely
function getCount($conn, $table, $whereClause = "1") {
    $sql = "SELECT COUNT(*) AS total FROM $table WHERE $whereClause";
    $res = $conn->query($sql);
    if ($res) {
        $row = $res->fetch_assoc();
        return $row['total'];
    } else {
        return 0; // fallback if query fails
    }
}

// Total Rooms: all rooms except pending or rejected (handles NULL, empty, spaces, capitalization)
$totalRooms = getCount($conn, 'properties', "(status IS NULL OR TRIM(status)='' OR LOWER(TRIM(status)) NOT IN ('pending','rejected'))");

// Pending Rooms: only pending (handles spaces, capitalization)
$totalPendingRooms = getCount($conn, 'properties', "LOWER(TRIM(status))='pending'");

// Other counts
$totalUsers = getCount($conn, 'users');
$totalReviews = getCount($conn, 'reviews');
$totalShiftRequests = getCount($conn, 'shift_requests');
?>

<h2>Dashboard Overview</h2>
<div style="display:flex; flex-wrap:wrap; gap:20px;">

    <div style="flex:1; min-width:180px; background:#3498db; color:white; padding:20px; border-radius:8px;">
        <h3>Registered Users</h3>
        <p style="font-size:24px;"><?php echo $totalUsers; ?></p>
    </div>

    <div style="flex:1; min-width:180px; background:#2ecc71; color:white; padding:20px; border-radius:8px;">
        <h3>Total Rooms</h3>
        <p style="font-size:24px;"><?php echo $totalRooms; ?></p>
    </div>

    <div style="flex:1; min-width:180px; background:#e67e22; color:white; padding:20px; border-radius:8px;">
        <h3>Total Reviews</h3>
        <p style="font-size:24px;"><?php echo $totalReviews; ?></p>
    </div>

    <div style="flex:1; min-width:180px; background:#9b59b6; color:white; padding:20px; border-radius:8px;">
        <h3>Total Shift Requests</h3>
        <p style="font-size:24px;"><?php echo $totalShiftRequests; ?></p>
    </div>

    <div style="flex:1; min-width:180px; background:#e74c3c; color:white; padding:20px; border-radius:8px;">
        <h3>Pending Rooms</h3>
        <p style="font-size:24px;"><?php echo $totalPendingRooms; ?></p>
    </div>

</div>
