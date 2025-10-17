<?php
session_start();
require_once '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../public/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['productID'], $_POST['offerAmount'])) {
    $productID = $_POST['productID'];
    $offerAmount = $_POST['offerAmount'];
    $userID = $_SESSION['id'];

    // Insert the offer into the database
    $sql = "INSERT INTO Offer (ProductID, UserID, OfferAmount) VALUES (?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iid", $productID, $userID, $offerAmount);
        if ($stmt->execute()) {
            // Redirect to the product page with a success message
            header("location: ../../public/product.php?id=" . $productID . "&offer=success");
        } else {
            // Redirect with a failure message
            header("location: ../../public/product.php?id=" . $productID . "&offer=failed");
        }
        $stmt->close();
    }
    $conn->close();
} else {
    // If the request method is not POST or data is missing
    die('Invalid request.');
}
?>