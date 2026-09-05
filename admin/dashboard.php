<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// DASHBOARD COUNTERS
$productCount = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$supplierCount = $conn->query("SELECT COUNT(*) as total FROM suppliers")->fetch_assoc()['total'];
$lowStockCount = $conn->query("SELECT COUNT(*) as total FROM products WHERE quantity < 10")->fetch_assoc()['total'];
$pendingRequests = $conn->query("SELECT COUNT(*) as total FROM stock_requests WHERE status = 'pending'")->fetch_assoc()['total'];

$stats = $conn->query("
    SELECT 
        COUNT(CASE WHEN status='pending' THEN 1 END) as pending,
        COUNT(CASE WHEN status='approved' THEN 1 END) as approved,
        COUNT(CASE WHEN status='rejected' THEN 1 END) as rejected
    FROM stock_requests
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - SupplySync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark px-4 py-3 shadow-lg" style="background: linear-gradient(135deg, #3EB489, #22C55E);">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-3" href="#">
                <i class="fas fa-utensils me-2"></i>SupplySync Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a href="staff_requests.php" class="nav-link position-relative">
                    <i class="fas fa-users me-1"></i>Staff Requests 
                    <?php if($pendingRequests > 0): ?>
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle"><?= $pendingRequests ?></span>
                    <?php endif; ?>
                </a>
                <a href="../auth/logout.php" class="nav-link btn btn-outline-light ms-2">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4 px-4">
        <!-- HEADER -->
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold mb-2" style="color: #1F2937;">Admin Dashboard</h1>
            <p class="lead text-muted mb-0">Hotel Kitchen Inventory Management</p>
        </div>

        <!-- COMPACT STATISTICS CARDS -->
        <div class="row g-3 mb-4">
            <!-- SUPPLIERS -->
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <a href="view_suppliers.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-3 dashboard-stat-card" 
                         style="background: linear-gradient(135deg, #10B981, #059669); color: dark; border-radius: 16px; min-height: 120px;">
                        <i class="fas fa-truck fa-2x mb-2 opacity-75"></i>
                        <div class="h4 fw-bold mb-1"><?= $supplierCount ?></div>
                        <small class="text-dark-50 fw-semibold">Suppliers</small>
                        <div class="mt-2 small bg-light text-dark rounded-pill px-2 py-1">🏪 VENDORS</div>
                    </div>
                </a>
            </div>

        <div class="row g-3 mb-5">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <a href="add_product.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 action-card hover-lift">
                        <div class="bg-primary-mint rounded-circle mx-auto mb-3 p-3 shadow-lg" style="width: 65px; height: 65px;">
                            <i class="fas fa-plus fa-2x text-dark"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color: #1F2937;">Add Product</h6>
                        <small class="text-muted">New inventory</small>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <a href="staff_requests.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 action-card hover-lift">
                        <div class="bg-success rounded-circle mx-auto mb-3 p-3 shadow-lg" style="width: 65px; height: 65px;">
                            <i class="fas fa-users fa-2x text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color: #1F2937;">Staff Requests</h6>
                        <small class="text-muted"><?= $stats['pending'] ?> pending</small>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <a href="list_products.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 action-card hover-lift">
                        <div class="bg-info rounded-circle mx-auto mb-3 p-3 shadow-lg" style="width: 65px; height: 65px;">
                            <i class="fas fa-list fa-2x text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color: #1F2937;">View Products</h6>
                        <small class="text-muted"><?= $productCount ?> items</small>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <a href="low_stock.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 action-card hover-lift <?= $lowStockCount > 0 ? 'border-start border-danger border-4' : '' ?>">
                        <div class="bg-warning rounded-circle mx-auto mb-3 p-3 shadow-lg <?= $lowStockCount > 0 ? 'animate-pulse' : '' ?>" style="width: 65px; height: 65px;">
                            <i class="fas fa-exclamation-triangle fa-2x text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color: #1F2937;">Low Stock</h6>
                        <small class="text-muted"><?= $lowStockCount ?> items</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- RECENT ACTIVITY - COMPACT TABLE -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h3 class="mb-0 fw-bold" style="color: #1F2937;">
                            <i class="fas fa-history me-2 text-primary-mint"></i>Recent Activity
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30%">Action</th>
                                        <th width="40%">Item/Staff</th>
                                        <th width="15%">Status</th>
                                        <th width="15%">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $recent = $conn->query("
                                        SELECT 'request' as type, sr.item_name, sr.status, sr.created_at, u.name as user_name
                                        FROM stock_requests sr 
                                        LEFT JOIN users u ON sr.staff_id = u.id 
                                        ORDER BY sr.created_at DESC LIMIT 10
                                    ");
                                    if($recent && $recent->num_rows > 0):
                                        while($row = $recent->fetch_assoc()): 
                                    ?>
                                    <tr class="hover-row">
                                        <td>
                                            <span class="badge bg-primary">
                                                <i class="fas fa-user-plus me-1"></i>New Request
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['item_name']) ?></strong><br>
                                            <small class="text-muted">by <?= htmlspecialchars($row['user_name'] ?? 'Staff') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?= $row['status'] == 'approved' ? 'bg-success' : 
                                                ($row['status'] == 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                                <?= ucfirst($row['status']) ?>
                                            </span>
                                        </td>
                                        <td><small><?= date('H:i', strtotime($row['created_at'])) ?></small></td>
                                    </tr>
                                    <?php 
                                        endwhile; 
                                    else: 
                                    ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-clock fa-2x mb-2"></i><br>No recent activity
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

