<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header("Location: list_products.php");
    exit();
}

// Delete IMMEDIATELY - no extra confirmation page needed
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: list_products.php?deleted=1");
} else {
    header("Location: list_products.php?error=1");
}
exit();
?>
