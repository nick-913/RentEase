<?php
session_start();
include '../DatabaseConn/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'You must be logged in to express interest.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Someone';

$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
if ($room_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid room ID.']);
    exit;
}

// Fetch owner info and property title
$stmt = $conn->prepare("SELECT user_id, title FROM properties WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Property not found.']);
    exit;
}

$property = $result->fetch_assoc();
$owner_id = $property['user_id'];
$property_title = $property['title'];

if ($owner_id == $user_id) {
    echo json_encode(['message' => 'You are the owner of this property.']);
    exit;
}

// Create notification message
$message = "$user_name is interested in your property: \"$property_title\". Please contact them.";

// Insert notification for the owner
$insert = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
$insert->bind_param("is", $owner_id, $message);

if ($insert->execute()) {
    echo json_encode(['message' => 'Notification sent to the room owner.']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send notification.']);
}
