<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../conn.php'; // Your DB connection

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri('http://localhost/optimabank/authentication/google-callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    $oauth2 = new Google_Service_Oauth2($client);
    $google_user = $oauth2->userinfo->get();

    $email = $google_user->email;
    $name = $google_user->name;

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Insert new Google user
        $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, '', '')");
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
    }

    $_SESSION['email'] = $email;
    $_SESSION['username'] = $name;

    header("Location: ../homepage.php");
    exit();
}
