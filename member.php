<?php
session_start();
require_once("db_connection.php");

// Function to sanitize user inputs
function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library Member Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>

<?php
    include("admin.php");
?>

<body>
    <div class="container">

        <h2 class="center-title display-5">Library Member Registration</h2>

        <?php if (isset($_SESSION['message'])) : ?>
            <div class="alert alert-<?= $_SESSION['msg_type'] ?>" role="alert">
                <?= $_SESSION['message'] ?>
                <!-- Close button -->
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            // Clear the message after displaying
            unset($_SESSION['message']);
            unset($_SESSION['msg_type']);
            ?>
        <?php endif; ?>

        <form action="member_process.php" method="post" class="mx-auto col-lg-6">
            <div class="form-group">
                <label for="memberID">Member ID:</label>
                <input type="text" class="form-control" id="memberID" name="memberID" required placeholder="Enter Member ID (e.g., M001)">
            </div>
            <div class="form-group">
                <label for="firstname">First Name:</label>
                <input type="text" class="form-control" id="firstname" name="firstname" required>
            </div>
            <div class="form-group">
                <label for="lastname">Last Name:</label>
                <input type="text" class="form-control" id="lastname" name="lastname" required>
            </div>
            <div class="form-group">
                <label for="birthday">Birthdate:</label>
                <input type="date" class="form-control" id="birthday" name="birthday" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="button-container">
                <button type="submit" class="btn btn-warning" name="add">Add Member</button>
            </div>
        </form>

        <br>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Birthdate</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $database->query("SELECT * FROM member") or die($database->error);

                while ($row = $result->fetch_assoc()) :
                ?>
                    <tr>
                        <td><?= $row['member_id'] ?></td>
                        <td><?= $row['first_name'] ?></td>
                        <td><?= $row['last_name'] ?></td>
                        <td><?= $row['birthday'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td>
                            <a href="member_process.php?edit=<?= $row['member_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="member_process.php?delete=<?= $row['member_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this member?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
