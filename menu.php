<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'functions.php';
require_once 'config.php'; // for $conn
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
        <span class="brand-text font-weight-light">Grow A Library</span>
    </a>

    <div class="sidebar d-flex flex-column" style="height: 100%;">
        <nav class="mt-2 flex-grow-1">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <li class="nav-item">
                    <a href="index.php?page=add" class="nav-link">
                        <i class="nav-icon fas fa-plus-square"></i>
                        <p>Add Book</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=view" class="nav-link">
                        <i class="nav-icon fas fa-book"></i>
                        <p>View All Books</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Footer showing logged-in user -->
        <div class="sidebar-footer text-center p-3 mt-auto" style="background-color: #343a40; color: #ccc;">
            <?php
            if (isset($_SESSION['user_id'])) {
                $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $stmt->bind_result($username, $role);
                if ($stmt->fetch()) {
                    echo 'Logged in as<br><strong>' . htmlspecialchars($username) . '</strong><br>';
                    echo '<small>(' . ucfirst($role) . ')</small>';
                } else {
                    echo 'User not found';
                }
                $stmt->close();
            } else {
                echo 'Not logged in';
            }
            ?>
        </div>
    </div>
</aside>
