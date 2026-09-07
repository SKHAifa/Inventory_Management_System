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

        table,
        th,
        td {
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

        body {
            background: #f6f8f7;
            color: #1f2933;
            font-family: "Segoe UI", Tahoma, sans-serif;
        }

        .container {
            max-width: 1100px;
        }

        h2 {
            color: #1f2933;
            margin-bottom: 24px;
        }

        table {
            border: 1px solid #d9e2de;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(31, 41, 51, 0.08);
            overflow: hidden;
        }

        th {
            background: #eef3f1;
            color: #1f2933;
            font-weight: 600;
        }

        td {
            color: #344054;
        }

        a,
        button {
            border: 1px solid #167c66;
            border-radius: 6px;
            padding: 6px 10px;
            background: #167c66;
            color: white;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
        }

        a:hover,
        button:hover {
            background: #115e50;
            color: white;
        }

        form {
            margin-left: 6px;
        }

        @media (max-width: 768px) {
            .container {
                width: 100%;
                margin: 20px auto;
                overflow-x: auto;
            }

            table {
                min-width: 760px;
            }
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
                <th>Actions</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . (int)$row['id'] . "</td>
                        <td>" . htmlspecialchars($row['supplier_name']) . "</td>
                        <td>" . htmlspecialchars($row['phone'] ?? '') . "</td>
                        <td>" . htmlspecialchars($row['email'] ?? '') . "</td>
                        <td>" . htmlspecialchars($row['address'] ?? '') . "</td>
                        <td>
                            <a href=\"edit_supplier.php?id=" . (int)$row['id'] . "\">Update</a>
                            <form method=\"POST\" action=\"delete_supplier.php\" style=\"display:inline;\" onsubmit=\"return confirm('Remove this supplier?');\">
                                <input type=\"hidden\" name=\"id\" value=\"" . (int)$row['id'] . "\">
                                <button type=\"submit\" name=\"delete_supplier\">Remove</button>
                            </form>
                        </td>
                      </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No suppliers found</td></tr>";
            }
            ?>
        </table>
    </div>

</body>

</html>

<?php
$conn->close();
?>