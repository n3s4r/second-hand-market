<?php
session_start();
require_once '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../public/login.php");
    exit;
}

if (isset($_POST['productID'], $_POST['amount'])) {
    $productID = $_POST['productID'];
    $totalAmount = $_POST['amount'];
    $buyerID = $_SESSION['id'];

    // Use a transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // 1. Check if the product is still available and get seller ID
        $sql_check = "SELECT SellerID FROM Product WHERE ProductID = ? AND Status = 'Available' FOR UPDATE";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $productID);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($result->num_rows == 0) {
            throw new Exception("Product is no longer available.");
        }

        $row = $result->fetch_assoc();
        $sellerID = $row['SellerID'];
        $stmt_check->close();

        // Check if the buyer is also the seller, prevent self-purchase
        if ($buyerID == $sellerID) {
            throw new Exception("You cannot buy your own product.");
        }

        // 2. Insert the new order
        $sql_order = "INSERT INTO `Order` (ProductID, BuyerID, TotalAmount) VALUES (?, ?, ?)";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("iid", $productID, $buyerID, $totalAmount);
        if (!$stmt_order->execute()) {
            throw new Exception("Order insertion failed: " . $stmt_order->error);
        }
        $new_order_id = $stmt_order->insert_id;
        $stmt_order->close();

        // 3. Update the product status to 'Sold'
        $sql_update = "UPDATE Product SET Status = 'Sold' WHERE ProductID = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $productID);
        if (!$stmt_update->execute()) {
            throw new Exception("Product status update failed: " . $stmt_update->error);
        }
        $stmt_update->close();

        // 4. Create a new Delivery record with a default 'Processing' status
        $sql_delivery = "INSERT INTO Delivery (OrderID, Status) VALUES (?, 'Processing')";
        $stmt_delivery = $conn->prepare($sql_delivery);
        $stmt_delivery->bind_param("i", $new_order_id);
        if (!$stmt_delivery->execute()) {
            throw new Exception("Delivery record creation failed: " . $stmt_delivery->error);
        }
        $stmt_delivery->close();

        // If all queries were successful, commit the transaction
        $conn->commit();
        header("location: ../../public/profile.php?orders=true");
        exit;

    } catch (Exception $e) {
        // Something went wrong, rollback the transaction
        $conn->rollback();
        header("location: ../../public/product.php?id=" . $productID . "&error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    die('Invalid request.');
}
$conn->close();
?>