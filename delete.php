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
   CHECK IF ORDER EXISTS
========================= */

$sql = "SELECT OrderID
        FROM orders
        WHERE OrderID = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("SQL ERROR: " . $conn->error);
}

$stmt->bind_param("i", $orderID);
$stmt->execute();

$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {

    $stmt->close();

    die("Order not found.");

}

$stmt->close();


/* =========================
   GET ORDER DETAILS
========================= */

$sql = "SELECT ProductID, Quantity
        FROM order_details
        WHERE OrderID = ?";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("SQL ERROR: " . $conn->error);
}

$stmt->bind_param("i", $orderID);
$stmt->execute();

$result = $stmt->get_result();

$orderItems = [];

while ($row = $result->fetch_assoc()) {

    $orderItems[] = [
        "ProductID" => (int)$row["ProductID"],
        "Quantity" => (int)$row["Quantity"]
    ];

}

$stmt->close();


/* =========================
   DELETE ORDER
========================= */

try {

    /* START TRANSACTION */

    $conn->begin_transaction();


    /* =========================
       RESTORE PRODUCT STOCK
    ========================= */

    foreach ($orderItems as $item) {

        $sql = "UPDATE products
                SET Stock = Stock + ?
                WHERE ProductID = ?";

        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new Exception($conn->error);
        }

        $stmt->bind_param(
            "ii",
            $item["Quantity"],
            $item["ProductID"]
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();

    }


    /* =========================
       DELETE ORDER DETAILS
    ========================= */

    $sql = "DELETE FROM order_details
            WHERE OrderID = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param("i", $orderID);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $stmt->close();


    /* =========================
       DELETE ORDER
    ========================= */

    $sql = "DELETE FROM orders
            WHERE OrderID = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param("i", $orderID);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $stmt->close();


    /* =========================
       COMMIT
    ========================= */

    $conn->commit();


    header("Location: index.php?success=Order deleted successfully");
    exit();


} catch (Exception $e) {

    /* =========================
       ROLLBACK
    ========================= */

    $conn->rollback();

    die(
        "Failed to delete order: " .
        htmlspecialchars($e->getMessage())
    );

}

?>