<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

session_start();

if (isset($_POST['reset_email'])) {
    $email = $_POST['reset_email'];

    // Generate token
    $token = bin2hex(random_bytes(16));
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // === DB: Update your database with the token ===
    $conn = new mysqli("localhost", "root", "", "optimabank");
    $stmt = $conn->prepare("UPDATE users SET reset_token=?, token_expires=? WHERE email=?");
    $stmt->bind_param("sss", $token, $expires, $email);
    $stmt->execute();

    // === Send Email via Gmail SMTP ===
    $mail = new PHPMailer(true);
    try {

        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true; 
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
        $mail->Port       = $_ENV['MAIL_PORT'];

        $mail->setFrom('optimabankgiftgroup2@gmail.com', 'OptimaBank');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $resetLink = "http://localhost/OptimaBank/reset_password.php?token=$token";
        $mail->Body    = "Click the link below to reset your password:<br><a href='$resetLink'>$resetLink</a>";

        $mail->send();
        $_SESSION['success'] = "Reset link sent! Check your email.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Email not sent. Error: {$mail->ErrorInfo}";
    }

    header("Location: Authentication/login.php");
    exit;
}
