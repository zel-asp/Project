<?php
session_start();
include 'server/connection.php';

$error = '';
$success = '';

// Registration logic
if (isset($_POST['register'])) {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
    $error = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
  } elseif (strlen($password) < 8) {
    $error = "Password must be at least 8 characters long.";
  } elseif ($password !== $confirm_password) {
    $error = "Passwords do not match.";
  } else {
    $stm1 = $conn->prepare("SELECT user_email FROM users WHERE user_email = ?");
    $stm1->bind_param("s", $email);
    $stm1->execute();
    $stm1->store_result();

    if ($stm1->num_rows > 0) {
      $error = "Email already exists.";
    }

    //this will add the user to the db if the email is not already in the db
    else {
      $stmt = $conn->prepare("INSERT INTO users (user_name, user_email, user_password) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $name, $email, $hashed_password);

      if ($stmt->execute()) {


        // Get the new user's ID
        $user_id = $stmt->insert_id;

        // Set session values
        $_SESSION['user_id'] = $user_id; // Store user ID in session
        $_SESSION['user_email'] = $email; // Store user email in session
        $_SESSION['user_name'] = $name; // Store user name in session
        $_SESSION['user_password'] = $hashed_password; // Store hashed password in session
        $_SESSION['user_email'] = $email; // Store user email in session
        $_SESSION['logged_in'] = true;


        $success = "Registration successful. Welcome, $name!";
        header("Location: index.php"); // Redirect to the homepage after successful registration
      } else {
        $error = "Registration failed. Please try again.";
      }
    }
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
  <title>Register | LianmotoTech</title>
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
        <a
          href="cart.php"
          class="btn btn-outline-dark me-2 position-relative cart-button">
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
              <a
                href="cart.php"
                class="btn btn-outline-dark me-2 position-relative d-none d-lg-block cart-button">
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

  <!-- Register Container -->
  <div class="container register-container" id="js-RegisterForm">
    <!-- Header with Logo -->
    <div class="register-header text-center mt-5">
      <img
        src="assets/images/logo.png"
        alt="Lianmototech Logo"
        class="img-fluid mb-3"
        style="width: 90px; filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3))" />
      <h4
        class="mb-2"
        style="font-weight: 700; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2)">
        Welcome to Lianmototech!
      </h4>
      <p class="mb-0 opacity-75">Your moto journey starts here</p>
    </div>

    <!-- Registration Card -->
    <div class="card register-card">
      <div class="card-header text-center">
        <h4 class="mb-0">
          <i class="bi bi-person-plus me-2"></i>Create Your Account
        </h4>
      </div>

      <div class="card-body p-4 p-md-5">
        <form action="signup.php" method="POST">
          <?php if ($error): ?>
            <p class="text-danger text-center"><?= $error ?></p>
          <?php endif; ?>

          <?php if ($success): ?>
            <p class="text-success text-center"><?= $success ?></p>
          <?php endif; ?>

          <!-- Full Name -->
          <div class="mb-4">
            <label for="fullname" class="form-label fw-medium">Full Name</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-fill text-muted"></i></span>
              <input
                type="text"
                class="form-control"
                id="fullname"
                name="name"
                placeholder="Enter full name"
                required />
            </div>
          </div>

          <!-- Email -->
          <div class="mb-4">
            <label for="email" class="form-label fw-medium">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope-fill text-muted"></i></span>
              <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                placeholder="abc@gmail.com"
                required />
            </div>
          </div>
          <!-- Password -->
          <div class="mb-3">
            <label for="password" class="form-label fw-medium">Password</label>
            <div class="input-group">
              <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                placeholder="Enter password"
                required />
            </div>
            <div class="password-strength mt-2">
              <div class="password-strength-bar" id="passwordStrength"></div>
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="mb-4">
            <label for="confirm_password" class="form-label fw-medium">Confirm Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock-fill text-muted"></i></span>
              <input
                type="password"
                class="form-control"
                id="confirm_password"
                name="confirm_password"
                placeholder="Confirm password"
                required />
            </div>
          </div>

          <!-- Register Button -->
          <button
            type="submit"
            class="btn register-btn w-100 py-3 mb-3 fw-bold text-white" name="register">
            <i class="bi bi-lightning-charge-fill me-2"></i>Get Started
          </button>

          <!-- Login Link -->
          <div class="text-center">
            <p class="text-muted mb-2">Already part of the ride?</p>
            <a
              href="login.php"
              class="btn btn-outline-danger w-100 py-2 fw-medium">
              <i class="bi bi-arrow-right-circle-fill me-2"></i>Log In
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