<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_POST) {
    $product_name = trim($_POST['product_name']);
    $quantity = (int)$_POST['quantity'];
    $supplier_id = (int)$_POST['supplier_id'];
    $price = (float)$_POST['price'];
    $image_name = trim($_POST['image_name']);
    
   $stmt = $conn->prepare("INSERT INTO products (product_name, quantity, supplier_id, price, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siids", $product_name, $quantity, $supplier_id, $price, $image_name);
    
    if ($stmt->execute()) {
        header("Location: list_products.php?success=1");
        exit();
    }
}

$suppliers = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product - SupplySync Admin</title>
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
        <h2>➕ Add New Product</h2>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">✅ Product added successfully!</div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product Name *</label>
                        <input type="text" name="product_name" class="form-control" required 
                               placeholder="Chicken Breast 5kg, Basmati Rice, etc.">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Quantity *</label>
                        <input type="number" name="quantity" class="form-control" min="0" required value="0">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Supplier *</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">Choose Supplier</option>
                            <?php while($s = $suppliers->fetch_assoc()): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['supplier_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Price (₹)</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" value="0">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label fw-bold">🖼️ Product Image</label>
                        <select name="image_name" class="form-select">
                            <option value="">No Image</option>
                            <option value="chicken.jpg">🐔 Chicken Breast</option>
                            <option value="rice.jpg">🍚 Basmati Rice</option>
                            <option value="atta.jpg">🥟 Wheat Flour (Atta)</option>
                            <option value="oil.jpg">🛢️ Cooking Oil</option>
                            <option value="saffron.jpg">🌿 Saffron</option>
                            <option value="paneer.jpg">🧀 Paneer</option>
                            <option value="veggies.jpg">🥬 Mixed Vegetables</option>
                            <option value="fruits.jpg">🍎 Fresh Fruits</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-plus me-2"></i>✅ Add Product
            </button>
            <a href="dashboard.php" class="btn btn-secondary btn-lg">← Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
