<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

$stmt = $conn->prepare("SELECT username, fullname, phone, address, street, postcode, city, state, about, profile_image FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$pointStmt = $conn->prepare("SELECT points FROM Points WHERE email = ?");
$pointStmt->bind_param("s", $email);
$pointStmt->execute();
$pointResult = $pointStmt->get_result()->fetch_assoc();
$points = $pointResult ? $pointResult['points'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="css/navbarProfile.css">
</head>
<body>

<nav class="top-navbar">
    <div class="logo">
        <a href="index.html"><img src="images/logo.png" alt="Logo"></a>
    </div>
    <ul>
        <li><a href="index.html">Home Page</a></li>
        <li><a href="voucher.html">Voucher</a></li>
        <li class="points-badge">Point Balance: <?= $points ?></li>
        <li><a href="logout.php">Sign Out</a></li>
    </ul>
</nav>

<div class="container">
<div class="left-panel position-relative text-center">

    <!-- Profile title -->
    <div class="profile-title">Profile</div><br><br>

    <!-- Profile image -->
    <div class="profile-img-wrapper">
        <img src="uploads/<?= htmlspecialchars($user['profile_image'] ?: 'default-profile.jpg') ?>" alt="Profile Picture" class="profile-img">
        <div class="edit-icon" onclick="openModal('picture')"><i class="fas fa-pen"></i></div>
    </div>

    <!-- User name -->
    <h3 class="mt-3"><?= htmlspecialchars($user['fullname'] ?? "") ?></h3>

    <!-- Points balance card -->
    <div class="points-card shadow">
        <h5 class="mb-1 text-muted">Points Balance</h5>
        <div class="points-value"><?= $points ?></div>
    </div>

</div>


    <div class="right-panel">
        <div class="info-section">
            <h4>Personal Information</h4>
            <p><strong>Full Name:</strong> <?= htmlspecialchars($user['fullname'] ?? "") ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username'] ?? "") ?></p>
            <p><strong>About Me:</strong> <?= htmlspecialchars($user['about'] ?? "") ?></p>
            <div class="action-buttons">
                <button onclick="openModal('profile')">Edit Profile</button>
            </div>
        </div>

        <div class="info-section">
            <h4>Contact Information</h4>
            <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? "") ?></p>
            <p><strong>Street:</strong> <?= htmlspecialchars($user['street'] ?? "") ?></p>
            <p><strong>Postcode:</strong> <?= htmlspecialchars($user['postcode'] ?? "") ?></p>
            <p><strong>City:</strong> <?= htmlspecialchars($user['city'] ?? "") ?></p>
            <p><strong>State:</strong> <?= htmlspecialchars($user['state'] ?? "") ?></p>
            <div class="action-buttons">
                <button onclick="openModal('contact')">Edit Contact</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-profile" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('profile')">&times;</span>
        <h2>Edit Profile</h2>
        <form method="post" action="update_profile.php">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname'] ?? "") ?>">
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? "") ?>">
            </div>
            <div class="form-group">
                <label>About Me</label>
                <textarea name="about"><?= htmlspecialchars($user['about'] ?? "") ?></textarea>
            </div>
            <button type="submit" name="update_profile">Save Changes</button>
        </form>
    </div>
</div>

<div id="modal-contact" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('contact')">&times;</span>
        <h2>Edit Contact</h2>
        <form method="post" action="update_profile.php">
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? "") ?>"></div>
            <div class="form-group"><label>Street</label><input type="text" name="street" value="<?= htmlspecialchars($user['street'] ?? "") ?>"></div>
            <div class="form-group"><label>Postcode</label><input type="text" name="postcode" value="<?= htmlspecialchars($user['postcode'] ?? "") ?>"></div>
            <div class="form-group"><label>City</label><input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? "") ?>"></div>
            <div class="form-group"><label>State</label><input type="text" name="state" value="<?= htmlspecialchars($user['state'] ?? "") ?>"></div>
            <button type="submit" name="update_contact">Save Changes</button>
        </form>
    </div>
</div>

<div id="modal-picture" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('picture')">&times;</span>
        <h2>Update Profile Picture</h2>
        <form method="post" action="update_profile.php" enctype="multipart/form-data">
            <div class="form-group">
                <label>Choose New Picture</label>
                <input type="file" name="profile_image" required>
            </div>
            <button type="submit" name="update_picture">Save Picture</button> </form>
    </div>
</div>

<script>
    function openModal(type) {
        document.getElementById('modal-' + type).style.display = 'flex';
    }
    function closeModal(type) {
        document.getElementById('modal-' + type).style.display = 'none';
    }
</script>

</body>
</html>