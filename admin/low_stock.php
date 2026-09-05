<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch LOW STOCK items only (quantity < 10)
$low_stock = $conn->query("
    SELECT p.*, s.supplier_name 
    FROM products p 
    LEFT JOIN suppliers s ON p.supplier_id = s.id 
    WHERE p.quantity < 10 
    ORDER BY p.quantity ASC
");

$total_low_stock = $low_stock->num_rows;
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
    <nav class="navbar navbar-dark bg-dark px-3">
        <a href="dashboard.php" class="navbar-brand">← Dashboard</a>
        <a href="list_products.php" class="btn btn-info btn-sm me-2">📋 All Products</a>
        <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🚨 Low Stock Alerts (<?= $total_low_stock ?> items)</h2>
            <a href="add_product.php" class="btn btn-success btn-lg">➕ Restock Now</a>
        </div>

        <?php if($total_low_stock > 0): ?>
            <div class="alert alert-warning">
                <strong>⚠️ <?= $total_low_stock ?> items need immediate attention!</strong>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-danger">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Current Qty</th>
                            <th>Supplier</th>
                            <th>Value Left</th>
                            <th>Action Needed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; while($row = $low_stock->fetch_assoc()): ?>
                        <tr class="table-warning">
                            <td><?= $i++ ?></td>
                            <td><strong><?= htmlspecialchars($row['product_name']) ?></strong></td>
                            <td>
                                <span class="badge bg-danger fs-5"><?= $row['quantity'] ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['supplier_name'] ?? 'No Supplier') ?></td>
                            <td>₹<?= number_format($row['quantity'] * $row['price'], 2) ?></td>
                            <td>
                                <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">🔄 Restock</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <h3>✅ All items well stocked!</h3>
                <p>Low stock threshold: <10 units</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
