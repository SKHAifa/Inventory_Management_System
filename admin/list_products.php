<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Auto-detect ID column name
    $idCol = $conn->query("SHOW COLUMNS FROM products LIKE 'id'")->num_rows ? 'id' : 'product_id';
    
    $stmt = $conn->prepare("DELETE FROM products WHERE $idCol = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: list_products.php?success=deleted");
    exit();
}

// HANDLE SEARCH & PAGINATION
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Detect columns
$columns = [];
$result = $conn->query("DESCRIBE products");
while($col = $result->fetch_assoc()) $columns[] = $col['Field'];

$productNameCol = in_array('name', $columns) ? 'name' : 
                 (in_array('product_name', $columns) ? 'product_name' : 'title');
$skuCol = in_array('sku', $columns) ? 'sku' : 'product_code';
$imageCol = in_array('image_name', $columns) ? 'image_name' : 
           (in_array('image', $columns) ? 'image' : 'product_image');

// Build search query
$whereClause = "1=1";
$params = [];
$types = "";
if (!empty($search)) {
    $whereClause .= " AND ($productNameCol LIKE ? OR $skuCol LIKE ?)";
    $params = ["%$search%", "%$search%"];
    $types = "ss";
}

$totalQuery = "SELECT COUNT(*) as total FROM products WHERE $whereClause";
if (!empty($params)) {
    $stmt = $conn->prepare($totalQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $totalProducts = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $totalProducts = $conn->query($totalQuery)->fetch_assoc()['total'];
}

$totalPages = ceil($totalProducts / $limit);

// Main query
$query = "SELECT * FROM products WHERE $whereClause ORDER BY $productNameCol LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

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
    <title>Products List - SupplySync Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark px-4 py-3 shadow-lg" style="background: linear-gradient(135deg, #3EB489, #22C55E);">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-4" href="dashboard.php">
                <i class="fas fa-utensils me-2"></i>SupplySync Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a href="dashboard.php" class="nav-link"><i class="fas fa-chart-bar me-1"></i>Dashboard</a>
                <a href="add_product.php" class="nav-link"><i class="fas fa-plus-circle me-1"></i>Add Product</a>
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
                            <i class="fas fa-boxes-stacked me-2 text-primary-mint"></i>
                            Products List (<?= $totalProducts ?> total)
                        </h1>
                        <p class="text-muted mb-0">Complete inventory management</p>
                    </div>
                    <a href="add_product.php" class="btn btn-primary-mint btn-lg shadow-lg">
                        <i class="fas fa-plus-circle me-2"></i>Add New Product
                    </a>
                </div>
                
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success shadow-sm mt-3">
                        ✅ Product <?= $_GET['success'] == 'deleted' ? 'deleted successfully!' : 'updated!' ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SEARCH & FILTERS -->
        <div class="card border-0 shadow-lg mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Search Products</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Rice, SKU-001, Basmati..." 
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary-mint w-100">
                            <i class="fas fa-magnifying-glass me-2"></i>Search Products
                        </button>
                    </div>
                    <div class="col-md-4">
                        <?php if($search): ?>
                            <a href="list_products.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>Clear (<?= $totalProducts ?> found)
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- PRODUCTS TABLE -->
        <div class="card border-0 shadow-xl">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-0 pb-0">
                <h3 class="mb-0 fw-bold" style="color: #1F2937;">
                    <i class="fas fa-list me-2 text-primary-mint"></i>All Products
                </h3>
                <div class="btn-group">
                    <span class="badge bg-light text-dark"><?= $result->num_rows ?> shown</span>
                    <span class="badge bg-secondary"><?= $page ?> of <?= $totalPages ?></span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="fas fa-image me-2"></i>Image</th>
                                <th><i class="fas fa-box me-2"></i>Product</th>
                                <th><i class="fas fa-hashtag me-2"></i>SKU</th>
                                <th><i class="fas fa-sort-numeric-down me-2"></i>Stock</th>
                                <th><i class="fas fa-rupee-sign me-2"></i>Price</th>
                                <th><i class="fas fa-calendar me-2"></i>Added</th>
                                <th><i class="fas fa-cogs me-2"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0): ?>
                                <?php while($product = $result->fetch_assoc()): ?>
                                <?php 
                                $idCol = $conn->query("SHOW COLUMNS FROM products LIKE 'id'")->num_rows ? 'id' : 'product_id';
                                $safeImage = $product[$imageCol] ?? null;
                                $imagePath = (!empty($safeImage) && file_exists('../uploads/' . $safeImage)) 
                                    ? '../uploads/' . htmlspecialchars($safeImage) 
                                    : 'https://via.placeholder.com/70x70/6B7280/FFFFFF?text=No+Image';
                                ?>
                                <tr class="<?= ($product['quantity'] ?? 0) < 10 ? 'table-danger' : 
                                            (($product['quantity'] ?? 0) < 20 ? 'table-warning' : '') ?>">
                                    
                                    <!-- SAFE IMAGE -->
                                    <td>
                                        <img src="<?= $imagePath ?>" class="img-thumbnail rounded shadow-sm" 
                                             alt="<?= htmlspecialchars($product[$productNameCol] ?? 'Product') ?>" 
                                             style="width: 70px; height: 70px; object-fit: cover;">
                                    </td>
                                    
                                    <!-- PRODUCT NAME -->
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($product[$productNameCol] ?? 'Unknown') ?></div>
                                        <?php if(isset($product['description'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars($product['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- SKU -->
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded fs-6">
                                            <?= htmlspecialchars($product[$skuCol] ?? 'N/A') ?>
                                        </code>
                                    </td>
                                    
                                    <!-- STOCK -->
                                    <td>
                                        <?php $qty = $product['quantity'] ?? 0; ?>
                                        <?php if($qty < 10): ?>
                                            <span class="badge bg-danger fs-6 fw-bold">
                                                <i class="fas fa-exclamation-triangle me-1"></i><?= $qty ?>
                                            </span>
                                        <?php elseif($qty < 20): ?>
                                            <span class="badge bg-warning fs-6"><?= $qty ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success fs-6"><?= $qty ?></span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- PRICE -->
                                    <td>
                                        <strong class="text-primary fs-5">
                                            ₹<?= number_format($product['price'] ?? 0, 2) ?>
                                        </strong>
                                    </td>
                                    
                                    <!-- DATE -->
                                    <td>
                                        <small class="text-muted">
                                            <?= isset($product['created_at']) ? date('d M Y', strtotime($product['created_at'])) : 'N/A' ?>
                                        </small>
                                    </td>
                                    
                                    <!-- ACTIONS -->
                                    <td>
                                        <div class="btn-group" role="group">
                                             <a href="edit_products.php?id=<?= $product[$idCol] ?>" 
                                             class="btn btn-sm btn-outline-primary" title="Edit">
                                               <i class="fas fa-edit"></i>
                                             </a>
                                             <a href="list_products.php?delete=<?= $product[$idCol] ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Delete <?= htmlspecialchars($product[$productNameCol] ?? 'this product') ?>?')"
                                               title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted">No products found</h4>
                                        <?php if($search): ?>
                                            <p class="text-muted">"<?= htmlspecialchars($search) ?>" not found</p>
                                        <?php endif; ?>
                                        <a href="add_product.php" class="btn btn-primary-mint mt-3">Add First Product</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <?php if($totalPages > 1): ?>
                <nav class="d-flex justify-content-center mt-4">
                    <ul class="pagination">
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
