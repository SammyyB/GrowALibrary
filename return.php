<?php
require_once 'config.php';
require_once 'functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$video_id = $_GET['id'] ?? null;

if (!$video_id) {
    echo "<div class='alert alert-danger'>Invalid video ID.</div>";
    exit;
}

// Get the active rental ID for this user and video
$stmt = $conn->prepare("SELECT id FROM rentals WHERE video_id = ? AND user_id = ? AND return_date IS NULL LIMIT 1");
$stmt->bind_param("ii", $video_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$rental = $result->fetch_assoc();

if (!$rental) {
    echo "<div class='alert alert-danger'>You haven't rented this video or it's already returned.</div>";
    exit;
}

$rental_id = $rental['id'];

if (returnVideo($rental_id)) {
    $_SESSION['alert'] = "Video returned successfully.";
    header("Location: rental_history.php");
    exit;
} else {
    echo "<div class='alert alert-danger'>Failed to return video.</div>";
}
?>
