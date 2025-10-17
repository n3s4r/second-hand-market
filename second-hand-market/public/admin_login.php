<?php require_once '../src/templates/header.php'; ?>

<?php
// If admin is already logged in, redirect them to the admin dashboard
if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
    header("location: admin_dashboard.php");
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2>Admin Login</h2>
            </div>
            <div class="card-body">
                <p>Please log in with your admin credentials.</p>
                <form action="../src/process/process_admin_login.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <input type="submit" class="btn btn-primary" value="Login as Admin">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>