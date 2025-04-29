<?php
include('server/connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {

  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php?error=Please log in to book a service.");
    exit();
  }

  $error = "";

  $fullname = trim($_POST['fullname']);
  $number = trim($_POST['number']);
  $email = trim($_POST['email']);
  $date = $_POST['date'];
  $time = $_POST['time'];
  $service = $_POST['service'];

  $_SESSION['booking'] = [
    'fullname' => $fullname,
    'number' => $number,
    'email' => $email,
    'date' => $date,
    'time' => $time,
    'service' => $service
  ];

  $bookingDateTimeStr = $date . ' ' . $time;
  $bookingDateTime = DateTime::createFromFormat('Y-m-d H:i', $bookingDateTimeStr);
  $now = new DateTime();

  if ($bookingDateTime < $now) {
    $error = "Booking date and time must be in the future.";
  } else {
    $bookingHour = (int)$bookingDateTime->format('H');

    $openingHour = 8;
    $closingHour = 22;

    if ($bookingHour < $openingHour || $bookingHour >= $closingHour) {
      $error = "Booking time must be between 8:00 AM and 10:00 PM.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = "Invalid email format.";
    } elseif (strlen($number) !== 11 || !preg_match('/^[0-9]+$/', $number)) {
      $error = "Invalid phone number format. Phone number must be 11 digits.";
    } else {
      $user_id = $_SESSION['user_id'];

      // Format the time to 12-hour AM/PM
      $formatted_time = $bookingDateTime->format('g:i A'); // example: "1:30 PM"

      // Check if booking already exists
      $check_stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? AND full_name = ? AND phone_number = ? AND email = ? AND preferred_date = ? AND preferred_time = ? AND service_type = ?");
      $check_stmt->bind_param("issssss", $user_id, $fullname, $number, $email, $date, $formatted_time, $service);
      $check_stmt->execute();
      $result = $check_stmt->get_result();

      if ($result->num_rows > 0) {
        echo "<script>alert('Too many attempts. You already booked this service.');</script>";
      } else {
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, full_name, phone_number, email, preferred_date, preferred_time, service_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $fullname, $number, $email, $date, $formatted_time, $service);

        if ($stmt->execute()) {
          echo "<script>alert('Booking successful!');</script>";
        } else {
          echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }

        $stmt->close();
      }

      $check_stmt->close();
    }
  }
}











