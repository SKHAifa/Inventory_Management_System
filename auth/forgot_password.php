<?php
session_start();
include(__DIR__ . "/../config/db.php");

$message = '';
$error = '';
$reset_email = trim($_POST['email'] ?? '');
$reset_code = trim($_POST['reset_code'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_code'])) {
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $reset_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $reset_code = (string)random_int(100000, 999999);
        $stmt = $conn->prepare("INSERT INTO password_resets (email, reset_code, expires) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR)) ON DUPLICATE KEY UPDATE reset_code = ?, expires = DATE_ADD(NOW(), INTERVAL 1 HOUR)");
        $stmt->bind_param("sss", $reset_email, $reset_code, $reset_code);
        if ($stmt->execute()) {
            $message = "Reset code: <strong>" . htmlspecialchars($reset_code) . "</strong>. It is valid for 1 hour.";
        } else {
            $error = 'Unable to create a reset code. Please try again.';
        }
    } else {
        $error = 'Email not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($reset_email === '' || $reset_code === '' || $new_password === '') {
        $error = 'Email, reset code, and new password are required.';
    } elseif (strlen($new_password) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'The passwords do not match.';
    } else {
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE email = ? AND reset_code = ? AND expires >= NOW()");
        $stmt->bind_param("ss", $reset_email, $reset_code);
        $stmt->execute();
        $valid_code = $stmt->get_result()->fetch_assoc();

        if (!$valid_code) {
            $error = 'The reset code is invalid or expired.';
        } else {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $password_hash, $reset_email);
            if ($stmt->execute() && $stmt->affected_rows === 1) {
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->bind_param("s", $reset_email);
                $stmt->execute();
                header('Location: login.php?reset=success');
                exit();
            }
            $error = 'Unable to update the password. Please try again.';
        }
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

                        <form method="POST" class="mb-4">
                            <div class="mb-4">
                                <label class="form-label fw-bold mb-2 d-block text-start" style="color: #1F2937;">
                                    <i class="fas fa-envelope me-2"></i>Registered Email
                                </label>
                                <input type="email" name="email" class="form-control form-control-lg" value="<?= htmlspecialchars($reset_email) ?>"
                                    placeholder="staff@hotel.com" required autofocus autocomplete="email">
                            </div>
                            <button type="submit" name="request_code" class="btn btn-warning btn-lg w-100 mb-3 shadow-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Reset Code
                            </button>
                        </form>

                        <form method="POST" class="text-start">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($reset_email) ?>">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Reset Code</label>
                                <input type="text" name="reset_code" class="form-control" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">New Password</label>
                                <input type="password" name="new_password" class="form-control" minlength="8" required autocomplete="new-password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" minlength="8" required autocomplete="new-password">
                            </div>
                            <button type="submit" name="reset_password" class="btn btn-primary w-100">
                                <i class="fas fa-lock me-2"></i>Update Password
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