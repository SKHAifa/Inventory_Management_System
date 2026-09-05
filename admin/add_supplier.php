<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Insert supplier
if (isset($_POST['add_supplier'])) {

    $name = $_POST['supplier_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    $sql = "INSERT INTO suppliers (supplier_name, phone, email, address) 
            VALUES ('$name', '$phone', '$email', '$address')";

    if ($conn->query($sql)) {
        echo "<div class='alert alert-success'>Supplier Added Successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Supplier - SupplySync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-3">
    <span class="navbar-brand">SupplySync Admin</span>
    <a href="dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
</nav>

<div class="container mt-4">
    <h2>Add Supplier</h2>

    <form method="POST" class="mt-3">

        <div class="mb-3">
            <label class="form-label">Supplier Name</label>
            <input type="text" name="supplier_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control"></textarea>
        </div>

        <button type="submit" name="add_supplier" class="btn btn-primary">
            Add Supplier
        </button>

    </form>
</div>

</body>
</html>