<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

// FIRST: Check what columns actually exist
$columns = [];
$result = $conn->query("DESCRIBE products");
while($col = $result->fetch_assoc()) {
    $columns[] = $col['Field'];
}

// Detect product name column (common names)
$productNameCol = 'name';
if (!in_array('name', $columns)) {
    $productNameCol = in_array('product_name', $columns) ? 'product_name' : 
                     (in_array('title', $columns) ? 'title' : 'productname');
}
$skuCol = in_array('sku', $columns) ? 'sku' : 'product_code';
$descCol = in_array('description', $columns) ? 'description' : 'details';


// Get total count
$totalProducts = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause .= " AND ($productNameCol LIKE ? OR $skuCol LIKE ?)";
    $params = ["%$search%", "%$search%"];
    $types = "ss";
}

// Build SAFE query using detected columns
$selectCols = "*";
$query = "SELECT $selectCols FROM products WHERE $whereClause ORDER BY $productNameCol ASC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Stock - SupplySync Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark px-4 py-3 shadow-lg" style="background: linear-gradient(135deg, #3EB489, #22C55E);">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-4" href="dashboard.php">
                <i class="fas fa-utensils me-2"></i>SupplySync Staff
            </a>
            <div class="navbar-nav ms-auto">
                <a href="dashboard.php" class="nav-link"><i class="fas fa-chart-bar me-1"></i>Dashboard</a>
                <a href="request_stock.php" class="nav-link"><i class="fas fa-plus-circle me-1"></i>Request Stock</a>
                <a href="../auth/logout.php" class="nav-link btn btn-outline-light ms-2">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4 px-4">
        <!-- HEADER -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h1 class="h2 fw-bold mb-1" style="color: #1F2937;">
                            <i class="fas fa-boxes-stacked me-2 text-primary-mint"></i>Inventory Stock (<?= $totalProducts ?> items)
                        </h1>
                        <p class="text-muted mb-0">Kitchen inventory overview  </p>
                    </div>
                    <a href="request_stock.php" class="btn btn-primary-mint btn-lg shadow-lg">
                        <i class="fas fa-plus-circle me-2"></i>Request Low Stock
                    </a>
                </div>
            </div>
        </div>

        <!-- STOCK TABLE -->
        <div class="card border-0 shadow-xl">
            <div class="card-header bg-transparent border-0 pb-0">
                <h3 class="mb-0 fw-bold" style="color: #1F2937;">
                    <i class="fas fa-list me-2 text-primary-mint"></i>Current Stock Levels
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="fas fa-image me-2"></i>Image</th>
                                <th><i class="fas fa-box me-2"></i>Product</th>
                                <th><i class="fas fa-hashtag me-2"></i>SKU</th>
                                <th><i class="fas fa-sort-numeric-down-alt me-2"></i>Stock</th>
                                <th><i class="fas fa-rupee-sign me-2"></i>Price</th>
                                <th><i class="fas fa-calendar me-2"></i>Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0): ?>
                                <?php while($product = $result->fetch_assoc()): ?>
                                <tr class="<?= ($product['quantity'] ?? 0) < 10 ? 'table-danger' : 
                                            (($product['quantity'] ?? 0) < 20 ? 'table-warning' : 'table-success') ?>">
                                    
                                    <!-- SAFE IMAGE -->
                                    <td>
    <?php $imageFile = trim($product['image'] ?? ''); ?>

    <?php if(!empty($imageFile)): ?>
        <img src="/supplysync/images/<?= htmlspecialchars($imageFile) ?>"
             class="img-thumbnail rounded shadow-sm"
             style="width:70px;height:70px;object-fit:cover;"
             alt="Product Image">
    <?php else: ?>
        <img src="https://via.placeholder.com/70x70/6B7280/FFFFFF?text=No+Image"
             class="img-thumbnail rounded shadow-sm"
             style="width:70px;height:70px;object-fit:cover;">
    <?php endif; ?>
    
</td>
                                    
                                    <!-- SAFE PRODUCT NAME -->
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($product[$productNameCol] ?? 'Unknown Product') ?></div>
                                        <?php if(isset($product[$descCol])): ?>
                                            <small class="text-muted d-block"><?= htmlspecialchars($product[$descCol]) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- SAFE SKU -->
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded">
                                            <?= htmlspecialchars($product[$skuCol] ?? 'N/A') ?>
                                        </code>
                                    </td>
                                    
                                    <!-- STOCK STATUS -->
                                    <td>
                                        <?php $qty = $product['quantity'] ?? 0; ?>
                                        <?php if($qty < 10): ?>
                                            <span class="badge bg-danger fs-6 fw-bold animate-pulse">
                                                <i class="fas fa-exclamation-triangle me-1"></i><?= $qty ?>
                                            </span>
                                            <div class="mt-1"><small class="text-danger">CRITICAL</small></div>
                                        <?php elseif($qty < 20): ?>
                                            <span class="badge bg-warning fs-6 fw-bold">
                                                <i class="fas fa-clock me-1"></i><?= $qty ?>
                                            </span>
                                            <div class="mt-1"><small class="text-warning">LOW</small></div>
                                        <?php else: ?>
                                            <span class="badge bg-success fs-6 fw-bold">
                                                <i class="fas fa-check-circle me-1"></i><?= $qty ?>
                                            </span>
                                            <div class="mt-1"><small class="text-success">GOOD</small></div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- PRICE -->
                                    <td>
                                        <strong class="text-primary">
                                            ₹<?= number_format($product['price'] ?? 0, 2) ?>
                                        </strong>
                                    </td>
                                    
                                    <!-- DATE -->
                                    <td>
                                        <small class="text-muted">
                                            <?= isset($product['created_at']) ? date('d M Y', strtotime($product['created_at'])) : 'N/A' ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted">No products found</h4>
                                        <p class="text-muted">Database empty or no matching results</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        

    <style>
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
