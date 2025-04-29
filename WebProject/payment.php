<?php
session_start();
include 'server/connection.php'; // You forgot this before

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // if not logged in
    exit();
}

// Initialize
$orderProducts = [];
$totalPrice = 0;

// If user is paying for an existing order
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Fetch the products from order_item table
    $stmt = $conn->prepare("SELECT * FROM order_item WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $orderProducts[] = $row;
        $totalPrice += $row['product_price'] * $row['product_quantity'];
    }

    $stmt->close();
} else if (!empty($_SESSION['cart'])) {
    // fallback for cart checkout (optional, if you want users to checkout cart directly)
    foreach ($_SESSION['cart'] as $item) {
        $totalPrice += $item['product_price'] * $item['quantity'];
    }
} else {
    header("Location: cart.php"); // Nothing to pay
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Discover quality motorcycle parts and services at WebMotorShop. Shop online for the best deals and book your service today!" />
    <meta name="keywords" content="motorcycle parts, moto shop, motorbike accessories, LianmotoTech, online moto store, bike service" />
    <meta name="author" content="LianmotoTech" />
    <!-- Favicon -->
    <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />

    <link href="https://fonts.googleapis.com/css2?family=League+Spartan&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Sofia&effect=neon|outline|emboss|shadow-multiple|fire">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
    <link href="assets/css/login-signup.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/WebMotorShop.css" />
    <title>Payment page</title>

</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark py-3 fixed-top">
        <div class="container">
            <div class="logoAndName">
                <img
                    src="assets/images/logo.png"
                    alt="Lianmototech Logo"
                    class="Motopart_Logo" />
                <h4 class="Shop_name text-dark">
                    Lian<span class="RedColorName">mototech </span>Motoparts
                </h4>
            </div>

            <div class="d-flex align-items-center d-lg-none">
                <a href="cart.php" class="btn btn-outline-dark me-2 position-relative cart-button">
                    <i class="bi bi-cart3 fs-5"></i>
                </a>
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navmenu">
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
                            <a
                                href="index.php#BookNow"
                                class="nav-link NavHovered text-dark p-2 mt-1 mb-1 flex-grow-1">Book now</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a
                            href="index.php#MotorParts"
                            class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">Motor Parts</a>
                    </li>
                    <li class="nav-item">
                        <a
                            href="index.php#AboutUs"
                            class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">About us</a>
                    </li>
                    <li class="nav-item">
                        <a
                            href="index.php#History"
                            class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">History</a>
                    </li>
                    <li class="nav-item">
                        <a
                            href="index.php#questions"
                            class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">Questions</a>
                    </li>
                    <li class="nav-item">
                        <a
                            href="index.php#Contact"
                            class="nav-link NavHovered text-dark p-2 mt-1 mb-1 d-block">Contact Us</a>
                    </li>
                    <?php
                    // Check if user is logged in
                    if (isset($_SESSION['user_id'])) {
                        // User is logged in, show logout button
                        echo '<div class="Log_SignButton d-flex flex-wrap justify-content--around align-items-start">
                    <li class="nav-item">
                      <a href="account.php">
                        <button class="btn btn-success">
                      <i class="bi bi-person-check-fill"></i>Account
                        </button>
                      </a>
                    </li>
                  </div>';
                    } else {
                        // User is not logged in, show login and signup buttons
                        echo '<div class="Log_SignButton d-flex flex-wrap justify-content--around align-items-start">
                        <li class="nav-item">
                            <a href="login.php"><button class="loginButton bg-success">Log in</button></a>
                        </li>
                    </div>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Order Summary -->
    <div class="col-lg-5 align-self-start mt-5 pt-5 mb-5 mx-auto">
        <div class="card checkout-card p-4 sticky-summary">


            <h1 class="fw-bold mb-3">
                <i class="bi bi-receipt me-2"></i>Order Summary
            </h1>

            <?php if (!empty($orderProducts)): ?>
                <?php foreach ($orderProducts as $item): ?>
                    <div class="order-item d-flex justify-content-between">
                        <span>Name: <?php echo htmlspecialchars($item['product_name']); ?></span>
                        <span>Price: ₱<?php echo number_format($item['product_price'] * $item['product_quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php elseif (!empty($_SESSION['cart'])): ?>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class=" order-item d-flex justify-content-between">
                        <span>Name: <?php echo htmlspecialchars($item['product_name']); ?></span>
                        <span>Price: ₱<?php echo number_format($item['product_price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="d-flex justify-content-between fw-bold border-top pt-3 mt-3">
                <span>Total</span>
                <strong>₱<?php echo number_format($totalPrice, 2); ?></strong>
            </div>

            <input class="btn gcash-btn bg-success text-white w-100 mt-4" type="button" value="Pay with GCash" />
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
        crossorigin="anonymous"></script>
</body>

</html>