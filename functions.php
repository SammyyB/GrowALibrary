<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$host = 'localhost';
$db   = 'rental_db'; 
$user = 'root';
$pass = ''; 

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function addBook($title, $author, $publish_year, $stock) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO books (title, author, publish_year, stock) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $title, $author, $publish_year, $stock);
    return $stmt->execute();
}

function getBooks() {
    global $conn;
    $result = $conn->query("SELECT * FROM books");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getBookById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function editBook($id, $title, $author, $publish_year, $stock) {
    global $conn;
    $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, publish_year = ?, stock = ? WHERE id = ?");
    $stmt->bind_param("ssiii", $title, $author, $publish_year, $stock, $id);
    return $stmt->execute();
}

function deleteBook($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function borrowBook($user_id, $book_id) {
    global $conn;

    if (!isset($_SESSION['user_id']) || !isCustomer()) {
        return "Access denied. Please log in as a customer.";
    }

    if (empty($book_id)) {
        return "No book selected.";
    }

    if (hasBorrowed($book_id, $user_id)) {
        return "You already borrowed this book and haven't returned it yet.";
    }

    $stmt = $conn->prepare("SELECT COUNT(*) as active FROM borrowals WHERE user_id = ? AND return_date IS NULL");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $activeCount = $stmt->get_result()->fetch_assoc()['active'];
    if ($activeCount >= 2) {
        return "Borrow limit reached. You can only borrow 2 books at a time.";
    }

    $book = getBookById($book_id);
    if (!$book) {
        return "Book not found.";
    }

    if (strtolower($book['status']) === 'archived') {
        return "This book is archived and cannot be borrowed.";
    }

    if ($book['stock'] <= 0) {
        return "This book is curborrowly out of stock.";
    }

    $dueDate = date('Y-m-d', strtotime('+7 days'));
    $conn->begin_transaction();

    try {
        $stmtInsert = $conn->prepare("INSERT INTO borrowals (user_id, book_id, borrow_date, due_date) VALUES (?, ?, NOW(), ?)");
        $stmtInsert->bind_param("iis", $user_id, $book_id, $dueDate);
        $stmtInsert->execute();

        $stmtUpdate = $conn->prepare("UPDATE books SET stock = stock - 1 WHERE id = ?");
        $stmtUpdate->bind_param("i", $book_id);
        $stmtUpdate->execute();

        $conn->commit();
        return "Book borrowed successfully! Due on $dueDate.";
    } catch (Exception $e) {
        $conn->rollback();
        return "Borrow failed. Please try again.";
    }
}

function returnBook($borrowal_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT book_id FROM borrowals WHERE id = ? AND return_date IS NULL");
    $stmt->bind_param("i", $borrowal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $borrowal = $result->fetch_assoc();

    if (!$borrowal) return false;

    $book_id = $borrowal['book_id'];

    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("UPDATE borrowals SET return_date = NOW() WHERE id = ?");
        $stmt1->bind_param("i", $borrowal_id);
        $stmt1->execute();

        $stmt2 = $conn->prepare("UPDATE books SET stock = stock + 1 WHERE id = ?");
        $stmt2->bind_param("i", $book_id);
        $stmt2->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function getBorrowalHistory($user_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT r.id, v.title, r.borrow_date, r.return_date, r.due_date
        FROM borrowals r
        JOIN books v ON r.book_id = v.id
        WHERE r.user_id = ?
        ORDER BY r.borrow_date DESC
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

function hasBorrowed($book_id, $user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM borrowals WHERE book_id = ? AND user_id = ? AND return_date IS NULL");
    $stmt->bind_param("ii", $book_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}


function getBorrowalHistoryWithUsers() {
    global $conn;

    $query = "
        SELECT r.id, u.username, v.title, r.borrow_date, r.return_date,
               CASE WHEN r.return_date IS NULL THEN 0 ELSE 1 END AS returned
        FROM borrowals r
        JOIN users u ON r.user_id = u.id
        JOIN books v ON r.book_id = v.id
        ORDER BY r.borrow_date DESC
    ";

    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}


function getCurborrowBorrowers($book_id) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT u.username
        FROM borrowals r
        JOIN users u ON r.user_id = u.id
        WHERE r.book_id = ? AND r.return_date IS NULL
    ");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $borrowers = [];
    while ($row = $result->fetch_assoc()) {
        $borrowers[] = $row['username'];
    }

    return $borrowers;
}

function calculateFine($due_date, $return_date = null) {
    $due = new DateTime($due_date);
    $end = $return_date ? new DateTime($return_date) : new DateTime();

    $interval = $due->diff($end);
    $daysLate = $interval->invert === 0 && $end > $due ? $interval->days : 0;

    return $daysLate * 10;
}

function applyPenalties() {
    global $conn;
    $today = date('Y-m-d');
    $penaltyPerDay = 10;

    $result = $conn->query("SELECT * FROM borrowals WHERE return_date IS NULL AND due_date < '$today'");
    while ($row = $result->fetch_assoc()) {
        $daysOverdue = (strtotime($today) - strtotime($row['due_date'])) / 86400;
        $penalty = $daysOverdue * $penaltyPerDay;
        $conn->query("UPDATE borrowals SET penalty_amount = $penalty WHERE id = {$row['id']}");
    }
}

function checkReturnWarnings($userId) {
    global $conn;
    $today = date('Y-m-d');

    $query = "SELECT b.title, r.due_date 
              FROM borrowals r 
              JOIN books b ON r.book_id = b.id 
              WHERE r.user_id = ? AND r.return_date IS NULL";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $dueDate = $row['due_date'];
        $daysLeft = (strtotime($dueDate) - strtotime($today)) / (60 * 60 * 24);
        $title = htmlspecialchars($row['title']);

        if ($daysLeft == 1) {
            echo "<script>alert('Reminder: Your book \"$title\" is due tomorrow!');</script>";
        } elseif ($daysLeft == 0) {
            echo "<script>alert('Reminder: Your book \"$title\" is due today!');</script>";
        }
    }
}

?>