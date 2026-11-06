<?php

session_start();

require_once "../config/auth.php";
require_once "../config/db.php";

requireAdmin();


/*
|--------------------------------------------------------------------------
| SUCCESS / ERROR MESSAGE
|--------------------------------------------------------------------------
*/

$success = trim($_GET["success"] ?? "");
$error = trim($_GET["error"] ?? "");


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");


/*
|--------------------------------------------------------------------------
| GET USERS
|--------------------------------------------------------------------------
*/

if ($search !== "") {

    $sql = "SELECT UserID, Username, Role
            FROM users
            WHERE Username LIKE ?
               OR Role LIKE ?
            ORDER BY UserID DESC";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("SQL ERROR: " . $conn->error);
    }

    $searchValue = "%" . $search . "%";

    $stmt->bind_param(
        "ss",
        $searchValue,
        $searchValue
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $sql = "SELECT UserID, Username, Role
            FROM users
            ORDER BY UserID DESC";

    $result = $conn->query($sql);

    if ($result === false) {
        die("SQL ERROR: " . $conn->error);
    }
}


/*
|--------------------------------------------------------------------------
| TOTAL USERS
|--------------------------------------------------------------------------
*/

$countResult = $conn->query(
    "SELECT COUNT(*) AS TotalUsers FROM users"
);

$totalUsers = 0;

if ($countResult) {

    $countData = $countResult->fetch_assoc();

    $totalUsers = (int)$countData["TotalUsers"];
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

    <title>User Management - HardwareHubs</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        body {
            background-color: #f4f6f9;
        }

        .page-header {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .content-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .table th {
            white-space: nowrap;
        }

        .role-badge {
            font-size: 0.85rem;
            padding: 6px 10px;
        }

        .action-btn {
            min-width: 70px;
        }

        .user-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            font-weight: bold;
            font-size: 18px;
        }

    </style>

</head>


<body>


<div class="container-fluid px-4 py-4">


    <!-- ========================================================= -->
    <!-- PAGE HEADER -->
    <!-- ========================================================= -->

    <div class="page-header">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

            <div>

                <h2 class="mb-1">
                    User Management
                </h2>

                <p class="text-muted mb-0">
                    Manage system users and their roles.
                </p>

            </div>


            <div class="d-flex gap-2">

                <a
                    href="../dashboard.php"
                    class="btn btn-secondary"
                >
                    ← Dashboard
                </a>

                <a
                    href="add.php"
                    class="btn btn-primary"
                >
                    + Add User
                </a>

            </div>

        </div>

    </div>



    <!-- ========================================================= -->
    <!-- SUCCESS MESSAGE -->
    <!-- ========================================================= -->

    <?php if ($success !== ""): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <strong>Success!</strong>

            <?= htmlspecialchars($success) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>



    <!-- ========================================================= -->
    <!-- ERROR MESSAGE -->
    <!-- ========================================================= -->

    <?php if ($error !== ""): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >

            <strong>Error!</strong>

            <?= htmlspecialchars($error) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>



    <!-- ========================================================= -->
    <!-- SUMMARY CARD -->
    <!-- ========================================================= -->

    <div class="row mb-4">

        <div class="col-md-4">

            <div class="card content-card">

                <div class="card-body">

                    <div class="d-flex align-items-center gap-3">

                        <div class="user-icon">
                            👤
                        </div>

                        <div>

                            <small class="text-muted">
                                TOTAL USERS
                            </small>

                            <h3 class="mb-0">
                                <?= $totalUsers ?>
                            </h3>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- ========================================================= -->
    <!-- SEARCH -->
    <!-- ========================================================= -->

    <div class="card content-card mb-4">

        <div class="card-body">

            <form
                method="GET"
                class="row g-2"
            >

                <div class="col-md-8">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search username or role..."
                        value="<?= htmlspecialchars($search) ?>"
                    >

                </div>


                <div class="col-md-2">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        🔎 Search
                    </button>

                </div>


                <div class="col-md-2">

                    <a
                        href="index.php"
                        class="btn btn-outline-secondary w-100"
                    >
                        Clear
                    </a>

                </div>

            </form>

        </div>

    </div>



    <!-- ========================================================= -->
    <!-- USER TABLE -->
    <!-- ========================================================= -->

    <div class="card content-card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">
                    System Users
                </h5>

                <?php if ($search !== ""): ?>

                    <span class="text-muted">

                        Search:

                        <strong>
                            <?= htmlspecialchars($search) ?>
                        </strong>

                    </span>

                <?php endif; ?>

            </div>



            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Username
                            </th>

                            <th>
                                Role
                            </th>

                            <th class="text-center">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if ($result->num_rows > 0): ?>


                        <?php while ($user = $result->fetch_assoc()): ?>


                            <tr>


                                <!-- USER ID -->

                                <td>

                                    <strong>
                                        <?= (int)$user["UserID"] ?>
                                    </strong>

                                </td>


                                <!-- USERNAME -->

                                <td>

                                    <div class="d-flex align-items-center gap-2">

                                        <div class="user-icon">

                                            <?= strtoupper(
                                                substr($user["Username"], 0, 1)
                                            ) ?>

                                        </div>


                                        <div>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $user["Username"]
                                                ) ?>
                                            </strong>


                                            <?php if (
                                                (int)$user["UserID"]
                                                ===
                                                (int)$_SESSION["UserID"]
                                            ): ?>

                                                <br>

                                                <span class="badge bg-success">

                                                    Current User

                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                </td>


                                <!-- ROLE -->

                                <td>


                                    <?php if (
                                        $user["Role"] === "Administrator"
                                    ): ?>

                                        <span
                                            class="badge bg-danger role-badge"
                                        >
                                            Administrator
                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="badge bg-primary role-badge"
                                        >
                                            Staff
                                        </span>

                                    <?php endif; ?>


                                </td>


                                <!-- ACTIONS -->

                                <td class="text-center">

                                    <div class="d-flex justify-content-center gap-2">


                                        <!-- EDIT -->

                                        <a
                                            href="edit.php?id=<?= (int)$user["UserID"] ?>"
                                            class="btn btn-sm btn-warning action-btn"
                                        >
                                            Edit
                                        </a>


                                        <?php if (
                                            (int)$user["UserID"]
                                            !==
                                            (int)$_SESSION["UserID"]
                                        ): ?>


                                            <!-- DELETE -->

                                            <a
                                                href="delete.php?id=<?= (int)$user["UserID"] ?>"
                                                class="btn btn-sm btn-danger action-btn"
                                                onclick="return confirm('Are you sure you want to delete this user?');"
                                            >
                                                Delete
                                            </a>


                                        <?php else: ?>


                                            <button
                                                type="button"
                                                class="btn btn-sm btn-secondary action-btn"
                                                disabled
                                            >
                                                Delete
                                            </button>


                                        <?php endif; ?>


                                    </div>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="4"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <h5>
                                        No users found
                                    </h5>

                                    <?php if ($search !== ""): ?>

                                        <p class="mb-2">
                                            No users matched your search.
                                        </p>

                                        <a
                                            href="index.php"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Clear Search
                                        </a>

                                    <?php else: ?>

                                        <p class="mb-0">
                                            There are no users in the system.
                                        </p>

                                    <?php endif; ?>

                                </div>

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>

    </div>


</div>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>