<?php

session_start();

if (!isset($_SESSION["UserID"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/db.php";

$customerID = (int)($_GET["id"] ?? 0);

if ($customerID <= 0) {
    header("Location: index.php");
    exit();
}


/* =========================
   CHECK CUSTOMER
========================= */

$sql = "SELECT CustomerID, CustomerName
        FROM customers
        WHERE CustomerID = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $customerID);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: index.php");
    exit();

}

$customer = $result->fetch_assoc();

$stmt->close();


/* =========================
   CHECK EXISTING ORDERS
========================= */

$sql = "SELECT COUNT(*) AS OrderCount
        FROM orders
        WHERE CustomerID = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $customerID);
$stmt->execute();

$result = $stmt->get_result();

$orderData = $result->fetch_assoc();

$stmt->close();

$orderCount = (int)$orderData["OrderCount"];


/* =========================
   DELETE CUSTOMER
========================= */

if ($orderCount > 0) {

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
            Cannot Delete Customer - HardwareHubs
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

        </style>

    </head>

    <body>

        <div class="container py-5">

            <div class="row justify-content-center">

                <div class="col-md-6">

                    <div class="card shadow-sm">

                        <div class="card-body p-4">

                            <h3 class="text-danger mb-3">
                                Cannot Delete Customer
                            </h3>

                            <div class="alert alert-warning">

                                Customer
                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $customer["CustomerName"]
                                    );
                                    ?>
                                </strong>

                                already has

                                <strong>
                                    <?php echo $orderCount; ?>
                                </strong>

                                order(s).

                                <br><br>

                                This customer cannot be deleted because
                                the order records must be preserved.

                            </div>

                            <a
                                href="index.php"
                                class="btn btn-secondary"
                            >
                                ← Back to Customers
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </body>

    </html>

    <?php

    exit();

}


/* =========================
   DELETE CUSTOMER
========================= */

$sql = "DELETE FROM customers
        WHERE CustomerID = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $customerID);

if (!$stmt->execute()) {

    $errorMessage = $stmt->error;

    $stmt->close();

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
            Delete Error - HardwareHubs
        </title>

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
        >

    </head>

    <body class="bg-light">

        <div class="container py-5">

            <div class="row justify-content-center">

                <div class="col-md-6">

                    <div class="card shadow-sm">

                        <div class="card-body p-4">

                            <h3 class="text-danger mb-3">
                                Delete Failed
                            </h3>

                            <div class="alert alert-danger">

                                <?php
                                echo htmlspecialchars($errorMessage);
                                ?>

                            </div>

                            <a
                                href="index.php"
                                class="btn btn-secondary"
                            >
                                ← Back to Customers
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </body>

    </html>

    <?php

    exit();

}

$stmt->close();


/* =========================
   RETURN TO CUSTOMERS
========================= */

header("Location: index.php");
exit();

?>