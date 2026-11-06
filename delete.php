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
// GET PRODUCT ID
// ========================================

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;


// ========================================
// CHECK ID
// ========================================

if ($id <= 0) {

    header("Location: index.php");
    exit();

}


// ========================================
// DELETE PRODUCT
// ========================================

$sql = "DELETE FROM products
        WHERE ProductID = ?";

$stmt = $conn->prepare($sql);


if (!$stmt) {

    die("Database error: " . $conn->error);

}


$stmt->bind_param("i", $id);

$stmt->execute();

$stmt->close();


// ========================================
// RETURN TO PRODUCTS
// ========================================

header("Location: index.php");
exit();

?>