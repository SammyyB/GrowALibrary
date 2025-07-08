<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'functions.php';
require 'config.php';
applyPenalties();

if (isset($_SESSION['alert'])) {
    echo "<script>alert('" . addslashes($_SESSION['alert']) . "');</script>";
    unset($_SESSION['alert']);
}

if (isCustomer() && isset($_SESSION['user_id'])) {
    checkReturnWarnings($_SESSION['user_id']);
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page = $_GET['page'] ?? '';

$userId = $_SESSION['user_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'menu.php'; ?>

    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <?php
                switch ($page) {
                    case 'add':
                        include 'add.php';
                        break;
                    case 'edit':
                        include 'edit.php';
                        break;
                    case 'delete':
                        include 'delete.php';
                        break;
                    case 'view_single':
                        include 'view_single.php';
                        break;

                    default:
                        echo '<h2>All Books</h2>';
                        echo '<table class="table table-bordered">';
                        echo '<thead><tr>';
                        echo '<th>Title</th><th>Author</th><th>Release Year</th>';
                        if (isCustomer()) {
                            echo '<th>Stock</th><th>Action</th><th>Book ID</th><th>Status</th>';
                        } else {
                            echo '<th>Actions</th><th>Stock</th><th>Book ID</th><th>Status</th>';
                        }
                        echo '</tr></thead><tbody>';

                        $books = getBooks();
                        foreach ($books as $book) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($book['title']) . '</td>';
                            echo '<td>' . htmlspecialchars($book['author']) . '</td>';
                            echo '<td>' . $book['publish_year'] . '</td>';

                            if (isCustomer()) {
                                echo '<td>' . $book['stock'] . '</td>';
                                echo '<td>';
                                if (hasBorrowed($book['id'], $userId)) {
                                    echo '<a href="return.php?id=' . $book['id'] . '" class="btn btn-warning btn-sm">Return</a>';
                                } elseif ($book['stock'] > 0 && $book['status'] !== 'archived') {
                                    echo '<a href="borrow.php?id=' . $book['id'] . '" class="btn btn-success btn-sm">Borrow</a>';
                                } else {
                                    echo '<span class="text-danger">Unavailable</span>';
                                }
                                echo '</td>';
                            } else {
                                echo '<td>';
                                echo '<a href="index.php?page=edit&id=' . $book['id'] . '" class="btn btn-warning btn-sm">Edit</a> ';
                                echo '<a href="index.php?page=delete&id=' . $book['id'] . '" class="btn btn-danger btn-sm">Delete</a> ';
                                echo '<a href="index.php?page=view_single&id=' . $book['id'] . '" class="btn btn-info btn-sm">View</a>';
                                echo '</td>';
                                echo '<td>' . htmlspecialchars($book['stock']) . '</td>';
                            }

                            echo '<td>' . htmlspecialchars($book['book_id']) . '</td>';

                            if ($book['status'] === 'archived') {
                                echo '<td><span class="badge bg-secondary">Archived</span></td>';
                            } elseif ($book['stock'] <= 0) {
                                echo '<td><span class="badge bg-danger">Out of Stock</span></td>';
                            } else {
                                echo '<td><span class="badge bg-success">Available</span></td>';
                            }

                            echo '</tr>';
                        }
                        echo '</tbody></table>';

                        echo '<hr><h4>Borrowing History</h4>';
                        echo '<table class="table table-striped">';
                        echo '<thead><tr><th>Username</th><th>Title</th><th>Borrow Date</th><th>Return Date</th><th>Status</th></tr></thead><tbody>';

                        $borrowalHistory = getBorrowalHistoryWithUsers();
                        if (count($borrowalHistory) > 0) {
                            foreach ($borrowalHistory as $borrowal) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($borrowal['username']) . '</td>';
                                echo '<td>' . htmlspecialchars($borrowal['title']) . '</td>';
                                echo '<td>' . htmlspecialchars($borrowal['borrow_date']) . '</td>';
                                echo '<td>' . ($borrowal['return_date'] ? htmlspecialchars($borrowal['return_date']) : '-') . '</td>';
                                echo '<td>' . ($borrowal['returned'] ? '<span class="badge bg-success">Returned</span>' : '<span class="badge bg-warning">Not Returned</span>') . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" class="text-center">No borrowal history.</td></tr>';
                        }

                        echo '</tbody></table>';
                        break;
                }
                ?>
            </div>
        </section>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
</body>
</html>