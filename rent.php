<?php
session_start();
require 'functions.php';

if (!isset($_SESSION['user_id']) || !isCustomer()) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['alert'] = "No book selected for rent.";
    header("Location: index.php");
    exit;
}

$videoId = $_GET['id'];
$userId = $_SESSION['user_id'];

// Already rented check
if (hasRented($videoId, $userId)) {
    $_SESSION['alert'] = "You already rented this book and haven't returned it yet.";
    header("Location: index.php");
    exit;
}

// Get book details
$book = getVideoById($videoId);
if (!$book) {
    $_SESSION['alert'] = "Book not found.";
} elseif (strtolower($book['status']) === 'archived') {
    $_SESSION['alert'] = "This book is archived and cannot be rented.";
} elseif ($book['stock'] <= 0) {
    $_SESSION['alert'] = "This book is currently out of stock.";
} elseif (!rentVideo($userId, $videoId)) {
    $_SESSION['alert'] = "Borrow failed. You may have already rented 2 books.";
} else {
    $_SESSION['alert'] = "Book rented successfully!";
}

header("Location: index.php");
exit;
