<?php
session_start();
include('server/connection.php');

$success = "";
$error = "";
// Initialize variables first to avoid undefined variable warnings
$latestOrders = null;
$latestUsers = null;

//dashboard query
$userCountResult = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$ordersCountResult = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
$pendingCountResult = $conn->query("SELECT COUNT(*) AS pending_orders FROM bookings WHERE status = 'Pending'");
$total_reviewsResult = $conn->query("SELECT COUNT(*) AS total_reviews FROM reviews");
$latestreview = $conn->query("SELECT * FROM reviews ORDER BY review_id DESC LIMIT 1");
$latestOrder = $conn->query("SELECT * FROM orders ORDER BY order_id DESC LIMIT 1");
$latestUser = $conn->query("SELECT * FROM users ORDER BY user_id DESC LIMIT 1");

//user count
if ($userCountResult) {
    $userCountRow = $userCountResult->fetch_assoc();
    $totalUsers = $userCountRow['total_users'];
} else {
    $totalUsers = 0;
}

//order count
if ($ordersCountResult) {
    $ordersCountRow = $ordersCountResult->fetch_assoc();
    $totalOrders = $ordersCountRow['total_orders'];
} else {
    $totalOrders = 0;
}

//pending booking count
if ($pendingCountResult) {
    $pendingCountRow = $pendingCountResult->fetch_assoc();
    $pendingOrders = $pendingCountRow['pending_orders'];
} else {
    $pendingOrders = 0;
}

//revies count
if ($total_reviewsResult) {
    $reviewsCountRow = $total_reviewsResult->fetch_assoc();
    $totalReviews = $reviewsCountRow['total_reviews'];
} else {
    $totalUsers = 0;
}


//latest order 
if ($latestOrder && $latestOrder->num_rows > 0) {
    $latestOrders = $latestOrder->fetch_assoc();

    $orderformattedDateTime = date("F j, Y \a\\t g:i A", strtotime($latestOrders['order_date']));
}

//latest review
if ($latestreview && $latestreview->num_rows > 0) {
    $latestreview = $latestreview->fetch_assoc();
    //change the format of time and date
    $reviewformattedDateTime = date("F j, Y \a\\t g:i A", strtotime($latestreview['created_at']));
}


//latest user
if ($latestUser && $latestUser->num_rows > 0) {
    $latestUsers = $latestUser->fetch_assoc();
    $userformattedDateTime = date("F j, Y \a\\t g:i A", strtotime($latestUsers['created_at']));
}

//end of dashboard query


// Check if the user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header("Location: login.php");
    exit();
}


// Users query
$stmt = $conn->prepare("SELECT user_id, user_name, user_email, created_at FROM users");
$stmt->execute();
$result = $stmt->get_result();
if (isset($_POST['delete_user'])) {
    if (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
        $user_id = (int) $_POST['user_id']; // Cast to int for safety

        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);


        if ($stmt->execute()) {
            $success = "User deleted successfully.";
            header("Location: admin.php#product");
            exit();
        } else {
            $error = "Error deleting user: " . htmlspecialchars($stmt->error) . "";
        }

        $stmt->close();
    }
}

// Orders query 
$stmt1 = $conn->prepare("SELECT order_id, user_id, user_name, order_date, order_cost, order_status FROM orders");
$stmt1->execute();
$result1 = $stmt1->get_result();

if (isset($_POST['save_order'])) {
    $order_id = $_POST['order_id'];
    $order_action = $_POST['order_action'];

    // Make sure an action was selected
    if (!empty($order_action)) {
        $update_stmt1 = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $update_stmt1->bind_param("si", $order_action, $order_id);

        if ($update_stmt1->execute()) {
            echo "<script>alert('Order status updated successfully!'); window.location.href='admin.php';</script>";
        } else {
            echo "<script>alert('Failed to update order status.'); window.location.href='admin.php';</script>";
        }
    }
}
if (isset($_POST['delete_order'])) {
    if (isset($_POST['order_id']) && is_numeric($_POST['order_id'])) {
        $order_id = (int) $_POST['order_id'];
    }
    // First, delete all related order_item entries (this step is optional if your DB constraint is already set to CASCADE)
    $stmt0 = $conn->prepare("DELETE FROM order_item WHERE order_id = ?");
    $stmt0->bind_param("i", $order_id);
    $stmt0->execute();

    $statement = $conn->prepare("DELETE FROM orders WHERE order_id=?");
    $statement->bind_param('i', $order_id);

    if ($statement->execute()) {
        $success = "Order deleted successfully.";
        header("Location: admin.php#product");
        exit();
    } else {
        $error = "Error deleting order: " . htmlspecialchars($stmt->error) . "";
    }

    $stmt->close();
}




