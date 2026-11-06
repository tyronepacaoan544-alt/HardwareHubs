<?php

session_start();

if (!isset($_SESSION["UserID"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/db.php";


/* =========================
   TOTAL SALES
========================= */

$sql = "SELECT COALESCE(SUM(TotalAmount), 0) AS TotalSales
        FROM orders";

$result = $conn->query($sql);

$totalSales = 0;

if ($result) {
    $data = $result->fetch_assoc();
    $totalSales = (float)$data["TotalSales"];
}


/* =========================
   TOTAL ORDERS
========================= */

$sql = "SELECT COUNT(*) AS TotalOrders
        FROM orders";

$result = $conn->query($sql);

$totalOrders = 0;

if ($result) {
    $data = $result->fetch_assoc();
    $totalOrders = (int)$data["TotalOrders"];
}


/* =========================
   TOTAL CUSTOMERS
========================= */

$sql = "SELECT COUNT(*) AS TotalCustomers
        FROM customers";

$result = $conn->query($sql);

$totalCustomers = 0;

if ($result) {
    $data = $result->fetch_assoc();
    $totalCustomers = (int)$data["TotalCustomers"];
}


/* =========================
   TOTAL PRODUCTS
========================= */

$sql = "SELECT COUNT(*) AS TotalProducts
        FROM products";

$result = $conn->query($sql);

$totalProducts = 0;

if ($result) {
    $data = $result->fetch_assoc();
    $totalProducts = (int)$data["TotalProducts"];
}


/* =========================
   TOTAL STOCK
========================= */

$sql = "SELECT COALESCE(SUM(Stock), 0) AS TotalStock
        FROM products";

$result = $conn->query($sql);

$totalStock = 0;

if ($result) {
    $data = $result->fetch_assoc();
    $totalStock = (int)$data["TotalStock"];
}


/* =========================
   LOW STOCK PRODUCTS
========================= */

$sql = "SELECT
            ProductID,
            ProductName,
            Category,
            Price,
            Stock,
            Status
        FROM products
        WHERE Stock <= 5
        ORDER BY Stock ASC, ProductName ASC";

$lowStockResult = $conn->query($sql);


/* =========================
   RECENT ORDERS
========================= */

$sql = "SELECT
            o.OrderID,
            o.OrderDate,
            o.TotalAmount,
            o.Payment,
            o.ChangeAmount,
            c.CustomerName
        FROM orders o
        INNER JOIN customers c
            ON o.CustomerID = c.CustomerID
        ORDER BY o.OrderID DESC
        LIMIT 10";

$recentOrdersResult = $conn->query($sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Reports - HardwareHubs
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background-color: #f1f3f5;
            font-family: Arial, sans-serif;
        }

        .navbar-brand {
            font-weight: bold;
        }

        .page-title {
            font-weight: bold;
        }

        .report-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            height: 100%;
        }

        .stat-number {
            font-size: 30px;
            font-weight: bold;
        }

        .stat-label {
            color: #6c757d;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
        }

        .table th {
            background-color: #212529;
            color: white;
        }

        .section-title {
            font-weight: bold;
        }

    </style>

</head>

<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a
            class="navbar-brand"
            href="../dashboard.php"
        >
            HardwareHubs
        </a>


        <div class="d-flex align-items-center">

            <span class="text-white me-3">

                Welcome,
                <?php
                echo htmlspecialchars($_SESSION["Username"]);
                ?>

            </span>


            <a
                href="../logout.php"
                class="btn btn-danger btn-sm"
            >
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- =========================
     MAIN CONTENT
========================= -->

<div class="container py-5">


    <!-- HEADER -->

    <div class="mb-4">

        <h1 class="page-title">
            Reports
        </h1>

        <p class="text-muted">
            View HardwareHubs sales, orders, customers, and inventory summaries.
        </p>

    </div>


    <!-- =========================
         SUMMARY CARDS
    ========================= -->

    <div class="row g-4 mb-4">


        <!-- TOTAL SALES -->

        <div class="col-md-6 col-lg-3">

            <div class="report-card shadow-sm">

                <div class="stat-label">
                    Total Sales
                </div>

                <div class="stat-number text-success">

                    ₱<?php
                    echo number_format(
                        $totalSales,
                        2
                    );
                    ?>

                </div>

            </div>

        </div>


        <!-- TOTAL ORDERS -->

        <div class="col-md-6 col-lg-3">

            <div class="report-card shadow-sm">

                <div class="stat-label">
                    Total Orders
                </div>

                <div class="stat-number text-primary">

                    <?php
                    echo number_format($totalOrders);
                    ?>

                </div>

            </div>

        </div>


        <!-- CUSTOMERS -->

        <div class="col-md-6 col-lg-3">

            <div class="report-card shadow-sm">

                <div class="stat-label">
                    Total Customers
                </div>

                <div class="stat-number text-info">

                    <?php
                    echo number_format($totalCustomers);
                    ?>

                </div>

            </div>

        </div>


        <!-- PRODUCTS -->

        <div class="col-md-6 col-lg-3">

            <div class="report-card shadow-sm">

                <div class="stat-label">
                    Total Products
                </div>

                <div class="stat-number text-warning">

                    <?php
                    echo number_format($totalProducts);
                    ?>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================
         INVENTORY SUMMARY
    ========================= -->

    <div class="row g-4 mb-4">

        <div class="col-md-4">

            <div class="report-card shadow-sm">

                <div class="stat-label">
                    Total Product Stock
                </div>

                <div class="stat-number">

                    <?php
                    echo number_format($totalStock);
                    ?>

                </div>

                <small class="text-muted">
                    Total quantity currently in inventory.
                </small>

            </div>

        </div>

    </div>


    <!-- =========================
         RECENT ORDERS
    ========================= -->

    <div class="table-container shadow-sm mb-4">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h4 class="section-title mb-0">
                Recent Orders
            </h4>

            <a
                href="../orders/index.php"
                class="btn btn-primary btn-sm"
            >
                View All Orders
            </a>

        </div>


        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead>

                    <tr>

                        <th>
                            Order ID
                        </th>

                        <th>
                            Customer
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Total Amount
                        </th>

                        <th>
                            Payment
                        </th>

                        <th>
                            Change
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (
                        $recentOrdersResult &&
                        $recentOrdersResult->num_rows > 0
                    ): ?>

                        <?php while (
                            $order = $recentOrdersResult->fetch_assoc()
                        ): ?>

                            <tr>

                                <td>

                                    #<?php
                                    echo $order["OrderID"];
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $order["CustomerName"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo date(
                                        "M d, Y h:i A",
                                        strtotime(
                                            $order["OrderDate"]
                                        )
                                    );
                                    ?>

                                </td>


                                <td>

                                    ₱<?php
                                    echo number_format(
                                        $order["TotalAmount"],
                                        2
                                    );
                                    ?>

                                </td>


                                <td>

                                    ₱<?php
                                    echo number_format(
                                        $order["Payment"],
                                        2
                                    );
                                    ?>

                                </td>


                                <td>

                                    ₱<?php
                                    echo number_format(
                                        $order["ChangeAmount"],
                                        2
                                    );
                                    ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>


                    <?php else: ?>

                        <tr>

                            <td
                                colspan="6"
                                class="text-center text-muted py-4"
                            >

                                No orders found.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- =========================
         LOW STOCK
    ========================= -->

    <div class="table-container shadow-sm mb-4">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h4 class="section-title mb-0">
                Low Stock Products
            </h4>

            <a
                href="../products/index.php"
                class="btn btn-primary btn-sm"
            >
                View Products
            </a>

        </div>


        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead>

                    <tr>

                        <th>
                            Product ID
                        </th>

                        <th>
                            Product Name
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Price
                        </th>

                        <th>
                            Stock
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (
                        $lowStockResult &&
                        $lowStockResult->num_rows > 0
                    ): ?>

                        <?php while (
                            $product = $lowStockResult->fetch_assoc()
                        ): ?>

                            <tr>

                                <td>

                                    #<?php
                                    echo $product["ProductID"];
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["ProductName"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["Category"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    ₱<?php
                                    echo number_format(
                                        $product["Price"],
                                        2
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php if (
                                        (int)$product["Stock"] <= 0
                                    ): ?>

                                        <span class="badge bg-danger">
                                            Out of Stock
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-warning text-dark">

                                            <?php
                                            echo $product["Stock"];
                                            ?>

                                            left

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["Status"]
                                    );
                                    ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>


                    <?php else: ?>

                        <tr>

                            <td
                                colspan="6"
                                class="text-center text-muted py-4"
                            >

                                No low-stock products found.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- BACK BUTTON -->

    <div class="mt-4">

        <a
            href="../dashboard.php"
            class="btn btn-secondary"
        >
            ← Back to Dashboard
        </a>

    </div>


</div>


</body>

</html>