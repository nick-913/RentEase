<?php
include("../../DatabaseConn/conn.php");

$sql = "SELECT * FROM properties WHERE status='pending'";
$result = $conn->query($sql);

echo "<h2>Pending Property Approvals</h2>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div id='property-{$row['id']}' style='border:1px solid #ccc; padding:15px; margin:15px 0; border-radius:8px;'>";
        echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
        echo "<p><strong>Location:</strong> " . htmlspecialchars($row['location']) . "</p>";
        echo "<p><strong>Price:</strong> Rs. " . htmlspecialchars($row['price']) . "</p>";

        if (!empty($row['document_file'])) {
            $docPath = "/RentEase-main 2/RentEase-main/uploads/" . $row['document_file'];
            if (file_exists(__DIR__ . "/../../uploads/" . $row['document_file'])) {
                echo "<p><strong>Verification Document:</strong> 
                        <a href='$docPath' target='_blank'>📄 View Document</a>
                      </p>";
            } else {
                echo "<p><strong>Verification Document:</strong> ⚠️ File not found in uploads/</p>";
            }
        } else {
            echo "<p><strong>Verification Document:</strong> ❌ Not Uploaded</p>";
        }

        echo "<div style='margin-top:10px;'>";
        echo "<button class='approve-btn' data-id='{$row['id']}' data-status='approved'
                style='background:#2ecc71;color:white;padding:6px 12px;border-radius:5px;margin-right:10px;cursor:pointer;'>✅ Approve</button>";
        echo "<button class='reject-btn' data-id='{$row['id']}' data-status='rejected'
                style='background:#e74c3c;color:white;padding:6px 12px;border-radius:5px;cursor:pointer;'>❌ Reject</button>";
        echo "</div>";

        echo "</div>";
    }
} else {
    echo "<p>No pending properties at the moment 🎉</p>";
}
?>
