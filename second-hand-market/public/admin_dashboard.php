<?php require_once '../src/templates/header.php'; ?>
<?php require_once '../src/includes/db_connect.php'; ?>

<?php
// Ensure only admins can access this page
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: login.php"); // Redirect non-admins to the login page
    exit;
}
?>

<div class="p-4 mb-4 bg-light rounded-3 text-center">
    <h1>Admin Dashboard</h1>
    <p>Manage all product listings on the marketplace.</p>
</div>

<div class="row">
    <?php
    $sql = "SELECT P.ProductID, P.Title, P.Price, U.Name as SellerName, (SELECT ImageURL FROM ProductImage WHERE ProductID = P.ProductID LIMIT 1) as ImageURL FROM Product P JOIN User U ON P.SellerID = U.UserID ORDER BY P.ListDate DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
    ?>
        <div class="col-md-4 col-sm-6">
            <div class="card mb-4">
                <img src="<?php echo htmlspecialchars($row['ImageURL'] ?? 'images/default.png'); ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?php echo htmlspecialchars($row['Title']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($row['Title']); ?></h5>
                    <p class="card-text fs-5 fw-bold">$<?php echo htmlspecialchars($row['Price']); ?></p>
                    <p class="card-text">Seller: <?php echo htmlspecialchars($row['SellerName']); ?></p>
                    <a href="product.php?id=<?php echo $row['ProductID']; ?>" class="btn btn-primary btn-sm">View Details</a>
                    <a href="../src/process/process_delete.php?id=<?php echo $row['ProductID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                </div>
            </div>
        </div>
    <?php
        }
    } else {
        echo "<p class='text-center'>No products found.</p>";
    }
    ?>
</div>

<?php require_once '../src/templates/footer.php'; ?>