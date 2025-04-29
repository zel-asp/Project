<?php
session_start();
include 'server/connection.php';

if (!isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    echo "Invalid request.";
    exit();
}

$order_id = $_GET['order_id'];

// Handle product removal
if (isset($_POST['remove_product']) && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $delete_stmt = $conn->prepare("DELETE FROM order_item WHERE product_id = ? AND order_id = ?");
    $delete_stmt->bind_param("ii", $product_id, $order_id);
    $delete_stmt->execute();

    // Recalculate order cost
    $stmt = $conn->prepare("SELECT SUM(product_price * product_quantity) AS new_total FROM order_item WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $new_total = $result->fetch_assoc()['new_total'];

    $update_order_stmt = $conn->prepare("UPDATE orders SET order_cost = ? WHERE order_id = ?");
    $update_order_stmt->bind_param("di", $new_total, $order_id);
    $update_order_stmt->execute();

    // If no items left, delete the order
    if ($new_total == 0) {
        $delete_order_stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $delete_order_stmt->bind_param("i", $order_id);
        $delete_order_stmt->execute();

        // Redirect back to account page since order is gone
        header("Location: account.php#orders");
        exit();
    }

    // Redirect back to this page to refresh product list
    header("Location: viewProducts.php?order_id=" . $order_id);
    exit();
}


// Fetch order items
$stmt = $conn->prepare("SELECT * FROM order_item WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Discover quality motorcycle parts and services at WebMotorShop. Shop online for the best deals and book your service today!" />
    <meta name="keywords" content="motorcycle parts, moto shop, motorbike accessories, LianmotoTech, online moto store, bike service" />
    <meta name="author" content="LianmotoTech" />
    <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Sofia&effect=neon|outline|emboss|shadow-multiple|fire">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="assets/css/WebMotorShop.css">
    <title>View Order Products</title>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark py-3 fixed-top">
        <div class="container">
            <div class="logoAndName">
                <img src="assets/images/logo.png" alt="Lianmototech Logo" class="Motopart_Logo" />
                <h4 class="Shop_name text-dark">Lian<span class="RedColorName">mototech </span>Motoparts</h4>
            </div>

            <div class="d-flex align-items-center d-lg-none">
                <a href="cart.php" class="btn btn-outline-dark me-2 position-relative cart-button">
                    <i class="bi bi-cart3 fs-5"></i>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
                    <i class="bi bi-list fs-2 text-dark"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="navmenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <div class="d-flex align-items-center">
                            <a href="cart.php" class="btn btn-outline-dark me-2 position-relative d-none d-lg-block cart-button">
                                <i class="bi bi-cart3 fs-5"></i>
                            </a>
                            <a href="index.php#BookNow" class="nav-link NavHovered text-dark p-2 mt-1 mb-1 flex-grow-1">Book now</a>
                        </div>
                    </li>
                    <li class="nav-item"><a href="index.php#MotorParts" class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">Motor Parts</a></li>
                    <li class="nav-item"><a href="index.php#AboutUs" class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">About us</a></li>
                    <li class="nav-item"><a href="index.php#History" class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">History</a></li>
                    <li class="nav-item"><a href="index.php#questions" class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">Questions</a></li>
                    <li class="nav-item"><a href="index.php#Contact" class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">Contact Us</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="Log_SignButton d-flex flex-wrap justify-content--around align-items-start">
                            <li class="nav-item">
                                <a href="account.php"><button class="btn btn-success"><i class="bi bi-person-check-fill"></i> Account</button></a>
                            </li>
                        </div>
                    <?php else: ?>
                        <div class="Log_SignButton d-flex flex-wrap justify-content--around align-items-start">
                            <li class="nav-item">
                                <a href="login.php"><button class="loginButton bg-success">Log in</button></a>
                            </li>
                        </div>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>



    <!-- Main content -->
    <div class="container" style="margin-top: 120px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="account.php#orders" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Orders</a>
        </div>
        <h2 class="text-center mb-4">Order #<?= htmlspecialchars($order_id); ?> - Products</h2>
        <div></div> <!-- Empty div for alignment -->


        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm border-0 h-100">
                            <img src="assets/images/<?= htmlspecialchars($row['product_image']); ?>" class="card-img-top fixedSize_orderImgs" alt="<?= htmlspecialchars($row['product_name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['product_name']); ?></h5>
                                <p class="card-text mb-1"><strong>Quantity:</strong> <?= $row['product_quantity']; ?></p>
                                <p class="card-text mb-1"><strong>Price:</strong> ₱<?= number_format($row['product_price'], 2); ?></p>
                            </div>
                            <div class="card-footer bg-transparent border-0 d-flex justify-content-end">
                                <form method="POST" onsubmit="return confirm('Are you sure you want to remove this product from the order?');">
                                    <!--get the user_id using $get-->
                                    <input type="hidden" name="product_id" value="<?= $row['product_id']; ?>">
                                    <button class="btn btn-outline-danger btn-sm" type="submit" name="remove_product">
                                        <i class="bi bi-trash-fill"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center mt-5">No items found for this order.</div>
        <?php endif; ?>
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>