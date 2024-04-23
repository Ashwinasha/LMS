<?php
session_start();
require_once('db_connection.php');

// Function to sanitize user inputs
function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to validate Member ID format
function validate_user_id($userID)
{
    return preg_match('/^U\d{3}$/', $userID);
}

// Function to validate Email format
function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate password format
function validate_password($password)
{
    return strlen($password) >= 8;
}

// Function to hash the password using SHA-256
function hash_password($password)
{
    return hash('sha256', $password);
}

// CRUD Operations
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    // Update member
    $originalUserID = sanitize_input($_POST['originalUserID']);
    $userID = sanitize_input($_POST['userID']);
    $firstname = sanitize_input($_POST['firstname']);
    $lastname = sanitize_input($_POST['lastname']);
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);

    // Validate email, Member ID, and Date format
    if (!validate_email($email)) {
        $_SESSION['message'] = "Invalid email format.";
        $_SESSION['msg_type'] = "danger";
    } elseif (!validate_user_id($userID)) {
        $_SESSION['message'] = "Invalid User ID format.";
        $_SESSION['msg_type'] = "danger";
    } elseif (!empty($password) && !validate_password($password)) {
        $_SESSION['message'] = "Password must be more than 8 characters.";
        $_SESSION['msg_type'] = "danger";
    } else {
        $hashedPassword = empty($password) ? null : hash_password($password);

        $updateQuery = "UPDATE user SET user_id='$userID', first_name='$firstname', last_name='$lastname', username='$username', email='$email'";

        if (!empty($password)) {
            $updateQuery .= ", password='$hashedPassword'";
        }

        $updateQuery .= " WHERE user_id='$originalUserID'";

        $database->query($updateQuery) or die($database->error);

        $_SESSION['message'] = "User updated successfully!";
        $_SESSION['msg_type'] = "warning";
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
}

// Delete member
if (isset($_GET['delete'])) {
    $userID = sanitize_input($_GET['delete']);
    $database->query("DELETE FROM user WHERE user_id='$userID'") or die($database->error);

    $_SESSION['message'] = "User deleted successfully!";
    $_SESSION['msg_type'] = "danger";
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

// Edit member - Populate form with existing data
if (isset($_GET['edit'])) {
    $editUserID = sanitize_input($_GET['edit']);
    $editResult = $database->query("SELECT * FROM user WHERE user_id='$editUserID'") or die($database->error);

    if ($editResult->num_rows == 1) {
        $editData = $editResult->fetch_assoc();
        $editUserID = $editData['user_id'];
        $editFirstname = $editData['first_name'];
        $editLastname = $editData['last_name'];
        $editUsername = $editData['username'];
        $editEmail = $editData['email'];
    } else {
        // Redirect to the main page if member not found
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library Member Registration</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<?php
    include("admin.php");
?>
<body>
    <div class="container">

        <h2 class="center-title display-5">User Details</h2>

        <?php if (isset($_SESSION['message'])) : ?>
            <div class="alert alert-<?= $_SESSION['msg_type'] ?>" role="alert">
                <?= $_SESSION['message'] ?>
            </div>
            <?php
            // Clear the message after displaying
            unset($_SESSION['message']);
            unset($_SESSION['msg_type']);
            ?>
        <?php endif; ?>

        <?php if (isset($editUserID)) : ?>
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" class="mx-auto col-lg-6">
                <div class="form-group">
                    <label for="userID">User ID:</label>
                    <input type="text" class="form-control" id="userID" name="userID" value="<?= isset($editUserID) ? $editUserID : '' ?>" required readonly>
                </div>
                <div class="form-group">
                    <label for="firstname">First Name:</label>
                    <input type="text" class="form-control" id="firstname" name="firstname" value="<?= isset($editFirstname) ? $editFirstname : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="lastname">Last Name:</label>
                    <input type="text" class="form-control" id="lastname" name="lastname" value="<?= isset($editLastname) ? $editLastname : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="userame">User name:</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= isset($editUsername) ? $editUsername : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="error-message">
                        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update']) && !validate_password($_POST['password'])) echo "Password must be more than 8 characters."; ?>
                    </small>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= isset($editEmail) ? $editEmail : '' ?>" required>
                    <small class="error-message">
                        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update']) && !validate_email($_POST['email'])) echo "Invalid email format."; ?>
                    </small>
                </div>

                <div class="button-container">
                    <button type="submit" class="btn btn-warning" name="update">Update User</button>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-danger" style="margin-left: 10px;">Cancel</a>
                </div>
                <input type="hidden" name="originalUserID" value="<?= $editUserID ?>">
            </form>
        <?php endif; ?>

        <br>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <!-- Add the Password column to the table structure -->
                    <th>Password</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $database->query("SELECT user_id, first_name, last_name, username, email, password FROM user") or die($database->error);

                while ($row = $result->fetch_assoc()) :
                ?>
                    <tr>
                        <td><?= $row['user_id'] ?></td>
                        <td><?= $row['first_name'] ?></td>
                        <td><?= $row['last_name'] ?></td>
                        <td><?= $row['username'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <!-- Displaying a placeholder text for the password -->
                        <td>********</td>
                        <td>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?edit=<?= $row['user_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?delete=<?= $row['user_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