//bookings query
$stmt2 = $conn->prepare("SELECT booking_id, user_id, full_name, service_type, preferred_time,  preferred_date, status FROM bookings");
$stmt2->execute();
$result2 = $stmt2->get_result();
if (isset($_POST['save_booking'])) {
    $booking_id = $_POST['booking_id'];
    $booking_action = $_POST['booking_action'];

    // Make sure an action was selected
    if (!empty($booking_action)) {
        $update_stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $update_stmt->bind_param("si", $booking_action, $booking_id);

        if ($update_stmt->execute()) {
            echo "<script>alert('Booking updated successfully!'); window.location.href='admin.php';</script>";
        } else {
            echo "<script>alert('Failed to update booking.'); window.location.href='admin.php';</script>";
        }
    }
}
if (isset($_POST['delete_booking'])) {
    if (isset($_POST['booking_id']) && is_numeric($_POST['booking_id'])) {
        $booking_id = (int) $_POST['booking_id'];
    }

    $statement1 = $conn->prepare("DELETE FROM bookings WHERE booking_id=?");
    $statement1->bind_param('i', $booking_id);

    if ($statement1->execute()) {
        $success = "Booking deleted successfully.";
        header("Location: admin.php#product");
        exit();
    } else {
        $error = "Error deleting booking: " . htmlspecialchars($stmt->error) . "";
    }

    $stmt->close();
}


//product query
$stmt3 = $conn->prepare("SELECT product_id, product_image, product_name, product_description, product_price FROM product");
$stmt3->execute();
$result3 = $stmt3->get_result();

if (isset($_POST['delete_product'])) {
    if (isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
        $product_id = (int) $_POST['product_id']; // Cast to int for safety

        $stmt = $conn->prepare("DELETE FROM product WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            $success = "Product deleted successfully.";
            header("Location: admin.php#product");
            exit();
        } else {
            $error = "Error deleting product: " . htmlspecialchars($stmt->error) . "";
        }

        $stmt->close();
    }
}

