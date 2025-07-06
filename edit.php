<?php
require_once 'functions.php';
require_once 'auth.php';

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
    $release_year = $_POST['release_year'];
    $stock = $_POST['stock'];

    $stmt = $conn->prepare("UPDATE videos SET title = ?, director = ?, release_year = ?, stock = ? WHERE id = ?");
    $stmt->bind_param("ssiii", $title, $director, $release_year, $stock, $id);
    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Video updated successfully.</div>';
        // Reload updated info
        $result = $conn->query("SELECT * FROM videos WHERE id = $id");
        $video = $result->fetch_assoc();
    } else {
        echo '<div class="alert alert-danger">Error updating video.</div>';
    }
}
?>

<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">Edit Video</h3>
    </div>
    <form action="index.php?page=edit&id=<?= $video['id'] ?>" method="post">
        <div class="card-body">
            <div class="form-group">
                <label>Title</label>
                <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($video['title']) ?>" required>
            </div>
            <div class="form-group">
                <label>Director</label>
                <input type="text" class="form-control" name="director" value="<?= htmlspecialchars($video['director']) ?>" required>
            </div>
            <div class="form-group">
                <label>Release Year</label>
                <input type="number" class="form-control" name="release_year" value="<?= htmlspecialchars($video['release_year']) ?>" required>
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" class="form-control" name="stock" min="0" value="<?= htmlspecialchars($video['stock']) ?>" required>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info">Update Video</button>
            <button type="button" class="btn btn-default" onclick="window.location.href='index.php?page=view';">Cancel</button>
        </div>
    </form>
</div>
