<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// SAFELY detect ONLY EXISTING columns
$columns = [];
$result = $conn->query("DESCRIBE products");
while($col = $result->fetch_assoc()) {
    $columns[] = $col['Field'];
}

// ONLY use columns that ACTUALLY exist
$idCol = in_array('id', $columns) ? 'id' : (in_array('product_id', $columns) ? 'product_id' : null);
$nameCol = (in_array('name', $columns) ? 'name' : 
           (in_array('product_name', $columns) ? 'product_name' : 
           (in_array('title', $columns) ? 'title' : null)));
$skuCol = in_array('sku', $columns) ? 'sku' : null;
$qtyCol = in_array('quantity', $columns) ? 'quantity' : null;
$priceCol = in_array('price', $columns) ? 'price' : null;
$descCol = in_array('description', $columns) ? 'description' : null;
$imageCol = (in_array('image_name', $columns) ? 'image_name' : 
            (in_array('image', $columns) ? 'image' : null));

if (!$idCol || !$nameCol || !$qtyCol || !$priceCol) {
    die("❌ Missing required columns in products table. Need: id/name/product_name/title, quantity, price");
}

// LOAD PRODUCT DATA
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE $idCol = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product) {
        header("Location: list_products.php?error=notfound");
        exit();
    }
} else {
    header("Location: list_products.php");
    exit();
}

$error = '';
$success = '';

