<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];

// LIVE COUNTERS for Staff
$total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$low_stock = $conn->query("SELECT COUNT(*) as total FROM products WHERE quantity < 10")->fetch_assoc()['total'];
$pending_requests = $conn->query("SELECT COUNT(*) as total FROM stock_requests WHERE staff_id = $staff_id AND status = 'pending'")->fetch_assoc()['total'];

// Recent Activity: last 5 staff requests
$recent_activity = $conn->query("
    SELECT item_name, quantity_needed, status, created_at 
    FROM stock_requests 
    WHERE staff_id = $staff_id 
    ORDER BY created_at DESC 
    LIMIT 5
");
?>


<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard - SupplySync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- NAVBAR (only Logout) -->
    <nav class="navbar navbar-dark bg-primary px-3">
        <a href="#" class="navbar-brand">🏨 SupplySync Kitchen </a>
        <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">👨‍🍳 Kitchen Dashboard</h2>

        <!-- CLICKABLE DASHBOARD CARDS -->
        <div class="row g-4 mb-5">

            <!-- 1. View Stock -->
            <div class="col-md-4">
                <a href="view_stock.php" class="text-decoration-none">
                    <div class="card text-dark bg-info shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">📦 View Stock</h5>
                            <h2><?= $total_products ?></h2>
                            <small class="opacity-75">Click to view full inventory</small>
                        </div>
                    </div>
                </a>
            </div>

            <!-- 2. Low Stock -->
            <div class="col-md-4">
                <a href="low_stock.php" class="text-decoration-none">
                    <div class="card text-white <?= $low_stock > 0 ? 'bg-danger' : 'bg-success' ?> shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">🚨 Low Stock</h5>
                            <h2><?= $low_stock ?></h2>
                            <?php if ($low_stock > 0): ?>
                                <small class="opacity-75 mt-2 d-block">Click to view critical items</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>

            <!-- 3. My Requests -->
            <div class="col-md-4">
                <a href="my_requests.php" class="text-decoration-none">
                    <div class="card text-white bg-warning shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">📋 My Requests</h5>
                            <h2><?= $pending_requests ?></h2>
                            <?php if ($pending_requests > 0): ?>
                                <small class="opacity-75 mt-2 d-block">Click to check status</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- QUICK ACTION BUTTON -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <a href="request_item.php" class="btn btn-success btn-lg">
                    <i class="fas fa-plus me-2"></i>Request New Item
                </a>
            </div>
        </div>

        <!-- RECENT ACTIVITY (like admin dashboard) -->
        <div class="card border-0 shadow-lg mb-4">
            <div class="card-header bg-light border-0">
                <h4 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Recent Activity</h4>
            </div>
            <div class="card-body p-0">
                <?php if ($recent_activity->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($act = $recent_activity->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($act['item_name']) ?></td>
                                    <td><span class="badge bg-primary"><?= $act['quantity_needed'] ?></span></td>
                                    <td>
                                        <span class="badge
                                            <?= $act['status'] == 'approved' ? 'bg-success' :
                                               ($act['status'] == 'rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>"
                                        >
                                            <?= $act['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y, H:i', strtotime($act['created_at'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted p-4">
                        <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i><br>
                        No recent activity found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
