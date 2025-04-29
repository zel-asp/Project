<?php
session_start();
//the php for insert in db is in placeOrder.php


// Redirect if cart is empty or checkout not triggered
if (empty($_SESSION['cart'])) {
    header("Location: index.php#MotorParts");
    exit();
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['product_price'] * $item['quantity'];
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
    <link rel="stylesheet" href="assets/css/checkout.css" />
    <title>Checkout page</title>

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


    <div class="container checkout-container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 mt-5 pt-4 mb-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-dark">Checkout with <span class="text-primary">GCash</span></h2>
                    <p class="text-muted fs-6">Fast, secure, and convenient payments</p>
                </div>

                <!-- Glass-style checkout card -->
                <div class="card p-4 shadow-lg border-0 rounded-4" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(12px);">
                    <div class="d-flex align-items-center mb-4">
                        <span class="badge bg-success text-white px-3 py-2 rounded-pill shadow-sm">
                            <i class="bi bi-shield-lock-fill me-1"></i> Secure Payment
                        </span>
                    </div>

                    <form action="server/placeOrder.php" method="POST">
                        <input type="hidden" name="total" value="<?php echo $total; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control rounded-3 shadow-sm" name="name" required />
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" class="form-control rounded-3 shadow-sm" name="phone" required />
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control rounded-3 shadow-sm" name="email" />
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" class="form-control rounded-3 shadow-sm" name="address" required />
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" class="form-control rounded-3 shadow-sm" name="city" required />
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea class="form-control rounded-3 shadow-sm" name="note" rows="3" placeholder="Additional instructions (optional)"></textarea>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            <button type="submit" name="place_order" class="btn btn-success px-5 py-2 fw-semibold rounded-pill shadow-sm">
                                <i class="bi bi-cash-coin me-1"></i> Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
        crossorigin="anonymous"></script>
</body>

</html>