<?php
session_start();
include '../conn.php'; // DB connection

// Check if form submitted via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepare and execute SQL
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Validate result
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login success
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../profile.php");
            exit();
        } else {
            // Wrong password
            $_SESSION['error'] = "Invalid password.";
        }
    } else {
        // Email not found
        $_SESSION['error'] = "No account found with that email.";
    }

    $stmt->close();
    $conn->close();

    // Redirect back to login form with error
    header("Location: login.php");
    exit();
} else {
    // If not POST, redirect
    header("Location: login.php");
    exit();
}
