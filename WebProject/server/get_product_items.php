<?php 
    // this will get the products in databse 
    include ("connection.php");
    $stmt = $conn->prepare("SELECT * FROM product");

    $stmt->execute();
    $product = $stmt->get_result();  

?>