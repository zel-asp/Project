<?php
include('server/connection.php');
session_start();

// Set dynamic cart message
$cart_message = empty($_SESSION['cart'])
  ? "Your cart is waiting to be filled with the best moto parts around!"
  : "You're just a few clicks away from powering up your ride!";

// Initialize cart array if not set
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = array();
}

// Handle actions
if (isset($_POST['add_to_cart'])) {
  $product_id = $_POST['product_id'];

  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != true) {
    header("location: login.php?error=Please log in to add items to your cart.");
    exit();
  } else if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity'] += 1;
  } else {
    $_SESSION['cart'][$product_id] = array(
      'product_id' => $product_id,
      'product_image' => $_POST['product_image'],
      'product_name' => $_POST['product_name'],
      'product_price' => $_POST['product_price'],
      'product_description' => $_POST['product_description'],
      'quantity' => $_POST['product_quantity'],
    );
  }
} elseif (isset($_POST['remove_product'])) {
  $product_id = $_POST['product_id'];
  unset($_SESSION['cart'][$product_id]);
} elseif (isset($_POST['edit_quantity'])) {
  $product_id = $_POST['product_id'];
  $product_quantity = intval($_POST['product_quantity']);
  if ($product_quantity > 0) {
    $_SESSION['cart'][$product_id]['quantity'] = $product_quantity;
  } else {
    unset($_SESSION['cart'][$product_id]);
  }
}

// Shipping fee query
$stmt = $conn->prepare("SELECT fee FROM shipping_fee LIMIT 1"); // Optional: LIMIT 1 if there's only one row
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();
$fee = $row['fee'];

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
  <link rel="stylesheet" href="assets/css/WebMotorShop.css" />
  <title> Cart page</title>

