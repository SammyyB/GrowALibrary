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

// Check if the user has rented this video
if (!hasRented($video_id, $user_id)) {
    echo "<div class='alert alert-danger'>You haven't rented this video.</div>";
    exit;
}

// Return logic: delete from rentals and increase stock
$stmt = $conn->prepare("DELETE FROM rentals WHERE video_id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("ii", $video_id, $user_id);

if ($stmt->execute()) {
    // Increase stock
    $updateStock = $conn->prepare("UPDATE videos SET stock = stock + 1 WHERE id = ?");
    $updateStock->bind_param("i", $video_id);
    $updateStock->execute();

    $_SESSION['alert'] = "Video returned successfully.";
    header("Location: index.php?page=home");
    exit;
} else {
    echo "<div class='alert alert-danger'>Failed to return video.</div>";
}
?>
