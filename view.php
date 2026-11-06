<?php

session_start();

if (!isset($_SESSION["UserID"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/db.php";

$orderID = (int)($_GET["id"] ?? 0);

if ($orderID <= 0) {
    header("Location: index.php");
    exit();
}


/* =========================
   GET ORDER INFORMATION
========================= */

$sql = "SELECT
            o.OrderID,
            o.CustomerID,
            o.OrderDate,
            o.TotalAmount,
            o.Payment,
            o.ChangeAmount,
            c.CustomerName
        FROM orders o
        INNER JOIN customers c
            ON o.CustomerID = c.CustomerID
        WHERE o.OrderID = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $orderID);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();

    echo "<h2 style='font-family: Arial; padding: 30px;'>Order not found.</h2>";
    echo "<p style='padding-left: 30px;'><a href='index.php'>Back to Orders</a></p>";
    exit();
}

$order = $result->fetch_assoc();

$stmt->close();


/* =========================
   GET ORDER DETAILS
========================= */

$sql = "SELECT
            od.OrderDetailID,
            od.OrderID,
            od.ProductID,
            od.Quantity,
            od.Price,
            od.Subtotal,
            p.ProductName
        FROM order_details od
        INNER JOIN products p
            ON od.ProductID = p.ProductID
        WHERE od.OrderID = ?
        ORDER BY od.OrderDetailID ASC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $orderID);
$stmt->execute();

$detailsResult = $stmt->get_result();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Order #<?php echo $order["OrderID"]; ?> - HardwareHubs
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

        .receipt-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
        }

        .order-info {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }

        .table th {
            background-color: #212529;
            color: white;
        }

        .total-box {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }

        .grand-total {
            font-size: 24px;
            font-weight: bold;
        }

        .system-name {
            font-size: 24px;
            font-weight: bold;
        }

        @media print {

            body {
                background: white;
            }

            .navbar,
            .no-print {
                display: none !important;
            }

            .receipt-card {
                box-shadow: none !important;
                padding: 0;
            }

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
                <?php echo htmlspecialchars($_SESSION["Username"]); ?>

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

    <div class="row justify-content-center">

        <div class="col-lg-10">


            <!-- PAGE HEADER -->

            <div class="d-flex justify-content-between align-items-center mb-4 no-print">

                <div>

                    <h1 class="page-title">
                        Order Details
                    </h1>

                    <p class="text-muted mb-0">
                        View order information and receipt.
                    </p>

                </div>

                <button
                    type="button"
                    class="btn btn-success"
                    onclick="window.print()"
                >
                    🖨 Print Receipt
                </button>

            </div>


            <!-- RECEIPT -->

            <div class="receipt-card shadow-sm">


                <!-- RECEIPT HEADER -->

                <div class="text-center mb-4">

                    <div class="system-name">
                        HardwareHubs
                    </div>

                    <div class="text-muted">
                        Ordering and Inventory Management System
                    </div>

                    <hr>

                    <h3>
                        ORDER #<?php echo $order["OrderID"]; ?>
                    </h3>

                </div>


                <!-- ORDER INFORMATION -->

                <div class="order-info mb-4">

                    <div class="row">

                        <div class="col-md-6 mb-3 mb-md-0">

                            <strong>
                                Customer:
                            </strong>

                            <br>

                            <?php
                            echo htmlspecialchars($order["CustomerName"]);
                            ?>

                        </div>


                        <div class="col-md-3 mb-3 mb-md-0">

                            <strong>
                                Order Date:
                            </strong>

                            <br>

                            <?php
                            echo date(
                                "M d, Y",
                                strtotime($order["OrderDate"])
                            );
                            ?>

                        </div>


                        <div class="col-md-3">

                            <strong>
                                Time:
                            </strong>

                            <br>

                            <?php
                            echo date(
                                "h:i A",
                                strtotime($order["OrderDate"])
                            );
                            ?>

                        </div>

                    </div>

                </div>


                <!-- ORDER ITEMS -->

                <h5 class="mb-3">
                    Order Items
                </h5>


                <div class="table-responsive">

                    <table class="table table-bordered align-middle">

                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    Product
                                </th>

                                <th class="text-center">
                                    Quantity
                                </th>

                                <th class="text-end">
                                    Price
                                </th>

                                <th class="text-end">
                                    Subtotal
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php

                            $itemNumber = 1;

                            if ($detailsResult && $detailsResult->num_rows > 0):

                                while ($item = $detailsResult->fetch_assoc()):

                            ?>

                                <tr>

                                    <td>
                                        <?php echo $itemNumber; ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $item["ProductName"]
                                        );
                                        ?>
                                    </td>

                                    <td class="text-center">
                                        <?php echo $item["Quantity"]; ?>
                                    </td>

                                    <td class="text-end">

                                        ₱<?php
                                        echo number_format(
                                            $item["Price"],
                                            2
                                        );
                                        ?>

                                    </td>

                                    <td class="text-end">

                                        ₱<?php
                                        echo number_format(
                                            $item["Subtotal"],
                                            2
                                        );
                                        ?>

                                    </td>

                                </tr>

                            <?php

                                $itemNumber++;

                                endwhile;

                            else:

                            ?>

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center text-muted"
                                    >
                                        No order items found.
                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>


                <!-- TOTAL INFORMATION -->

                <div class="row justify-content-end mt-4">

                    <div class="col-md-5">

                        <div class="total-box">


                            <div class="d-flex justify-content-between mb-2">

                                <span>
                                    Total Amount:
                                </span>

                                <strong>

                                    ₱<?php
                                    echo number_format(
                                        $order["TotalAmount"],
                                        2
                                    );
                                    ?>

                                </strong>

                            </div>


                            <div class="d-flex justify-content-between mb-2">

                                <span>
                                    Payment:
                                </span>

                                <strong>

                                    ₱<?php
                                    echo number_format(
                                        $order["Payment"],
                                        2
                                    );
                                    ?>

                                </strong>

                            </div>


                            <hr>


                            <div class="d-flex justify-content-between">

                                <span>
                                    Change:
                                </span>

                                <strong class="text-success">

                                    ₱<?php
                                    echo number_format(
                                        $order["ChangeAmount"],
                                        2
                                    );
                                    ?>

                                </strong>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- RECEIPT FOOTER -->

                <div class="text-center text-muted mt-5">

                    <small>
                        Thank you for your order!
                    </small>

                </div>


            </div>


            <!-- BUTTONS -->

            <div class="mt-4 no-print">

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    ← Back to Orders
                </a>

                <button
                    type="button"
                    class="btn btn-success"
                    onclick="window.print()"
                >
                    🖨 Print Receipt
                </button>

            </div>


        </div>

    </div>

</div>


</body>

</html>