<?php
session_start();
require_once '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../public/login.php");
    exit;
}

$userID = $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productID = $_POST['productID'];
    $comment = trim($_POST['comment']);
    $productRating = $_POST['productRating'];
    $sellerRating = $_POST['sellerRating'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Check if the user has a completed order for this product
        $sql_order = "SELECT OrderID, BuyerID FROM `Order` WHERE ProductID = ? AND BuyerID = ? AND Status = 'Delivered'";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("ii", $productID, $userID);
        $stmt_order->execute();
        $result_order = $stmt_order->get_result();

        if ($result_order->num_rows == 0) {
            throw new Exception("You can only review products you have purchased and received.");
        }
        $order = $result_order->fetch_assoc();
        $orderID = $order['OrderID'];
        $stmt_order->close();

        // Check if a review already exists for this order
        $sql_check_review = "SELECT ReviewID FROM Review WHERE OrderID = ?";
        $stmt_check_review = $conn->prepare($sql_check_review);
        $stmt_check_review->bind_param("i", $orderID);
        $stmt_check_review->execute();
        $stmt_check_review->store_result();

        if ($stmt_check_review->num_rows > 0) {
            throw new Exception("You have already reviewed this product.");
        }
        $stmt_check_review->close();

        // Insert the new review
        $sql_insert = "INSERT INTO Review (OrderID, ReviewerID, SellerRating, ProductRating, Comment) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iiiis", $orderID, $userID, $sellerRating, $productRating, $comment);
        $stmt_insert->execute();
        $stmt_insert->close();

        // Update the seller's average rating
        $sql_seller_id = "SELECT SellerID FROM Product WHERE ProductID = ?";
        $stmt_seller_id = $conn->prepare($sql_seller_id);
        $stmt_seller_id->bind_param("i", $productID);
        $stmt_seller_id->execute();
        $result_seller_id = $stmt_seller_id->get_result();
        $sellerID = $result_seller_id->fetch_assoc()['SellerID'];
        $stmt_seller_id->close();

        // Calculate the new average rating for the seller
        $sql_avg_rating = "SELECT AVG(SellerRating) as AvgRating FROM Review r JOIN `Order` o ON r.OrderID = o.OrderID JOIN Product p ON o.ProductID = p.ProductID WHERE p.SellerID = ?";
        $stmt_avg_rating = $conn->prepare($sql_avg_rating);
        $stmt_avg_rating->bind_param("i", $sellerID);
        $stmt_avg_rating->execute();
        $result_avg_rating = $stmt_avg_rating->get_result();
        $newAvgRating = $result_avg_rating->fetch_assoc()['AvgRating'];
        $stmt_avg_rating->close();

        // Update the User table with the new average rating
        $sql_update_user = "UPDATE User SET AvgRating = ? WHERE UserID = ?";
        $stmt_update_user = $conn->prepare($sql_update_user);
        $stmt_update_user->bind_param("di", $newAvgRating, $sellerID);
        $stmt_update_user->execute();
        $stmt_update_user->close();


        $conn->commit();
        header("location: ../../public/product.php?id=" . $productID . "&status=review_success");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
    $conn->close();
}
?>