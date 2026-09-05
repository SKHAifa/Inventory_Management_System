<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$low_stock = $conn->query("
    SELECT p.*, s.supplier_name 
    FROM products p 
    LEFT JOIN suppliers s ON p.supplier_id = s.id 
    WHERE p.quantity < 10 
    ORDER BY p.quantity ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Low Stock Alerts - SupplySync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="dashboard.php" class="navbar-brand">← Dashboard</a>
        <div>
            <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>🚨 LOW STOCK ALERTS (<?= $low_stock->num_rows ?>)</h2>
        
        <?php if($low_stock->num_rows == 0): ?>
            <div class="alert alert-success">
                ✅ All stock levels are good! Kitchen fully stocked.
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                ⚠️ <?= $low_stock->num_rows ?> items need attention!
            </div>
            
            <div class="table-responsive">
                <table class="table table-danger table-striped">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Supplier</th>
                            <th>Current Qty</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $low_stock->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['product_name']) ?></strong></td>
                            <td><?= htmlspecialchars($row['supplier_name'] ?: 'Not assigned') ?></td>
                            <td><span class="badge bg-danger fs-6"><?= $row['quantity'] ?></span></td>
                            <td><span class="badge bg-danger">CRITICAL</span></td>
                            <td>
                                <a href="request_item.php" class="btn btn-warning btn-sm">
                                    🛒 Request Replenishment
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
