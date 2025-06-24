<?php
session_start();
include 'conn.php'; // Make sure this path is correct for your database connection

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// ✅ Update Personal Information (separate from image upload)
if (isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $about = trim($_POST['about']);

    // Prepare and execute the update statement for personal info
    $stmt = $conn->prepare("UPDATE users SET username=?, fullname=?, about=? WHERE email=?");
    $stmt->bind_param("ssss", $username, $fullname, $about, $email);

    if ($stmt->execute()) {
        header("Location: profile.php?update=profile_info_success");
    } else {
        // Handle error, e.g., log it or show a message
        header("Location: profile.php?update=profile_info_error");
    }
    exit();
}

// ✅ Update Profile Picture (dedicated block)
if (isset($_POST['update_picture'])) { // This will be triggered by the form that updates the picture
    $profile_image = null;

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $imageTmp = $_FILES['profile_image']['tmp_name'];
        $imageName = basename($_FILES['profile_image']['name']);
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        // Generate a unique filename to prevent overwrites and provide security
        $imageFileType = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $uniqueFileName = uniqid() . "." . $imageFileType;
        $targetFile = $targetDir . $uniqueFileName;

        // Basic validation (optional but recommended)
        $allowTypes = ['jpg', 'png', 'jpeg', 'gif'];
        if (!in_array($imageFileType, $allowTypes)) {
            // Handle invalid file type error
            header("Location: profile.php?update=picture_invalid_type");
            exit();
        }

        if (move_uploaded_file($imageTmp, $targetFile)) {
            $profile_image = $uniqueFileName;

            // Update only the profile_image field
            $stmt = $conn->prepare("UPDATE users SET profile_image=? WHERE email=?");
            $stmt->bind_param("ss", $profile_image, $email);

            if ($stmt->execute()) {
                header("Location: profile.php?update=picture_success");
            } else {
                // Handle database error
                header("Location: profile.php?update=picture_db_error");
            }
        } else {
            // Handle file upload error
            header("Location: profile.php?update=picture_upload_error");
        }
    } else {
        // No file uploaded or upload error
        header("Location: profile.php?update=picture_no_file");
    }
    exit();
}


// ✅ Update Contact Details
if (isset($_POST['update_contact'])) {
    $phone = trim($_POST['phone']);
    $street = trim($_POST['street']);
    $postcode = trim($_POST['postcode']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);

    $stmt = $conn->prepare("UPDATE users SET phone=?, street=?, postcode=?, city=?, state=? WHERE email=?");
    $stmt->bind_param("ssssss", $phone, $street, $postcode, $city, $state, $email);

    if ($stmt->execute()) {
        header("Location: profile.php?update=contact_success");
    } else {
        // Handle error
        header("Location: profile.php?update=contact_error");
    }
    exit();
}

// If no specific update action was requested, redirect back to profile
header("Location: profile.php");
exit();

?>