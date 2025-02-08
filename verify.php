<?php
require_once 'includes/functions.php';

// Clean up expired pending users
$cleanup_sql = "DELETE FROM pending_users WHERE expires_at < CURRENT_TIMESTAMP";
$conn->query($cleanup_sql);

if (isset($_GET['token'])) {
    $token = sanitizeInput($_GET['token']);
    
    // Find pending user with this token
    $sql = "SELECT * FROM pending_users WHERE verification_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $pending_user = $result->fetch_assoc();
        
        // Check if email already exists in users table (double check)
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $pending_user['email']);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $_SESSION['error'] = "Email already registered. Please login or use a different email.";
        } else {
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Insert into users table
                $insert_sql = "INSERT INTO users (email, password, first_name, last_name, user_type) 
                              VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("sssss", 
                    $pending_user['email'],
                    $pending_user['password'],
                    $pending_user['first_name'],
                    $pending_user['last_name'],
                    $pending_user['user_type']
                );
                
                if ($insert_stmt->execute()) {
                    // Delete from pending_users
                    $delete_sql = "DELETE FROM pending_users WHERE id = ?";
                    $delete_stmt = $conn->prepare($delete_sql);
                    $delete_stmt->bind_param("i", $pending_user['id']);
                    $delete_stmt->execute();
                    
                    // Commit transaction
                    $conn->commit();
                    
                    $_SESSION['message'] = "Email verified successfully! You can now login.";
                } else {
                    throw new Exception("Error creating user account.");
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $_SESSION['error'] = "Error verifying email: " . $e->getMessage();
            }
        }
    } else {
        $_SESSION['error'] = "Invalid or expired verification token. Please register again.";
    }
    
    redirect("login.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Healthcare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h2 class="mb-4">Email Verification</h2>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                        <?php endif; ?>
                        
                        <p>Please check your email for the verification link.</p>
                        <p>The verification link will expire in 24 hours.</p>
                        
                        <div class="mt-4">
                            <a href="login.php" class="btn btn-primary">Go to Login</a>
                            <a href="register.php" class="btn btn-outline-primary ms-2">Register Again</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
