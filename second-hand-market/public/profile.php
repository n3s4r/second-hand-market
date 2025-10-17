<?php
require_once '../src/templates/header.php';
require_once '../src/includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$userID = $_SESSION['id'];
?>

<h2>My Profile</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>

<ul class="nav nav-tabs" id="profileTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="listings-tab" data-bs-toggle="tab" data-bs-target="#listings" type="button" role="tab">My Listings</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">My Orders</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="offers-tab" data-bs-toggle="tab" data-bs-target="#offers" type="button" role="tab">Offers on My Items</button>
    </li>
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="admin_dashboard.php">Admin Dashboard</a>
    </li>
    <?php endif; ?>
</ul>

<div class="tab-content" id="profileTabContent">
    <div class="tab-pane fade show active" id="listings" role="tabpanel">
        <h4 class="mt-3">My Listings</h4>
        <div class="row">
            <?php
            // Fetch products listed by the logged-in user
            $sql_listings = "SELECT ProductID, Title, Price, Status, (SELECT ImageURL FROM ProductImage WHERE ProductID = P.ProductID LIMIT 1) as ImageURL FROM Product P WHERE SellerID = ? ORDER BY ListDate DESC";
            if ($stmt_listings = $conn->prepare($sql_listings)) {
                $stmt_listings->bind_param("i", $userID);
                $stmt_listings->execute();
                $result_listings = $stmt_listings->get_result();
                if ($result_listings->num_rows > 0) {
                    while ($row = $result_listings->fetch_assoc()) {
                        $status_class = ($row['Status'] == 'Sold') ? 'border-danger' : '';
            ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="card <?php echo $status_class; ?>">
                                <img src="<?php echo htmlspecialchars($row['ImageURL'] ?? 'images/default.png'); ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?php echo htmlspecialchars($row['Title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['Title']); ?></h5>
                                    <p class="card-text fs-5 fw-bold">$<?php echo htmlspecialchars($row['Price']); ?></p>
                                    <p class="card-text"><small class="text-muted">Status: <?php echo htmlspecialchars($row['Status']); ?></small></p>
                                    <a href="product.php?id=<?php echo $row['ProductID']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                    <a href="edit_product.php?id=<?php echo $row['ProductID']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                </div>
                            </div>
                        </div>
            <?php
                    }
                } else {
                    echo "<p>You haven't listed any products yet. <a href=\"post-ad.php\">Post one now!</a></p>";
                }
                $stmt_listings->close();
            }
            ?>
        </div>
    </div>

    <div class="tab-pane fade" id="orders" role="tabpanel">
        <h4 class="mt-3">My Orders</h4>
        <div class="list-group">
            <?php
            $sql_orders = "SELECT O.OrderID, O.TotalAmount, O.OrderDate, P.Title, U.Name as SellerName FROM `Order` O JOIN Product P ON O.ProductID = P.ProductID JOIN User U ON P.SellerID = U.UserID WHERE O.BuyerID = ? ORDER BY O.OrderDate DESC";
            if ($stmt_orders = $conn->prepare($sql_orders)) {
                $stmt_orders->bind_param("i", $userID);
                $stmt_orders->execute();
                $result_orders = $stmt_orders->get_result();
                if ($result_orders->num_rows > 0) {
                    while ($row = $result_orders->fetch_assoc()) {
            ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Order #<?php echo htmlspecialchars($row['OrderID']); ?></h5>
                                <small class="text-muted"><?php echo htmlspecialchars(date('M j, Y', strtotime($row['OrderDate']))); ?></small>
                            </div>
                            <p class="mb-1"><strong>Item:</strong> <?php echo htmlspecialchars($row['Title']); ?></p>
                            <p class="mb-1"><strong>Seller:</strong> <?php echo htmlspecialchars($row['SellerName']); ?></p>
                            <p class="mb-1"><strong>Total:</strong> $<?php echo htmlspecialchars($row['TotalAmount']); ?></p>
                        </div>
            <?php
                    }
                } else {
                    echo "<p>You haven't purchased any items yet.</p>";
                }
                $stmt_orders->close();
            }
            ?>
        </div>
    </div>

    <div class="tab-pane fade" id="offers" role="tabpanel">
        <h4 class="mt-3">Offers Received</h4>
        <div class="list-group">
            <?php
            $sql_offers = "SELECT O.OfferID, O.OfferAmount, O.OfferDate, P.ProductID, P.Title, U.Name as BuyerName FROM Offer O JOIN Product P ON O.ProductID = P.ProductID JOIN User U ON O.UserID = U.UserID WHERE P.SellerID = ? AND O.Status = 'Pending' ORDER BY O.OfferDate DESC";
            if ($stmt_offers = $conn->prepare($sql_offers)) {
                $stmt_offers->bind_param("i", $userID);
                $stmt_offers->execute();
                $result_offers = $stmt_offers->get_result();

                if ($result_offers->num_rows > 0) {
                    while ($row = $result_offers->fetch_assoc()) {
            ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>$<?php echo htmlspecialchars($row['OfferAmount']); ?></strong> offer for
                                <a href="product.php?id=<?php echo $row['ProductID']; ?>">"<?php echo htmlspecialchars($row['Title']); ?>"</a>
                                from <?php echo htmlspecialchars($row['BuyerName']); ?>
                            </div>
                            <div class="btn-group" role="group">
                                <a href="../src/process/process_accept.php?offer_id=<?php echo $row['OfferID']; ?>" class="btn btn-success btn-sm">Accept</a>
                                <a href="../src/process/process_reject.php?offer_id=<?php echo $row['OfferID']; ?>" class="btn btn-danger btn-sm">Reject</a>
                            </div>
                        </div>
            <?php
                    }
                } else {
                    echo "<p>You have no pending offers.</p>";
                }
                $stmt_offers->close();
            }
            ?>
        </div>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>