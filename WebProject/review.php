<?php
include('server/connection.php');
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Check if the form is submitted
if (isset($_POST['submit_review'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = htmlspecialchars($_POST['user_name']);
    $product_name = htmlspecialchars($_POST['product_name']);
    $rating = htmlspecialchars($_POST['rating']);
    $user_review = htmlspecialchars($_POST['user_review']);

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, user_name, product_name, user_rating, user_review) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $user_name, $product_name, $rating, $user_review);
    if ($stmt->execute()) {
        echo "<script>alert('Review submitted successfully!');</script>";
    } else {
        echo "<script>alert('Error submitting review. Please try again.');</script>";
    }
    $stmt->close();
}

// ALWAYS load all reviews here
$stmt_reviews = $conn->prepare("SELECT user_name, product_name, user_rating, user_review, created_at FROM reviews ORDER BY created_at DESC");
$stmt_reviews->execute();
$result = $stmt_reviews->get_result();


$stmt1 = $conn->prepare("SELECT * FROM product");
$stmt1->execute();
$result1 = $stmt1->get_result();
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
    <link rel="stylesheet" href="assets/css/review.css" />
    <link rel="stylesheet" href="assets/css/WebMotorShop.css" />
    <title>Login | LianmotoTech</title>
</head>

<body>
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


    <!-- Review Container -->
    <div class="review-container">
        <div class="review-header">
            <h2>Product Reviews</h2>
            <p class="text-muted">What our customers say about our products</p>
        </div>

        <!-- Reviews -->
        <div class="review-card">
            <?php while ($row1 = $result->fetch_assoc()) { ?>
                <div class="review-user">
                    <div>
                        <div class="user-name"><?php echo $row1['user_name']; ?></div>
                        <div class="product-name"><?php echo $row1['product_name']; ?></div>
                    </div>
                </div>
                <div class="review-stars">
                    <?php
                    // Display star rating based on user rating
                    $rating = (int)$row1['user_rating'];
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
                    <?php echo $row1['user_review']; ?>
                </div>
                <?php $reviewformattedDateTime = date("F j, Y \a\\t g:i A", strtotime($row1['created_at'])); ?>
                <div class="review-date">Posted on <?php echo $reviewformattedDateTime; ?></div>
            <?php } ?>
        </div>

        <!-- Review Form -->
        <div class="review-form">
            <h4 class="form-title">Write a Review</h4>
            <form id="reviewForm" method="POST" action="review.php">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

                <div class="mb-3">
                    <label for="userName" class="form-label">Your Name</label>
                    <input type="text" class="form-control" id="userName" name="user_name" required>
                </div>
                <div class="mb-3">
                    <label for="productSelect" class="form-label">Product</label>
                    <select class="form-select" id="productSelect" name="product_name">
                        <option value="">Select a product</option>
                        <?php while ($row = $result1->fetch_assoc()) { ?>
                            <option><?php echo $row['product_name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Your Rating</label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5">
                        <label for="star5"><i class="bi bi-star-fill"></i></label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4"><i class="bi bi-star-fill"></i></label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3"><i class="bi bi-star-fill"></i></label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2"><i class="bi bi-star-fill"></i></label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1"><i class="bi bi-star-fill"></i></label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="reviewText" class="form-label">Your Review</label>
                    <textarea class="form-control" id="reviewText" rows="4" name="user_review"></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-submit" name="submit_review">Submit Review</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>