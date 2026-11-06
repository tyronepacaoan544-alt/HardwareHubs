<?php

session_start();

require_once __DIR__ . "/config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $error = "Please enter your username and password.";

    } else {

        $sql = "SELECT UserID, Username, Password, Role
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

                $user = $result->fetch_assoc();

                /*
                 * Check hashed password
                 */
                if (password_verify($password, $user["Password"])) {

                    $_SESSION["UserID"] = $user["UserID"];
                    $_SESSION["Username"] = $user["Username"];
                    $_SESSION["Role"] = $user["Role"];

                    header("Location: dashboard.php");
                    exit();

                } else {

                    $error = "Incorrect username or password.";

                }

            } else {

                $error = "Incorrect username or password.";

            }

            $stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>HardwareHubs - Login</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f4f6f9;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.10);
        }

        .login-title {
            font-weight: 700;
        }

        .login-subtitle {
            color: #6c757d;
        }

        .btn-login {
            width: 100%;
            padding: 10px;
            font-weight: 600;
        }

    </style>

</head>

<body>

<div class="container login-container">

    <div class="card login-card">

        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">

                <h2 class="login-title">
                    HardwareHubs
                </h2>

                <p class="login-subtitle mb-0">
                    Inventory Management System
                </p>

            </div>


            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <form method="POST" action="">

                <div class="mb-3">

                    <label class="form-label">
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        placeholder="Enter username"
                        required
                        autocomplete="username"
                    >

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter password"
                        required
                        autocomplete="current-password"
                    >

                </div>


                <button
                    type="submit"
                    class="btn btn-primary btn-login"
                >
                    Login
                </button>

            </form>

        </div>

    </div>

</div>

</body>

</html>