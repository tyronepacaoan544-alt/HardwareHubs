<?php

session_start();

/*
|--------------------------------------------------------------------------
| AUTHENTICATION & DATABASE
|--------------------------------------------------------------------------
*/

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| ADMINISTRATOR ONLY
|--------------------------------------------------------------------------
|
| Only Administrator accounts can delete users.
|
*/

requireAdmin();


/*
|--------------------------------------------------------------------------
| GET USER ID
|--------------------------------------------------------------------------
*/

$userID = (int)($_GET["id"] ?? 0);


/*
|--------------------------------------------------------------------------
| CHECK VALID USER ID
|--------------------------------------------------------------------------
*/

if ($userID <= 0) {

    header("Location: index.php?error=" . urlencode("Invalid user ID"));

    exit();
}


/*
|--------------------------------------------------------------------------
| PREVENT DELETING CURRENTLY LOGGED-IN USER
|--------------------------------------------------------------------------
*/

if ($userID === (int)$_SESSION["UserID"]) {

    header(
        "Location: index.php?error=" .
        urlencode("You cannot delete your own account")
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| CHECK IF USER EXISTS
|--------------------------------------------------------------------------
*/

$sql = "SELECT UserID, Username, Role
        FROM users
        WHERE UserID = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);


if ($stmt === false) {

    die(
        "SQL ERROR: " .
        htmlspecialchars($conn->error)
    );
}


$stmt->bind_param("i", $userID);

$stmt->execute();

$result = $stmt->get_result();


if (!$result || $result->num_rows === 0) {

    $stmt->close();

    header(
        "Location: index.php?error=" .
        urlencode("User not found")
    );

    exit();
}


$user = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| DELETE USER
|--------------------------------------------------------------------------
*/

$sql = "DELETE FROM users
        WHERE UserID = ?";

$stmt = $conn->prepare($sql);


if ($stmt === false) {

    die(
        "SQL ERROR: " .
        htmlspecialchars($conn->error)
    );
}


$stmt->bind_param("i", $userID);


/*
|--------------------------------------------------------------------------
| EXECUTE DELETE
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success=" .
        urlencode("User deleted successfully")
    );

    exit();


} else {

    $errorMessage = $stmt->error;

    $stmt->close();

    die(
        "Failed to delete user: " .
        htmlspecialchars($errorMessage)
    );
}

?>