// Add Product
if (isset($_POST['add_productBtn'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_description = $_POST['product_description'];

    // Handling the file upload securely
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $product_image = $_FILES['product_image']['name'];
        $product_image_tmp = $_FILES['product_image']['tmp_name'];

        // Check if the uploaded file is an image
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($product_image_tmp);

        if (in_array($file_type, $allowed_types)) {
            // Validate file size (max 5MB)
            if ($_FILES['product_image']['size'] <= 5 * 1024 * 1024) {
                $target_dir = "assets/images/";
                $target_file = $target_dir . basename($product_image);

                // Check if the product already exists
                $check_stmt = $conn->prepare("SELECT COUNT(*) FROM product WHERE product_name = ? OR product_image = ?");
                $check_stmt->bind_param("ss", $product_name, $product_image);
                $check_stmt->execute();
                $check_stmt->bind_result($product_count);
                $check_stmt->fetch();
                $check_stmt->close();

                if ($product_count > 0) {
                    $error = "Product with the same name or image already exists.";
                } else {
                    // Move the uploaded file
                    if (move_uploaded_file($product_image_tmp, $target_file)) {
                        // Insert into the database
                        $stmt = $conn->prepare("INSERT INTO product (product_name, product_price, product_description, product_image) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("ssss", $product_name, $product_price, $product_description, $product_image);

                        if ($stmt->execute()) {
                            $success = "Product added successfully.";
                        } else {
                            $error = "Error adding product: " . htmlspecialchars($stmt->error);
                        }
                        $stmt->close();
                    } else {
                        $error = "Error uploading file.";
                    }
                }
            } else {
                $error = "File size exceeds the limit (5MB).";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
    } else {
        $error = "No image selected or an error occurred during upload.";
    }
}




//for reviews
// Load all product reviews
// Check if the product already exists
$totallReviews_stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE product_name = ? ");
$totallReviews_stmt->bind_param("s", $product_name);
$totallReviews_stmt->execute();
$totallReviews_stmt->bind_result($reviews_count);
$totallReviews_stmt->fetch();
$totallReviews_stmt->close();

$stmt_reviews = $conn->prepare("SELECT user_name, product_name, user_rating, user_review, created_at FROM reviews ORDER BY created_at DESC");
$stmt_reviews->execute();
$review_result = $stmt_reviews->get_result();

// Load all products
$stmt_products = $conn->prepare("SELECT * FROM product");
$stmt_products->execute();
$product_result = $stmt_products->get_result();


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
    <link rel="stylesheet" href="assets/css/WebMotorShop.css" />
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/review.css">
    <title>Admin page</title>

</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand d-flex align-items-center justify-content-between">
            <div class="d-flex  align-items-center justify-content-between gap-2">
                <img src="assets/images/logo.png" alt="LianmotoTech logo">
                <h6>Lian<span class="RedColorName mt-1">mototech </span>Motoparts</h6>
            </div>
            <i class="btn  bi bi-x close-btn d-block d-lg-none ml-2" id="closeBtn"></i>
        </div>

        <div class="sidebar-menu">
            <a href="#dashboard" class="menu-item active" data-section="dashboard">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="#users" class="menu-item" data-section="users">
                <i class="bi bi-people"></i>
                <span>Users</span>
            </a>
            <a href="#orders" class="menu-item" data-section="orders">
                <i class="bi bi-cart"></i>
                <span>Orders</span>
            </a>
            <a href="#bookings" class="menu-item" data-section="bookings">
                <i class="bi bi-calendar-check"></i>
                <span>Bookings</span>
            </a>
            <a href="#products" class="menu-item" data-section="products">
                <i class="bi bi-box-seam"></i>
                <span>Products</span>
            </a>
            <div class="gap-2"></div>
            <a href="server/logOut.php" class="logout-btn btn  text-danger d-flex align-items-center gap-2">
                <i class="bi bi-box-arrow-right "></i>
                <span>Log Out</span>
            </a>
        </div>
    </div>

    <!-- menu bar  -->
    <div class="topbar ">
        <div class="container-fluid ">
            <div class="d-flex  justify-content-between align-items-center">
                <button class="btn menu-toggle d-lg-none d-flex align-items-center" id="menuToggle">
                    <i class="bi bi-list " style="font-size: 30px;"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <?php if ($error) {
            echo '<p class="text-danger text-center">' . $error . '</p>';
        } else if ($success) {
            echo '<p class="text-success text-center">' . $success . '</p>';
        } ?>
        <!-- Dashboard Section -->
        <div class="section active" id="dashboard-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2>Admin Dashboard Overview</h2>
                    <p class="text-muted">Welcome back! Here's what's happening with your store today.</p>
                </div>
            </div>

            <div class="row">
                <!-- Quick Stats -->
                <div class="col-md-4 mb-4">
                    <div class="data-table">
                        <div class="table-header">
                            <h5 class="mb-0">Quick Stats</h5>
                        </div>
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span>Total Users</span>
                                <strong><?php echo number_format($totalUsers); ?></strong>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span>Orders</span>
                                <strong><?php echo number_format($totalOrders); ?></strong>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span>Pending Bookings</span>
                                <strong><?php echo number_format($pendingOrders); ?></strong>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span>Reviews</span>
                                <strong><?php echo number_format($totalReviews); ?></strong>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-md-8 mb-4">
                    <div class="data-table">
                        <div class="table-header">
                            <h5 class="mb-0">Recent Activity</h5>
                        </div>
                        <div class="p-3">
                            <!-- Latest Order -->
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-cart-check fs-4 text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <?php if ($latestOrders): ?>
                                        <strong>New order #<?php echo htmlspecialchars($latestOrders['order_id']); ?></strong>
                                        <p class="mb-0">
                                            <?php echo htmlspecialchars($latestOrders['user_name']); ?> placed a new order for ₱ <?php echo htmlspecialchars($latestOrders['order_cost']); ?>
                                        </p>
                                        <small class="text-muted">on <?php echo $orderformattedDateTime; ?></small>
                                    <?php else: ?>
                                        <p class="mb-0 text-muted">No recent orders.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Latest User -->
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-person-plus fs-4 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <?php if ($latestUsers): ?>
                                        <strong>New user registered</strong>
                                        <p class="mb-0"><?php echo htmlspecialchars($latestUsers['user_name']); ?> created an account</p>
                                        <small class="text-muted">on <?php echo $userformattedDateTime; ?></small>
                                    <?php else: ?>
                                        <p class="mb-0 text-muted">No recent user registrations.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-chat-square-text fs-4 text-warning"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>New review received</strong>
                                    <p class="mb-0"><?Php echo $latestreview['user_name']; ?> reviewed "<?Php echo $latestreview['product_name']; ?>"</p>
                                    <small class="text-muted">on <?Php echo $reviewformattedDateTime; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Review Container -->
                <div class="review-container">
                    <div class="review-header">
                        <h2>Product Reviews</h2>
                        <p class="text-muted">What our customers say about our products</p>
                    </div>

                    <!-- Existing Reviews -->
                    <div class="review-card">
                        <?php while ($rows = $review_result->fetch_assoc()) { ?>

                            <div class="review-user">
                                <div>
                                    <div class="user-name"><?php echo $rows['user_name']; ?></div>
                                    <div class="product-name"><?php echo $rows['product_name']; ?></div>
                                </div>
                            </div>
                            <div class="review-stars">
                                <?php
                                // Display star rating based on user rating
                                $rating = (int)$rows['user_rating'];
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rating) {
                                        echo '<i class="bi bi-star-fill"></i>';
                                    } else {
                                        echo '<i class="bi bi-star"></i>';
                                    }
                                }
                                ?>
                            </div>

                            <div class="review-text">
                                <?php echo $rows['user_review']; ?>
                            </div>
                            <div class="review-date">Posted on <?php echo  $reviewformattedDateTime; ?></div>
                        <?php } ?>
                    </div>

                </div>
            </div>

        </div> <!-- end section -->


        <!-- Users Section -->
        <div class="section" id="users-section">
            <div class="table-header">
                <h5 class="mb-0">All Users</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">

                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Created at</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                        ?>
                            <tr>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo $row['user_name']; ?></td>
                                <td><?php echo $row['user_email']; ?></td>
                                <?php $alluserformattedDateTime = date("F j, Y \a\\t g:i A", strtotime($row['created_at'])); ?>
                                <td><?php echo  $alluserformattedDateTime; ?></td>
                                <td>
                                    <form action="admin.php#users" method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit" name="delete_user" onclick="return confirm('Are you sure you want to delete this user?')">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </form>


                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Orders Section -->
        <div class="container section" id="orders-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2>Order Management</h2>
                    <p class="text-muted">View and manage all customer orders.</p>
                </div>
            </div>

            <div class="data-table ">
                <div class="table-header">
                    <h5 class="mb-0 text-center">Recent Orders</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row1 = $result1->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $row1['order_id']; ?></td>
                                    <td><?php echo $row1['user_id']; ?></td>
                                    <td><?php echo $row1['user_name']; ?></td>
                                    <?php $allorderformattedDateTime = date("F j, Y \a\\t g:i A", strtotime($row1['order_date'])); ?>
                                    <td><?php echo  $allorderformattedDateTime; ?></td>
                                    <td><?php echo $row1['order_cost']; ?></td>
                                    <?php
                                    if ($row1['order_status'] == 'Pending') {
                                        echo '<td><span class="status-badge bg-warning text-white">Pending</span></td>';
                                    } elseif ($row1['order_status'] == 'Shipped') {
                                        echo '<td><span class="status-badge bg-info text-white">Shipped</span></td>';
                                    } elseif ($row1['order_status'] == 'Completed') {
                                        echo '<td><span class="status-badge bg-success text-white">Completed</span></td>';
                                    }
                                    ?>
                                    <td>
                                        <form action="admin.php" method="POST">
                                            <div class="d-flex align-items-center gap-2">
                                                <select name="order_action" class="form-select form-select-sm me-2" style="width: auto;">
                                                    <option value="Pending">Pending</option>
                                                    <option value="Shipped">Shipped</option>
                                                    <option value="Completed">Completed</option>
                                                </select>
                                                <button type="submit" name="save_order" class="btn btn-sm btn-outline-success edit-product">
                                                    Save
                                                </button>
                                                <input type="hidden" name="order_id" value="<?php echo $row1['order_id']; ?>">
                                                <button class="btn btn-sm btn-outline-danger" type="submit" name="delete_order" onclick="return confirm('Are you sure you want to delete this order?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bookings Section -->
        <div class="section" id="bookings-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2>Booking Management</h2>
                    <p class="text-muted">Manage all service bookings.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Add Booking
                    </button>
                </div>
            </div>

            <div class="data-table">
                <div class="table-header">
                    <h5 class="mb-0">Bookings</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>User ID</th>
                                <th>Service</th>
                                <th>Customer</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row2 = $result2->fetch_assoc()) {
                            ?>
                                <tr>
                                    <td><?php echo $row2['booking_id']; ?></td>
                                    <td><?php echo $row2['user_id']; ?></td>
                                    <td><?php echo $row2['service_type']; ?></td>
                                    <td><?php echo $row2['full_name']; ?></td>
                                    <?php
                                    // Combine date and time
                                    $fullDateTime = $row2['preferred_date'] . ' ' . $row2['preferred_time'];
                                    $formattedDateTime = date("F j, Y \\a\\t g:i A", strtotime($fullDateTime));
                                    ?>
                                    <td><?php echo $formattedDateTime; ?></td>

                                    <?php
                                    if ($row2['status'] == 'Complete') {
                                        echo '<td><span class="status-badge status-active text-white">Completed</span></td>';
                                    } elseif ($row2['status'] == 'Pending') {
                                        echo '<td><span class="status-badge bg-warning text-white">Pending</span></td>';
                                    } elseif ($row2['status'] == 'Confirm') {
                                        echo '<td><span class="status-badge bg-success text-white">Confirmed</span></td>';
                                    } elseif ($row2['status'] == 'Decline') {
                                        echo '<td><span class="status-badge bg-danger text-white">Declined</span></td>';
                                    }
                                    ?>
                                    <td>
                                        <form action="admin.php" method="POST">
                                            <div class="d-flex align-items-center gap-2">
                                                <select name="booking_action" class="form-select form-select-sm me-2" style="width: auto;">
                                                    <option value="Pending">Pending</option>
                                                    <option value="Confirm">Confirm</option>
                                                    <option value="Decline">Decline</option>
                                                </select>
                                                <button type="submit" name="save_booking" class="btn btn-sm btn-outline-success edit-product">
                                                    Save
                                                </button>
                                                <input type="hidden" name="booking_id" value="<?php echo $row2['booking_id']; ?>">
                                                <button class="btn btn-sm btn-outline-danger" type="submit" name="delete_booking" onclick="return confirm('Are you sure you want to delete this booking?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="section" id="products-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2>Product Management</h2>
                    <p class="text-muted">Add, edit or remove products from your store.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-primary" id="addProductBtn">
                        <i class="bi bi-plus-circle me-2"></i>Add Product
                    </button>
                </div>
            </div>


            <!-- Product List -->
            <div class="data-table" id="productList">
                <div class="table-header">
                    <h5 class="mb-0">All Products</h5>
                    <div class="input-group" style="width: 250px;">
                        <input type="text" class="form-control" placeholder="Search products...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product</th>
                                <th>Product description</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row3 = $result3->fetch_assoc()) {
                            ?>
                                <tr>
                                    <td><?php echo $row3['product_id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="assets/images/<?php echo $row3['product_image']; ?>" class="product-thumb me-2">
                                            <span><?php echo $row3['product_name']; ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo $row3['product_description']; ?></td>
                                    <td>₱<?php echo $row3['product_price']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <form action="admin.php" method="POST">
                                                <input type="hidden" value=" <?php echo $row3['product_id']; ?>" name="product_id">
                                                <button class="btn btn-sm btn-outline-danger delete-product" name="delete_product" type="submit" onclick="return confirm('Are you sure you want to delete this product?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>


                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>

                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Product Form -->
            <div class="form-card" id="Add_product" style="display: none;">
                <h4 class="mb-4" id="formTitle">Add New Product</h4>
                <form id="productFormData" method="POST" action="admin.php" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="product_name" id="productName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productPrice" class="form-label">Price</label>
                            <input type="number" class="form-control" id="productPrice" name="product_price" value="₱ " required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" name="product_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="productImage" class="form-label">Product Image</label>
                        <input type="file" class="form-control" name="product_image" id="productImage" required>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-secondary me-2" id="cancelProductForm">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="add_productBtn">Save Product</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/MotoPart.js"></script>

</body>

</html>