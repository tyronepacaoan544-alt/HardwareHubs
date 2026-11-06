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
// DATABASE CONNECTION
// ========================================

require_once "../config/db.php";


// ========================================
// VARIABLES
// ========================================

$error = "";

$productName = "";
$category = "";
$price = "";
$stock = "";
$status = "Available";


// ========================================
// ADD PRODUCT
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form values

    $productName = trim($_POST["productName"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $status = trim($_POST["status"] ?? "Available");


    // ========================================
    // VALIDATION
    // ========================================

    if ($productName === "") {

        $error = "Please enter the product name.";

    } elseif ($category === "") {

        $error = "Please enter the category.";

    } elseif ($price === "") {

        $error = "Please enter the price.";

    } elseif (!is_numeric($price) || $price < 0) {

        $error = "Please enter a valid price.";

    } elseif ($stock === "") {

        $error = "Please enter the stock quantity.";

    } elseif (!is_numeric($stock) || $stock < 0 || floor((float)$stock) != (float)$stock) {

        $error = "Stock must be a whole number and cannot be negative.";

    } elseif ($status === "") {

        $error = "Please select a status.";

    } else {


        // Convert values

        $price = (float)$price;
        $stock = (int)$stock;


        // ========================================
        // INSERT PRODUCT
        // ========================================

        $sql = "INSERT INTO products
                (ProductName, Category, Price, Stock, Status)
                VALUES (?, ?, ?, ?, ?)";


        $stmt = $conn->prepare($sql);


        if ($stmt === false) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "ssdis",
                $productName,
                $category,
                $price,
                $stock,
                $status
            );


            if ($stmt->execute()) {

                // Successfully added

                header("Location: index.php");
                exit();

            } else {

                $error = "Failed to add product: " . $stmt->error;

            }


            $stmt->close();

        }

    }

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

    <title>HardwareHubs - Add Product</title>


    <!-- Bootstrap 5 -->

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
            font-size: 22px;
        }

        .page-title {
            font-weight: bold;
        }

        .form-card {
            background-color: white;
            border: none;
            border-radius: 15px;
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
        }

        .form-control,
        .form-select {
            min-height: 45px;
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
                    <?php
                    echo htmlspecialchars($_SESSION["Username"]);
                    ?>
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


    <div class="row justify-content-center">

        <div class="col-lg-7">


            <!-- TITLE -->

            <div class="mb-4">

                <h1 class="page-title">
                    Add Product
                </h1>

                <p class="text-muted">
                    Add a new hardware product to the inventory.
                </p>

            </div>



            <!-- ======================================== -->
            <!-- ERROR MESSAGE -->
            <!-- ======================================== -->

            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>



            <!-- ======================================== -->
            <!-- FORM CARD -->
            <!-- ======================================== -->

            <div class="card form-card shadow-sm">


                <form
                    method="POST"
                    action="add.php"
                >


                    <!-- PRODUCT NAME -->

                    <div class="mb-3">

                        <label
                            for="productName"
                            class="form-label"
                        >
                            Product Name
                        </label>


                        <input
                            type="text"
                            id="productName"
                            name="productName"
                            class="form-control"
                            placeholder="Enter product name"
                            maxlength="100"
                            value="<?php echo htmlspecialchars($productName); ?>"
                            required
                        >

                    </div>



                    <!-- CATEGORY -->

                    <div class="mb-3">

                        <label
                            for="category"
                            class="form-label"
                        >
                            Category
                        </label>


                        <input
                            type="text"
                            id="category"
                            name="category"
                            class="form-control"
                            placeholder="e.g. Power Tools, Hand Tools, Electrical"
                            maxlength="100"
                            value="<?php echo htmlspecialchars($category); ?>"
                            required
                        >

                    </div>



                    <!-- PRICE -->

                    <div class="mb-3">

                        <label
                            for="price"
                            class="form-label"
                        >
                            Price
                        </label>


                        <div class="input-group">

                            <span class="input-group-text">
                                ₱
                            </span>


                            <input
                                type="number"
                                id="price"
                                name="price"
                                class="form-control"
                                placeholder="0.00"
                                min="0"
                                step="0.01"
                                value="<?php echo htmlspecialchars($price); ?>"
                                required
                            >

                        </div>

                    </div>



                    <!-- STOCK -->

                    <div class="mb-3">

                        <label
                            for="stock"
                            class="form-label"
                        >
                            Stock Quantity
                        </label>


                        <input
                            type="number"
                            id="stock"
                            name="stock"
                            class="form-control"
                            placeholder="Enter stock quantity"
                            min="0"
                            step="1"
                            value="<?php echo htmlspecialchars($stock); ?>"
                            required
                        >

                    </div>



                    <!-- STATUS -->

                    <div class="mb-4">

                        <label
                            for="status"
                            class="form-label"
                        >
                            Status
                        </label>


                        <select
                            id="status"
                            name="status"
                            class="form-select"
                            required
                        >

                            <option
                                value="Available"
                                <?php
                                echo ($status === "Available")
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Available
                            </option>


                            <option
                                value="Unavailable"
                                <?php
                                echo ($status === "Unavailable")
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Unavailable
                            </option>

                        </select>

                    </div>



                    <!-- ======================================== -->
                    <!-- BUTTONS -->
                    <!-- ======================================== -->

                    <div class="d-flex gap-2">


                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Add Product
                        </button>


                        <a
                            href="index.php"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </a>


                    </div>


                </form>


            </div>


        </div>

    </div>


</div>


</body>

</html>