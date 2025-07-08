<?php
require_once 'config.php';
require_once 'functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = $_GET['id'] ?? null;

if (!$book_id) {
    echo "<div class='alert alert-danger'>Invalid book ID.</div>";
    exit;
}

// Get the active borrowal ID for this user and book
$stmt = $conn->prepare("SELECT id FROM borrowals WHERE book_id = ? AND user_id = ? AND return_date IS NULL LIMIT 1");
$stmt->bind_param("ii", $book_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$borrowal = $result->fetch_assoc();

if (!$borrowal) {
    echo "<div class='alert alert-danger'>You haven't borrowed this book or it's already returned.</div>";
    exit;
}

$borrowal_id = $borrowal['id'];

if (returnBook($borrowal_id)) {
    $_SESSION['alert'] = "Book returned successfully.";
    header("Location: borrowal_history.php");
    exit;
} else {
    echo "<div class='alert alert-danger'>Failed to return book.</div>";
}
?>
