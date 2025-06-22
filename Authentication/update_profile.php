<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$username = trim($_POST['username']);
$fullname = trim($_POST['fullname']);
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);
$about = trim($_POST['about']);

$profile_image = null;

// ✅ Handle profile image upload
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $imageTmp = $_FILES['profile_image']['tmp_name'];
    $imageName = basename($_FILES['profile_image']['name']);
    $targetDir = "uploads/";

    // Create upload dir if not exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $targetFile = $targetDir . time() . "_" . $imageName;

    if (move_uploaded_file($imageTmp, $targetFile)) {
        $profile_image = basename($targetFile);
    }
}

// ✅ Update user info
if ($profile_image) {
   $stmt = $conn->prepare("UPDATE users SET username=?, fullname=?, phone=?, address=?, about=?, profile_image=? WHERE email=?");
$stmt->bind_param("sssssss", $username, $fullname, $phone, $address, $about, $profile_image, $email);
} else {
 $stmt = $conn->prepare("UPDATE users SET username=?, fullname=?, phone=?, address=?, about=? WHERE email=?");
$stmt->bind_param("ssssss", $username, $fullname, $phone, $address, $about, $email);
}

if ($stmt->execute()) {
    header("Location: profile.php?update=success");
    exit();
} else {
    echo "Error updating profile: " . $stmt->error;
}
?>
