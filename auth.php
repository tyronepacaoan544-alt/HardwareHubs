<?php

function requireLogin()
{
    if (!isset($_SESSION["UserID"])) {

        header("Location: ../login.php");

        exit();
    }
}


function isAdmin()
{
    return isset($_SESSION["Role"])
        && $_SESSION["Role"] === "Administrator";
}


function requireAdmin()
{
    requireLogin();

    if (!isAdmin()) {

        header(
            "Location: ../dashboard.php?error=" .
            urlencode("Access denied. Administrator only.")
        );

        exit();
    }
}


function requireStaffOrAdmin()
{
    requireLogin();

    if (
        !isset($_SESSION["Role"])
        ||
        (
            $_SESSION["Role"] !== "Administrator"
            &&
            $_SESSION["Role"] !== "Staff"
        )
    ) {

        header("Location: ../login.php");

        exit();
    }
}

?>