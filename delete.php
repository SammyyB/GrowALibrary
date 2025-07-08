<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'functions.php';

function setAlert($message, $type = 'success') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

if (isset($_SESSION['alert'])) {
    echo "<script>alert('" . addslashes($_SESSION['alert']) . "');</script>";
    unset($_SESSION['alert']);
}

if (isset($_GET['id']) && !isset($_GET['confirm'])) {
    $bookId = htmlspecialchars($_GET['id']);
    $book = getBookById($bookId);

    if ($book):
?>
        <div class="container mt-3">
            <h1>Delete Book</h1>
            <p>Are you sure you want to delete this book?</p>
            <div class="card">
                <div class="card-body">
                    <p><strong>Title:</strong> <?= htmlspecialchars($book['title']) ?></p>
                    <p><strong>Book ID:</strong> <?= htmlspecialchars($book['book_id']) ?></p>
                    <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
                    <p><strong>Published On:</strong> 
                        <?= isset($book['release_date']) ? date("F d, Y", strtotime($book['release_date'])) : 'N/A' ?>
                    </p>
                </div>
            </div>
            <div>
                <a href="delete.php?confirm=yes&id=<?= $bookId; ?>" class="btn btn-danger">Delete</a>
                <a href="index.php?page=view" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
<?php
    else:
        setAlert("Book not found.", "danger");
        header('Location: index.php?page=view');
        exit();
    endif;

} elseif (isset($_GET['confirm']) && $_GET['confirm'] === 'yes' && isset($_GET['id'])) {
    if (deleteBook($_GET['id'])) {
        setAlert('Book deleted successfully.', 'success');
    } else {
        setAlert('Failed to delete book. Book not found.', 'danger');
    }
    header('Location: index.php?page=view');
    exit();

} else {
    setAlert('No book ID specified.', 'danger');
    header('Location: index.php?page=view');
    exit();
}
?>