// HANDLE FORM UPDATE
if ($_POST) {
    $updateFields = [];
    $updateParams = [];
    $types = '';
    
    // ONLY update columns that exist
    if ($nameCol && isset($_POST[$nameCol])) {
        $updateFields[] = "$nameCol = ?";
        $updateParams[] = trim($_POST[$nameCol]);
        $types .= 's';
    }
    
    if ($skuCol && isset($_POST[$skuCol])) {
        $updateFields[] = "$skuCol = ?";
        $updateParams[] = trim($_POST[$skuCol]);
        $types .= 's';
    }
    
    if ($descCol && isset($_POST['description'])) {
        $updateFields[] = "description = ?";
        $updateParams[] = trim($_POST['description']);
        $types .= 's';
    }
    
    $updateFields[] = "$qtyCol = ?";
    $updateParams[] = (int)$_POST['quantity'];
    $types .= 'i';
    
    $updateFields[] = "$priceCol = ?";
    $updateParams[] = (float)$_POST['price'];
    $types .= 'd';
    
    // Handle image ONLY if column exists
    if ($imageCol && !empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $imageName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $updateFields[] = "$imageCol = ?";
            $updateParams[] = $imageName;
            $types .= 's';
            // Delete old image
            $oldImage = $product[$imageCol] ?? '';
            if (!empty($oldImage) && file_exists($targetDir . $oldImage)) {
                unlink($targetDir . $oldImage);
            }
        }
    }
    
    $updateParams[] = $id;
    $types .= 'i';
    
    $updateQuery = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE $idCol = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param($types, ...$updateParams);
    
    if ($stmt->execute()) {
        header("Location: list_products.php?success=updated");
        exit();
    } else {
        $error = "❌ Update failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product - SupplySync Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark px-4 py-3 shadow-lg" style="background: linear-gradient(135deg, #3EB489, #22C55E);">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-4" href="dashboard.php">
                <i class="fas fa-utensils me-2"></i>SupplySync Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a href="dashboard.php" class="nav-link"><i class="fas fa-chart-bar me-1"></i>Dashboard</a>
                <a href="list_products.php" class="nav-link"><i class="fas fa-list me-1"></i>Products</a>
                <a href="../auth/logout.php" class="nav-link btn btn-outline-light ms-2">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card border-0 shadow-xl">
                    <div class="card-header bg-white border-0 py-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h3 fw-bold mb-1" style="color: #1F2937;">
                                    <i class="fas fa-edit me-2 text-primary-mint"></i>
                                    Edit Product #<?= $product[$idCol] ?>
                                </h2>
                                <p class="text-muted mb-0">
                                    <?php if($nameCol): ?>Name: <?= htmlspecialchars($product[$nameCol]) ?><?php endif; ?>
                                    <?php if($skuCol): ?> | SKU: <?= htmlspecialchars($product[$skuCol]) ?><?php endif; ?>
                                </p>
                            </div>
                            <a href="list_products.php" class="btn btn-outline-secondary shadow-sm">
                                <i class="fas fa-arrow-left me-2"></i>Back to Products
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body p-5">
                        <?php if($error): ?>
                            <div class="alert alert-danger shadow-sm mb-4"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $product[$idCol] ?>">
                            
                            <!-- NAME FIELD (if exists) -->
                            <?php if($nameCol): ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold mb-2">
                                    <i class="fas fa-box me-2 text-primary-mint"></i>Product Name
                                </label>
                                <input type="text" name="<?= $nameCol ?>" class="form-control form-control-lg" required
                                       value="<?= htmlspecialchars($product[$nameCol] ?? '') ?>">
                            </div>
                            <?php endif; ?>

                            <!-- SKU FIELD (if exists) -->
                            <?php if($skuCol): ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold mb-2">
                                    <i class="fas fa-hashtag me-2 text-info"></i>SKU
                                </label>
                                <input type="text" name="<?= $skuCol ?>" class="form-control form-control-lg"
                                       value="<?= htmlspecialchars($product[$skuCol] ?? '') ?>">
                            </div>
                            <?php endif; ?>

                            <!-- DESCRIPTION (if exists) -->
                            <?php if($descCol): ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold mb-2">
                                    <i class="fas fa-align-left me-2 text-success"></i>Description
                                </label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                            </div>
                            <?php endif; ?>

                            <!-- STOCK & PRICE -->
                            <div class="row g-4 mb-4">
                                <div class="col-lg-6">
                                    <label class="form-label fw-bold mb-2">
                                        <i class="fas fa-sort-numeric-down me-2 text-warning"></i>Stock Quantity
                                    </label>
                                    <input type="number" name="quantity" class="form-control form-control-lg" required
                                           value="<?= $product['quantity'] ?? 0 ?>" min="0">
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label fw-bold mb-2">
                                        <i class="fas fa-rupee-sign me-2 text-success"></i>Price (₹)
                                    </label>
                                    <input type="number" name="price" step="0.01" class="form-control form-control-lg" required
                                           value="<?= $product['price'] ?? 0 ?>" min="0">
                                </div>
                            </div>

                            <!-- IMAGE (if column exists) -->
                            <?php if($imageCol): ?>
                            <div class="mb-5">
                                <label class="form-label fw-bold mb-3">
                                    <i class="fas fa-image me-2 text-primary"></i>Product Image
                                </label>
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <?php if(!empty($product[$imageCol]) && file_exists('../uploads/' . $product[$imageCol])): ?>
                                            <img src="../uploads/<?= htmlspecialchars($product[$imageCol]) ?>" 
                                                 class="img-thumbnail rounded shadow-sm" style="max-height: 150px;">
                                            <input type="hidden" name="<?= $imageCol ?>" value="<?= htmlspecialchars($product[$imageCol]) ?>">
                                            <small class="text-muted d-block mt-1">Current image</small>
                                        <?php else: ?>
                                            <div class="bg-light rounded p-4 text-center">
                                                <i class="fas fa-image fa-3x text-muted"></i>
                                                <small class="text-muted">No image</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                        <small class="text-muted mt-1 d-block">Optional - leave empty to keep current</small>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- SUBMIT -->
                            <div class="d-flex gap-3 pt-3 border-top">
                                <button type="submit" class="btn btn-primary-mint btn-lg px-5 shadow-lg">
                                    <i class="fas fa-save me-2"></i>Update Product
                                </button>
                                <a href="list_products.php" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>

                        <!-- DEBUG INFO -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <small class="text-muted">
                                <strong>Detected columns:</strong> <?= implode(', ', array_slice($columns, 0, 8)) ?>...
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
