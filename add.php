<?php

session_start();

if (!isset($_SESSION["UserID"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/db.php";

$error = "";

/* =========================
   GET CUSTOMERS
========================= */

$customers = [];

$sql = "SELECT CustomerID, CustomerName
        FROM customers
        ORDER BY CustomerName ASC";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }

}


/* =========================
   GET AVAILABLE PRODUCTS
========================= */

$products = [];

$sql = "SELECT ProductID, ProductName, Price, Stock, Status
        FROM products
        WHERE Status = 'Available'
        AND Stock > 0
        ORDER BY ProductName ASC";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

}


/* =========================
   CREATE ORDER
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customerID = (int)($_POST["customer_id"] ?? 0);

    $payment = (float)($_POST["payment"] ?? 0);

    $productIDs = $_POST["product_id"] ?? [];

    $quantities = $_POST["quantity"] ?? [];


    /* =========================
       BASIC VALIDATION
    ========================= */

    if ($customerID <= 0) {

        $error = "Please select a customer.";

    } elseif ($payment <= 0) {

        $error = "Please enter a valid payment.";

    } elseif (empty($productIDs)) {

        $error = "Please add at least one product.";

    } else {

        $orderItems = [];
        $totalAmount = 0;


        /* =========================
           CHECK PRODUCTS
        ========================= */

        foreach ($productIDs as $index => $productID) {

            $productID = (int)$productID;
            $quantity = (int)($quantities[$index] ?? 0);


            if ($productID <= 0 || $quantity <= 0) {

                $error = "Invalid product or quantity.";
                break;

            }


            /* GET CURRENT PRODUCT DATA */

            $sql = "SELECT ProductID, ProductName, Price, Stock, Status
                    FROM products
                    WHERE ProductID = ?
                    LIMIT 1";

            $stmt = $conn->prepare($sql);

            if ($stmt === false) {

                $error = "SQL ERROR: " . $conn->error;
                break;

            }

            $stmt->bind_param("i", $productID);
            $stmt->execute();

            $result = $stmt->get_result();

            if (!$result || $result->num_rows === 0) {

                $error = "Product not found.";
                $stmt->close();
                break;

            }


            $product = $result->fetch_assoc();

            $stmt->close();


            /* CHECK STATUS */

            if ($product["Status"] !== "Available") {

                $error = $product["ProductName"] . " is not available.";
                break;

            }


            /* CHECK STOCK */

            if ((int)$product["Stock"] < $quantity) {

                $error = "Not enough stock for " .
                         $product["ProductName"] .
                         ". Available stock: " .
                         $product["Stock"];

                break;

            }


            /* CALCULATE SUBTOTAL */

            $price = (float)$product["Price"];

            $subtotal = $price * $quantity;

            $totalAmount += $subtotal;


            $orderItems[] = [
                "ProductID" => $productID,
                "ProductName" => $product["ProductName"],
                "Quantity" => $quantity,
                "Price" => $price,
                "Subtotal" => $subtotal
            ];

        }


        /* =========================
           CHECK TOTAL
        ========================= */

        if ($error === "") {

            if (empty($orderItems)) {

                $error = "Please add at least one valid product.";

            } elseif ($payment < $totalAmount) {

                $error = "Insufficient payment. Total amount is ₱" .
                         number_format($totalAmount, 2);

            }

        }


        /* =========================
           SAVE ORDER
        ========================= */

        if ($error === "") {

            $changeAmount = $payment - $totalAmount;


            try {

                /* START TRANSACTION */

                $conn->begin_transaction();


                /* =========================
                   INSERT ORDER
                ========================= */

                $sql = "INSERT INTO orders
                        (CustomerID, OrderDate, TotalAmount, Payment, ChangeAmount)
                        VALUES (?, NOW(), ?, ?, ?)";

                $stmt = $conn->prepare($sql);

                if ($stmt === false) {
                    throw new Exception($conn->error);
                }

                $stmt->bind_param(
                    "iddd",
                    $customerID,
                    $totalAmount,
                    $payment,
                    $changeAmount
                );

                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }

                $orderID = $conn->insert_id;

                $stmt->close();


                /* =========================
                   INSERT ORDER DETAILS
                ========================= */

                foreach ($orderItems as $item) {

                    $sql = "INSERT INTO order_details
                            (OrderID, ProductID, Quantity, Price, Subtotal)
                            VALUES (?, ?, ?, ?, ?)";

                    $stmt = $conn->prepare($sql);

                    if ($stmt === false) {
                        throw new Exception($conn->error);
                    }

                    $stmt->bind_param(
                        "iiidd",
                        $orderID,
                        $item["ProductID"],
                        $item["Quantity"],
                        $item["Price"],
                        $item["Subtotal"]
                    );

                    if (!$stmt->execute()) {
                        throw new Exception($stmt->error);
                    }

                    $stmt->close();


                    /* =========================
                       UPDATE PRODUCT STOCK
                    ========================= */

                    $sql = "UPDATE products
                            SET Stock = Stock - ?
                            WHERE ProductID = ?
                            AND Stock >= ?";

                    $stmt = $conn->prepare($sql);

                    if ($stmt === false) {
                        throw new Exception($conn->error);
                    }

                    $stmt->bind_param(
                        "iii",
                        $item["Quantity"],
                        $item["ProductID"],
                        $item["Quantity"]
                    );

                    if (!$stmt->execute()) {
                        throw new Exception($stmt->error);
                    }

                    if ($stmt->affected_rows === 0) {

                        throw new Exception(
                            "Stock changed while creating the order. Please try again."
                        );

                    }

                    $stmt->close();

                }


                /* =========================
                   COMMIT
                ========================= */

                $conn->commit();


                /* =========================
                   REDIRECT TO RECEIPT
                ========================= */

                header("Location: view.php?id=" . $orderID);
                exit();


            } catch (Exception $e) {

                /* ROLLBACK IF ERROR */

                $conn->rollback();

                $error = "Failed to create order: " . $e->getMessage();

            }

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Order - HardwareHubs</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background-color: #f5f6f8;
        }

        .navbar-brand {
            font-weight: bold;
        }

        .page-title {
            font-size: 42px;
            font-weight: 700;
        }

        .order-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .total-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }

        .grand-total {
            font-size: 28px;
            font-weight: bold;
        }

        .change-amount {
            font-size: 24px;
            font-weight: bold;
        }

    </style>

