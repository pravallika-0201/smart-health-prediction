<?php
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Healthcare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="display-4 mb-4">Smart Health Prediction Using Data Mining</h1>
                <p class="lead mb-4">Your trusted platform for managing healthcare needs efficiently and securely.</p>
                
                <div class="row g-4 py-4">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <!-- <i class="bi bi-person-plus fs-1 text-primary mb-3"></i> -->
                                <h5 class="card-title">Patient Care</h5>
                                <p class="card-text">Book appointments, check symptoms, and manage your health records.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <i class="bi bi-heart-pulse fs-1 text-danger mb-3"></i>
                                <h5 class="card-title">Expert Doctors</h5>
                                <p class="card-text">Connect with qualified healthcare professionals.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <i class="bi bi-clipboard2-pulse fs-1 text-success mb-3"></i>
                                <h5 class="card-title">Digital Records</h5>
                                <p class="card-text">Secure storage and easy access to your medical history.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!isLoggedIn()): ?>
                    <div class="d-grid gap-2 d-md-block">
                        <a href="login.php" class="btn btn-primary btn-lg mx-2">Login</a>
                        <a href="register.php" class="btn btn-outline-primary btn-lg mx-2">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