</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark py-3 fixed-top bg-dark ">
    <div class="container">
      <div class="logoAndName">
        <img src="assets/images/logo.png" alt="Lianmototech Logo" class="Motopart_Logo" />
        <h4 class="Shop_name text-white">Lian<span class="RedColorName">mototech</span> Motoparts</h4>
      </div>
      <a href="index.php" class="btn btn-outline-light">
        <i class="bi bi-chevron-left"></i> Back to Shop
      </a>
    </div>
  </nav>

  <!-- Cart header-->
  <div class="container mt-5 py-5 mb-5 ">
    <div class="cart-header bg-dark p-3 d-flex flex-wrap justify-content-between align-items-center">
      <h2 class="mb-0 text-white"><i class="bi bi-cart3 "></i> Your Shopping Cart</h2>
      <span class="badge bg-white text-dark fs-6" id="cart-count">
        <?php echo count($_SESSION['cart']); ?> items
      </span>
    </div>
    <!-- Total Price Section -->
    <?php
    // Initialize total price
    $total = 0;

    // Loop through the cart to calculate the total price
    foreach ($_SESSION['cart'] as $key => $value) {
      $total += $value['product_price'] * $value['quantity'];
    }
    ?>
    <!--  Message -->
    <div class="alert alert-info text-center mt-4" role="alert">
      <?php echo $cart_message; ?>
    </div>


    <!-- Cart Section -->
    <section>
      <div class="p-5 bg-light rounded-4 shadow-sm mb-4">
        <div class="d-flex justify-content-between align-items-center flex-column flex-md-row gap-3">
          <h3 class="text-dark mb-0">
            <i class="bi bi-cart4 me-2"></i>Search Added Product:
          </h3>
          <div class="MotorParsImgs w-100 w-md-auto">
            <div class="input-group search-parts">
              <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
              <input type="text" class="form-control Search" placeholder="Search motor parts" />
              <button class="btn btn-dark btn-lg" type="button">Search</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Section -->
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4" id="MotorParts">
        <?php if (!empty($_SESSION['cart'])): ?>
          <?php foreach ($_SESSION['cart'] as $key => $value):
            $productId = htmlspecialchars($value['product_id']);
            $productName = htmlspecialchars($value['product_name']);
            $productDesc = htmlspecialchars($value['product_description']);
            $productImage = htmlspecialchars($value['product_image']);
            $productPrice = $value['product_price'];
            $quantity = $value['quantity'];
            $totalPrice = $productPrice * $quantity;
          ?>
            <div class="col">
              <div class="card bg-white shadow-sm h-100 rounded-4 border-0 d-flex flex-column">
                <!-- Image -->
                <div class="image-container rounded-top-4 overflow-hidden" style="height:200px;">
                  <img src="assets/images/<?php echo $productImage; ?>" class="img-fluid w-100 h-100 object-fit-cover" alt="Product Image">
                </div>

                <!-- Card Body -->
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title text-truncate fw-semibold">Name: <?php echo $productName; ?></h5>
                  <p class="card-text flex-grow-1 small text-muted overflow-hidden">
                    Description: <?php echo $productDesc; ?>
                  </p>
                </div>

                <!-- Price and Actions -->
                <div class="p-3 border-top bg-light rounded-bottom-4">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                      <span class="small text-muted">Price:</span>
                      <span class="fw-bold text-success">₱<?php echo $productPrice; ?></span>
                    </div>
                    <span class="fw-semibold text-dark">Total: ₱<?php echo $totalPrice; ?></span>
                  </div>

                  <!-- Update Quantity Form -->
                  <form action="#" method="POST" class="mb-2">
                    <div class="d-flex align-items-center gap-2">
                      <label for="product_quantity" class="text-dark small mb-0">Qty:</label>
                      <input type="number" id="product_quantity" name="product_quantity" value="<?php echo $quantity; ?>" min="1" class="form-control" style="width: 70px;">
                      <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                      <button class="btn btn-primary w-100 py-2 fw-semibold" type="submit" name="edit_quantity">
                        Update
                      </button>
                    </div>
                  </form>

                  <!-- Remove Product Form -->
                  <form action="#" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                    <button class="btn btn-danger w-100 py-2 fw-semibold" name="remove_product">
                      <i class="bi bi-trash-fill me-1"></i> Delete Product
                    </button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>


    <!-- Total Price Section -->
    <?php
    // Initialize total price
    $total = 0;

    // Loop through the cart to calculate the total price
    foreach ($_SESSION['cart'] as $key => $value) {
      $total += $value['product_price'] * $value['quantity'];
    }
    ?>
    <div class="container my-4">
      <div class="card shadow-lg border-0 rounded-4" style="background: #f8f9fa;">
        <div class="card-body p-4">

          <!-- Summary Title -->
          <h4 class="mb-4 fw-bold text-center text-lg-start text-primary">
            <i class="bi bi-receipt-cutoff"></i> Order Summary
          </h4>

          <!-- Price Breakdown -->
          <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-3 px-2">
            <div class="d-flex flex-column text-center text-sm-start">
              <span class="mb-1 text-muted"><i class="bi bi-truck"></i> Shipping Fee</span>
              <span class="fw-semibold text-dark">₱<?php echo number_format($fee, 2); ?></span>
            </div>
            <div class="d-flex flex-column text-center text-sm-start mt-3 mt-sm-0">
              <span class="mb-1 text-muted"><i class="bi bi-wallet"></i> Subtotal</span>
              <span class="fw-semibold text-dark">₱<?php echo number_format($total, 2); ?></span>
            </div>
          </div>

          <hr class="my-3">

          <!-- Total -->
          <div class="d-flex justify-content-between align-items-center px-2">
            <span class="fs-5 fw-bold text-success">
              <i class="bi bi-cash-stack"></i> Total
            </span>
            <span class="fs-5 fw-bold text-success">
              ₱<?php echo number_format($total + $fee, 2); ?>
            </span>
          </div>

          <!-- Buttons -->
          <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center mt-4 px-2 gap-2">
            <form action="checkout.php" method="POST">
              <input type="submit" class="btn btn-success px-4 py-2 fw-semibold shadow-sm" value="🛒 Check Out" name="checkout">
            </form>
            <a href="review.php" class="btn btn-info px-4 py-2 fw-semibold shadow-sm text-white">
              <i class="bi bi-chat-left-text"></i> See Reviews
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/MotoPart.js"></script>
</body>

</html>