<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../public/login.php");
    exit;
}

// Get product data
$sellerID = $_SESSION["id"];
$title = $_POST['title'];
$description = $_POST['description'];
$price = $_POST['price'];

// Start a transaction to ensure both product and image are saved or none are
$conn->begin_transaction();
$product_success = false;
$image_success = true; // Assume true unless an image is uploaded and fails

try {
    // 1. Insert product details into the Product table
    $sql_product = "INSERT INTO Product (SellerID, Title, Description, Price) VALUES (?, ?, ?, ?)";
    if ($stmt_product = $conn->prepare($sql_product)) {
        $stmt_product->bind_param("issd", $sellerID, $title, $description, $price);
        if ($stmt_product->execute()) {
            $productID = $conn->insert_id;
            $product_success = true;
        } else {
            throw new Exception("Product insertion failed: " . $stmt_product->error);
        }
        $stmt_product->close();
    } else {
        throw new Exception("Product statement preparation failed: " . $conn->error);
    }

    // 2. Handle image upload and insert into ProductImage table
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../public/images/";
        $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $unique_name = uniqid('product_', true) . '.' . $imageFileType;
        $target_file = $target_dir . $unique_name;
        $image_url_db = "images/" . $unique_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $sql_image = "INSERT INTO productimage (ProductID, ImageURL) VALUES (?, ?)";
            if ($stmt_image = $conn->prepare($sql_image)) {
                $stmt_image->bind_param("is", $productID, $image_url_db);
                if (!$stmt_image->execute()) {
                    throw new Exception("Image URL insertion failed: " . $stmt_image->error);
                }
                $stmt_image->close();
            } else {
                throw new Exception("Image statement preparation failed: " . $conn->error);
            }
        } else {
            throw new Exception("Image upload failed. Check folder permissions.");
        }
    }

    // If all steps were successful, commit the transaction
    $conn->commit();
    header("location: ../../public/product.php?id=" . $productID);
    exit;

} catch (Exception $e) {
    // If any step failed, roll back the transaction
    $conn->rollback();
    echo "Error: " . $e->getMessage();
    exit;
}

$conn->close();
?>