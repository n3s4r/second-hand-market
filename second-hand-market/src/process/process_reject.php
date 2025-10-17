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

// Check if the logged-in user is the seller of the product associated with the offer
$sql_check = "SELECT P.SellerID FROM Offer O JOIN Product P ON O.ProductID = P.ProductID WHERE O.OfferID = ? AND P.SellerID = ?";

if ($stmt = $conn->prepare($sql_check)) {
    $stmt->bind_param("ii", $offerID, $sellerID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("You do not have permission to reject this offer.");
    }
    $stmt->close();

    // Prepare an UPDATE statement to reject the offer
    $sql_reject = "UPDATE Offer SET Status = 'Rejected' WHERE OfferID = ?";
    if ($stmt_reject = $conn->prepare($sql_reject)) {
        $stmt_reject->bind_param("i", $offerID);
        if ($stmt_reject->execute()) {
            header("location: ../../public/profile.php?offers=true&status=rejected");
            exit;
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        $stmt_reject->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    echo "Error preparing statement: " . $conn->error;
}

$conn->close();
?>