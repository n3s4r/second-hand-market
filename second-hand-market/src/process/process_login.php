<?php
session_start();
require_once '../includes/db_connect.php';

$email = $_POST['email'];
$password = $_POST['password'];

// Prepare a select statement
$sql = "SELECT UserID, Name, Email, PasswordHash FROM user WHERE Email = ?";
//                                                     ^^^^ Change `User` to `user`
if($stmt = $conn->prepare($sql)){
    $stmt->bind_param("s", $email);
    
    if($stmt->execute()){
        $stmt->store_result();
        
        // Check if email exists
        if($stmt->num_rows == 1){                      
            $stmt->bind_result($id, $name, $email, $hashed_password);
            if($stmt->fetch()){
                // Verify password
                if(password_verify($password, $hashed_password)){
                    // Password is correct, so start a new session
                    
                    // Store data in session variables
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["name"] = $name;                         
                    
                    // Redirect user to home page
                    header("location: ../../public/index.php");
                    exit; // Important: Stop script execution
                } else{
                    // Display an error message if password is not valid
                    echo "The password you entered was not valid.";
                }
            }
        } else{
            echo "No account found with that email.";
        }
    } else{
        echo "Oops! Something went wrong.";
    }
    $stmt->close();
}
$conn->close();
?>