// Initialize the cart session variable if it doesn't exist
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Handle add to cart logic
if (isset($_POST['add_to_cart'])) {
  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php?error=Please log in to add items to your cart.");
    exit();
  }
  $product_id = $_POST['product_id'];

  // Check if the product is already in the cart
  if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity'] += 1;
  } else {
    $_SESSION['cart'][$product_id] = array(
      'product_id' => $product_id,
      'product_image' => $_POST['product_image'],
      'product_name' => $_POST['product_name'],
      'product_price' => $_POST['product_price'],
      'product_description' => $_POST['product_description'],
      'quantity' => 1
    );
  }

  // Redirect to prevent form resubmission
  header("Location: index.php#MotorParts");
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
  <link rel="icon" href="assets/images/logo.png" type="image/x-icon" />

  <link href="https://fonts.googleapis.com/css2?family=League+Spartan&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Sofia&effect=neon|outline|emboss|shadow-multiple|fire">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
  <link rel="stylesheet" href="assets/css/WebMotorShop.css">
  <title>WebProject Main page</title>

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

  <!-- Contenta fter header -->
  <section class="background text-light p-5 p-lg-0 pt-lg-5 text-center text-sm-start" id="BookNow">
    <div class="container">
      <div class="d-lg-flex align-items-center justify-content-center">
        <div class="FastParagrapButtonContainer d-flex flex-column justify-content-center align-items-center ">
          <h1 class="FastEasyParagraph fw-bolder text-center   font-effect-emboss">FAST, EASY, AND RELIABLE BOOKING FOR <span class="MotoNeeds-Color">YOUR MOTO NEEDS!</span></h1>
          <p class="lead my-4 text-center">
            Let’s get your moto road-ready today!
          </p>
          <?php if (!empty($error)): ?>
            <div class="text-center text-white p-2 bg-danger rounded-3 mb-3"><i class="bi bi-exclamation-triangle-fill"></i>
              <?php echo htmlspecialchars($error); ?> <i class=" btn btn-ountline-primary bi bi-x" id="js-ex_button"></i>
            </div>
          <?php endif; ?>
          <button class="btn btn-lg mb-lg-3 BookBtn" data-bs-toggle="modal" data-bs-target="#Book">Book now</button>
        </div>
      </div>
    </div>
  </section>

  <!-- Product Section -->
  <section>
    <div class="text-light p-5">
      <div class="d-flex justify-content-between align-items-center flex-column">
        <h3 class="mb-3 mb-md-0 text-dark">Search Motor parts you need:</h3>
        <div class="MotorParsImgs">
          <div class="input-group search-parts d-flex">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control ml-5 Search" placeholder="Search motor parts" />
            <button class="btn btn-dark btn-lg" type="button">Search</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Products Section -->
    <div id="MotorParts" class="p-2">
      <div class="container mb-1">
        <h2 class="text-center text-dark fw-bold mb-3">Our Motor Parts</h2>
        <p class="lead text-center text-muted mb-5">
          Explore our high-quality motor parts and accessories. Whether you're looking for performance upgrades or essential replacements.
        </p>

        <!-- this will get all the product -->
        <?php include('server/get_product_items.php'); ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
          <?php while ($row = $product->fetch_assoc()) { ?>
            <div class="col">
              <div class="card bg-white shadow-sm h-100 d-flex flex-column rounded-4 border-0">

                <!-- Image -->
                <div class="image-container rounded-top-4 overflow-hidden" style="height: 200px;">
                  <img src="assets/images/<?php echo $row['product_image']; ?>"
                    class="img-fluid w-100 h-100 object-fit-cover p-2"
                    alt="product image">
                </div>

                <!-- Card Body -->
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title text-truncate fw-semibold">Name: <?php echo $row['product_name']; ?></h5>
                  <p class="card-text flex-grow-1 small text-muted overflow-hidden">
                    Description: <?php echo $row['product_description']; ?>
                  </p>
                </div>

                <!-- Price and Action -->
                <div class="mt-auto p-3 border-top bg-light rounded-bottom-4">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">Price:</span>
                    <span class="fw-bold text-success">₱<?php echo $row['product_price']; ?></span>
                  </div>

                  <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <form method="POST" action="index.php">
                      <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>" />
                      <input type="hidden" name="product_image" value="<?php echo $row['product_image']; ?>" />
                      <input type="hidden" name="product_name" value="<?php echo $row['product_name']; ?>" />
                      <input type="hidden" name="product_description" value="<?php echo $row['product_description']; ?>" />
                      <input type="hidden" name="product_price" value="<?php echo $row['product_price']; ?>" />
                      <input type="hidden" name="product_quantity" value="1" />

                      <button class="btn btn-primary w-100 mt-2 py-2 fw-semibold shadow-sm" type="submit" name="add_to_cart">
                        <i class="bi bi-cart-plus"></i> Add to Cart
                      </button>
                    </form>
                  <?php else: ?>
                    <a href="login.php" class="btn btn-secondary w-100 mt-2 py-2 fw-semibold shadow-sm">
                      <i class="bi bi-box-arrow-in-right"></i> Login to Add
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>



    <!-- About Sections -->
    <section class="p-5" id="AboutUs">
      <div class="container">
        <div class="row align-items-center justify-content-between">
          <div class="col-md AboutUs-Picture">
            <img src="https://assets.onecompiler.app/43eamseq2/43eh4vpss/1000019861.jpg" class="img-fluid" alt="MotorParts Shop Picture" />
          </div>
          <div class="col-md p-5">
            <h2>
              <center>About Us</center>
            </h2>
            <p>
              We specialize in providing a wide range of motor parts and accessories for various vehicle types, ensuring optimal performance, safety, and durability

              We are committed to providing high quality motor parts that meet or exceed industry standards.
              We prioritize customer satisfaction, ensuring that our products and services meet their needs and expectation

              We ensure timely and secure delivery of our products
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- History Section -->
    <section id="History" class="p-5 text-light" style="background-color: #ff1924;">
      <div class="container">
        <div class="row align-items-center justify-content-between">
          <div class="col-md p-5">
            <h2>
              <center>History & background:</center>
            </h2>
            <p class="lead">
              Elisa Valerio (Owner)
              Ryan Ilarde (Co-Owner)
            </p>
            <p>
              Elisa Valerio is the owner of the motorparts and accessories shop. We only use name Ryan Ilarde with our shop name because Ryan Ilarde is known in the motorcycle parts and accessories industry.
            </p>
          </div>
          <div class="col-md coOwnerProfile">
            <img src="https://assets.onecompiler.app/43eamseq2/43eh3zvma/1000020394.jpg" class="img-fluid rounded-3 border-white" alt="Co-owner picture" />
          </div>
        </div>
      </div>
    </section>


    <!-- Question Accordion -->
    <section id="questions" class="p-5">
      <div class="container">
        <h2 class="text-center mb-4">Frequently Asked Questions</h2>
        <div class="accordion accordion-flush" id="questions">
          <!-- Item 1 -->
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button
                class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#question-one">
                Where exactly are you located?
              </button>
            </h2>
            <div
              id="question-one"
              class="accordion-collapse collapse"
              data-bs-parent="#questions">
              <div class="accordion-body">
                We are conveniently located at<i> 334 Hon. B. Soliven Avenue, Commonwealth, Quezon City.</i>
              </div>
            </div>
          </div>
          <!-- Item 2 -->
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button
                class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#question-two">
                Why choose us?
              </button>
            </h2>
            <div
              id="question-two"
              class="accordion-collapse collapse"
              data-bs-parent="#questions">
              <div class="accordion-body">
                We offer a wide selection of high-quality motorcycle parts and allow you to reserve them online. Plus, you can book service appointments hassle-free. Pay onsite or online and ride away with confidence!
              </div>
            </div>
          </div>
          <!-- Item 3 -->
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button
                class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#question-three">
                What do I need to Know?
              </button>
            </h2>
            <div
              id="question-three"
              class="accordion-collapse collapse"
              data-bs-parent="#questions">
              <div class="accordion-body">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Numquam
                beatae fuga animi distinctio perspiciatis adipisci velit maiores
                totam tempora accusamus modi explicabo accusantium consequatur,
                praesentium rem quisquam molestias at quos vero. Officiis ad
                velit doloremque at. Dignissimos praesentium necessitatibus
                natus corrupti cum consequatur aliquam! Minima molestias iure
                quam distinctio velit.
              </div>
            </div>
          </div>
          <!-- Item 4 -->
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button
                class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#question-four">
                How do I sign up and log in
              </button>
            </h2>
            <div
              id="question-four"
              class="accordion-collapse collapse"
              data-bs-parent="#questions">
              <div class="accordion-body">
                To sign up, click the “Sign Up” button on the homepage, fill in your details, and submit the form. If you already have an account, just click “Log In” and enter your username and password.
              </div>
            </div>
          </div>
          <!-- Item 5 -->
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button
                class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#question-five">
                How do it works?
              </button>
            </h2>
            <div
              id="question-five"
              class="accordion-collapse collapse"
              data-bs-parent="#questions">
              <div class="accordion-body">
                1. Choose your parts. 2. Reserve a date. 3. Show up, pay, and get your ride upgraded!
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact -->
    <section class="p-5" id="Contact">
      <div class="container">
        <div class="row g-4 justify-content-center">
          <div class="col-md w-100 d-flex flex-column align-items-center">
            <h2 class="text-center mb-4">Contact Info</h2>
            <ul class="list-group list-group-flush lead w-100" style="max-width: 600px;">
              <li class="list-group-item">
                <i class="bi bi-geo-alt-fill"></i>
                <span class="fw-bold"> Main Location: </span>
                <span class="fs-6">334 Hon B Soliven Commonwealth Quezon, City</span>
              </li>
              <li class="list-group-item">
                <i class="bi bi-telephone-fill"></i>
                <span class="fw-bold"> Call us: </span>
                <span class="fs-6">09452107571 Or 09471129389</span>
              </li>
              <li class="list-group-item text-break">
                <i class="bi bi-envelope-fill"></i>
                <span class="fw-bold text-decoration-none"> Email us: </span>
                <a href="mailto:lianmototechmotorparts@gmail.com" class="text-decoration-none">
                  <span class="fs-6">lianmototechmotorparts@gmail.com</span>
                </a>
              </li>
              <li class="list-group-item ">
                <i class="bi bi-facebook"></i>
                <span class="fw-bold"> Message us: </span>
                <a href="https://www.facebook.com/share/1AhgrxWfAc/?mibextid=qi2Omg" class="text-decoration-none">
                  <span class="fs-6">lianmototech motorparts</span>
                </a>
              </li>
              <li class="list-group-item ">
                <i class="bi bi-facebook"></i>
                <span class="fw-bold"> Message me: </span>
                <a href="https://www.facebook.com/share/1EFtJpKMf3/?mibextid=qi2Omg" class="text-decoration-none">
                  <span class="fs-6">Ryan Ilarde</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="p-5 bg-dark text-white text-center position-relative">
      <div class="container d-flex align-items-center justify-content-center ">
        <p class="lead">Copyright &copy;2025 Ilanmototech Motoparts.</p>

        <a href="#" class="position-absolute bottom-0 end-0 p-5">
          <i class="bi bi-arrow-up-circle h1"></i>
        </a>
      </div>
    </footer>



    <!-- Booking Modal -->
    <div
      class="modal fade"
      id="Book"
      tabindex="-1"
      aria-labelledby="BookLabel"
      aria-hidden="true">

      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title w-100 text-center" id="BookLabel">Booking</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">

            <form method="post" action="index.php">

              <div class="mb-3">
                <label for="full-name" class="col-form-label">
                  Full Name:
                </label>
                <input type="text" class="form-control" id="full-name" name="fullname" placeholder="Enter your full name" required />
              </div>
              <div class="mb-3">
                <label for="phone-number" class="col-form-label">Phone Number:</label>
                <input type="tel" class="form-control" id="phone-number" name="number" placeholder="Enter your phone number" required />
              </div>
              <div class="mb-3">
                <label for="email" class="col-form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required />
              </div>
              <div class="mb-3">
                <label for="date" class="col-form-label">Select Preferred Date:</label>
                <input type="Date" class="form-control" id="date" name="date" placeholder="Select preferred date" required />
              </div>
              <div class="mb-3">
                <label for="time" class="col-form-label">Select Preferred Time:</label>
                <input type="time" class="form-control" id="time" name="time" placeholder="Select preferred time" required />
              </div>
              <div class="mb-3">
                <label for="service" class="col-form-label">Select Service:</label>
                <select class="form-select w-100" id="service" name="service" value="repair">
                  <option value="consultation">Consultation</option>
                  <option value="repair">Repair/Install motor parts</option>
                </select>
              </div>
              <div class="modal-footer">
                <button
                  type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal">
                  Close
                </button>
                <button type="submit" class="btn btn-primary" name="submit_booking">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
      crossorigin="anonymous"></script>
    <script src="assets/js/MotoPart.js"></script>
</body>

</html>