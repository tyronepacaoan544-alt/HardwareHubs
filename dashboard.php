<?php

session_start();

if (!isset($_SESSION["UserID"])) {
    header("Location: login.php");
    exit();
}

require_once "config/db.php";

$username = $_SESSION["Username"];
$role = $_SESSION["Role"];


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
   LOW STOCK
========================= */

$sql = "SELECT COUNT(*) AS LowStock
        FROM products
        WHERE Stock <= 5";

$result = $conn->query($sql);

$lowStock = 0;

if ($result) {
    $data = $result->fetch_assoc();
    $lowStock = (int)$data["LowStock"];
}


/* =========================
   TOTAL USERS
========================= */

$sql = "SELECT COUNT(*) AS TotalUsers
        FROM users";

$result = $conn->query($sql);

$totalUsers = 0;

if ($result) {
    $data = $result->fetch_assoc();
    $totalUsers = (int)$data["TotalUsers"];
}

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
        HardwareHubs - Dashboard
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            margin: 0;
            background-color: #f1f3f5;
            font-family: Arial, sans-serif;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 22px;
        }

        .dashboard-title {
            font-weight: bold;
        }

        .welcome-box {
            background: white;
            border-radius: 15px;
            padding: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            height: 100%;
            transition: 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-icon {
            font-size: 35px;
        }

        .stat-number {
            font-size: 30px;
            font-weight: bold;
            margin-top: 10px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 15px;
        }

        .module-card {
            border: none;
            border-radius: 15px;
            transition: 0.2s;
        }

        .module-card:hover {
            transform: translateY(-4px);
        }

        .module-icon {
            font-size: 45px;
        }

        .module-title {
            font-weight: bold;
        }

        .section-title {
            font-weight: bold;
        }

        .stock-warning {
            color: #dc3545;
            font-weight: bold;
        }

        .role-badge {
            font-size: 12px;
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
            href="dashboard.php"
        >
            HardwareHubs
        </a>


        <div class="d-flex align-items-center">

            <span class="text-white me-3">

                Welcome,

                <strong>
                    <?= htmlspecialchars($username) ?>
                </strong>

                <span class="badge bg-secondary role-badge ms-1">
                    <?= htmlspecialchars($role) ?>
                </span>

            </span>


            <a
                href="logout.php"
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


    <!-- =========================
         HEADER
    ========================= -->

    <div class="mb-4">

        <h1 class="dashboard-title">
            Dashboard
        </h1>

        <p class="text-muted">
            HardwareHubs Ordering and Inventory Management System
        </p>

    </div>


    <!-- =========================
         WELCOME
    ========================= -->

    <div class="welcome-box shadow-sm mb-4">

        <h5 class="mb-2">
            Welcome to HardwareHubs!
        </h5>

        <p class="text-muted mb-0">

            You are logged in as

            <strong>
                <?= htmlspecialchars($username) ?>
            </strong>

            with role

            <strong>
                <?= htmlspecialchars($role) ?>
            </strong>.

        </p>

    </div>


    <!-- =========================
         STATISTICS
    ========================= -->

    <div class="row g-4 mb-5">


        <!-- PRODUCTS -->

        <div class="col-md-6 col-lg-3">

            <div class="stat-card shadow-sm">

                <div class="stat-icon">
                    📦
                </div>

                <div class="stat-number">

                    <?= number_format($totalProducts) ?>

                </div>

                <div class="stat-label">
                    Total Products
                </div>

            </div>

        </div>


        <!-- CUSTOMERS -->

        <div class="col-md-6 col-lg-3">

            <div class="stat-card shadow-sm">

                <div class="stat-icon">
                    👥
                </div>

                <div class="stat-number">

                    <?= number_format($totalCustomers) ?>

                </div>

                <div class="stat-label">
                    Total Customers
                </div>

            </div>

        </div>


        <!-- ORDERS -->

        <div class="col-md-6 col-lg-3">

            <div class="stat-card shadow-sm">

                <div class="stat-icon">
                    🛒
                </div>

                <div class="stat-number">

                    <?= number_format($totalOrders) ?>

                </div>

                <div class="stat-label">
                    Total Orders
                </div>

            </div>

        </div>


        <!-- SALES -->

        <div class="col-md-6 col-lg-3">

            <div class="stat-card shadow-sm">

                <div class="stat-icon">
                    💰
                </div>

                <div class="stat-number">

                    ₱<?= number_format($totalSales, 2) ?>

                </div>

                <div class="stat-label">
                    Total Sales
                </div>

            </div>

        </div>


    </div>


    <!-- =========================
         INVENTORY SUMMARY
    ========================= -->

    <h4 class="section-title mb-3">
        Inventory Summary
    </h4>


    <div class="row g-4 mb-5">


        <!-- TOTAL STOCK -->

        <div class="col-md-6">

            <div class="stat-card shadow-sm">

                <div class="stat-icon">
                    📦
                </div>

                <div class="stat-number">

                    <?= number_format($totalStock) ?>

                </div>

                <div class="stat-label">
                    Total Items in Stock
                </div>

            </div>

        </div>


        <!-- LOW STOCK -->

        <div class="col-md-6">

            <div class="stat-card shadow-sm">

                <div class="stat-icon">
                    ⚠️
                </div>

                <div class="stat-number stock-warning">

                    <?= number_format($lowStock) ?>

                </div>

                <div class="stat-label">
                    Products with Low Stock
                </div>

            </div>

        </div>


    </div>


    <!-- =========================
         SYSTEM MODULES
    ========================= -->

    <h4 class="section-title mb-3">
        System Modules
    </h4>


    <div class="row g-4">


        <!-- PRODUCTS -->

        <div class="col-md-6 col-lg-3">

            <div class="card module-card shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="module-icon">
                        📦
                    </div>

                    <h4 class="module-title mt-3">
                        Products
                    </h4>

                    <p class="text-muted">
                        Manage hardware products, prices,
                        categories, and stock.
                    </p>

                    <a
                        href="products/index.php"
                        class="btn btn-primary"
                    >
                        Manage Products
                    </a>

                </div>

            </div>

        </div>


        <!-- CUSTOMERS -->

        <div class="col-md-6 col-lg-3">

            <div class="card module-card shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="module-icon">
                        👥
                    </div>

                    <h4 class="module-title mt-3">
                        Customers
                    </h4>

                    <p class="text-muted">
                        Manage customer information
                        and records.
                    </p>

                    <a
                        href="customers/index.php"
                        class="btn btn-primary"
                    >
                        Manage Customers
                    </a>

                </div>

            </div>

        </div>


        <!-- ORDERS -->

        <div class="col-md-6 col-lg-3">

            <div class="card module-card shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="module-icon">
                        🛒
                    </div>

                    <h4 class="module-title mt-3">
                        Orders
                    </h4>

                    <p class="text-muted">
                        Create and manage customer
                        orders.
                    </p>

                    <a
                        href="orders/index.php"
                        class="btn btn-primary"
                    >
                        Manage Orders
                    </a>

                </div>

            </div>

        </div>


        <!-- REPORTS -->

        <div class="col-md-6 col-lg-3">

            <div class="card module-card shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="module-icon">
                        📊
                    </div>

                    <h4 class="module-title mt-3">
                        Reports
                    </h4>

                    <p class="text-muted">
                        View sales, orders,
                        and inventory reports.
                    </p>

                    <a
                        href="reports/index.php"
                        class="btn btn-primary"
                    >
                        View Reports
                    </a>

                </div>

            </div>

        </div>


        <!-- USER MANAGEMENT -->

        <div class="col-md-6 col-lg-3">

            <div class="card module-card shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="module-icon">
                        👤
                    </div>

                    <h4 class="module-title mt-3">
                        User Management
                    </h4>

                    <p class="text-muted">
                        Manage system users,
                        passwords, and roles.
                    </p>

                    <a
                        href="users/index.php"
                        class="btn btn-dark"
                    >
                        Manage Users
                    </a>

                </div>

            </div>

        </div>


    </div>


    <!-- =========================
         FOOTER
    ========================= -->

    <div class="text-center text-muted mt-5">

        <small>
            HardwareHubs Ordering and Inventory Management System
        </small>

    </div>


</div>


</body>

</html>