</head>

<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container-fluid">

        <a
            class="navbar-brand"
            href="../dashboard.php"
        >
            HardwareHubs
        </a>

        <div class="text-white">

            Welcome,
            <strong>
                <?= htmlspecialchars($_SESSION["Username"]) ?>
            </strong>

            <a
                href="../logout.php"
                class="btn btn-danger btn-sm ms-3"
            >
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- =========================
     MAIN
========================= -->

<div class="container py-5">


    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="page-title">
                Create Order
            </h1>

            <p class="text-muted">
                Create a new customer order and payment.
            </p>

        </div>

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back to Orders
        </a>

    </div>


    <?php if ($error !== ""): ?>

        <div class="alert alert-danger">

            <strong>Error:</strong>

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <div class="card order-card">

        <div class="card-body p-4">


            <form method="POST" id="orderForm">


                <!-- =========================
                     CUSTOMER
                ========================= -->

                <div class="mb-4">

                    <label class="form-label fw-bold">
                        Customer
                    </label>

                    <select
                        name="customer_id"
                        class="form-select"
                        required
                    >

                        <option value="">
                            -- Select Customer --
                        </option>

                        <?php foreach ($customers as $customer): ?>

                            <option
                                value="<?= $customer["CustomerID"] ?>"
                                <?= (
                                    ($_POST["customer_id"] ?? "") ==
                                    $customer["CustomerID"]
                                ) ? "selected" : "" ?>
                            >

                                <?= htmlspecialchars($customer["CustomerName"]) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                    <?php if (empty($customers)): ?>

                        <small class="text-danger">
                            No customers found. Please add a customer first.
                        </small>

                    <?php endif; ?>

                </div>


                <!-- =========================
                     PRODUCTS
                ========================= -->

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h5 class="mb-0">
                        Order Products
                    </h5>

                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        onclick="addProductRow()"
                    >
                        + Add Product
                    </button>

                </div>


                <div id="productRows">


                    <!-- FIRST PRODUCT ROW -->

                    <div class="product-row border rounded p-3 mb-3">

                        <div class="row g-3 align-items-end">


                            <!-- PRODUCT -->

                            <div class="col-md-5">

                                <label class="form-label">
                                    Product
                                </label>

                                <select
                                    name="product_id[]"
                                    class="form-select product-select"
                                    onchange="updateRow(this)"
                                    required
                                >

                                    <option
                                        value=""
                                        data-price="0"
                                        data-stock="0"
                                    >
                                        -- Select Product --
                                    </option>

                                    <?php foreach ($products as $product): ?>

                                        <option
                                            value="<?= $product["ProductID"] ?>"
                                            data-price="<?= $product["Price"] ?>"
                                            data-stock="<?= $product["Stock"] ?>"
                                        >

                                            <?= htmlspecialchars($product["ProductName"]) ?>
                                            -
                                            ₱<?= number_format($product["Price"], 2) ?>
                                            (Stock:
                                            <?= $product["Stock"] ?>)

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- PRICE -->

                            <div class="col-md-2">

                                <label class="form-label">
                                    Price
                                </label>

                                <input
                                    type="text"
                                    class="form-control price"
                                    value="₱0.00"
                                    readonly
                                >

                            </div>


                            <!-- QUANTITY -->

                            <div class="col-md-2">

                                <label class="form-label">
                                    Quantity
                                </label>

                                <input
                                    type="number"
                                    name="quantity[]"
                                    class="form-control quantity"
                                    value="1"
                                    min="1"
                                    onchange="updateRow(this)"
                                    oninput="updateRow(this)"
                                    required
                                >

                            </div>


                            <!-- SUBTOTAL -->

                            <div class="col-md-2">

                                <label class="form-label">
                                    Subtotal
                                </label>

                                <input
                                    type="text"
                                    class="form-control subtotal"
                                    value="₱0.00"
                                    readonly
                                >

                            </div>


                            <!-- REMOVE -->

                            <div class="col-md-1">

                                <button
                                    type="button"
                                    class="btn btn-danger w-100"
                                    onclick="removeProductRow(this)"
                                >
                                    ×
                                </button>

                            </div>


                        </div>

                    </div>


                </div>


                <hr class="my-4">


                <!-- =========================
                     TOTAL
                ========================= -->

                <div class="row justify-content-end">

                    <div class="col-md-5">

                        <div class="total-box">


                            <div class="d-flex justify-content-between mb-3">

                                <span>
                                    Total Amount
                                </span>

                                <span
                                    id="totalAmount"
                                    class="grand-total"
                                >
                                    ₱0.00
                                </span>

                            </div>


                            <div class="mb-3">

                                <label class="form-label fw-bold">
                                    Payment
                                </label>

                                <input
                                    type="number"
                                    name="payment"
                                    id="payment"
                                    class="form-control form-control-lg"
                                    min="0"
                                    step="0.01"
                                    placeholder="Enter payment"
                                    value="<?= htmlspecialchars($_POST["payment"] ?? "") ?>"
                                    oninput="calculateChange()"
                                    required
                                >

                            </div>


                            <div class="d-flex justify-content-between">

                                <span>
                                    Change
                                </span>

                                <span
                                    id="changeAmount"
                                    class="change-amount"
                                >
                                    ₱0.00
                                </span>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- =========================
                     BUTTONS
                ========================= -->

                <div class="mt-4 d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-success btn-lg"
                    >
                        Create Order
                    </button>

                    <a
                        href="index.php"
                        class="btn btn-secondary btn-lg"
                    >
                        Cancel
                    </a>

                </div>


            </form>


        </div>

    </div>

