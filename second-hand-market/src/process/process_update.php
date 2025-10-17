<?php
session_start();
require_once '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../public/login.php");
    exit;
}

// Check if form data is set
if (!isset($_POST['productID'], $_POST['title'], $_POST['description'], $_POST['price'], $_POST['condition'])) {
    die('Please fill out the form completely.');
}

// Assign variables
$productID = $_POST['productID'];
$title = $_POST['title'];
$description = $_POST['description'];
$price = $_POST['price'];
$condition = $_POST['condition'];
$sellerID = $_SESSION['id']; // Get the logged-in user's ID

// Prepare the UPDATE statement
// The "AND SellerID = ?" clause is a CRITICAL security measure to ensure users can only update their own products.
$sql = "UPDATE Product SET Title = ?, Description = ?, Price = ?, `Condition` = ? WHERE ProductID = ? AND SellerID = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ssdssi", $title, $description, $price, $condition, $productID, $sellerID);

    if ($stmt->execute()) {
        // Check if any row was actually updated
        if ($stmt->affected_rows > 0) {
            // Redirect back to the product page on success
            header("location: ../../public/product.php?id=" . $productID . "&status=updated");
        } else {
            // No rows updated - could be because the user doesn't own the product or data was the same
            header("location: ../../public/product.php?id=" . $productID . "&status=nochange");
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    $stmt->close();
}
$conn->close();
?>