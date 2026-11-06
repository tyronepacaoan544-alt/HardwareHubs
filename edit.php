<?php

session_start();

require_once "../config/auth.php";
require_once "../config/db.php";

requireAdmin();


/*
|--------------------------------------------------------------------------
| GET USER ID
|--------------------------------------------------------------------------
*/

$userID = (int)($_GET["id"] ?? 0);

if ($userID <= 0) {

    header(
        "Location: index.php?error=" .
        urlencode("Invalid user ID")
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| GET USER
|--------------------------------------------------------------------------
*/

$sql = "SELECT UserID, Username, Role
        FROM users
        WHERE UserID = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("SQL ERROR: " . $conn->error);
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
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $role = trim($_POST["role"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($username === "") {

        $error = "Username is required.";

    } elseif (strlen($username) < 3) {

        $error = "Username must be at least 3 characters.";

    } elseif (
        $role !== "Administrator"
        &&
        $role !== "Staff"
    ) {

        $error = "Invalid role selected.";

    } elseif (
        $password !== ""
        &&
        strlen($password) < 6
    ) {

        $error = "Password must be at least 6 characters.";

    } elseif (
        $password !== ""
        &&
        $password !== $confirmPassword
    ) {

        $error = "Passwords do not match.";

    }


    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE USERNAME
    |--------------------------------------------------------------------------
    */

    if ($error === "") {

        $sql = "SELECT UserID
                FROM users
                WHERE Username = ?
                AND UserID != ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("SQL ERROR: " . $conn->error);
        }

        $stmt->bind_param(
            "si",
            $username,
            $userID
        );

        $stmt->execute();

        $resultCheck = $stmt->get_result();

        if ($resultCheck->num_rows > 0) {

            $error = "Username already exists.";

        }

        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE USER
    |--------------------------------------------------------------------------
    */

    if ($error === "") {

        if ($password !== "") {

            /*
            | Password was changed
            */

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $sql = "UPDATE users
                    SET Username = ?,
                        Password = ?,
                        Role = ?
                    WHERE UserID = ?";

            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                die("SQL ERROR: " . $conn->error);
            }

            $stmt->bind_param(
                "sssi",
                $username,
                $hashedPassword,
                $role,
                $userID
            );

        } else {

            /*
            | Password stays the same
            */

            $sql = "UPDATE users
                    SET Username = ?,
                        Role = ?
                    WHERE UserID = ?";

            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                die("SQL ERROR: " . $conn->error);
            }

            $stmt->bind_param(
                "ssi",
                $username,
                $role,
                $userID
            );
        }


        if ($stmt->execute()) {

            /*
            | Update current session
            | if Administrator edits own account
            */

            if (
                (int)$userID ===
                (int)$_SESSION["UserID"]
            ) {

                $_SESSION["Username"] = $username;
                $_SESSION["Role"] = $role;
            }


            $stmt->close();

            header(
                "Location: index.php?success=" .
                urlencode("User updated successfully")
            );

            exit();

        } else {

            $error = "Failed to update user: " . $stmt->error;

            $stmt->close();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | KEEP FORM VALUES
    |--------------------------------------------------------------------------
    */

    $user["Username"] = $username;
    $user["Role"] = $role;
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

    <title>Edit User - HardwareHubs</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background-color: #f4f6f9;
        }

        .form-card {
            max-width: 650px;
            margin: 40px auto;
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

    </style>

</head>


<body>


<div class="container">

    <div class="card form-card">

        <div class="card-body p-4">


            <!-- HEADER -->

            <div class="mb-4">

                <h2 class="mb-1">
                    Edit User
                </h2>

                <p class="text-muted mb-0">
                    Update username, password, or role.
                </p>

            </div>


            <!-- ERROR -->

            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">

                    <strong>Error!</strong>

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                method="POST"
                autocomplete="off"
            >


                <!-- USERNAME -->

                <div class="mb-3">

                    <label class="form-label">
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        value="<?= htmlspecialchars($user["Username"]) ?>"
                        required
                    >

                </div>


                <!-- ROLE -->

                <div class="mb-3">

                    <label class="form-label">
                        Role
                    </label>

                    <select
                        name="role"
                        class="form-select"
                        required
                    >

                        <option
                            value="Administrator"
                            <?= $user["Role"] === "Administrator" ? "selected" : "" ?>
                        >
                            Administrator
                        </option>

                        <option
                            value="Staff"
                            <?= $user["Role"] === "Staff" ? "selected" : "" ?>
                        >
                            Staff
                        </option>

                    </select>

                </div>


                <hr>


                <!-- NEW PASSWORD -->

                <div class="mb-3">

                    <label class="form-label">
                        New Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Leave blank to keep current password"
                    >

                    <div class="form-text">
                        Leave blank if you do not want to change the password.
                    </div>

                </div>


                <!-- CONFIRM PASSWORD -->

                <div class="mb-4">

                    <label class="form-label">
                        Confirm New Password
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        class="form-control"
                        placeholder="Repeat new password"
                    >

                </div>


                <!-- BUTTONS -->

                <div class="d-flex gap-2">

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        ← Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Changes
                    </button>

                </div>


            </form>

        </div>

    </div>

</div>


</body>

</html>