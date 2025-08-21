<?php
session_start();
include 'conn.php';

// Function to get counts from the database
function getCount($conn, $table) {
    $sql = "SELECT COUNT(*) AS count FROM $table";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

// Function to get counts for specific categories
function getCategoryCount($conn) {
    $sql = "SELECT COUNT(DISTINCT category) AS count FROM vouchers";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

function getRedeemedVoucherData($conn, $month = null, $year = null) {
    $data = [];

    if (!$month) $month = date('m');
    if (!$year) $year = date('Y');

    // 1. Find first and last day of the month
    $firstDay = "$year-$month-01";
    $lastDay = date("Y-m-t", strtotime($firstDay));

    // 2. Query actual redeemed data grouped by week
    $sql = "
        SELECT 
            WEEK(redeemed_at, 1) - WEEK(DATE_SUB(redeemed_at, INTERVAL DAY(redeemed_at)-1 DAY), 1) + 1 AS week_number,
            COUNT(*) AS total_redeemed
        FROM redeemed_vouchers
        WHERE MONTH(redeemed_at) = $month AND YEAR(redeemed_at) = $year
        GROUP BY week_number
    ";
    $result = $conn->query($sql);

    $redeemedMap = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $redeemedMap[$row['week_number']] = $row['total_redeemed'];
        }
    }

    // 3. Generate all weeks for that month
    $week = 1;
    $current = strtotime($firstDay);
    while ($current <= strtotime($lastDay)) {
        $weekStart = date("Y-m-d", $current);
        $weekEnd = date("Y-m-d", min(strtotime("+6 days", $current), strtotime($lastDay)));

        $data[] = [
            'week_label' => "Week $week ($weekStart - $weekEnd)",
            'total_redeemed' => $redeemedMap[$week] ?? 0
        ];

        $week++;
        $current = strtotime("+7 days", $current);
    }

    return $data;
}


// Get counts
$totalUsers = getCount($conn, 'users');
$totalVouchers = getCount($conn, 'vouchers');
$totalCategories = getCategoryCount($conn);
$redeemedVouchers = getCount($conn, 'redeemed_vouchers');

// Get selected month filter
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
list($year, $month) = explode('-', $selectedMonth);

// Get data for the graph
$redeemedData = getRedeemedVoucherData($conn, $month, $year);
$chartLabels = json_encode(array_column($redeemedData, 'week_label'));
$chartData = json_encode(array_column($redeemedData, 'total_redeemed'));

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboardAdmin.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
      :root {
          --poppins: 'Poppins', sans-serif;
          --lato: 'Lato', sans-serif;
          --light: #F9F9F9;
          --blue: #006400; /* Dark Green */
          --light-blue: #90ee90; /* Light Green */
          --grey: #eee;
          --dark-grey: #AAAAAA;
          --dark: #1e1e1e; /* A dark color for text */
          --red: #DB504A; 
          --yellow: #FFD700; /* Gold */
          --light-yellow: #FFFACD; /* Lemon Chiffon */
          --orange: #FD7238;
          --light-orange: #FFE0D3;
      }

      body.dark {
          --light: #000000; /* Black for dark mode background */
          --grey: #1a1a1a; /* Dark gray for elements */
          --dark: #FBFBFB; /* White for text */
      }

      .count-card {
          background-color: var(--light);
          border-radius: 8px;
          padding: 20px;
          text-align: center;
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }

      .count-card h3 {
          font-size: 2.5rem;
          color: var(--blue);
          margin-bottom: 5px;
      }

      .count-card p {
          font-size: 1rem;
          color: var(--dark-grey);
          font-weight: 500;
      }

      .card-icon {
          font-size: 3rem;
          color: var(--blue);
          margin-bottom: 10px;
      }

      .chart-container {
          background-color: var(--light);
          padding: 20px;
          border-radius: 8px;
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }
  </style>
</head>
<body>

<section id="sidebar">
  <a href="#" class="brand">
    <img src="images/logo.png" alt="AdminHub Logo" class="logo-img" style="width: 100%; height:auto">
  </a>
  <ul class="side-menu top">
    <li class="active">
      <a href="infoAdmin.php"><i class='bx bxs-dashboard bx-sm'></i><span class="text">Dashboard</span></a>
    </li>
    <li>
      <a href="dashboardAdmin.php"><i class='bx bxs-dashboard bx-sm'></i><span class="text">Manage Voucher</span></a>
    </li>
  </ul>
  <ul class="side-menu bottom">
    <li><a href="logout.php" class="logout"><i class='bx bx-power-off bx-sm bx-burst-hover'></i><span class="text">Logout</span></a></li>
  </ul>
</section>

<section id="content">
  <nav></nav>

  <main class="container-fluid py-4">
    <div class="head-title d-flex justify-content-between align-items-center mb-4">
      <h1>Dashboard</h1>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="count-card">
          <i class="fa-solid fa-users card-icon"></i>
          <h3><?php echo $totalUsers; ?></h3>
          <p>Total Users</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="count-card">
          <i class="fa-solid fa-tags card-icon"></i>
          <h3><?php echo $totalCategories; ?></h3>
          <p>Total Categories</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="count-card">
          <i class="fa-solid fa-ticket-simple card-icon"></i>
          <h3><?php echo $totalVouchers; ?></h3>
          <p>Total Vouchers</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="count-card">
          <i class="fa-solid fa-gift card-icon"></i>
          <h3><?php echo $redeemedVouchers; ?></h3>
          <p>Vouchers Redeemed</p>
        </div>
      </div>
    </div>

    <div class="chart-container">
      <h2>Voucher Redemptions Over Time</h2>
      <!-- Month Filter -->
    <form method="GET" class="mb-3">
      <label for="month">Select Month:</label>
      <input type="month" name="month" id="month" 
             value="<?php echo $selectedMonth; ?>">
      <button type="submit" class="btn btn-sm btn-success">Filter</button>
    </form>
      <canvas id="redemptionChart" height="90"></canvas>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var ctx = document.getElementById('redemptionChart').getContext('2d');
      var redemptionChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: <?php echo $chartLabels; ?>,
          datasets: [{
            label: 'Vouchers Redeemed',
            data: <?php echo $chartData; ?>,
            backgroundColor: 'rgba(0, 100, 0, 0.2)',
            borderColor: 'rgba(0, 100, 0, 1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Number of Redemptions'
              }
            },
            x: {
              title: {
                display: true,
                text: 'Weeks'
              }
            }
          },
          plugins: {
            legend: {
              display: true
            }
          }
        }
      });
    });
  </script>
</section>
</body>
</html>
