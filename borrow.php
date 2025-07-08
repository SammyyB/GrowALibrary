<?php
session_start();
require 'functions.php';

if (isset($_SESSION['alert'])) {
    echo "<script>alert('" . addslashes($_SESSION['alert']) . "');</script>";
    unset($_SESSION['alert']);
}

if (!isset($_SESSION['user_id']) || !isCustomer()) {
    header("Location: login.php");
    exit;
}

$bookId = $_GET['id'] ?? null;
$userId = $_SESSION['user_id'];

$_SESSION['alert'] = borrowBook($userId, $bookId);

header("Location: index.php");
exit;