<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 📌 1. DATABASE CONNECTION
$host = 'localhost';
$db   = 'rental_db';  // Change to your actual DB name
$user = 'root';
$pass = '';  // Change if your MySQL has a password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 📌 2. ADD A VIDEO
function addVideo($title, $director, $release_year, $stock) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO videos (title, director, release_year, stock) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $title, $director, $release_year, $stock);
    return $stmt->execute();
}

// 📌 3. GET ALL VIDEOS
function getVideos() {
    global $conn;
    $result = $conn->query("SELECT * FROM videos");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// 📌 4. GET SINGLE VIDEO
function getVideoById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// 📌 5. EDIT A VIDEO
function editVideo($id, $title, $director, $release_year, $stock) {
    global $conn;
    $stmt = $conn->prepare("UPDATE videos SET title = ?, director = ?, release_year = ?, stock = ? WHERE id = ?");
    $stmt->bind_param("ssiii", $title, $director, $release_year, $stock, $id);
    return $stmt->execute();
}

// 📌 6. DELETE A VIDEO
function deleteVideo($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// 📌 7. RENT A VIDEO
function rentVideo($user_id, $video_id) {
    global $conn;

    // Check stock
    $video = getVideoById($video_id);
    if (!$video || $video['stock'] <= 0) {
        return false; // Not available
    }

    // Rent it
    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("INSERT INTO rentals (user_id, video_id) VALUES (?, ?)");
        $stmt1->bind_param("ii", $user_id, $video_id);
        $stmt1->execute();

        $stmt2 = $conn->prepare("UPDATE videos SET stock = stock - 1 WHERE id = ?");
        $stmt2->bind_param("i", $video_id);
        $stmt2->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// 📌 8. RETURN A VIDEO
function returnVideo($rental_id) {
    global $conn;

    // Get rental
    $stmt = $conn->prepare("SELECT video_id FROM rentals WHERE id = ? AND return_date IS NULL");
    $stmt->bind_param("i", $rental_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rental = $result->fetch_assoc();

    if (!$rental) return false;

    $video_id = $rental['video_id'];

    // Update return date and stock
    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("UPDATE rentals SET return_date = NOW() WHERE id = ?");
        $stmt1->bind_param("i", $rental_id);
        $stmt1->execute();

        $stmt2 = $conn->prepare("UPDATE videos SET stock = stock + 1 WHERE id = ?");
        $stmt2->bind_param("i", $video_id);
        $stmt2->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// 📌 9. GET USER RENTAL HISTORY
function getRentalHistory($user_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT r.id, v.title, r.rent_date, r.return_date
        FROM rentals r
        JOIN videos v ON r.video_id = v.id
        WHERE r.user_id = ?
        ORDER BY r.rent_date DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function usernameExists($username) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

// REGISTER USER
function registerUser($username, $password, $role = 'customer') {
    global $conn;
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed, $role);
    return $stmt->execute();
}
function isAdmin() {
    return isset($_SESSION['user'], $_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isCustomer() {
    return isset($_SESSION['user'], $_SESSION['role']) && $_SESSION['role'] === 'customer';
}

function hasRented($video_id, $user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM rentals WHERE video_id = ? AND user_id = ? AND return_date IS NULL");
    $stmt->bind_param("ii", $video_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}


function getRentalHistoryWithUsers() {
    global $conn;

    $query = "
        SELECT r.id, u.username, v.title, r.rent_date, r.return_date,
               CASE WHEN r.return_date IS NULL THEN 0 ELSE 1 END AS returned
        FROM rentals r
        JOIN users u ON r.user_id = u.id
        JOIN videos v ON r.video_id = v.id
        ORDER BY r.rent_date DESC
    ";

    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}


function getCurrentRenters($video_id) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT u.username
        FROM rentals r
        JOIN users u ON r.user_id = u.id
        WHERE r.video_id = ? AND r.return_date IS NULL
    ");
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $renters = [];
    while ($row = $result->fetch_assoc()) {
        $renters[] = $row['username'];
    }

    return $renters;
}

?>