</div>


<!-- =========================
     JAVASCRIPT
========================= -->

<script>

function addProductRow() {

    const container = document.getElementById("productRows");

    const firstRow = document.querySelector(".product-row");

    const newRow = firstRow.cloneNode(true);


    /* RESET VALUES */

    const select = newRow.querySelector(".product-select");

    const price = newRow.querySelector(".price");

    const quantity = newRow.querySelector(".quantity");

    const subtotal = newRow.querySelector(".subtotal");


    select.selectedIndex = 0;

    price.value = "₱0.00";

    quantity.value = 1;

    subtotal.value = "₱0.00";


    container.appendChild(newRow);


    calculateTotal();

}


function removeProductRow(button) {

    const rows = document.querySelectorAll(".product-row");


    /* ALWAYS KEEP AT LEAST ONE ROW */

    if (rows.length === 1) {

        alert("At least one product is required.");

        return;

    }


    button.closest(".product-row").remove();


    calculateTotal();

}


function updateRow(element) {

    const row = element.closest(".product-row");

    const select = row.querySelector(".product-select");

    const priceInput = row.querySelector(".price");

    const quantityInput = row.querySelector(".quantity");

    const subtotalInput = row.querySelector(".subtotal");


    const selectedOption =
        select.options[select.selectedIndex];


    if (!selectedOption || !select.value) {

        priceInput.value = "₱0.00";

        subtotalInput.value = "₱0.00";

        calculateTotal();

        return;

    }


    const price =
        parseFloat(selectedOption.dataset.price) || 0;

    const stock =
        parseInt(selectedOption.dataset.stock) || 0;


    let quantity =
        parseInt(quantityInput.value) || 1;


    if (quantity < 1) {
        quantity = 1;
        quantityInput.value = 1;
    }


    /* CHECK STOCK IN FRONT-END */

    if (quantity > stock) {

        alert(
            "Only " +
            stock +
            " item(s) available for this product."
        );

        quantity = stock;

        quantityInput.value = stock;

    }


    const subtotal = price * quantity;


    priceInput.value =
        "₱" + price.toLocaleString("en-PH", {
            minimumFractionDigits: 2
        });


    subtotalInput.value =
        "₱" + subtotal.toLocaleString("en-PH", {
            minimumFractionDigits: 2
        });


    calculateTotal();

}


