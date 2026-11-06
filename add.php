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
| Only Administrator accounts can add new users.
|
*/

requireAdmin();


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$error = "";

$username = "";
$role = "Staff";


/*
|--------------------------------------------------------------------------
| ADD USER
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    $role = trim($_POST["role"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($username === "") {

        $error = "Username is required.";

    } elseif (strlen($username) < 3) {

        $error = "Username must be at least 3 characters.";

    } elseif ($password === "") {

        $error = "Password is required.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } elseif ($password !== $confirmPassword) {

        $error = "Passwords do not match.";

    } elseif (!in_array($role, ["Administrator", "Staff"], true)) {

        $error = "Invalid role selected.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | CHECK DUPLICATE USERNAME
        |--------------------------------------------------------------------------
        */

        $sql = "SELECT UserID
                FROM users
                WHERE Username = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if ($stmt === false) {

            $error = "SQL ERROR: " . $conn->error;

        } else {

            $stmt->bind_param("s", $username);

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result && $result->num_rows > 0) {

                $error = "Username already exists.";

            }


            $stmt->close();
        }


        /*
        |--------------------------------------------------------------------------
        | INSERT USER
        |--------------------------------------------------------------------------
        */

        if ($error === "") {


            /*
            |--------------------------------------------------------------------------
            | HASH PASSWORD
            |--------------------------------------------------------------------------
            */

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            /*
            |--------------------------------------------------------------------------
            | INSERT
            |--------------------------------------------------------------------------
            */

            $sql = "INSERT INTO users
                    (Username, Password, Role)
                    VALUES (?, ?, ?)";

            $stmt = $conn->prepare($sql);


            if ($stmt === false) {

                $error = "SQL ERROR: " . $conn->error;

            } else {


                $stmt->bind_param(
                    "sss",
                    $username,
                    $hashedPassword,
                    $role
                );


                if ($stmt->execute()) {

                    $stmt->close();


                    /*
                    |--------------------------------------------------------------------------
                    | SUCCESS
                    |--------------------------------------------------------------------------
                    */

                    header(
                        "Location: index.php?success=" .
                        urlencode("User added successfully")
                    );

                    exit();


                } else {

                    $error = "Failed to add user: " . $stmt->error;

                    $stmt->close();
                }
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

    <title>
        Add User - HardwareHubs
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        body {
            background-color: #f4f6f9;
        }


        .page-container {
            min-height: 100vh;
        }


        .user-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }


        .card-header-custom {
            background-color: #0d6efd;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }


        .form-label {
            font-weight: 600;
        }


        .required {
            color: red;
        }


        .password-note {
            font-size: 13px;
            color: #6c757d;
        }


        .role-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
        }


        .btn-action {
            min-width: 120px;
        }

    </style>

</head>


<body>


<div class="container page-container py-5">


    <div class="row justify-content-center">

        <div class="col-lg-6 col-md-8">


            <div class="card user-card">


                <!-- ===================================================== -->
                <!-- HEADER -->
                <!-- ===================================================== -->

                <div class="card-header-custom">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h4 class="mb-1">
                                Add New User
                            </h4>

                            <small>
                                Create a new HardwareHubs account
                            </small>

                        </div>


                        <span class="badge bg-light text-primary">

                            <?= htmlspecialchars($_SESSION["Role"]) ?>

                        </span>

                    </div>

                </div>


                <!-- ===================================================== -->
                <!-- BODY -->
                <!-- ===================================================== -->

                <div class="card-body p-4">


                    <!-- ERROR -->

                    <?php if ($error !== ""): ?>

                        <div
                            class="alert alert-danger"
                            role="alert"
                        >

                            <strong>Error:</strong>

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <!-- FORM -->

                    <form
                        method="POST"
                        action=""
                        autocomplete="off"
                    >


                        <!-- ================================================= -->
                        <!-- USERNAME -->
                        <!-- ================================================= -->

                        <div class="mb-4">

                            <label class="form-label">

                                Username

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                value="<?= htmlspecialchars($username) ?>"
                                placeholder="Enter username"
                                minlength="3"
                                maxlength="100"
                                required
                            >


                            <div class="password-note mt-1">

                                Username must be at least 3 characters.

                            </div>

                        </div>


                        <!-- ================================================= -->
                        <!-- PASSWORD -->
                        <!-- ================================================= -->

                        <div class="mb-4">

                            <label class="form-label">

                                Password

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Enter password"
                                minlength="6"
                                required
                            >


                            <div class="password-note mt-1">

                                Password must be at least 6 characters.

                            </div>

                        </div>


                        <!-- ================================================= -->
                        <!-- CONFIRM PASSWORD -->
                        <!-- ================================================= -->

                        <div class="mb-4">

                            <label class="form-label">

                                Confirm Password

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="password"
                                name="confirm_password"
                                id="confirm_password"
                                class="form-control"
                                placeholder="Confirm password"
                                minlength="6"
                                required
                            >


                            <div
                                id="passwordMessage"
                                class="password-note mt-1"
                            ></div>

                        </div>


                        <!-- ================================================= -->
                        <!-- ROLE -->
                        <!-- ================================================= -->

                        <div class="mb-4">

                            <label class="form-label">

                                Role

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <select
                                name="role"
                                class="form-select"
                                required
                            >

                                <option
                                    value="Staff"
                                    <?= $role === "Staff" ? "selected" : "" ?>
                                >
                                    Staff
                                </option>


                                <option
                                    value="Administrator"
                                    <?= $role === "Administrator" ? "selected" : "" ?>
                                >
                                    Administrator
                                </option>

                            </select>


                            <div class="role-info mt-2">

                                <strong>
                                    Administrator:
                                </strong>

                                Full system access including User Management.

                                <br><br>

                                <strong>
                                    Staff:
                                </strong>

                                Can use Products, Customers, Orders, and Reports.

                                User Management is restricted.

                            </div>

                        </div>


                        <!-- ================================================= -->
                        <!-- BUTTONS -->
                        <!-- ================================================= -->

                        <div class="d-flex gap-2">


                            <button
                                type="submit"
                                class="btn btn-primary btn-action"
                            >

                                Add User

                            </button>


                            <a
                                href="index.php"
                                class="btn btn-secondary btn-action"
                            >

                                Cancel

                            </a>


                        </div>


                    </form>


                </div>

            </div>


            <!-- ========================================================= -->
            <!-- FOOTER NOTE -->
            <!-- ========================================================= -->

            <div class="text-center mt-3">

                <small class="text-muted">

                    Logged in as:

                    <strong>
                        <?= htmlspecialchars($_SESSION["Username"]) ?>
                    </strong>

                    -

                    <?= htmlspecialchars($_SESSION["Role"]) ?>

                </small>

            </div>


        </div>

    </div>

</div>



<!-- ============================================================= -->
<!-- PASSWORD CHECK SCRIPT -->
<!-- ============================================================= -->

<script>

const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");
const passwordMessage = document.getElementById("passwordMessage");


function checkPassword()
{

    if (confirmPassword.value === "") {

        passwordMessage.textContent = "";

        return;
    }


    if (password.value === confirmPassword.value) {

        passwordMessage.textContent = "✓ Passwords match.";

        passwordMessage.className =
            "text-success mt-1";


    } else {

        passwordMessage.textContent = "✗ Passwords do not match.";

        passwordMessage.className =
            "text-danger mt-1";
    }

}


password.addEventListener(
    "input",
    checkPassword
);


confirmPassword.addEventListener(
    "input",
    checkPassword
);

</script>


</body>

</html>