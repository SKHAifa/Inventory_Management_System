<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$sql = "SELECT * FROM suppliers";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Suppliers</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f4f6f9;
        }
        .container {
            width: 90%;
            margin: 40px auto;
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th {
            background-color: #178422;
            color: white;
            padding: 10px;
        }
        td {
            padding: 8px;
            text-align: center;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Supplier List</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Supplier Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Address</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>".$row['id']."</td>
                        <td>".$row['supplier_name']."</td>
                        <td>".$row['phone']."</td>
                        <td>".$row['email']."</td>
                        <td>".$row['address']."</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No suppliers found</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>

<?php
$conn->close();
?>