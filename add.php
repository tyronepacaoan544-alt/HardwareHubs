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
// VARIABLES
// ========================================

$error = "";

$customerName = "";
$address = "";
$contact = "";


// ========================================
// ADD CUSTOMER
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customerName = trim($_POST["customerName"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $contact = trim($_POST["contact"] ?? "");


    // ========================================
    // VALIDATION
    // ========================================

    if ($customerName === "") {

        $error = "Please enter the customer name.";

    } elseif (strlen($customerName) > 100) {

        $error = "Customer name cannot exceed 100 characters.";

    } elseif (strlen($address) > 255) {

        $error = "Address cannot exceed 255 characters.";

    } elseif (strlen($contact) > 30) {

        $error = "Contact cannot exceed 30 characters.";

    } else {


        // ========================================
        // INSERT CUSTOMER
        // ========================================

        $sql = "INSERT INTO customers
                (CustomerName, Address, Contact)
                VALUES (?, ?, ?)";


        $stmt = $conn->prepare($sql);


        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "sss",
                $customerName,
                $address,
                $contact
            );


            if ($stmt->execute()) {

                $stmt->close();

                header("Location: index.php");
                exit();

            } else {

                $error = "Failed to add customer: " . $stmt->error;

                $stmt->close();

            }

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

    <title>HardwareHubs - Add Customer</title>


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

        .form-control {
            min-height: 45px;
        }

    </style>

</head>


<body>


<!-- NAVBAR -->

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



<!-- MAIN -->

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7">


            <div class="mb-4">

                <h1 class="page-title">
                    Add Customer
                </h1>

                <p class="text-muted">
                    Add a new customer to the system.
                </p>

            </div>


            <!-- ERROR -->

            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <div class="card form-card shadow-sm">

                <form
                    method="POST"
                    action="add.php"
                >


                    <!-- CUSTOMER NAME -->

                    <div class="mb-3">

                        <label
                            for="customerName"
                            class="form-label"
                        >
                            Customer Name
                        </label>

                        <input
                            type="text"
                            id="customerName"
                            name="customerName"
                            class="form-control"
                            placeholder="Enter customer name"
                            maxlength="100"
                            value="<?php echo htmlspecialchars($customerName); ?>"
                            required
                        >

                    </div>


                    <!-- ADDRESS -->

                    <div class="mb-3">

                        <label
                            for="address"
                            class="form-label"
                        >
                            Address
                        </label>

                        <textarea
                            id="address"
                            name="address"
                            class="form-control"
                            rows="4"
                            maxlength="255"
                            placeholder="Enter customer address"
                        ><?php echo htmlspecialchars($address); ?></textarea>

                    </div>


                    <!-- CONTACT -->

                    <div class="mb-4">

                        <label
                            for="contact"
                            class="form-label"
                        >
                            Contact Number
                        </label>

                        <input
                            type="text"
                            id="contact"
                            name="contact"
                            class="form-control"
                            placeholder="e.g. 09123456789"
                            maxlength="30"
                            value="<?php echo htmlspecialchars($contact); ?>"
                        >

                    </div>


                    <!-- BUTTONS -->

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Add Customer
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