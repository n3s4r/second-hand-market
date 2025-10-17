<?php
session_start();
require_once '../includes/db_connect.php';

$email = $_POST['email'];
$password = $_POST['password'];

// Prepare a select statement to get user data and the IsAdmin flag
$sql = "SELECT UserID, Name, Email, PasswordHash, IsAdmin FROM user WHERE Email = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $name, $email, $hashed_password, $is_admin);
            if ($stmt->fetch()) {
                if (password_verify($password, $hashed_password)) {
                    // Password is correct. Now check if the user is an admin.
                    if ($is_admin == 1) {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["is_admin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["name"] = $name;

                        header("location: ../../public/admin_dashboard.php");
                        exit;
                    } else {
                        // User is not an admin
                        echo "You do not have administrative access.";
                    }
                } else {
                    echo "The password you entered was not valid.";
                }
            }
        } else {
            echo "No account found with that email.";
        }
    } else {
        echo "Oops! Something went wrong.";
    }
    $stmt->close();
}
$conn->close();
?>