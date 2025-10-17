<?php
session_start();
require_once '../includes/db_connect.php';

// Ensure user is logged in and is an administrator
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: ../../public/login.php");
    exit;
}

// Check if the product ID is provided in the POST request
if (isset($_POST['productID'])) {
    $productID = $_POST['productID'];

    // Prepare an UPDATE statement to flag the product
    $sql = "UPDATE Product SET IsFlagged = TRUE WHERE ProductID = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $productID);

        if ($stmt->execute()) {
            // Redirect back to the product page with a success message
            header("location: ../../public/product.php?id=" . $productID . "&status=flagged");
            exit;
        } else {
            echo "Error flagging the product. Please try again later.";
        }
        $stmt->close();
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
} else {
    echo "Invalid request. No product ID provided.";
}
$conn->close();
?>