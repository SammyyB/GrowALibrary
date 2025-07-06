<?php
require 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (usernameExists($username)) {
        $error = "Username already taken.";
    } else {
        registerUser($username, $password, 'customer'); // default role: customer
        header("Location: login.php?registered=1");
        exit();
    }
}
?>

<h2>Register as Customer</h2>
<?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

<form method="post">
    <input type="text" name="username" placeholder="Username" required class="form-control"><br>
    <input type="password" name="password" placeholder="Password" required class="form-control"><br>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required class="form-control"><br>
    <button type="submit" class="btn btn-primary">Register</button>
</form>