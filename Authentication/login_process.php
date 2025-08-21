<?php
session_start();
include '../conn.php'; // DB connection

// Only handle POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); // Trim to remove spaces

    // Prepare SQL statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login success
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] == 1) {
                header("Location: ../infoAdmin.php");
            } else {
                header("Location: ../index.php");
            }
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

    // Redirect back to login page with error
    header("Location: login.php");
    exit();
} else {
    // If accessed directly, redirect to login
    header("Location: login.php");
    exit();
}
