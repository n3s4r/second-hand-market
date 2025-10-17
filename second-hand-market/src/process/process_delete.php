<?php
session_start();
require_once '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../public/login.php");
    exit;
}

// Check for Product ID in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Invalid request.');
}

$productID = $_GET['id'];
$userID = $_SESSION['id'];
$is_admin = isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;

// A new security check to determine if the user has permission to delete this product.
// The user can delete the product if they are the seller OR if they are an admin.
$sql_check_owner = "SELECT SellerID FROM Product WHERE ProductID = ?";
if ($stmt_check = $conn->prepare($sql_check_owner)) {
    $stmt_check->bind_param("i", $productID);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $product = $result_check->fetch_assoc();
    $stmt_check->close();
    
    // Check if the user is the owner or an admin
    if ($product['SellerID'] == $userID || $is_admin) {
        // User has permission, proceed with deletion
        $sql_delete = "DELETE FROM Product WHERE ProductID = ?";
        if ($stmt_delete = $conn->prepare($sql_delete)) {
            $stmt_delete->bind_param("i", $productID);
            
            if ($stmt_delete->execute()) {
                if ($stmt_delete->affected_rows > 0) {
                    // Redirect to admin dashboard on success
                    header("location: ../../public/admin_dashboard.php?status=deleted");
                    exit;
                } else {
                    echo "Error: Product not found or already deleted.";
                }
            } else {
                echo "Oops! Something went wrong with the deletion.";
            }
            $stmt_delete->close();
        }
    } else {
        // User is neither the owner nor an admin
        echo "Error: You do not have permission to delete this item.";
    }
}
$conn->close();
?>