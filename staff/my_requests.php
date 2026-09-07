<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];

// Fetch ALL staff requests with status and supplier info
$requests = $conn->query("
    SELECT 
        sr.id, sr.item_name, sr.quantity_needed, sr.notes, sr.status, sr.created_at,
        u.name as staff_name,
        CASE 
            WHEN sr.status = 'pending' THEN '⏳ Pending - Awaiting Admin Approval'
            WHEN sr.status = 'approved' THEN '✅ APPROVED & Added to Inventory'
            WHEN sr.status = 'rejected' THEN '❌ REJECTED by Admin'
        END as status_display
    FROM stock_requests sr 
    JOIN users u ON sr.staff_id = u.id 
    WHERE sr.staff_id = $staff_id 
    ORDER BY sr.created_at DESC
");

// Count status summary
$stats = $conn->query("
    SELECT 
        COUNT(CASE WHEN status='pending' THEN 1 END) as pending,
        COUNT(CASE WHEN status='approved' THEN 1 END) as approved,
        COUNT(CASE WHEN status='rejected' THEN 1 END) as rejected
    FROM stock_requests WHERE staff_id = $staff_id
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - SupplySync Kitchen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3 sticky-top">
        <div class="container-fluid">
            <a href="dashboard.php" class="navbar-brand">
                <i class="fas fa-utensils me-2"></i>SupplySync Kitchen
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_stock.php"><i class="fas fa-boxes me-1"></i>Stock</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="low_stock.php"><i class="fas fa-exclamation-triangle me-1"></i>Low Stock</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="my_requests.php" class="btn btn-info btn-sm me-2 active">
                        <i class="fas fa-list me-1"></i>My Requests
                    </a>
                    <a href="request_item.php" class="btn btn-success btn-sm me-2">
                        <i class="fas fa-plus me-1"></i>New Request
                    </a>
                    <a href="../auth/logout.php" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- STATUS SUMMARY CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-warning text-dark">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x mb-2 opacity-75"></i>
                        <h3 class="mb-0"><?= $stats['pending'] ?></h3>
                        <h6 class="mb-0">Pending Requests</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h3 class="mb-0"><?= $stats['approved'] ?></h3>
                        <h6 class="mb-0">Approved</h6>
                        <?php if($stats['approved'] > 0): ?>
                            <small class="opacity-75">✓ Now in Stock!</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-danger text-dark">
                    <div class="card-body text-center">
                        <i class="fas fa-times-circle fa-2x mb-2 opacity-75"></i>
                        <h3 class="mb-0"><?= $stats['rejected'] ?></h3>
                        <h6 class="mb-0">Rejected</h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-list text-primary me-2"></i>
                My Stock Requests (<?= $requests->num_rows ?> Total)
            </h2>
            <a href="request_item.php" class="btn btn-success btn-lg">
                <i class="fas fa-plus me-2"></i>New Request
            </a>
        </div>

        <?php if($requests->num_rows == 0): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                <h4>No requests yet</h4>
                <p class="mb-0">Your request history is empty. 
                    <a href="request_item.php" class="alert-link fw-bold">Make your first request →</a>
                </p>
            </div>
        <?php else: ?>
            <!-- REQUESTS TABLE -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="200">Item Requested</th>
                            <th width="100">Quantity</th>
                            <th width="200">Notes</th>
                            <th width="150">Date & Time</th>
                            <th width="200">Status</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $requests->fetch_assoc()): ?>
                        <tr class="table-group-divider">
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($row['item_name']) ?></strong>
                                <br><small class="text-muted">by <?= htmlspecialchars($row['staff_name']) ?></small>
                            </td>
                            <td>
                                <span class="badge fs-6 bg-primary"><?= $row['quantity_needed'] ?> units</span>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['notes'] ?: '<span class="text-muted">No notes provided</span>') ?>
                            </td>
                            <td>
                                <strong><?= date('M j, Y', strtotime($row['created_at'])) ?></strong>
                                <br><small class="text-muted"><?= date('g:i A', strtotime($row['created_at'])) ?></small>
                            </td>
                            <td>
                                <?php 
                                $status_class = $row['status'] == 'approved' ? 'bg-success' : 
                                               ($row['status'] == 'rejected' ? 'bg-danger' : 'bg-warning text-dark');
                                ?>
                                <span class="badge <?= $status_class ?> fs-6 px-3 py-2">
                                    <?= $row['status_display'] ?>
                                </span>
                                <?php if($row['status'] == 'approved'): ?>
                                    <div class="mt-1">
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        <small class="text-success fw-bold">Now available in Stock!</small>
                                    </div>
                                <?php elseif($row['status'] == 'rejected'): ?>
                                    <div class="mt-1">
                                        <i class="fas fa-info-circle text-warning me-1"></i>
                                        <small class="text-muted">Contact Admin for details</small>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['status'] == 'pending'): ?>
                                    <div class="text-center">
                                        <div class="spinner-border spinner-border-sm text-warning mb-1" role="status">
                                            <span class="visually-hidden">Pending...</span>
                                        </div>
                                        <small class="text-muted">Waiting...</small>
                                    </div>
                                <?php else: ?>
                                    <a href="request_item.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i>New Request
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

