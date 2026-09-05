<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: ../" . ($role == 'admin' ? 'admin' : 'staff') . "/dashboard.php");
    exit();
}

$error = '';
if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            header("Location: ../" . ($user['role'] == 'admin' ? 'admin' : 'staff') . "/dashboard.php");
            exit();
        } else {
            $error = "❌ Invalid email or password!";
        }
    } else {
        $error = "❌ Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SupplySync - Hotel Kitchen Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="vh-100 d-flex align-items-center justify-content-center p-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-sm-8 col-12">
                <!-- MAIN LOGIN CARD -->
                <div class="card shadow-lg border-0" style="max-width: 450px; margin: auto;">
                    <div class="card-body p-5 text-center">
                        <!-- LOGO HEADER -->
                        <div class="mb-5">
                            <div class="bg-primary-mint rounded-circle mx-auto mb-4 p-4 shadow" 
                                 style="width: 100px; height: 100px; background: linear-gradient(135deg, #3EB489, #22C55E);">
                                <i class="fas fa-utensils fa-2x text-white"></i>
                            </div>
                            <h1 class="h3 fw-bold mb-1" style="color: #1F2937;">SupplySync</h1>
                            <p class="text-muted mb-0">Hotel Kitchen Inventory</p>
                        </div>

                        <!-- LOGIN FORM -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger shadow-sm mb-4"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold mb-2 d-block text-start" style="color: #1F2937;">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                </label>
                                <input type="email" name="email" class="form-control form-control-lg" 
                                       placeholder="staff@hotel.com" required autofocus autocomplete="email">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold mb-2 d-block text-start" style="color: #1F2937;">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <input type="password" name="password" class="form-control form-control-lg" 
                                       placeholder="Enter your password" required autocomplete="current-password">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 shadow-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </form>

                        

                        <!-- FORGOT PASSWORD -->
                        <div class="mt-4">
                            <a href="forgot_password.php" class="text-decoration-none fw-semibold" 
                               style="color: #3EB489;">
                                <i class="fas fa-question-circle me-1"></i>Forgot Password?
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .divider-dashed {
            position: relative;
            height: 1px;
            background: #E8F8F2;
        }
        .divider-dashed::before {
            content: 'or';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 0 15px;
            color: #6B7280;
            font-size: 0.85rem;
        }
    </style>
</body>
</html>
