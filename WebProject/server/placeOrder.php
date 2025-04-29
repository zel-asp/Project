<?php
include('connection.php');
session_start();

/* Seps or logic
1. get user input from form
2. insert into orders table
3. get the product in caart from session
4. insert each product into order_item table in database
5. 
*/

if (isset($_POST['place_order'])) {
    // Get user input - this from the form in checkout.php
    $name    = $_POST['name'];
    $email   = $_POST['email'];
    $phone   = $_POST['phone'];
    $address = $_POST['address'];
    $city    = $_POST['city'];
    $note    = $_POST['note'];
    $order_cost = $_POST['total'];
    $order_status = "Pending";
    $user_id = $_SESSION['user_id']; // get user id from session
    $order_date = date("Y-m-d H:i:s");

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (
        order_cost, order_status, user_id, user_phone, user_city, user_address, product_note, order_date, user_name, user_email
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        //i for int, s for string, d for double
        "ssisssssss",
        $order_cost,
        $order_status,
        $user_id,
        $phone,
        $city,
        $address,
        $note,
        $order_date,
        $name,
        $email
    );

    $stmt->execute();

    // Check if the order was successfully inserted
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert each product from cart into order_item table
    foreach ($_SESSION['cart'] as $product) {
        $product_id      = $product['product_id'];
        $product_quantity = $product['quantity'];
        $product_price   = $product['product_price'];
        $product_name    = $product['product_name'];
        $product_image   = $product['product_image'];

        $stmt2 = $conn->prepare("INSERT INTO order_item (
            order_id, product_id, product_name, product_image, product_price, product_quantity, user_id, order_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        // same operation as above
        $stmt2->bind_param(
            "iissiiis",
            $order_id,
            $product_id,
            $product_name,
            $product_image,
            $product_price,
            $product_quantity,
            $user_id,
            $order_date
        );

        $stmt2->execute();
        $stmt2->close();
    }
    header('Location:../payment.php?$order_status = "Order Place Successfully"');
    exit();


    // Clear the cart after order
    //unset($_SESSION['cart']);
} else {
    header("Location:cart.php");
    exit();
}
