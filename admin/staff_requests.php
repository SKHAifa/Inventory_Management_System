<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$requests = $conn->query("
    SELECT sr.*, u.name as staff_name 
    FROM stock_requests sr 
    JOIN users u ON sr.staff_id = u.id 
    ORDER BY sr.created_at DESC
");

// ACCEPT REQUEST - Add to Products
if (isset($_POST['accept'])) {
    $item_name = $_POST['item_name'];
    $quantity = (int)$_POST['quantity_needed'];
    $supplier_id = (int)$_POST['supplier_id']; // CHOOSE SUPPLIER!
    
    $stmt = $conn->prepare("INSERT INTO products (product_name, quantity, supplier_id, price) VALUES (?, ?, ?, 0.00)");
    $stmt->bind_param("sii", $item_name, $quantity, $supplier_id);
    $stmt->execute();
    
    $request_id = $_POST['request_id'];
    $conn->query("UPDATE stock_requests SET status = 'approved' WHERE id = $request_id");
    header("Location: staff_requests.php?success=1");
    exit();
}

// DECLINE REQUEST
if (isset($_POST['decline'])) {
    $request_id = $_POST['request_id'];
    $conn->query("UPDATE stock_requests SET status = 'rejected' WHERE id = $request_id");
    header("Location: staff_requests.php?declined=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Requests - SupplySync</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<body>
    <nav class="navbar navbar-dark bg-dark px-3">
        <a href="dashboard.php" class="navbar-brand">← Dashboard</a>
        <a href="list_products.php" class="btn btn-info btn-sm me-2">📋 Products</a>
        <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
    </nav>

    <div class="container mt-4">
        <h2>📋 Staff Stock Requests (<?= $requests->num_rows ?>)</h2>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">✅ Request APPROVED & product added to inventory!</div>
        <?php endif; ?>
        
        <?php if(isset($_GET['declined'])): ?>
            <div class="alert alert-warning">❌ Request DECLINED successfully!</div>
        <?php endif; ?>
        
        <?php if($requests->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-warning">
                        <tr>
                            <th>Staff</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Notes</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $requests->fetch_assoc()): ?>
                        <tr class="<?= $row['status'] == 'approved' ? 'table-success' : ($row['status'] == 'rejected' ? 'table-danger' : 'table-warning') ?>">
                            <td><strong><?= htmlspecialchars($row['staff_name']) ?></strong></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><span class="badge bg-primary"><?= $row['quantity_needed'] ?></span></td>
                            <td><?= htmlspecialchars($row['notes'] ?: 'No notes') ?></td>
                            <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <?php if($row['status'] == 'approved'): ?>
                                    <span class="badge bg-success">✅ Approved</span>
                                <?php elseif($row['status'] == 'rejected'): ?>
                                    <span class="badge bg-danger">❌ Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">⏳ Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['status'] == 'pending'): ?>
                                    <div class="btn-group" role="group">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="item_name" value="<?= htmlspecialchars($row['item_name']) ?>">
                                            <input type="hidden" name="quantity_needed" value="<?= $row['quantity_needed'] ?>">
                                            <select name="supplier_id" class="form-select form-select-sm d-none">
                                                <?php
                                                $suppliers = $conn->query("SELECT id, supplier_name FROM suppliers");
                                                while($s = $suppliers->fetch_assoc()) {
                                                    echo "<option value='{$s['id']}'>{$s['supplier_name']}</option>";
                                                }
                                                ?>
                                            </select>
                                            <button type="submit" name="accept" class="btn btn-success btn-sm" 
                                                    onclick="return confirm('✅ APPROVE & Add <?= htmlspecialchars($row['item_name']) ?> to inventory?')">
                                                ✅ Accept
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                            <button type="submit" name="decline" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('❌ DECLINE this request?')">
                                                ❌ Decline
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Action completed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No pending requests. Kitchen is fully stocked! 😊</div>
        <?php endif; ?>
    </div>
</body>
</html>

