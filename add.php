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
    $author = $_POST['author'];
    $stock = $_POST['stock'];
    $synopsis = $_POST['synopsis'] ?? null;

    $publish_year = date("Y", strtotime($release_date));

    // Generate Book ID
    $firstLetters = strtoupper(substr($title, 0, 2));
    $month = strtoupper(date("M", strtotime($release_date)));
    $day = date("d");
    $year = date("Y", strtotime($release_date));
    $categoryCode = strtoupper(substr($category, 0, 3));

    $result = $conn->query("SELECT COUNT(*) as count FROM books");
    $count = $result->fetch_assoc()['count'] + 1;
    $bookId = "{$firstLetters}{$month}{$day}{$year}-{$categoryCode}" . str_pad($count, 5, "0", STR_PAD_LEFT);

    // Handle Book Cover Upload
    $coverPath = null;
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $fileTmp = $_FILES['cover']['tmp_name'];
        $fileName = basename($_FILES['cover']['name']);
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = uniqid('cover_', true) . '.' . $fileExt;
        $fullPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmp, $fullPath)) {
            $coverPath = $fullPath;
        }
    }

    // Insert into database with synopsis
    $stmt = $conn->prepare("INSERT INTO books (book_id, title, author, publish_year, release_date, stock, category, status, cover_path, synopsis) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssissss", $bookId, $title, $author, $publish_year, $release_date, $stock, $category, $status, $coverPath, $synopsis);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Book added successfully.</div>';
    } else {
        echo '<div class="alert alert-danger">Error adding book: ' . $stmt->error . '</div>';
    }
}
?>

<!-- 🧾 HTML Form -->
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Add New Book</h3>
    </div>
    <form action="index.php?page=add" method="post" enctype="multipart/form-data">
        <div class="card-body">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control" name="title" required>
            </div>
            <div class="form-group">
                <label for="author">Author</label>
                <input type="text" class="form-control" name="author" required>
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
            <div class="form-group">
                <label for="synopsis">Book Synopsis</label>
                <textarea name="synopsis" class="form-control" rows="4" placeholder="Enter a brief synopsis of the book..."></textarea>
            </div>
            <div class="form-group">
                <label for="cover">Book Cover (Optional)</label>
                <input type="file" class="form-control" name="cover" accept="image/*">
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Add Book</button>
        </div>
    </form>
</div>

