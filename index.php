<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'functions.php';
require 'config.php';

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
    <title>Video Rental System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
                        echo '<h2>All Videos</h2>';
                        echo '<table class="table table-bordered">';
                        echo '<thead><tr>';
                        echo '<th>Title</th><th>Director</th><th>Release Year</th>';
                        if (isCustomer()) {
                            echo '<th>Stock</th><th>Action</th><th>Renter</th>';
                        } else {
                            echo '<th>Actions</th><th>Stock</th><th>Renter</th>';
                        }
                        echo '</tr></thead><tbody>';

                        $videos = getVideos();
                        foreach ($videos as $video) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($video['title']) . '</td>';
                            echo '<td>' . htmlspecialchars($video['director']) . '</td>';
                            echo '<td>' . $video['release_year'] . '</td>';

                            if (isCustomer()) {
                                echo '<td>' . $video['stock'] . '</td>';
                                echo '<td>';
                                if (hasRented($video['id'], $userId)) {
                                    echo '<a href="return.php?id=' . $video['id'] . '" class="btn btn-warning btn-sm">Return</a>';
                                } elseif ($video['stock'] > 0) {
                                    echo '<a href="rent.php?id=' . $video['id'] . '" class="btn btn-success btn-sm">Rent</a>';
                                } else {
                                    echo '<span class="text-danger">Out of Stock</span>';
                                }
                                echo '</td>';
                            } else {
                                echo '<td>';
                                echo '<a href="index.php?page=edit&id=' . $video['id'] . '" class="btn btn-warning btn-sm">Edit</a> ';
                                echo '<a href="index.php?page=delete&id=' . $video['id'] . '" class="btn btn-danger btn-sm">Delete</a> ';
                                echo '<a href="index.php?page=view_single&id=' . $video['id'] . '" class="btn btn-info btn-sm">View</a>';
                                echo '</td>';
                                echo '<td>' . htmlspecialchars($video['stock']) . '</td>';
                            }

                            // Show renters
                            $renters = getCurrentRenters($video['id']);
                            echo '<td>' . implode(', ', array_map('htmlspecialchars', $renters)) . '</td>';

                            echo '</tr>';
                        }
                        echo '</tbody></table>';

                        echo '<hr><h4>Rental History</h4>';
                        echo '<table class="table table-striped">';
                        echo '<thead><tr><th>Username</th><th>Title</th><th>Rent Date</th><th>Return Date</th><th>Status</th></tr></thead><tbody>';

                        $rentalHistory = getRentalHistoryWithUsers();
                        if (count($rentalHistory) > 0) {
                            foreach ($rentalHistory as $rental) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($rental['username']) . '</td>';
                                echo '<td>' . htmlspecialchars($rental['title']) . '</td>';
                                echo '<td>' . htmlspecialchars($rental['rent_date']) . '</td>';
                                echo '<td>' . ($rental['return_date'] ? htmlspecialchars($rental['return_date']) : '-') . '</td>';
                                echo '<td>' . ($rental['returned'] ? '<span class="badge bg-success">Returned</span>' : '<span class="badge bg-warning">Not Returned</span>') . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" class="text-center">No rental history.</td></tr>';
                        }

                        echo '</tbody></table>';
                        break;
                }
                ?>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>&copy; 2023 Your Company.</strong> All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 3.2.0
        </div>
    </footer>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
</body>
</html>