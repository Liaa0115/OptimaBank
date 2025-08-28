<?php
session_start();
include 'conn.php';

// Pagination setup
$limit = 10; // Number of vouchers per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total vouchers count for pagination
$total_vouchers_query = $conn->query("SELECT COUNT(*) AS total FROM vouchers");
$total_vouchers = $total_vouchers_query->fetch_assoc()['total'];
$total_pages = ceil($total_vouchers / $limit);

// Fetch vouchers for the current page, ordered by ID descending
$result = $conn->query("SELECT * FROM vouchers ORDER BY id DESC LIMIT $limit OFFSET $offset");

// CREATE
if (isset($_POST['addVoucher'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $price = $_POST['price'];
    $points_required = $_POST['points_required'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];

    $imageName = $_FILES['image']['name'];
    $targetDir = "images/food/";
    $targetFile = $targetDir . basename($imageName);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO vouchers 
            (name, image, category, subcategory, price, points_required, description, quantity) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdisi", $name, $imageName, $category, $subcategory, $price, $points_required, $description, $quantity);
        $stmt->execute();
    }
    header("Location: dashboardAdmin.php");
    exit;
}

// UPDATE
if (isset($_POST['updateVoucher'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $price = $_POST['price'];
    $points_required = $_POST['points_required'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];

    if (!empty($_FILES['image']['name'])) {
        $imageName = $_FILES['image']['name'];
        $targetDir = "images/food/";
        $targetFile = $targetDir . basename($imageName);
        move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);

        $stmt = $conn->prepare("UPDATE vouchers SET name=?, image=?, category=?, subcategory=?, price=?, points_required=?, description=?, quantity=? WHERE id=?");
        $stmt->bind_param("ssssdisii", $name, $imageName, $category, $subcategory, $price, $points_required, $description, $quantity, $id);
    } else {
        $stmt = $conn->prepare("UPDATE vouchers SET name=?, category=?, subcategory=?, price=?, points_required=?, description=?, quantity=? WHERE id=?");
        $stmt->bind_param("sssdisii", $name, $category, $subcategory, $price, $points_required, $description, $quantity, $id);
    }
    $stmt->execute();
    header("Location: dashboardAdmin.php");
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = $conn->query("SELECT image FROM vouchers WHERE id=$id");
    $row = $result->fetch_assoc();
    // Correctly build the path to the image file
    $imagePath = "images/food/" . $row['image'];
    $conn->query("DELETE FROM vouchers WHERE id=$id");
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
    header("Location: dashboardAdmin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vouchers</title>
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

    .page-item.active .page-link {
        background-color: #006400 !important;
        color: white !important;
        border-color: #006400 !important;
    }

</style>
</head>
<body>

<section id="sidebar">
    <a href="#" class="brand">
        <img src="images/logo.png" alt="AdminHub Logo" class="logo-img" style="width: 100%; height:auto">
    </a>    
    <ul class="side-menu top">
        <li>
            <a href="infoAdmin.php"><i class='bx bxs-dashboard bx-sm'></i><span class="text">Dashboard</span></a>
        </li>
        <li class="active">
            <a href="dashboardAdmin.php"><i class='bx bxs-dashboard bx-sm'></i><span class="text">Manage Voucher</span></a>
        </li>
    </ul>
    <ul class="side-menu bottom">
        <!-- <li><a href="#"><i class='bx bxs-cog bx-sm bx-spin-hover'></i><span class="text">Settings</span></a></li> -->
        <li><a href="logout.php" class="logout"><i class='bx bx-power-off bx-sm bx-burst-hover'></i><span class="text">Logout</span></a></li>
    </ul>
</section>

<section id="content">
    <nav>
        <!-- <i class='bx bx-menu bx-sm'></i>
        <a href="#" class="nav-link">Categories</a>
        <form action="#">
            <div class="form-input">
                <input type="search" placeholder="Search...">
                <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
            </div>
        </form> -->
    </nav>

    <main class="container-fluid py-4">
        <div class="head-title d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Vouchers</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal" style="background-color: #006400; color:white">+ Add Voucher</button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>No.</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Category</th>
                                <th>Subcategory</th>
                                <th>Price</th>
                                <th>Points</th>
                                <th>Description</th>
                                <th>Qty</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $row_number = ($page - 1) * $limit + 1;
                            while ($row = $result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= $row_number++ ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><img src="images/food/<?= htmlspecialchars($row['image']) ?>" width="60" class="img-fluid rounded"></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= htmlspecialchars($row['subcategory']) ?></td>
                                <td><?= htmlspecialchars($row['price']) ?></td>
                                <td><?= htmlspecialchars($row['points_required']) ?></td>
                                <td>
                                    <?= strlen($row['description']) > 50 ? substr($row['description'], 0, 50) . '...' : $row['description'] ?>
                                </td>
                                <td><?= htmlspecialchars($row['quantity']) ?></td>
                                <td class="d-flex gap-2">
                                    <button class="btn btn-warning btn-sm" onclick='openEditModal(<?= json_encode($row) ?>)' style="background-color: #006400; color:white">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <a href="?delete=<?= htmlspecialchars($row['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this voucher?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <nav class="mt-4  d-flex justify-content-end">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" style="background-color: #006400; color:white" href="?page=<?= $page - 1 ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" style="background-color: #006400; color:white" href="?page=<?= $page + 1 ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </main>
</section>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Voucher Name" required>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="image" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="category" class="form-control" placeholder="Category" required>
                    </div>
                    <div class="mb-3">
                        <label for="subcategory" class="form-label">Subcategory</label>
                        <select name="subcategory" id="subcategory" class="form-control" required>
                            <option value="" disabled selected>Select a Subcategory</option>
                            <option value="Western Food">Western Food</option>
                            <option value="Malay Food">Malay Food</option>
                            <option value="Chinese Food">Chinese Food</option>
                            <option value="Indian Food">Indian Food</option>
                            </select>
                    </div>
                    <div class="mb-3">
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required>
                    </div>
                    <div class="mb-3">
                        <input type="number" name="points_required" class="form-control" placeholder="Points Required" required>
                    </div>
                    <div class="mb-3">
                        <textarea name="description" class="form-control" placeholder="Description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <input type="number" name="quantity" class="form-control" placeholder="Quantity" required>
                    </div>
                    <button type="submit" name="addVoucher" class="btn btn-primary w-100" style="background-color: #006400; color:white">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="image" class="form-control">
                    </div>
                    <div class="mb-3">
                        <input type="text" name="category" id="editCategory" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="editSubcategory" class="form-label">Subcategory</label>
                        <select name="subcategory" id="editSubcategory" class="form-control" required>
                            <option value="Western Food">Western Food</option>
                            <option value="Malay Food">Malay Food</option>
                            <option value="Chinese Food">Chinese Food</option>
                            <option value="Indian Food">Indian Food</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="number" step="0.01" name="price" id="editPrice" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <input type="number" name="points_required" id="editPoints" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <textarea name="description" id="editDescription" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <input type="number" name="quantity" id="editQuantity" class="form-control" required>
                    </div>
                    <button type="submit" name="updateVoucher" class="btn btn-primary w-100" style="background-color: #006400; color:white">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openEditModal(data) {
        document.getElementById('editId').value = data.id;
        document.getElementById('editName').value = data.name;
        document.getElementById('editCategory').value = data.category;
        document.getElementById('editSubcategory').value = data.subcategory;
        document.getElementById('editPrice').value = data.price;
        document.getElementById('editPoints').value = data.points_required;
        document.getElementById('editDescription').value = data.description;
        document.getElementById('editQuantity').value = data.quantity;
        
        // Use Bootstrap's modal method to show the modal
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

        allSideMenu.forEach(item => {
            const li = item.parentElement;

            item.addEventListener('click', function() {
                // Remove 'active' class from all list items in the top menu
                allSideMenu.forEach(i => {
                    i.parentElement.classList.remove('active');
                });
                // Add 'active' class to the parent <li> of the clicked <a>
                li.classList.add('active');
            });
        });
    });
</script>
</body>
</html>