<?php
session_start();
require_once '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../public/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['orderID'], $_POST['deliveryStatus'])) {
    $orderID = $_POST['orderID'];
    $deliveryStatus = $_POST['deliveryStatus'];
    $trackingNumber = isset($_POST['trackingNumber']) ? $_POST['trackingNumber'] : null;

    $sellerID = $_SESSION['id'];

    // Use a transaction to ensure both product and image are saved or none are
    $conn->begin_transaction();

    try {
        // First, verify that the logged-in user is the seller of this order's product
        $sql_verify = "SELECT P.ProductID FROM `Order` O JOIN Product P ON O.ProductID = P.ProductID WHERE O.OrderID = ? AND P.SellerID = ?";
        if ($stmt_verify = $conn->prepare($sql_verify)) {
            $stmt_verify->bind_param("ii", $orderID, $sellerID);
            $stmt_verify->execute();
            $result_verify = $stmt_verify->get_result();
            if ($result_verify->num_rows == 0) {
                throw new Exception("You do not have permission to update this order.");
            }
            $stmt_verify->close();
        } else {
            throw new Exception("Statement preparation failed: " . $conn->error);
        }

        // Update the delivery record
        $sql_update = "UPDATE Delivery SET Status = ?, TrackingNumber = ? WHERE OrderID = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("ssi", $deliveryStatus, $trackingNumber, $orderID);
            if (!$stmt_update->execute()) {
                throw new Exception("Delivery update failed: " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            throw new Exception("Update statement preparation failed: " . $conn->error);
        }

        // If all queries were successful, commit the transaction
        $conn->commit();
        header("location: ../../public/profile.php?sales=true");
        exit;

    } catch (Exception $e) {
        // Something went wrong, rollback the transaction
        $conn->rollback();
        header("location: ../../public/profile.php?error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    die('Invalid request.');
}

$conn->close();
?>