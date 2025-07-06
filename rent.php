<?php
session_start();
require 'functions.php';

if (!isset($_SESSION['user_id']) || !isCustomer()) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['alert'] = "No video selected for rent.";
    header("Location: index.php");
    exit;
}

$videoId = $_GET['id'];
$userId = $_SESSION['user_id'];

if (rentVideo($videoId, $userId)) {
    $_SESSION['alert'] = "Video rented successfully!";
} else {
    $_SESSION['alert'] = "Unable to rent video. You may have already rented it or it's out of stock.";
}

header("Location: index.php");
exit;
