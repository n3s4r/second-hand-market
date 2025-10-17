<?php
session_start();
require_once '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../public/login.php");
    exit;
}

if (!isset($_GET['offer_id'])) {
    die('Invalid request.');
}

$offerID = $_GET['offer_id'];
$sellerID = $_SESSION['id'];

// Start a transaction
$conn->begin_transaction();

try {
    // 1. Fetch offer and product details, and check ownership
    $sql_fetch = "SELECT O.ProductID, O.OfferAmount, P.SellerID, O.UserID AS BuyerID FROM Offer O JOIN Product P ON O.ProductID = P.ProductID WHERE O.OfferID = ? AND P.SellerID = ? AND O.Status = 'Pending' FOR UPDATE";
    if ($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("ii", $offerID, $sellerID);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Offer not found or you do not have permission to accept it.");
        }

        $offer = $result->fetch_assoc();
        $productID = $offer['ProductID'];
        $offerAmount = $offer['OfferAmount'];
        $buyerID = $offer['BuyerID'];
        $stmt_fetch->close();
    } else {
        throw new Exception("Statement preparation failed: " . $conn->error);
    }

    // 2. Accept the specific offer
    $sql_accept_offer = "UPDATE Offer SET Status = 'Accepted' WHERE OfferID = ?";
    if ($stmt_accept = $conn->prepare($sql_accept_offer)) {
        $stmt_accept->bind_param("i", $offerID);
        if (!$stmt_accept->execute()) {
            throw new Exception("Failed to accept offer: " . $stmt_accept->error);
        }
        $stmt_accept->close();
    } else {
        throw new Exception("Statement preparation failed: " . $conn->error);
    }

    // 3. Update the product status to 'Sold'
    $sql_update_product = "UPDATE Product SET Status = 'Sold' WHERE ProductID = ?";
    if ($stmt_update_product = $conn->prepare($sql_update_product)) {
        $stmt_update_product->bind_param("i", $productID);
        if (!$stmt_update_product->execute()) {
            throw new Exception("Failed to update product status: " . $stmt_update_product->error);
        }
        $stmt_update_product->close();
    } else {
        throw new Exception("Statement preparation failed: " . $conn->error);
    }

    // 4. Create a new Order record
    $sql_create_order = "INSERT INTO `Order` (ProductID, BuyerID, TotalAmount) VALUES (?, ?, ?)";
    if ($stmt_create_order = $conn->prepare($sql_create_order)) {
        $stmt_create_order->bind_param("iid", $productID, $buyerID, $offerAmount);
        if (!$stmt_create_order->execute()) {
            throw new Exception("Failed to create order: " . $stmt_create_order->error);
        }
        $new_order_id = $stmt_create_order->insert_id;
        $stmt_create_order->close();
    } else {
        throw new Exception("Statement preparation failed: " . $conn->error);
    }

    // 5. Create a new Delivery record
    $sql_create_delivery = "INSERT INTO Delivery (OrderID, Status) VALUES (?, 'Processing')";
    if ($stmt_create_delivery = $conn->prepare($sql_create_delivery)) {
        $stmt_create_delivery->bind_param("i", $new_order_id);
        if (!$stmt_create_delivery->execute()) {
            throw new Exception("Failed to create delivery record: " . $stmt_create_delivery->error);
        }
        $stmt_create_delivery->close();
    } else {
        throw new Exception("Statement preparation failed: " . $conn->error);
    }

    // 6. Reject all other pending offers for this product
    $sql_reject_others = "UPDATE Offer SET Status = 'Rejected' WHERE ProductID = ? AND Status = 'Pending'";
    if ($stmt_reject_others = $conn->prepare($sql_reject_others)) {
        $stmt_reject_others->bind_param("i", $productID);
        if (!$stmt_reject_others->execute()) {
            throw new Exception("Failed to reject other offers: " . $stmt_reject_others->error);
        }
        $stmt_reject_others->close();
    } else {
        throw new Exception("Statement preparation failed: " . $conn->error);
    }

    // Commit the transaction
    $conn->commit();
    header("location: ../../public/profile.php?offers=true&status=accepted");
    exit;

} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    header("location: ../../public/profile.php?offers=true&status=error&message=" . urlencode($e->getMessage()));
    exit;
}
$conn->close();
?>