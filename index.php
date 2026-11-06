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
// GET ORDERS
// ========================================

if ($search !== "") {

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
            WHERE
                c.CustomerName LIKE ?
                OR CAST(o.OrderID AS CHAR) LIKE ?
            ORDER BY o.OrderID DESC";


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
            ORDER BY o.OrderID DESC";


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

    <title>HardwareHubs - Orders</title>


    <!-- BOOTSTRAP -->

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


        .table-container {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
        }


        .table th {
            background-color: #212529;
            color: white;
        }


        .order-total {
            font-weight: bold;
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

                    echo htmlspecialchars(
                        $_SESSION["Username"]
                    );

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


    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">


        <div>

            <h1 class="page-title">
                Orders
            </h1>


            <p class="text-muted mb-0">

                Manage customer orders and payments.

            </p>

        </div>


        <!-- ADD ORDER -->

        <a
            href="add.php"
            class="btn btn-primary"
        >
            + Create Order
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
                        placeholder="Search by Order ID or customer name..."
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
    <!-- ORDERS TABLE -->
    <!-- ======================================== -->

    <div class="table-container shadow-sm">


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
                            Order Date
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


                        <th>
                            Actions
                        </th>

                    </tr>


                </thead>


                <tbody>


                <?php if ($result && $result->num_rows > 0): ?>


                    <?php while ($order = $result->fetch_assoc()): ?>


                        <tr>


                            <!-- ORDER ID -->

                            <td>

                                <strong>

                                    #<?php

                                    echo $order["OrderID"];

                                    ?>

                                </strong>

                            </td>


                            <!-- CUSTOMER -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $order["CustomerName"]
                                );

                                ?>

                            </td>


                            <!-- ORDER DATE -->

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


                            <!-- TOTAL -->

                            <td class="order-total">

                                ₱

                                <?php

                                echo number_format(
                                    $order["TotalAmount"],
                                    2
                                );

                                ?>

                            </td>


                            <!-- PAYMENT -->

                            <td>

                                ₱

                                <?php

                                echo number_format(
                                    $order["Payment"],
                                    2
                                );

                                ?>

                            </td>


                            <!-- CHANGE -->

                            <td>

                                ₱

                                <?php

                                echo number_format(
                                    $order["ChangeAmount"],
                                    2
                                );

                                ?>

                            </td>


                            <!-- ACTIONS -->

                            <td>


                                <a
                                    href="view.php?id=<?php echo $order["OrderID"]; ?>"
                                    class="btn btn-info btn-sm"
                                >
                                    View
                                </a>


                                <a
                                    href="delete.php?id=<?php echo $order["OrderID"]; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this order?');"
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

                            No orders found.

                        </td>


                    </tr>


                <?php endif; ?>


                </tbody>


            </table>


        </div>


    </div>



    <!-- ======================================== -->
    <!-- BACK TO DASHBOARD -->
    <!-- ======================================== -->

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