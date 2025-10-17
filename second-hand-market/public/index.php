<?php require_once '../src/templates/header.php'; ?>
<?php require_once '../src/includes/db_connect.php'; ?>

<div class="p-4 mb-4 bg-light rounded-3 text-center">
    <h1>Welcome to the Marketplace!</h1>
    <p>Find the best deals on used items from people near you.</p>
</div>

<div class="row">
    <?php
    // Your PHP code from the previous step to display products goes here
    $sql = "SELECT P.ProductID, P.Title, P.Price, (SELECT ImageURL FROM ProductImage WHERE ProductID = P.ProductID LIMIT 1) as ImageURL FROM Product P WHERE P.Status = 'Available'";
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
                    <a href="product.php?id=<?php echo $row['ProductID']; ?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>
    <?php
        }
    } else {
        echo "<p class='text-center'>No products found.</p>";
    }
    $conn->close();
    ?>
</div>

<?php require_once '../src/templates/footer.php'; ?>