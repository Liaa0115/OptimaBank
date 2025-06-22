<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
?>

<h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
<p>You are logged in as: <?php echo $_SESSION['email']; ?></p>
<a href="logout.php">Logout</a>
