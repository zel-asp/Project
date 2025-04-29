<?php

session_start();
include 'server/connection.php';

$error = '';
$success = '';


if (isset($_POST['edit_btn'])) {
  $user_id = $_SESSION['user_id'];

  $update_name = trim($_POST['update_name']);
  $update_email = trim($_POST['update_email']);
  $user_password = $_POST['user_password']; // current password input
  $update_password = $_POST['update_password'];


  // Get current user data from database
  $stmt = $conn->prepare("SELECT user_password FROM users WHERE user_id = ? LIMIT 1");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  // Check if user exists
  if (!$user) {
    $error = "User not found.";
  } elseif (empty($update_name) && empty($update_email) && empty($user_password)) {
    $error = "Nothing Changed. Please fill in all fields.";
  } elseif (!password_verify($user_password, $user['user_password'])) {
    $error = "Incorrect current password.";
  } elseif (!password_verify($user_password, $user['user_password'])) {
    $error = "Incorrect current password.";
  } else {
    // Proceed with email check and update
    if (!filter_var($update_email, FILTER_VALIDATE_EMAIL)) {
      $error = "Invalid email format.";
    } elseif (!empty($update_password) && strlen($update_password) < 8) {
      $error = "New password must be at least 8 characters.";
    } else {

      // Check if the email already exists for another user
      $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_email = ? AND user_id != ?");
      $stmt->bind_param("si", $update_email, $user_id);
      $stmt->execute();
      $stmt->store_result();

      if ($stmt->num_rows > 0) {
        $error = "Email is already in use.";
      } else {
        // Hash new password if provided, or keep old one
        $final_password = !empty($update_password) ? password_hash($update_password, PASSWORD_DEFAULT) : $user['user_password'];


        $stmt = $conn->prepare("UPDATE users SET user_name = ?, user_email = ?, user_password = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $update_name, $update_email, $final_password, $user_id);

        if ($stmt->execute()) {
          $_SESSION['user_name'] = $update_name;
          $_SESSION['user_email'] = $update_email;
          $success = "Information updated successfully.";
        } else {
          $error = "Failed to update information.";
        }
      }
    }
  }
}

$error2 = '';
$success2 = '';


//get orders and connect user id to orders
if (isset($_SESSION['logged_in'])) {

  $user_id = $_SESSION['user_id'];
  $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $orders = $stmt->get_result();
} else {
  $error = "You must be logged in to view your account.";
  header("Location: login.php?error=" . urlencode($error));
  exit();
}

// Handle product removal
// Handle product removal and order deletion
if (isset($_POST['remove_order'])) {
  $order_id = $_POST['order_id'];

  // First, delete all related order_item entries (this step is optional if your DB constraint is already set to CASCADE)
  $stmt2 = $conn->prepare("DELETE FROM order_item WHERE order_id = ?");
  $stmt2->bind_param("i", $order_id);
  $stmt2->execute();

  // Now delete the order itself
  $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ? AND user_id = ?");
  $stmt->bind_param("ii", $order_id, $user_id);
  if ($stmt->execute()) {
    $success = "Order and its products deleted successfully.";
    header("Location:account.php");
  } else {
    $error = "Failed to delete order.";
  }
}





//for booking history

$error3 = '';
$success3 = '';

if (isset($_SESSION['logged_in'])) {

  $user_id = $_SESSION['user_id'];
  $stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $bookings = $stmt->get_result();
} else {
  $error = "You must be logged in to view your account.";
  header("Location: login.php?error=" . urlencode($error));
  exit();
}

// Handle booking removal
if (isset($_POST['remove_booking'])) {
  $bookings = $_POST['booking_id'];

  $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ? AND user_id = ?");
  $stmt->bind_param("ii",  $bookings, $user_id); // $user_id from session
  if ($stmt->execute()) {
    $success = "Booking deleted successfully.";
    header("Location: account.php?success=" . urlencode($success));
    exit(); // Redirect to avoid form resubmission
  } else {
    $error = "Failed to delete booking.";
    header("Location: account.php?error=" . urlencode($error));
    exit(); // Redirect to avoid form resubmission
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

  <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Sofia&effect=neon|outline|emboss|shadow-multiple|fire">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
  <!-- css is found here -->
  <link href="assets/css/login-signup.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/WebMotorShop.css" />
  <title>Account page</title>

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

  <div class="container my-5  pt-5">
    <div class="card-glass">
      <h2 class="text-center mb-4">My Account</h2>

      <ul class="nav nav-tabs mb-3" id="accountTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">Account Info</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">Order History</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">Booking History</button>
        </li>


      </ul>

      <div class="tab-content" id="accountTabsContent">


        <!-- Account Info -->
        <div class="tab-pane fade show active" id="info" role="tabpanel">
          <h5 class="mb-3">Account Information</h5>
          <p><strong>Name: </strong><?php echo $_SESSION['user_name']; ?></p>
          <p><strong>Email:</strong> <?php echo $_SESSION['user_email']; ?></p>

          <hr class="my-4" />

          <h5 class="mb-3">Update Your Information</h5>
          <!--Form to update user information-->
          <form method="post" action="account.php">
            <!-- this will show the message-->
            <?php if ($error) {
              echo '<p class="text-danger text-center">' . $error . '</p>';
            } else if ($success) {
              echo '<p class="text-success text-center">' . $success . '</p>';
            } ?>

            <div class="mb-3">
              <label for="update_name" class="form-label">Name</label>
              <input type="text" class="form-control" id="update_name" name="update_name" placeholder="Enter your name">
            </div>

            <!-- Email -->
            <div class="mb-3">
              <label for="update_email" class="form-label">Email</label>
              <input type="email" class="form-control" id="update_email" name="update_email" placeholder="Enter your email">
            </div>


            <div class="mb-3">
              <label for="user_password" class="form-label">Password</label>
              <input type="password" class="form-control" id="updatePassword" name="user_password" placeholder="Enter your old password" required>
            </div>
            <div class="mb-3">
              <label for="update_password" class="form-label">Password</label>
              <input type="password" class="form-control" id="updatePassword" name="update_password" placeholder="Enter new password">
            </div>

            <input type="submit" class="btn bg-success text-white" name="edit_btn" value="Change" />
            <a href="server/logOut.php" class="btn text-white" style="background-color: #f50000;">Logout</a>
          </form>
        </div>


        <!-- Order History Tab -->
        <div class="tab-pane fade" id="orders" role="tabpanel">
          <!-- this will show the message-->
          <?php if ($error2) {
            echo '<p class="text-danger text-center">' . $error2 . '</p>';
          } else if ($success2) {
            echo '<p class="text-success text-center">' . $success2 . '</p>';
          } ?>
          <h5>Order History</h5>
          <div class="table-responsive mt-3">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Total<!--order_cost in db--></th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>View</th>
                  <th>Action</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <!-- Data from order history -->
                <?php while ($row = $orders->fetch_assoc()) { ?>
                  <tr class="zebra-cards">
                    <td><?php echo $row['order_id']; ?></td>
                    <td><?php echo $row['order_cost']; ?></td>

                    <?php if ($row['order_status'] == 'Pending') { ?>
                      <td><span class="badge bg-warning text-dark"><?php echo $row['order_status']; ?></span></td>
                    <?php } elseif ($row['order_status'] == 'Shipped') { ?>
                      <td><span class="badge bg-info text-white"><?php echo $row['order_status']; ?></span></td>
                    <?php } elseif ($row['order_status'] == 'Completed') { ?>
                      <td><span class="badge bg-success text-white"><?php echo $row['order_status']; ?></span></td>
                    <?php } ?>

                    <?php $allorderformattedDateTime = date("F j, Y \a\\t g:i A", strtotime($row['order_date'])); ?>
                    <td><?php echo  $allorderformattedDateTime; ?></td>
                    <td><a href="viewProducts.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye-fill"></i> View
                      </a>
                    </td>
                    <td>
                      <a href="payment.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-outline-success btn-sm" onclick="return confirm('Are you sure you want to pay for this order?');">
                        <i class="bi bi-credit-card"></i> Pay now
                      </a>
                    </td>

                    <td>
                      <form action="" method="POST" onsubmit="return confirm('Are you sure you want to remove this order?');" style=" display: inline;">
                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        <button type="submit" class="btn bg-danger text-white btn-sm" name="remove_order">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </td>
                  <?php } ?>
              </tbody>
            </table>
          </div>
        </div>



        <!-- Booking Histor-->
        <div class="tab-pane fade" id="bookings" role="tabpanel">
          <!-- this will show the message-->
          <?php if ($error3) {
            echo '<p class="text-danger text-center">' . $error3 . '</p>';
          } else if ($success3) {
            echo '<p class="text-success text-center">' . $success3 . '</p>';
          } ?>
          <h5>Booking History</h5>

          <!-- Desktop Table -->
          <div class="table-responsive mt-3">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Booking ID</th>
                  <th>Service</th>
                  <th>Status</th>
                  <th>Time</th>
                  <th>Date</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $bookings->fetch_assoc()) { ?>
                  <tr>
                    <td><?php echo $row['booking_id']; ?></td>
                    <td><?php echo $row['service_type']; ?></td>
                    <?php
                    if ($row['status'] == 'Pending') {
                      echo '<td><span class="badge bg-warning text-dark">' . $row['status'] . '</span></td>';
                    } elseif ($row['status'] == 'Confirm') {
                      echo '<td><span class="badge bg-success text-white">Confirmed</span></td>';
                    } elseif ($row['status'] == 'Decline') {
                      echo '<td><span class="badge bg-danger text-white">Declined</span></td>';
                    }
                    ?>
                    <?php
                    $formattedTime = date("g:i A", strtotime($row['preferred_time']));
                    ?>
                    <td><?php echo $formattedTime; ?></td>
                    <?php
                    $formattedDate = date("F j, Y", strtotime($row['preferred_date']));
                    ?>
                    <td><?php echo $formattedDate; ?></td>
                    <td>
                      <form action="" method="POST" onsubmit="return confirm('Are you sure you want to remove this booking?');" style=" display: inline;">
                        <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                        <button type="submit" class="btn bg-danger text-white btn-sm" name="remove_booking">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                  </tr>
                <?php  } ?>
              </tbody>
            </table>
          </div>

          <!-- Mobile Cards -->
          <div class="responsive-cards">
            <div class="card">
              <p><strong>Booking ID:</strong> #BK1001</p>
              <p><strong>Service:</strong> repair</p>
              <p><strong>Status:</strong> <span class="badge bg-info text-dark">Scheduled</span></p>
              <p><strong>Date:</strong> April 22, 2025</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/MotoPart.js"></script>
</body>

</html>