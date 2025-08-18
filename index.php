<?php
session_start();
include 'conn.php';

// if (!isset($_SESSION['email'])) {
//     header("Location: login.php");
//     exit();
// }

$points = 0; // Default points

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

// Fetch voucher data
$vouchers = [];
$result = $conn->query("SELECT id, name, image, description FROM vouchers ORDER BY id ASC LIMIT 9");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $vouchers[] = $row;
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Optima Bank | Homepage</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/indexStyle.css?v=<?= time() ?>">
</head>
<body>


 <!-- Header / Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--mb-black);">
  <div class="container">
    <!-- Logo + Text -->
    <a class="navbar-brand d-flex align-items-center" href="index.html">
      <img src="images/logo.png" alt="Logo" height="36" style="border-radius:6px; background:white; padding:4px;">
      <span style="margin-left:6px;color:var(--mb-yellow); font-weight:700;">TREATS POINTS</span>
    </a>

    <!-- Mobile menu toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home Page</a></li>
        <li class="nav-item"><a class="nav-link" href="voucher_list.php">Voucher</a></li>
        <?php if (isset($_SESSION['email'])): ?>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item points-badge d-flex align-items-center">
            Point Balance: <?= $points ?>
          </li>
          <li class="nav-item">
            <a class="nav-link cart-icon" href="cart.php">
                <i class="fa-solid fa-cart-shopping"></i>
            </a>
          </li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Sign Out</a></li>
          <?php else: ?>
        <li class="nav-item"><a class="nav-item points-badge d-flex align-items-center" style="text-decoration: none;" href="authentication/login.php">Login</a></li>
      <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>


<!-- Full-width Hero / Carousel -->
<div class="hero-carousel position-relative w-100">
  <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">

      <!-- Slide 1 -->
<div class="carousel-item active">
  <div class="container">
    <div class="row align-items-center g-4 hero-slide">
      <div class="col-md-6 text-center text-md-start">
        <span class="badge-feature">Weekly Spotlight</span>
        <h2 class="mt-3">Up to 50% Off on Popular Dishes</h2>
        <p class="text-muted">Enjoy mouth-watering meals from top restaurants at unbeatable prices — this week only.</p>
        <!-- <div class="mt-3">
          <a href="#" class="btn btn-dark me-2">Explore Menu</a>
          <a href="#" class="btn btn-outline-dark">See Details</a>
        </div> -->
      </div>
      <div class="col-md-6 text-center hero-img-container">
        <img src="images/food/pizza.png" class="hero-img" alt="promo">
      </div>
    </div>
  </div>
</div>

<!-- Slide 2 -->
<div class="carousel-item">
  <div class="container">
    <div class="row align-items-center g-4 hero-slide">
      <div class="col-md-6 text-center text-md-start">
        <span class="badge-feature">New</span>
        <h2 class="mt-3">Exclusive Dining Rewards</h2>
        <p class="text-muted">Earn cashback while enjoying your favorite meals at selected outlets.</p>
        <!-- <div class="mt-3">
          <a href="#" class="btn btn-dark me-2">Dine & Earn</a>
          <a href="#" class="btn btn-outline-dark">Learn More</a>
        </div> -->
      </div>
      <div class="col-md-6 text-center hero-img-container">
        <img src="images/food/satay.jpg" class="hero-img" alt="promo2">
      </div>
    </div>
  </div>
</div>

<!-- Slide 3 -->
<div class="carousel-item">
  <div class="container">
    <div class="row align-items-center g-4 hero-slide">
      <div class="col-md-6 text-center text-md-start">
        <span class="badge-feature">Limited Time</span>
        <h2 class="mt-3">Special Offers on Global Cuisine</h2>
        <p class="text-muted">Taste the best from around the world — from Italian pasta to Japanese sushi.</p>
        <!-- <div class="mt-3">
          <a href="#" class="btn btn-dark me-2">Book a Table</a>
          <a href="#" class="btn btn-outline-dark">View Deals</a>
        </div> -->
      </div>
      <div class="col-md-6 text-center hero-img-container">
        <img src="images/food/mapo_tofu.jpg" class="hero-img" alt="promo3">
      </div>
    </div>
  </div>
</div>

