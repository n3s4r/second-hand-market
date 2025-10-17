<?php require_once '../src/templates/header.php'; ?>
<?php require_once '../src/includes/db_connect.php'; ?>

<?php
// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check for Product ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='alert alert-danger'>Invalid Product ID.</div>";
    exit;
}

$productID = $_GET['id'];
$userID = $_SESSION['id'];

// Fetch product data and verify ownership
$sql = "SELECT * FROM Product WHERE ProductID = ? AND SellerID = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $productID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $product = $result->fetch_assoc();
    } else {
        // Either product doesn't exist or user doesn't own it
        echo "<div class='alert alert-danger'>Product not found or you do not have permission to edit it.</div>";
        exit;
    }
    $stmt->close();
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2>Edit Product</h2>
        <form action="../src/process/process_update.php" method="post">
            <!-- Hidden input to pass the product ID -->
            <input type="hidden" name="productID" value="<?php echo $product['ProductID']; ?>">

            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($product['Title']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="5" required><?php echo htmlspecialchars($product['Description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" name="price" id="price" class="form-control" step="0.01" value="<?php echo htmlspecialchars($product['Price']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="condition" class="form-label">Condition</label>
                <select name="condition" id="condition" class="form-select">
                    <option value="New" <?php echo ($product['Condition'] == 'New') ? 'selected' : ''; ?>>New</option>
                    <option value="Used - Like New" <?php echo ($product['Condition'] == 'Used - Like New') ? 'selected' : ''; ?>>Used - Like New</option>
                    <option value="Used - Good" <?php echo ($product['Condition'] == 'Used - Good') ? 'selected' : ''; ?>>Used - Good</option>
                    <option value="Used - Fair" <?php echo ($product['Condition'] == 'Used - Fair') ? 'selected' : ''; ?>>Used - Fair</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="product.php?id=<?php echo $product['ProductID']; ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php
$conn->close();
require_once '../src/templates/footer.php';
?>