function calculateTotal() {

    const rows =
        document.querySelectorAll(".product-row");

    let total = 0;


    rows.forEach(function(row) {

        const select =
            row.querySelector(".product-select");

        const quantity =
            parseInt(
                row.querySelector(".quantity").value
            ) || 0;


        if (select.value) {

            const selectedOption =
                select.options[select.selectedIndex];

            const price =
                parseFloat(
                    selectedOption.dataset.price
                ) || 0;


            total += price * quantity;

        }

    });


    document.getElementById("totalAmount").textContent =
        "₱" + total.toLocaleString("en-PH", {
            minimumFractionDigits: 2
        });


    calculateChange();

}


function calculateChange() {

    const totalText =
        document.getElementById("totalAmount").textContent
        .replace("₱", "")
        .replace(/,/g, "");


    const total =
        parseFloat(totalText) || 0;


    const payment =
        parseFloat(
            document.getElementById("payment").value
        ) || 0;


    let change = payment - total;


    if (change < 0) {
        change = 0;
    }


    document.getElementById("changeAmount").textContent =
        "₱" + change.toLocaleString("en-PH", {
            minimumFractionDigits: 2
        });

}


/* INITIAL CALCULATION */

document.addEventListener("DOMContentLoaded", function() {

    calculateTotal();

});

</script>


</body>

</html>