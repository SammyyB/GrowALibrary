<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'functions.php';

function setAlert($message, $type = 'success') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

if (isset($_GET['id']) && !isset($_GET['confirm'])) {
    $videoId = htmlspecialchars($_GET['id']);
    $video = getVideoById($videoId);

    if ($video):
?>
        <div class="container mt-3">
            <h1>Delete Video</h1>
            <p>Are you sure you want to delete this video?</p>
            <div class="card">
                <div class="card-body">
                    <p><strong>Title:</strong> <?= htmlspecialchars($video['title']) ?></p>
                    <p><strong>Book ID:</strong> <?= htmlspecialchars($video['book_id']) ?></p>
                    <p><strong>Director:</strong> <?= htmlspecialchars($video['director']) ?></p>
                    <p><strong>Published On:</strong> 
                        <?= isset($video['release_date']) ? date("F d, Y", strtotime($video['release_date'])) : 'N/A' ?>
                    </p>
                </div>
            </div>
            <div>
                <a href="delete.php?confirm=yes&id=<?= $videoId; ?>" class="btn btn-danger">Delete</a>
                <a href="index.php?page=view" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
<?php
    else:
        setAlert("Video not found.", "danger");
        header('Location: index.php?page=view');
        exit();
    endif;

} elseif (isset($_GET['confirm']) && $_GET['confirm'] === 'yes' && isset($_GET['id'])) {
    if (deleteVideo($_GET['id'])) {
        setAlert('Video deleted successfully.', 'success');
    } else {
        setAlert('Failed to delete video. Video not found.', 'danger');
    }
    header('Location: index.php?page=view');
    exit();

} else {
    setAlert('No video ID specified.', 'danger');
    header('Location: index.php?page=view');
    exit();
}
?>
