<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['id'])) {
    $book = getBookById($_GET['id']);
    if ($book !== null) {
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Book Details</h3>
    </div>
    <div class="card-body">
        <p><strong>Title:</strong> <?php echo htmlspecialchars($book['title']); ?></p>
        <p><strong>Book ID:</strong> <?= htmlspecialchars($book['book_id']) ?></p>
        <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
        <p><strong>Published On:</strong> 
                        <?= isset($book['release_date']) ? date("F d, Y", strtotime($book['release_date'])) : 'N/A' ?>
                    </p>
    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-secondary" onclick="history.back();">Back</button>
    </div>
</div>
<?php
    } else {
        echo '<div class="alert alert-warning">Book not found.</div>';
    }
} else {
    echo '<div class="alert alert-danger">No book ID specified.</div>';
}
?>