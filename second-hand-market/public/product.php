<?php require_once '../src/templates/header.php'; ?>
<?php require_once '../src/includes/db_connect.php'; ?>

<?php
// Check if an ID was passed
if(!isset($_GET['id']) || empty($_GET['id'])){
    echo "<h1>Product not found!</h1>";
    exit;
}

$productID = $_GET['id'];

// Fetch product details and seller info using a prepared statement
$sql = "SELECT P.*, U.Name as SellerName, U.AvgRating, (SELECT ImageURL FROM ProductImage WHERE ProductID = P.ProductID LIMIT 1) as ImageURL FROM Product P JOIN User U ON P.SellerID = U.UserID WHERE P.ProductID = ?";
if($stmt = $conn->prepare($sql)){
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $product = $result->fetch_assoc();

        // Fetch reviews for this product
        $sql_reviews = "SELECT R.*, U.Name as ReviewerName FROM Review R JOIN `Order` O ON R.OrderID = O.OrderID JOIN Product P ON O.ProductID = P.ProductID JOIN User U ON R.ReviewerID = U.UserID WHERE P.ProductID = ?";
        $stmt_reviews = $conn->prepare($sql_reviews);
        $stmt_reviews->bind_param("i", $productID);
        $stmt_reviews->execute();
        $reviews = $stmt_reviews->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_reviews->close();
?>
        <div class="row">
            <div class="col-md-7">
                <img src="<?php echo htmlspecialchars($product['ImageURL'] ?? 'images/default.png'); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['Title']); ?>">
            </div>
            <div class="col-md-5">
                <h1><?php echo htmlspecialchars($product['Title']); ?></h1>
                <h3 class="text-success">$<?php echo htmlspecialchars($product['Price']); ?></h3>
                <p><strong>Condition:</strong> <?php echo htmlspecialchars($product['Condition']); ?></p>
                <p><strong>Seller:</strong> <?php echo htmlspecialchars($product['SellerName']); ?> (Rating: <?php echo number_format($product['AvgRating'], 2); ?>/5)</p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($product['Status']); ?></p>
                <p><strong>Posted On:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($product['ListDate']))); ?></p>

                <?php 
                // Check if the user is the seller
                $isSeller = (isset($_SESSION['id']) && $_SESSION['id'] == $product['SellerID']);
                $isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
                $isAvailable = $product['Status'] == 'Available';

                if ($isLoggedIn && !$isSeller && $isAvailable): 
                ?>
                <form action="../src/process/process_order.php" method="post" class="d-inline">
                    <input type="hidden" name="productID" value="<?php echo $product['ProductID']; ?>">
                    <input type="hidden" name="amount" value="<?php echo $product['Price']; ?>">
                    <button type="submit" class="btn btn-primary btn-lg mt-3">Buy Now</button>
                </form>
                <button type="button" class="btn btn-secondary btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#makeOfferModal">
                    Make an Offer
                </button>
                <?php elseif ($isLoggedIn && $isSeller): ?>
                    <a href="edit_product.php?id=<?php echo $product['ProductID']; ?>" class="btn btn-secondary btn-lg mt-3">Edit Listing</a>
                    <a href="../src/process/process_delete.php?id=<?php echo $product['ProductID']; ?>" class="btn btn-danger btn-lg mt-3" onclick="return confirm('Are you sure you want to delete this product?');">Delete Listing</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                    <form action="../src/process/process_flag_product.php" method="post" class="d-inline">
                        <input type="hidden" name="productID" value="<?php echo $product['ProductID']; ?>">
                        <button type="submit" class="btn btn-warning btn-lg mt-3">Flag as Fake</button>
                    </form>
                <?php endif; ?>

            </div>
        </div>

        <div class="mt-5">
            <h4>Description</h4>
            <p><?php echo nl2br(htmlspecialchars($product['Description'])); ?></p>
        </div>

        <div class="mt-5">
            <h4>Customer Reviews</h4>
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($review['ReviewerName']); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted">Product Rating: <?php echo htmlspecialchars($review['ProductRating']); ?>/5 | Seller Rating: <?php echo htmlspecialchars($review['SellerRating']); ?>/5</h6>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($review['Comment'])); ?></p>
                            <p class="card-text"><small class="text-muted">Reviewed on: <?php echo htmlspecialchars(date('F j, Y', strtotime($review['ReviewDate']))); ?></small></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No reviews yet. Be the first to review this product!</p>
            <?php endif; ?>

            <?php
            // Check if the user has a completed order and has not reviewed yet
            if ($isLoggedIn && !$isSeller) {
                $sql_can_review = "SELECT O.OrderID FROM `Order` O JOIN `Product` P ON O.ProductID = P.ProductID WHERE O.BuyerID = ? AND P.ProductID = ? AND O.Status = 'Delivered' AND NOT EXISTS (SELECT 1 FROM Review R WHERE R.OrderID = O.OrderID)";
                $stmt_can_review = $conn->prepare($sql_can_review);
                $stmt_can_review->bind_param("ii", $userID, $productID);
                $stmt_can_review->execute();
                $result_can_review = $stmt_can_review->get_result();
                $canReview = $result_can_review->num_rows > 0;
                $stmt_can_review->close();

                if ($canReview):
            ?>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title">Write a Review</h5>
                            <form action="../src/process/process_review.php" method="post">
                                <input type="hidden" name="productID" value="<?php echo $productID; ?>">
                                <div class="mb-3">
                                    <label for="productRating" class="form-label">Product Rating</label>
                                    <select name="productRating" id="productRating" class="form-control" required>
                                        <option value="">Select a rating</option>
                                        <option value="5">5 - Excellent</option>
                                        <option value="4">4 - Very Good</option>
                                        <option value="3">3 - Good</option>
                                        <option value="2">2 - Fair</option>
                                        <option value="1">1 - Poor</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="sellerRating" class="form-label">Seller Rating</label>
                                    <select name="sellerRating" id="sellerRating" class="form-control" required>
                                        <option value="">Select a rating</option>
                                        <option value="5">5 - Excellent</option>
                                        <option value="4">4 - Very Good</option>
                                        <option value="3">3 - Good</option>
                                        <option value="2">2 - Fair</option>
                                        <option value="1">1 - Poor</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Comment</label>
                                    <textarea name="comment" id="comment" class="form-control" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                            </form>
                        </div>
                    </div>
            <?php
                endif;
            }
            ?>
        </div>

        <div class="modal fade" id="makeOfferModal" tabindex="-1" aria-labelledby="makeOfferModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="../src/process/process_offer.php" method="post">
                        <div class="modal-header">
                            <h5 class="modal-title" id="makeOfferModalLabel">Make an Offer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>You are making an offer for: <strong><?php echo htmlspecialchars($product['Title']); ?></strong></p>
                            <p>Listing Price: $<?php echo htmlspecialchars($product['Price']); ?></p>
                            <input type="hidden" name="productID" value="<?php echo $product['ProductID']; ?>">
                            <div class="mb-3">
                                <label for="offerAmount" class="form-label">Your Offer Amount ($)</label>
                                <input type="number" name="offerAmount" id="offerAmount" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit Offer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
<?php
    } else {
        echo "<h1>Product not found!</h1>";
    }
    $stmt->close();
}
$conn->close();
?>

<?php require_once '../src/templates/footer.php'; ?>