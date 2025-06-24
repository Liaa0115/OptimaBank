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
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f7fdfc;
            color: #333;
        }

        nav.top-navbar {
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .top-navbar .logo img {
            height: 30px;
        }

        .top-navbar ul {
            list-style: none;
            display: flex;
            gap: 25px;
            margin: 0;
            padding: 0;
            align-items: center; /* Align items vertically in the navbar */
        }

        .top-navbar ul li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .top-navbar .points-badge {
            background: #f9c26b;
            padding: 8px 15px; /* Increased padding */
            border-radius: 20px;
            font-weight: bold;
            color: #333;
            display: flex; /* Use flexbox to align icon and text */
            align-items: center;
            gap: 8px; /* Space between icon and text */
        }

        .top-navbar .points-badge i {
            color: #333; /* Color for the shopping cart icon */
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            display: flex;
            gap: 40px;
        }

        .left-panel, .right-panel {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .left-panel {
            width: 300px;
            text-align: center;
            position: relative;
        }

        .left-panel img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #e0e0e0; /* Added a subtle border to the profile image */
        }

        /* UPDATED EDIT ICON STYLES */
        .edit-icon {
            position: absolute;
            top: 150px; /* Adjust as needed to position at the bottom */
            right: 110px; /* Adjust as needed to position at the right */
            background: #189d82; /* Light grey background */
            border-radius: 50%;
            padding: 8px;
            cursor: pointer;
            color: white; /* Dark grey pencil color */
            border: none; /* Remove the white border */
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px; /* Slightly smaller */
            height: 20px; /* Slightly smaller */
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); /* Subtle shadow */
        }

        .edit-icon i.fas.fa-pen {
            font-size: 16px; /* Adjust the size of the pen icon */
        }

        .left-panel h3 {
            margin: 20px 0 5px;
            color: #333; /* Match image text color */
        }

        .left-panel .point-balance {
            margin-top: 25px; /* Increased margin for better spacing */
            background: #eafaf5;
            padding: 10px 20px; /* Adjusted padding */
            border-radius: 12px;
            font-weight: bold;
            color: #189d82;
            display: inline-block; /* Make it an inline block to size to content */
            width: auto; /* Allow width to adjust */
            min-width: 150px; /* Minimum width for the badge */
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); /* Subtle shadow for the badge */
        }

        .right-panel {
            flex: 1;
            display: flex; /* Use flexbox for vertical layout */
            flex-direction: column;
            gap: 30px; /* Space between info sections */
        }

        .info-section {
            padding: 20px 30px; /* Padding for the info boxes */
            border: 1px solid #e0e0e0; /* Subtle border for the info boxes */
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03); /* Lighter shadow */
        }

        .info-section h4 {
            color: #189d82;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee; /* Separator for the heading */
            padding-bottom: 10px;
        }

        .info-section p {
            margin: 8px 0; /* Adjusted margin */
            font-size: 15px;
            line-height: 1.5;
        }

        .info-section p strong {
            color: #555;
            min-width: 100px; /* Ensure labels align */
            display: inline-block;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px; /* Keep this for spacing if buttons are placed here */
            justify-content: flex-end; /* Align buttons to the right within their container */
        }

        .action-buttons button {
            background-color: #189d82;
            color: white;
            border: none;
            padding: 10px 20px; /* Increased padding */
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease; /* Smooth transition on hover */
        }

        .action-buttons button:hover {
            background-color: #15866d; /* Darker shade on hover */
        }

        /* Modal Styles (no significant changes needed for layout matching) */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 500px;
            max-width: 90%; /* Ensure responsiveness */
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px; /* Slightly larger close icon */
            cursor: pointer;
            color: #777;
        }

        .modal-content h2 {
            color: #189d82;
            margin-bottom: 25px; /* Increased margin */
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px; /* Increased margin */
        }

        .form-group label {
            display: block;
            margin-bottom: 8px; /* Adjusted margin */
            font-weight: 600;
            color: #555;
        }

        .form-group input,
        .form-group textarea {
            width: calc(100% - 20px); /* Account for padding */
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd; /* Lighter border */
            font-size: 15px;
        }

        .form-group textarea {
            resize: vertical; /* Allow vertical resizing */
            min-height: 80px;
        }

        .form-group button {
            display: block; /* Make button full width */
            width: 100%;
            margin-top: 25px; /* Increased margin */
            padding: 12px 18px; /* Increased padding */
            background-color: #189d82;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-group button:hover {
            background-color: #15866d;
        }
    </style>
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
    <div class="left-panel">
        <img src="uploads/<?= htmlspecialchars($user['profile_image'] ?: 'default-profile.jpg') ?>" alt="Profile Picture">
        <div class="edit-icon" onclick="openModal('picture')"><i class="fas fa-pen"></i></div>
        <h3><?= htmlspecialchars($user['fullname'] ?? "") ?></h3>
        <div class="point-balance">Points Balance: <?= $points ?></div>
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