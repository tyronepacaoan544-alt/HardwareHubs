<?php

session_start();


// ========================================
// CHECK LOGIN
// ========================================

if (!isset($_SESSION["UserID"])) {

    header("Location: ../login.php");
    exit();

}


// ========================================
// DATABASE
// ========================================

require_once "../config/db.php";


// ========================================
// SEARCH
// ========================================

$search = trim($_GET["search"] ?? "");


// ========================================
// GET PRODUCTS
// ========================================

if ($search !== "") {

    $sql = "SELECT ProductID, ProductName, Category, Price, Stock, Status
            FROM products
            WHERE ProductName LIKE ?
               OR Category LIKE ?
            ORDER BY ProductID DESC";

    $stmt = $conn->prepare($sql);

    $searchValue = "%" . $search . "%";

    $stmt->bind_param(
        "ss",
        $searchValue,
        $searchValue
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $sql = "SELECT ProductID, ProductName, Category, Price, Stock, Status
            FROM products
            ORDER BY ProductID DESC";

    $result = $conn->query($sql);

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

    <title>HardwareHubs - Products</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        body {
            background-color: #f5f6f8;
            font-family: Arial, sans-serif;
        }

        .navbar-brand {
            font-weight: bold;
        }

        .page-title {
            font-weight: bold;
        }

        .product-card {
            border: none;
            border-radius: 15px;
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

    </style>

</head>


<body>


<!-- ======================================== -->
<!-- NAVBAR -->
<!-- ======================================== -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container-fluid px-4">

        <a
            class="navbar-brand"
            href="../dashboard.php"
        >
            HardwareHubs
        </a>


        <div class="d-flex align-items-center">

            <span class="text-white me-3">

                Welcome,

                <strong>
                    <?php echo htmlspecialchars($_SESSION["Username"]); ?>
                </strong>

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



<!-- ======================================== -->
<!-- MAIN CONTENT -->
<!-- ======================================== -->

<div class="container py-5">


    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="page-title">
                Products
            </h1>

            <p class="text-muted mb-0">
                Manage hardware products and inventory.
            </p>

        </div>


        <a
            href="add.php"
            class="btn btn-primary"
        >
            + Add Product
        </a>

    </div>



    <!-- ======================================== -->
    <!-- SEARCH -->
    <!-- ======================================== -->

    <div class="table-container shadow-sm mb-4">

        <form
            method="GET"
            action="index.php"
        >

            <div class="row g-2">

                <div class="col-md-10">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search product name or category..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >

                </div>


                <div class="col-md-2">

                    <button
                        type="submit"
                        class="btn btn-dark w-100"
                    >
                        Search
                    </button>

                </div>

            </div>

        </form>

    </div>



    <!-- ======================================== -->
    <!-- PRODUCTS TABLE -->
    <!-- ======================================== -->

    <div class="table-container shadow-sm">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead>

                    <tr>

                        <th>
                            ID
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

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($product = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php echo $product["ProductID"]; ?>
                            </td>


                            <td>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $product["ProductName"]
                                    );
                                    ?>
                                </strong>

                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $product["Category"]
                                );
                                ?>
                            </td>


                            <td>

                                ₱
                                <?php
                                echo number_format(
                                    $product["Price"],
                                    2
                                );
                                ?>

                            </td>


                            <td>
                                <?php echo $product["Stock"]; ?>
                            </td>


                            <td>

                                <?php if ($product["Status"] === "Available"): ?>

                                    <span class="badge bg-success">
                                        Available
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        <?php
                                        echo htmlspecialchars(
                                            $product["Status"]
                                        );
                                        ?>
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <a
                                    href="edit.php?id=<?php echo $product["ProductID"]; ?>"
                                    class="btn btn-warning btn-sm"
                                >
                                    Edit
                                </a>


                                <a
                                    href="delete.php?id=<?php echo $product["ProductID"]; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this product?');"
                                >
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>


                <?php else: ?>

                    <tr>

                        <td
                            colspan="7"
                            class="text-center text-muted py-4"
                        >
                            No products found.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>



    <!-- BACK TO DASHBOARD -->

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