<!-- Slide 4 -->
<div class="carousel-item">
  <div class="container">
    <div class="row align-items-center g-4 hero-slide">
      <div class="col-md-6 text-center text-md-start">
        <span class="badge-feature">Hot Deal</span>
        <h2 class="mt-3">Up to 60% Off on Desserts</h2>
        <p class="text-muted">Indulge in creamy cakes, rich chocolates, and sweet treats at irresistible prices.</p>
        <!-- <div class="mt-3">
          <a href="#" class="btn btn-dark me-2">Order Now</a>
          <a href="#" class="btn btn-outline-dark">More Sweets</a>
        </div> -->
      </div>
      <div class="col-md-6 text-center hero-img-container">
        <img src="images/food/chocolate_cake.jpg" class="hero-img" alt="Dessert Deals">
      </div>
    </div>
  </div>
</div>

<!-- Slide 5 -->
<div class="carousel-item">
  <div class="container">
    <div class="row align-items-center g-4 hero-slide">
      <div class="col-md-6 text-center text-md-start">
        <span class="badge-feature">Best Seller</span>
        <h2 class="mt-3">Signature Dishes You Can’t Miss</h2>
        <p class="text-muted">From juicy burgers to aromatic curries — savor our most-loved dishes today.</p>
        <!-- <div class="mt-3">
          <a href="#" class="btn btn-dark me-2">Order Now</a>
          <a href="#" class="btn btn-outline-dark">See Menu</a>
        </div> -->
      </div>
      <div class="col-md-6 text-center hero-img-container">
        <img src="images/food/nasi_lemak.jpg" class="hero-img" alt="Best Seller Dishes">
      </div>
    </div>
  </div>
</div>


    </div>

    <!-- Custom bottom navigation buttons -->
    <div class="carousel-nav d-flex justify-content-center gap-2 mb-3">
      <button class="nav-btn active" data-bs-target="#heroCarousel" data-bs-slide-to="0"></button>
      <button class="nav-btn" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
      <button class="nav-btn" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
      <button class="nav-btn" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
      <button class="nav-btn" data-bs-target="#heroCarousel" data-bs-slide-to="4"></button>
    </div>
  </div>
</div>

<!-- Categories -->
<div class="row mt-4 g-3">
  <div class="col-6 col-md-3">
    <a href="voucher_list.php?subcategory=Western Food" class="btn btn-light w-100 category-tile p-3 text-center">
      <i class="fa fa-hamburger fa-2x mb-2"></i>
      <div class="fw-bold">Western Food</div>
      <small class="text-muted">Savor rich flavors inspired by the best of Western cuisine</small>
    </a>
  </div>

  <div class="col-6 col-md-3">
    <a href="voucher_list.php?subcategory=Malay Food" class="btn btn-light w-100 category-tile p-3 text-center">
      <i class="fa fa-drumstick-bite fa-2x mb-2"></i>
      <div class="fw-bold">Malay Food</div>
      <small class="text-muted">Experience the authentic taste of Malaysia’s heritage</small>
    </a>
  </div>

  <div class="col-6 col-md-3">
    <a href="voucher_list.php?subcategory=Chinese Food" class="btn btn-light w-100 category-tile p-3 text-center">
      <i class="fa fa-bowl-rice fa-2x mb-2"></i>
      <div class="fw-bold">Chinese Food</div>
      <small class="text-muted">Enjoy timeless Chinese recipes bursting with tradition</small>
    </a>
  </div>

  <div class="col-6 col-md-3">
    <a href="voucher_list.php?subcategory=Indian Food" class="btn btn-light w-100 category-tile p-3 text-center">
      <i class="fa fa-pepper-hot fa-2x mb-2"></i>
      <div class="fw-bold">Indian Food</div>
      <small class="text-muted">Delight in the vibrant spices and flavors of India</small>
    </a>
  </div>
</div>




         <!-- Featured deals -->
<div class="container my-5">

  <h5 class="text-center mb-4">Featured Deals</h5>
 <!-- Search Bar -->
<div class="mb-4 text-center">
  <div class="position-relative w-50 mx-auto">
    <i class="fa fa-search position-absolute" 
       style="left: 15px; top: 50%; transform: translateY(-50%); color: #888;"></i>
    <input type="text" id="dealSearch" class="form-control ps-5 py-2 rounded-pill shadow-sm"
           placeholder="Search deals..." 
           style="border: 1px solid #ddd; transition: all 0.3s;">
  </div>
</div>

<!-- Extra styling -->
<style>
  #dealSearch:focus {
    outline: none;
    border-color: #000;
    box-shadow: 0 0 8px rgba(0,0,0,0.2);
  }
