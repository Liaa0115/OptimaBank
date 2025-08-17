<?php
session_start();
include 'conn.php';

$points = 0;

if (isset($_SESSION['email'])) {
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
}

// Fetch vouchers
$vouchers = [];
$result = $conn->query("SELECT id, name, image, category, subcategory, price, points_required FROM vouchers ORDER BY id ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $vouchers[] = $row;
    }
}

// Fetch distinct subcategories
$subcategories = [];
$subcatResult = $conn->query("SELECT DISTINCT subcategory FROM vouchers ORDER BY subcategory ASC");
if ($subcatResult && $subcatResult->num_rows > 0) {
    while ($row = $subcatResult->fetch_assoc()) {
        $subcategories[] = $row['subcategory'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Voucher List</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
    body { background-color: #f9f9f9; }
    .points-badge {
        background-color: #d4a373;
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        margin-left: 15px;
        font-weight: 600;
    }
    .voucher-card {
        border: 1px solid #ddd;
        border-radius: 10px;
        overflow: hidden;
        background: white;
        transition: all 0.2s ease-in-out;
    }
    .voucher-card:hover {
        box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-3px);
    }
    .voucher-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }
    .cart-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0f6f4a;
        margin-left: 10px;
    }
    .cart-icon i {
        font-size: 18px;
    }
    .cart-icon:hover {
        background-color: #ffc600;
        color: #0f6f4a; 
    }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #0f6f4a;">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="images/logo.png" alt="Logo" height="36" style="border-radius:6px; background:white; padding:4px;">
      <span style="margin-left:6px; color:#ffc600; font-weight:700;">TREATS POINTS</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php" style="color: #f9f9f9;">Home Page</a></li>
        <li class="nav-item"><a class="nav-link" href="voucher_list.php" style="color: #f9f9f9;">Voucher</a></li>
        <?php if (isset($_SESSION['email'])): ?>
          <li class="nav-item"><a class="nav-link" href="profile.php" style="color: #f9f9f9;">Profile</a></li>
          <li class="nav-item points-badge d-flex align-items-center" style="text-decoration: none; color: #0f6f4a; background-color: #ffc600 !important; border-color: #ffc600 !important;">
            Point Balance: <?= $points ?>
          </li>
          <li class="nav-item">
            <a class="nav-link cart-icon" href="cart.php">
                <i class="fa-solid fa-cart-shopping"></i>
            </a>
          </li>
          <li class="nav-item"><a class="nav-link" href="logout.php" style="color: #f9f9f9;">Sign Out</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-item points-badge d-flex align-items-center" style="text-decoration: none; color: #0f6f4a; background-color: #ffc600 !important; border-color: #ffc600 !important;" href="authentication/login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Section -->
<div class="container mt-4">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
      <h5 class="fw-bold">CATEGORY</h5>
      <div>
        <input type="checkbox" checked disabled> Food
      </div>

      <hr>

      <h5 class="fw-bold">TYPE</h5>
      <?php foreach ($subcategories as $subcat): ?>
        <div>
          <input type="checkbox" class="subcategory-filter" value="<?= htmlspecialchars($subcat) ?>"> 
          <?= htmlspecialchars($subcat) ?>
        </div>
      <?php endforeach; ?>

      <hr>

      <!-- Search by voucher name -->
      <h5 class="fw-bold">SEARCH</h5>
      <input type="text" id="search-name" class="form-control mb-3" placeholder="Search voucher name...">

      <hr>

      <!-- Filter by points -->
      <div class="d-flex justify-content-between align-items-center">
          <h5 class="fw-bold mb-0">POINTS</h5>
          <a href="#" id="points-clear-btn" class="text-muted text-decoration-none">Clear</a>
      </div>

      <div class="d-flex align-items-center mb-2 mt-2">
          <input type="text" class="form-control me-2" id="min-points" placeholder="Min Points">
          <input type="text" class="form-control" id="max-points" placeholder="Max Points">
          <button class="btn btn-outline-secondary ms-2" id="points-search-btn">
              <i class="fa-solid fa-magnifying-glass"></i>
          </button>
      </div>
    </div>

    <!-- Voucher List -->
    <div class="col-md-9">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">List Of Voucher</h4>
        <div>
          <select class="form-select form-select-sm">
            <option>Newest</option>
            <option>Points: Low to High</option>
            <option>Points: High to Low</option>
          </select>
        </div>
      </div>

      <div class="row g-3" id="voucher-list">
        <?php if (!empty($vouchers)): ?>
          <?php foreach ($vouchers as $voucher): ?>
            <div class="col-md-4 voucher-item" 
                 data-subcategory="<?= htmlspecialchars($voucher['subcategory']) ?>" 
                 data-points="<?= $voucher['points_required'] ?>" 
                 data-name="<?= strtolower(htmlspecialchars($voucher['name'])) ?>">
              <div class="voucher-card">
                <img src="<?= htmlspecialchars($voucher['image']) ?>" alt="<?= htmlspecialchars($voucher['name']) ?>">
                <div class="p-3">
                  <h6 class="fw-bold"><?= htmlspecialchars($voucher['name']) ?></h6>
                  <p class="mb-1 text-muted"><?= htmlspecialchars($voucher['subcategory']) ?></p>
                  <p class="mb-1">RM <?= number_format($voucher['price'], 2) ?></p>
                  <p class="mb-2"><i class="fa-solid fa-coins text-warning"></i> <?= $voucher['points_required'] ?> Points</p>
                  <div class="d-flex justify-content-between">
                    <a href="voucher_info.php?id=<?= $voucher['id'] ?>" class="btn btn-warning btn-sm">Redeem Now</a>
                    <button class="btn btn-success btn-sm">Add to Cart</button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No vouchers found.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Script for filtering -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const checkboxes = document.querySelectorAll(".subcategory-filter");
    const vouchers = document.querySelectorAll(".voucher-item");
    const minPointsInput = document.getElementById("min-points");
    const maxPointsInput = document.getElementById("max-points");
    const pointsSearchBtn = document.getElementById("points-search-btn");
    const pointsClearBtn = document.getElementById("points-clear-btn");
    const searchNameInput = document.getElementById("search-name");

    function filterVouchers() {
        const selectedSubcategories = Array.from(checkboxes)
            .filter(c => c.checked)
            .map(c => c.value);

        const minPoints = parseFloat(minPointsInput.value) || 0;
        const maxPoints = parseFloat(maxPointsInput.value) || Infinity;
        const searchTerm = searchNameInput.value.toLowerCase().trim();

        vouchers.forEach(voucher => {
            const subcat = voucher.getAttribute("data-subcategory");
            const points = parseFloat(voucher.getAttribute("data-points"));
            const name = voucher.getAttribute("data-name");

            const matchesSubcategory = selectedSubcategories.length === 0 || selectedSubcategories.includes(subcat);
            const matchesPoints = points >= minPoints && points <= maxPoints;
            const matchesName = name.includes(searchTerm);

            if (matchesSubcategory && matchesPoints && matchesName) {
                voucher.style.display = "block";
            } else {
                voucher.style.display = "none";
            }
        });
    }

    function clearPointsFilter() {
        minPointsInput.value = '';
        maxPointsInput.value = '';
        filterVouchers();
    }

    checkboxes.forEach(cb => cb.addEventListener("change", filterVouchers));
    pointsSearchBtn.addEventListener("click", filterVouchers);
    pointsClearBtn.addEventListener("click", function(e) {
        e.preventDefault();
        clearPointsFilter();
    });
    searchNameInput.addEventListener("input", filterVouchers);
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
