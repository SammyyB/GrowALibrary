<?php
require_once 'functions.php';
require_once 'auth.php';
$require_admin = true;

if (!isAdmin()) {
    echo "<div class='alert alert-danger'>Access denied. Admins only.</div>";
    exit;
}

if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>No video ID specified.</div>";
    exit;
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM videos WHERE id = $id");
$video = $result->fetch_assoc();

if (!$video) {
    echo "<div class='alert alert-warning'>Video not found.</div>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $director = $_POST['director'];
    $release_date = $_POST['release_date'];
    $release_year = date("Y", strtotime($release_date));
    $category = $_POST['category'];
    $status = $_POST['status'];
    $stock = $_POST['stock'];

    $stmt = $conn->prepare("UPDATE videos SET title = ?, director = ?, release_year = ?, release_date = ?, category = ?, status = ?, stock = ? WHERE id = ?");
    $stmt->bind_param("ssisssii", $title, $director, $release_year, $release_date, $category, $status, $stock, $id);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Book updated successfully.</div>';
        // Reload updated info
        $result = $conn->query("SELECT * FROM videos WHERE id = $id");
        $video = $result->fetch_assoc();
    } else {
        echo '<div class="alert alert-danger">Error updating book: ' . $stmt->error . '</div>';
    }
}
?>

<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">Edit Book</h3>
    </div>
    <form action="index.php?page=edit&id=<?= $video['id'] ?>" method="post">
        <div class="card-body">
            <div class="form-group">
                <label>Title</label>
                <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($video['title']) ?>" required>
            </div>
            <div class="form-group">
                <label>Author / Director</label>
                <input type="text" class="form-control" name="director" value="<?= htmlspecialchars($video['director']) ?>" required>
            </div>
            <div class="form-group">
                <label>Published Date</label>
                <input type="date" class="form-control" name="release_date" value="<?= htmlspecialchars($video['release_date']) ?>" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" class="form-control" name="category" value="<?= htmlspecialchars($video['category']) ?>" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="available" <?= $video['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="archived" <?= $video['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" class="form-control" name="stock" min="0" value="<?= htmlspecialchars($video['stock']) ?>" required>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info">Update Book</button>
            <button type="button" class="btn btn-default" onclick="window.location.href='index.php?page=view';">Cancel</button>
        </div>
    </form>
</div>