</style>

  
<div class="row justify-content-center g-4">
  <?php if (!empty($vouchers)): ?>
    <?php foreach ($vouchers as $voucher): ?>
      <div class="col-md-4 col-sm-6">
        <div class="p-3 bg-white deal-card text-center">
          <img src="<?= htmlspecialchars($voucher['image']) ?>" 
               class="rounded mb-3 img-fluid mx-auto d-block" 
               style="max-width: 400px;" 
               alt="<?= htmlspecialchars($voucher['name']) ?>">

          <div class="fw-bold"><?= htmlspecialchars($voucher['name']) ?></div>
          <div class="text-muted small">
            <?= htmlspecialchars($voucher['description']) ?>
          </div>

          <!-- Butang align tengah -->
          <div class="mt-3 d-flex justify-content-center align-items-center gap-2">
            <a href="voucher_info.php?id=<?= $voucher['id'] ?>" class="btn btn-sm btn-dark">
              <i class="fa fa-gift me-1"></i> Redeem
            </a>
            <button 
              class="btn btn-add-to-cart btn-dark rounded-circle d-flex align-items-center justify-content-center" 
              type="button" 
              data-voucher-id="<?= $voucher['id']; ?>" 
              title="Add to Cart" 
              style="width:40px; height:40px;">
              <i class="fa fa-shopping-cart fa-sm"></i>
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="text-center">No vouchers available right now.</p>
  <?php endif; ?>
</div>


</div>

       
      </div>

    </div>
    <!-- Success Modal -->
<div class="modal fade" id="cartSuccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4 text-center">
      <div class="modal-body">
        <i class="fa fa-check-circle text-success fa-3x mb-3"></i>
        <h5 class="mb-2">Added to Cart!</h5>
        <p class="text-muted">Your item has been successfully added to the cart.</p>
        <button type="button" class="btn btn-dark mt-3" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

  </main>

  <footer>
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <h6 class="text-white">OptimaBank<span style="margin-left:6px;color:var(--mb-yellow); font-weight:700;">TREATS POINTS</span></h6>
          <p class="small text-white">OptimaBank | 2025 | All Right Reserved ©.</p>
        </div>
        <div class="col-md-6 text-md-end small text-white">
          <div>© OptimaBank • Terms • Privacy</div>
        </div>
      </div>
    </div>
  </footer>

   <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Sync custom nav buttons with carousel
    const carousel = document.querySelector('#heroCarousel');
    const navBtns = document.querySelectorAll('.nav-btn');
    carousel.addEventListener('slide.bs.carousel', function (e) {
      navBtns.forEach(btn => btn.classList.remove('active'));
      navBtns[e.to].classList.add('active');
    });
  </script>
    <!-- Bootstrap Tooltip Init -->
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })
      </script>
<!-- Search Script -->
<script>
  document.getElementById('dealSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let deals = document.querySelectorAll('.deal-card-item');

    deals.forEach(function(deal) {
      let title = deal.querySelector('.fw-bold').textContent.toLowerCase();
      let desc = deal.querySelector('.text-muted').textContent.toLowerCase();

      if (title.includes(filter) || desc.includes(filter)) {
        deal.style.display = '';
      } else {
        deal.style.display = 'none';
      }
    });
  });
</script>
<script>
document.getElementById('dealSearch').addEventListener('keyup', function() {
  let query = this.value.toLowerCase();
  let cards = document.querySelectorAll('.deal-card');

  cards.forEach(card => {
    let text = card.innerText.toLowerCase();
    if (text.includes(query)) {
      card.parentElement.style.display = ""; // show col
    } else {
      card.parentElement.style.display = "none"; // hide col
    }
  });
});
</script>
  <script>
    // Handle Add to Cart
    document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
    btn.addEventListener('click', function() {
        const voucherId = this.dataset.voucherId;
        const quantity = 1; // default quantity

        fetch('cart_controller.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
        action: 'increase',
        voucher_id: voucherId,
        quantity: quantity
    })
})
.then(res => {
    if (!res.ok) {
        throw new Error("HTTP status " + res.status);
    }
    return res.json();
})
.then(data => {
    console.log("Response:", data);
    if (data.status === 'success') {
        const successModal = new bootstrap.Modal(document.getElementById('cartSuccessModal'));
        successModal.show();
    } else {
        alert(data.message || 'Something went wrong');
    }
})
.catch(err => {
    console.error("Fetch error:", err);
    alert('Request failed: ' + err.message);
});
    });
});

  </script>
</body>
</html>