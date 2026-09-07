<?php
session_start();
include(__DIR__ . "/../config/db.php");

$message = '';
$error = '';

if ($_POST) {
    $email = trim($_POST['email']);
    
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Generate reset code (simple version)
        $reset_code = substr(md5(uniqid()), 0, 8);
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $conn->prepare("INSERT INTO password_resets (email, reset_code, expires) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE reset_code = ?, expires = ?");
        $stmt->bind_param("sssss", $email, $reset_code, $expires, $reset_code, $expires);
        $stmt->execute();
        
        $message = "✅ Reset code sent! Check your email for: <strong>$reset_code</strong> (valid 1 hour)";
    } else {
        $error = "❌ Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - SupplySync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="vh-100 d-flex align-items-center justify-content-center p-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-sm-8 col-12">
                <div class="card shadow-lg border-0" style="max-width: 450px; margin: auto;">
                    <div class="card-body p-5 text-center">
                        <div class="mb-5">
                            <div class="bg-warning rounded-circle mx-auto mb-4 p-4 shadow" 
                                 style="width: 100px; height: 100px; background: linear-gradient(135deg, #F59E0B, #D97706);">
                                <i class="fas fa-key fa-2x text-white"></i>
                            </div>
                            <h1 class="h3 fw-bold mb-1" style="color: #1F2937;">Reset Password</h1>
                            <p class="text-muted mb-0">Enter email to receive reset code</p>
                        </div>

                        <?php if ($message): ?>
                            <div class="alert alert-success shadow-sm mb-4"><?= $message ?></div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger shadow-sm mb-4"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold mb-2 d-block text-start" style="color: #1F2937;">
                                    <i class="fas fa-envelope me-2"></i>Registered Email
                                </label>
                                <input type="email" name="email" class="form-control form-control-lg" 
                                       placeholder="staff@hotel.com" required autofocus autocomplete="email">
                            </div>
                            <button type="submit" class="btn btn-warning btn-lg w-100 mb-3 shadow-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Reset Code
                            </button>
                        </form>

                        <div class="mt-4">
                            <a href="login.php" class="text-decoration-none fw-semibold" style="color: #3EB489;">
                                <i class="fas fa-arrow-left me-2"></i>Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
