<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id < 1) {
    header("Location: view_suppliers.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['supplier_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') {
        $error = 'Supplier name is required.';
    } else {
        $stmt = $conn->prepare("UPDATE suppliers SET supplier_name = ?, phone = ?, email = ?, address = ? WHERE id = ?");
        $stmt->bind_param('ssssi', $name, $phone, $email, $address, $id);
        if ($stmt->execute()) {
            header("Location: view_suppliers.php?updated=1");
            exit();
        }
        $error = 'Unable to update supplier.';
    }
}

$stmt = $conn->prepare("SELECT supplier_name, phone, email, address FROM suppliers WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$supplier = $stmt->get_result()->fetch_assoc();
if (!$supplier) {
    header("Location: view_suppliers.php");
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Update Supplier - SupplySync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-dark bg-dark px-3">
        <a href="view_suppliers.php" class="navbar-brand">SupplySync Suppliers</a>
        <a href="dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
    </nav>
    <div class="container mt-4" style="max-width: 700px;">
        <h2>Update Supplier</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $id ?>">
            <div class="mb-3"><label class="form-label">Supplier Name</label><input type="text" name="supplier_name" class="form-control" value="<?= htmlspecialchars($supplier['supplier_name']) ?>" required></div>
            <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($supplier['phone'] ?? '') ?>"></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($supplier['email'] ?? '') ?>"></div>
            <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control"><?= htmlspecialchars($supplier['address'] ?? '') ?></textarea></div>
            <button type="submit" class="btn btn-primary">Update Supplier</button>
            <a href="view_suppliers.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>

</html>