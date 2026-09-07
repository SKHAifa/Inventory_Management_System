<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Total inventory value
$total_value_query = $conn->query("SELECT SUM(quantity * price) as total FROM products");
$total_value = $total_value_query->fetch_assoc()['total'] ?? 0;

// Low stock count
$low_stock_count = $conn->query("SELECT COUNT(*) as total FROM products WHERE quantity < 10")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Reports - SupplySync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-dark bg-dark px-3">
        <a href="dashboard.php" class="navbar-brand">← Dashboard</a>
        <a href="list_products.php" class="btn btn-info btn-sm me-2">📋 Products</a>
        <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
    </nav>

    <div class="container mt-4">
        <h2>📊 Inventory Reports</h2>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-bg-primary">
                    <div class="card-body">
                        <h5>Total Inventory Value</h5>
                        <h3>₹<?= number_format($total_value, 2) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-warning">
                    <div class="card-body">
                        <h5>Low Stock Items</h5>
                        <h3><?= $low_stock_count ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-success">
                    <div class="card-body">
                        <h5>Total Products</h5>
                        <h3><?= $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'] ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs" id="reportTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#inventory">Inventory Report</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#supplier">Supplier Report</a>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Inventory Report Tab -->
            <div class="tab-pane fade show active" id="inventory">
                <table class="table table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <?php
                    $products = $conn->query("SELECT p.*, s.supplier_name FROM products p LEFT JOIN suppliers s ON p.supplier_id = s.id ORDER BY p.quantity * p.price DESC");
                    while ($row = $products->fetch_assoc()):
                    ?>
                        <tbody>
                            <tr>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><span class="badge <?= $row['quantity'] < 10 ? 'bg-danger' : 'bg-success' ?>"><?= $row['quantity'] ?></span></td>
                                <td>₹<?= number_format($row['price'], 2) ?></td>
                                <td><strong>₹<?= number_format($row['quantity'] * $row['price'], 2) ?></strong></td>
                                <td>
                                    <?php if ($row['quantity'] < 10): ?>
                                        <span class="badge bg-warning">⚠️ Low Stock</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">✅ Good</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- Supplier Report Tab -->
            <div class="tab-pane fade" id="supplier">
                <table class="table table-striped">
                    <thead class="table-info">
                        <tr>
                            <th>Supplier</th>
                            <th>Total Products</th>
                            <th>Total Quantity</th>
                            <th>Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $suppliers = $conn->query("
                            SELECT s.supplier_name, 
                                   COUNT(p.id) as product_count,
                                   SUM(p.quantity) as total_qty,
                                   SUM(p.quantity * p.price) as total_value
                            FROM suppliers s 
                            LEFT JOIN products p ON s.id = p.supplier_id 
                            GROUP BY s.id
                        ");
                        while ($row = $suppliers->fetch_assoc()):
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['supplier_name']) ?></strong></td>
                                <td><?= $row['product_count'] ?></td>
                                <td><?= $row['total_qty'] ?></td>
                                <td><strong>₹<?= number_format($row['total_value'] ?? 0, 2) ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>