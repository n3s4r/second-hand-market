<?php
session_start();
require_once '../includes/db_connect.php';

// Get form data
$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

// Use prepared statements to check if the email already exists
$sql_check = "SELECT UserID FROM user WHERE Email = ?";
//                                 ^^^^ Change `User` to `user`
if ($stmt_check = $conn->prepare($sql_check)) {
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        echo "Error: An account with this email already exists.";
        $stmt_check->close();
        $conn->close();
        exit;
    }
    $stmt_check->close();
}

// Hash the password for security
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Use prepared statements to prevent SQL injection
$sql = "INSERT INTO user (Name, Email, PasswordHash) VALUES (?, ?, ?)";
//                    ^^^^ Change `User` to `user`

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("sss", $name, $email, $passwordHash);
    
    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        // Redirect to login page
        header("location: ../../public/login.php");
        exit;
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>