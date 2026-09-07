<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];

if ($_POST) {
    $item_name = trim($_POST['item_name']);
    $quantity_needed = (int)$_POST['quantity_needed'];
    $notes = trim($_POST['notes']);
    
    if ($item_name && $quantity_needed > 0) {
        $stmt = $conn->prepare("INSERT INTO stock_requests (item_name, quantity_needed, notes, staff_id, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sisi", $item_name, $quantity_needed, $notes, $staff_id);
        $stmt->execute();
        $success = "✅ Request submitted! Waiting for admin approval.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Item - SupplySync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="dashboard.php" class="navbar-brand">← Dashboard</a>
        <div>
            <a href="view_stock.php" class="btn btn-light btn-sm me-2">📋 Stock</a>
            <a href="low_stock.php" class="btn btn-warning btn-sm me-2">🚨 Low Stock</a>
            <a href="my_requests.php" class="btn btn-info btn-sm me-2">📋 My Requests</a>
            <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>🛒 Request New Stock Item</h2>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
            <a href="my_requests.php" class="btn btn-primary">📋 View My Requests</a>
        <?php else: ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Item Name *</label>
                    <input type="text" name="item_name" class="form-control" required 
                           placeholder="Chicken Breast 5kg, Saffron 10g, etc.">
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity Needed *</label>
                    <input type="number" name="quantity_needed" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="3" 
                              placeholder="For biryani preparation, urgent, etc."></textarea>
                </div>
                <button type="submit" class="btn btn-success btn-lg">✅ Submit Request</button>
                <a href="dashboard.php" class="btn btn-secondary btn-lg">← Back to Dashboard</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
