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

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Optima Bank | Homepage</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
:root {
  --mb-yellow: #ffc600; /* Maybank-like yellow */
  --mb-black: #0f6f4a;
  --muted: #6c757d;
  --card-radius: 12px;
}

body {
  font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  background: #f8f9fb;
  color: #222;
}

/* Top bar */
.topbar {
  background: var(--mb-black);
  color: white;
  padding: 6px 0;
  font-size: .9rem;
}
/* Navbar custom styling */
.navbar {
  background-color: var(--mb-black) !important;
  padding: 0.75rem 0;
}

.navbar-brand img {
  border-radius: 6px;
  background: white;
  padding: 4px;
}

.navbar-brand span {
  margin-left: 6px;
  color: var(--mb-yellow);
  font-weight: 700;
  font-size: 1.2rem;
}

.navbar-nav .nav-link {
  color: white !important;
  font-weight: 500;
  padding: 0.5rem 1rem;
  transition: color 0.2s ease;
}

.navbar-nav .nav-link:hover {
  color: var(--mb-yellow) !important;
}

.points-badge {
  background-color: var(--mb-yellow);
  color: var(--mb-black) !important;
  font-weight: 600;
  border-radius: 20px;
  padding: 0.3rem 0.75rem;
  margin: 0.25rem 0;
}

.navbar-toggler {
  border-color: var(--mb-yellow);
}

.navbar-toggler-icon {
  filter: invert(80%) sepia(75%) saturate(300%) hue-rotate(360deg);
}

/* Header */
.site-header {
  background: linear-gradient(90deg, rgba(11,11,11,1) 0%, rgba(19,19,19,1) 100%);
  color: white;
  padding: 18px 0;
}
.brand-logo {
  display:inline-flex;align-items:center;gap:.6rem;font-weight:700;font-size:1.2rem;color:var(--mb-yellow)
}

/* Hero */
.hero-carousel {
  background: linear-gradient(90deg, #ffeaa7 0%, #0f6f4a 100%);
}
.hero-carousel .badge-feature {
  background: var(--mb-yellow);
  color: #111;
  font-weight:600;
  border-radius: 999px;
  padding: 6px 12px;
  font-size: .85rem;
}

/* Uniform slide height */
.hero-slide {
  min-height: 400px; /* all slides same height */
}
.hero-img-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}
.hero-img {
  max-height: 350px; /* fits inside slide */
  width: 100%;
  height: 100%;
  object-fit: contain; /* keeps aspect ratio */
  border-radius: 8px;
}

/* Bottom nav buttons */
.carousel-nav {
  position: relative;
  margin-top: 1rem;
}
.nav-btn {
  width: 24px;
  height: 8px;
  background-color: rgba(0,0,0,0.3);
  border: none;
  border-radius: 4px;
  transition: background-color 0.3s ease;
}
.nav-btn.active,
.nav-btn:hover {
  background-color: var(--mb-yellow);
}

/* Cards */
.deal-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out;
  }
  .deal-card:hover {
    transform: translateY(-5px);
  }
.deal-card img {
  width: 100%;                 /* responsive scaling in Bootstrap column */
  max-width: 300px;            /* or adjust to the size you want */
  aspect-ratio: 1 / 1;         /* force square shape (1080x1080 ratio) */
  object-fit: cover;           /* fill square, crop overflow */
  border-radius: 8px;
  background-color: #fff;
  padding: 4px;
  margin: 0 auto;              /* center in card */
  display: block;
}

/* Category tiles */
.category-tile {border-radius:12px;background:white;padding:18px;text-align:center;box-shadow:0 4px 12px rgba(18,20,25,0.04)}

