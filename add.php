<?php
require_once 'functions.php';
require_once 'auth.php';
require 'config.php';

if (!isAdmin()) {
    echo "<div class='alert alert-danger'>Access denied. Admins only.</div>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $release_date = $_POST['release_date'];
    $category = $_POST['category'];
    $status = $_POST['status'];
    $director = $_POST['director'];
    $stock = $_POST['stock'];

    $release_year = date("Y", strtotime($release_date));

    // Generate Book ID: e.g. THFEB102022-FIC00001
    $firstLetters = strtoupper(substr($title, 0, 2));
    $month = strtoupper(date("M", strtotime($release_date)));
    $day = date("d");
    $year = date("Y", strtotime($release_date));
    $categoryCode = strtoupper(substr($category, 0, 3));

    $result = $conn->query("SELECT COUNT(*) as count FROM videos");
    $count = $result->fetch_assoc()['count'] + 1;
    $bookId = "{$firstLetters}{$month}{$day}{$year}-{$categoryCode}" . str_pad($count, 5, "0", STR_PAD_LEFT);

    // Insert new video/book with release_date
    $stmt = $conn->prepare("INSERT INTO videos (book_id, title, director, release_year, release_date, stock, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiss", $bookId, $title, $director, $release_year, $release_date, $stock, $category, $status);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Book added successfully.</div>';
    } else {
        echo '<div class="alert alert-danger">Error adding book: ' . $stmt->error . '</div>';
    }
}
?>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Add New Book</h3>
    </div>
    <form action="index.php?page=add" method="post">
        <div class="card-body">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control" name="title" required>
            </div>
            <div class="form-group">
                <label for="director">Author / Director</label>
                <input type="text" class="form-control" name="director" required>
            </div>
            <div class="form-group">
                <label for="release_date">Published Date</label>
                <input type="date" class="form-control" name="release_date" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" class="form-control" name="category" placeholder="e.g. Fiction, Non-Fiction" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" class="form-control" required>
                    <option value="available">Available</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" class="form-control" name="stock" min="1" required>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Add Book</button>
        </div>
    </form>
</div>
