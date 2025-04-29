<?php
include('server/connection.php');
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
  header("Location: account.php");
  exit();
}

if (isset($_POST['Login_btn'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Prepare SQL statement
  $stmt = $conn->prepare("SELECT user_id, user_name, user_email, user_password FROM users WHERE user_email = ? LIMIT 1");
  $stmt->bind_param("s", $email);

  if ($stmt->execute()) {
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
      // Bind results
      $stmt->bind_result($user_id, $user_name, $user_email, $user_password);
      $stmt->fetch();

      // Admin check - hardcoded credentials for admin
      if ($user_email === 'admin1@gmail.com' && $password === 'admin159') {
        // Set admin session
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $user_name;
        $_SESSION['user_email'] = $user_email;

        header("Location: admin.php?success=Welcome back, Admin!");
        exit();
      }

      // For regular users, verify the password
      if (password_verify($password, $user_password)) {
        // Set session for regular users
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $user_name;
        $_SESSION['user_email'] = $user_email;

        header("Location: index.php?success=Login successful");
        exit();
      } else {
        // Invalid password
        header("Location: login.php?error=Invalid email or password.");
        exit();
      }
    } else {
      // No user found
      header("Location: login.php?error=Invalid email or password.");
      exit();
    }
  } else {
    // Query error
    header("Location: login.php?error=Something went wrong.");
    exit();
  }
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
  <title>Login | LianmotoTech</title>
</head>

<body class="background">
  <!--Navigation-->
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
        </ul>
      </div>
    </div>
  </nav>

  <!-- Login Container -->
  <div class="container login-container mt-5" id="js-LogInForm">
    <!-- Header with Logo -->
    <div class="login-header text-center mt-5">
      <img
        src="assets/images/logo.png"
        alt="Lianmototech Logo"
        class="img-fluid mb-3"
        style="width: 90px; filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3))" />
      <h4
        class="mb-2"
        style="font-weight: 700; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2)">
        Welcome Back!
      </h4>
      <p class="mb-0 opacity-75">Sign in to continue your moto journey</p>
    </div>

    <!-- Login Card -->
    <div class="card login-card">
      <div class="card-body p-4 p-md-5">
        <p class="container text-danger text-center"><?php if (isset($_GET['error'])) {
                                                        echo $_GET['error'];
                                                      } ?></p>
        <form action="login.php" method="POST">
          <!-- Email Input -->
          <div class="mb-4">
            <label for="email" class="form-label fw-medium">Email Address</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent"><i class="bi bi-envelope-fill text-muted"></i></span>
              <input
                type="email"
                class="form-control py-2"
                id="email"
                name="email"
                placeholder="Enter your email"
                required />
            </div>
          </div>

          <!-- Password Input -->
          <div class="mb-4">
            <label for="password" class="form-label fw-medium">Password</label>
            <div class="input-group mb-2">
              <span class="input-group-text bg-transparent"><i class="bi bi-lock-fill text-muted"></i></span>
              <input
                type="password"
                class="form-control py-2"
                id="password"
                name="password"
                placeholder="Enter your password"
                required />
            </div>
            <div class="d-flex justify-content-end">
              <a
                href="forgot_password.php"
                class="text-decoration-none small text-muted">Forgot password?</a>
            </div>
          </div>

          <!-- Login Button -->
          <button
            type="submit"
            class="btn login-btn w-100 mb-3 fw-bold text-white" name="Login_btn">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
          </button>
          <!-- Sign Up Link -->
          <div class="text-center">
            <p class="text-muted mb-2">Don't have an account?</p>
            <a
              href="signup.php"
              class="btn btn-outline-danger w-100 fw-medium"
              id="js-logInToYourAcc">
              <i class="bi bi-person-plus-fill me-2"></i>Create Account
            </a>
          </div>
        </form>
      </div>

      <!-- Footer Links -->
      <div class="card-footer bg-transparent border-0 text-center py-3">
        <p class="footer-links text-muted mb-0 small">
          By registering, you agree to our
          <a href="terms.html" class="fw-medium">Terms</a> and
          <a href="privacy.html" class="fw-medium">Privacy Policy</a>
        </p>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>