/* Footer */
footer {padding:30px 0;background:#0f1720;color:#cbd5e1}

@media (max-width:767px) {
  .brand-logo {font-size:1rem}
  .hero-slide {min-height: auto;}
  .hero-img {max-height: 250px;}
}
.btn-dark {
  background-color: #0f6f4a !important;
  border-color: #0f6f4a !important;
}
.btn-dark:hover {
  background-color: #ffc600 !important;
  border-color: #ffc600 !important;
}

</style>
</head>
<body>

  <!-- Top announcement bar -->
  <!-- <div class="topbar text-center">
    <div class="container d-flex justify-content-between align-items-center">
      <div>Enjoy exclusive deals & cashback — sign in to unlock more!</div>
      <div class="d-none d-md-block">Need help? <a href="#" class="text-decoration-underline text-white">Support</a></div>
    </div>
  </div> -->

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
        <li class="nav-item"><a class="nav-link" href="voucher.html">Voucher</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        <li class="nav-item points-badge d-flex align-items-center">
          Point Balance: <?= $points ?>
        </li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Sign Out</a></li>
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
              <h2 class="mt-3">Up to 50% off at top restaurants & outlets</h2>
              <p class="text-muted">Redeem rewards with your card or app — limited time only.</p>
              <div class="mt-3">
                <a href="#" class="btn btn-dark me-2">Explore deals</a>
                <a href="#" class="btn btn-outline-dark">How it works</a>
              </div>
            </div>
            <div class="col-md-6 text-center hero-img-container">
              <img src="images/promo.jpg" class="hero-img" alt="promo">
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
              <h2 class="mt-3">Exclusive Cashback for cardholders</h2>
              <p class="text-muted">Stack rewards and save more on everyday spends.</p>
              <div class="mt-3">
                <a href="#" class="btn btn-dark me-2">Get Cashback</a>
                <a href="#" class="btn btn-outline-dark">Learn more</a>
              </div>
            </div>
            <div class="col-md-6 text-center hero-img-container">
              <img src="images/promo1.jpg" class="hero-img" alt="promo2">
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
                <h2 class="mt-3">Special Deals on Travel & Dining</h2>
                <p class="text-muted">Enjoy up to 50% off at selected hotels and restaurants worldwide.</p>
                <div class="mt-3">
                <a href="#" class="btn btn-dark me-2">Book Now</a>
                <a href="#" class="btn btn-outline-dark">View Offers</a>
                </div>
            </div>
            <div class="col-md-6 text-center hero-img-container">
                <img src="images/airasia.jpg" class="hero-img" alt="promo3">
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
                <h2 class="mt-3">Skintific Skincare – 60% OFF</h2>
                <p class="text-muted">Reveal your best skin yet with dermatologist-approved products at unbeatable prices.</p>
                <div class="mt-3">
                <a href="#" class="btn btn-dark me-2">Shop Now</a>
                <a href="#" class="btn btn-outline-dark">Learn More</a>
                </div>
            </div>
            <div class="col-md-6 text-center hero-img-container">
                <img src="images/promo3.png" class="hero-img" alt="Skintific 60% Off">
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
                <h2 class="mt-3">Samsung Electronics Mega Sale</h2>
                <p class="text-muted">Save big on the latest Samsung smartphones, TVs, and home appliances.</p>
                <div class="mt-3">
                <a href="#" class="btn btn-dark me-2">Shop Now</a>
                <a href="#" class="btn btn-outline-dark">Browse All</a>
                </div>
            </div>
            <div class="col-md-6 text-center hero-img-container">
                <img src="images/promo4.jpg" class="hero-img" alt="Samsung Electronics">
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
            <div class="col-6 col-md-4">
              <div class="category-tile">
                <i class="fa fa-utensils fa-2x mb-2"></i>
                <div class="fw-bold">Food & Beverage</div>
                <small class="text-muted">Dining & delivery</small>
              </div>
            </div>
            <div class="col-6 col-md-4">
              <div class="category-tile">
                <i class="fa fa-shopping-bag fa-2x mb-2"></i>
                <div class="fw-bold">Retail</div>
                <small class="text-muted">Shopping deals</small>
              </div>
            </div>
            <div class="col-6 col-md-4">
              <div class="category-tile">
                <i class="fa fa-ticket-alt fa-2x mb-2"></i>
                <div class="fw-bold">Entertainment</div>
                <small class="text-muted">Movies & events</small>
              </div>
            </div>
          </div>

         <!-- Featured deals -->
<div class="container my-5">
  <h5 class="text-center mb-4">Featured Deals</h5>
  <div class="row justify-content-center g-4">

    <!-- Deal 1 -->
    <div class="col-md-4 col-sm-6">
      <div class="p-3 bg-white deal-card text-center">
        <img src="images/iphone.png" class="rounded mb-3 img-fluid" style="max-width: 400px;" alt="deal">
        <div class="fw-bold">Apple Products</div>
        <div class="text-muted small">Up to 30% off on selected items</div>
        <div class="mt-3">
          <a href="#" class="btn btn-sm btn-dark">Redeem</a>
        </div>
      </div>
    </div>

    <!-- Deal 2 -->
    <div class="col-md-4 col-sm-6">
      <div class="p-3 bg-white deal-card text-center">
        <img src="images/zus2.jpg" class="rounded mb-3 img-fluid" style="max-width: 400px;" alt="deal">
        <div class="fw-bold">ZUS Coffee</div>
        <div class="text-muted small">RM10 off with min spend RM40</div>
        <div class="mt-3">
          <a href="#" class="btn btn-sm btn-dark">Redeem</a>
        </div>
      </div>
    </div>

    <!-- Deal 3 -->
    <div class="col-md-4 col-sm-6">
      <div class="p-3 bg-white deal-card text-center">
        <img src="images/travel1.jpg" class="rounded mb-3 img-fluid" style="max-width: 400px;" alt="deal">
        <div class="fw-bold">Travel Deals</div>
        <div class="text-muted small">Save up to RM200 on flights</div>
        <div class="mt-3">
          <a href="#" class="btn btn-sm btn-dark">Redeem</a>
        </div>
      </div>
    </div>

     <!-- Deal 4 -->
    <div class="col-md-4 col-sm-6">
      <div class="p-3 bg-white deal-card text-center">
        <img src="images/mcd.jpg" class="rounded mb-3 img-fluid" style="max-width: 400px;" alt="deal">
        <div class="fw-bold">Mc Donald's</div>
        <div class="text-muted small">Limited time offer Gong Xi Savings Bundle</div>
        <div class="mt-3">
          <a href="#" class="btn btn-sm btn-dark">Redeem</a>
        </div>
      </div>
    </div>

     <!-- Deal 5 -->
    <div class="col-md-4 col-sm-6">
      <div class="p-3 bg-white deal-card text-center">
        <img src="images/mb.jpg" class="rounded mb-3 img-fluid" style="max-width: 400px;" alt="deal">
        <div class="fw-bold">Marrybrown Products</div>
        <div class="text-muted small">20% MB Gift Voucher + 1pcs Jurassic World Pillow </div>
        <div class="mt-3">
          <a href="#" class="btn btn-sm btn-dark">Redeem</a>
        </div>
      </div>
    </div>

    <!-- Deal 6 -->
    <div class="col-md-4 col-sm-6">
      <div class="p-3 bg-white deal-card text-center">
        <img src="images/starbucks.jpg" class="rounded mb-3 img-fluid" style="max-width: 400px;" alt="deal">
        <div class="fw-bold">Starbucks Coffee</div>
        <div class="text-muted small">Only RM8.80 for Tall-sized handcrafted beverage</div>
        <div class="mt-3">
          <a href="#" class="btn btn-sm btn-dark">Redeem</a>
        </div>
      </div>
    </div>

    <!-- Deal 7 -->
    <div class="col-md-4 col-sm-6">
      <div class="p-3 bg-white deal-card text-center">
        <img src="images/xiaomi.jpg" class="rounded mb-3 img-fluid" style="max-width: 400px;" alt="deal">
        <div class="fw-bold">Xiaomi Products</div>
        <div class="text-muted small">Only for RM 729 for Redmi 15 5G limited time offer</div>
        <div class="mt-3">
          <a href="#" class="btn btn-sm btn-dark">Redeem</a>
        </div>
      </div>
    </div>

     <!-- Deal 8 -->
    <div class="col-md-4 col-sm-6">
      <div class="p-3 bg-white deal-card text-center">
        <img src="images/oppo.jpg" class="rounded mb-3 img-fluid" style="max-width: 400px;" alt="deal">
        <div class="fw-bold">Oppo Products</div>
        <div class="text-muted small">Save up to RM200 on discounts</div>
        <div class="mt-3">
          <a href="#" class="btn btn-sm btn-dark">Redeem</a>
        </div>
      </div>
    </div>

    <!-- Deal 9 -->
    <div class="col-md-4 col-sm-6">
      <div class="p-3 bg-white deal-card text-center">
        <img src="images/eucerin.jpg" class="rounded mb-3 img-fluid" style="max-width: 400px;" alt="deal">
        <div class="fw-bold">Eucerins Products</div>
        <div class="text-muted small">Buy 1 Free 1 starting 30 July until 1 Sept</div>
        <div class="mt-3">
          <a href="#" class="btn btn-sm btn-dark">Redeem</a>
        </div>
      </div>
    </div>

  </div>
</div>

        <!-- Right column (sidebar) -->
        <!-- <div class="col-lg-4">
          <div class="position-sticky" style="top:90px">
            <div class="bg-white p-3 rounded deal-card">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="small text-muted">Your balance</div>
                  <div class="fw-bold">RM 120.00</div>
                </div>
                <div>
                  <a href="#" class="btn btn-outline-secondary btn-sm">Top up</a>
                </div>
              </div>
            </div>

            <div class="bg-white p-3 rounded mt-3 deal-card">
              <div class="fw-bold">Popular nearby</div>
              <div class="mt-2 small text-muted">Shops near you getting great reviews</div>

              <ul class="list-unstyled mt-3">
                <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                  <div>
                    <div class="fw-semibold">Coffee Spot</div>
                    <small class="text-muted">0.4km away</small>
                  </div>
                  <a href="#" class="btn btn-sm btn-outline-dark">View</a>
                </li>

                <li class="d-flex justify-content-between align-items-center py-2">
                  <div>
                    <div class="fw-semibold">Noodle House</div>
                    <small class="text-muted">0.6km away</small>
                  </div>
                  <a href="#" class="btn btn-sm btn-outline-dark">View</a>
                </li>
              </ul>
            </div>

            <div class="bg-white p-3 rounded mt-3 text-center deal-card">
              <div class="fw-bold">Get extra perks</div>
              <p class="small text-muted">Link your card for faster redemptions and exclusive offers.</p>
              <a href="#" class="btn btn-dark">Link card</a>
            </div>
          </div>
        </div> -->

      </div>

      <!-- More sections -->
      <div class="row mt-5">
        <div class="col-12">
          <center><h5>All Categories</h5></center>
          <div class="mt-3 row g-3">
            <!-- Repeat tiles -->
            <div class="col-6 col-md-3">
              <div class="category-tile">Groceries</div>
            </div>
            <div class="col-6 col-md-3">
              <div class="category-tile">Travel</div>
            </div>
            <div class="col-6 col-md-3">
              <div class="category-tile">Health</div>
            </div>
            <div class="col-6 col-md-3">
              <div class="category-tile">Beauty</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>

  <footer>
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <h6 class="text-white">MyTreats Demo</h6>
          <p class="small text-muted">This is a demo clone for layout and styling only — replace images and copy with your own assets.</p>
        </div>
        <div class="col-md-6 text-md-end small text-muted">
          <div>© Your Company • Terms • Privacy</div>
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
</body>
</html>