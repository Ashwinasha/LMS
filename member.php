<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library_system";

// Create connection
$database = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($database->connect_error) {
    die("Connection failed: " . $database->connect_error);
}

// Function to sanitize user inputs
function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to validate Member ID format
function validate_member_id($memberID)
{
    return preg_match('/^M\d{3}$/', $memberID);
}

// Function to validate Email format
function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate Date format
function validate_date($date)
{
    $d1 = DateTime::createFromFormat('Y-m-d', $date);
    $d2 = DateTime::createFromFormat('m/d/Y', $date);
    return ($d1 && $d1->format('Y-m-d') === $date) || ($d2 && $d2->format('m/d/Y') === $date);
}

// CRUD Operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        // Add new member
        $memberID = sanitize_input($_POST['memberID']);
        $firstname = sanitize_input($_POST['firstname']);
        $lastname = sanitize_input($_POST['lastname']);
        $birthday = sanitize_input($_POST['birthday']);
        $email = sanitize_input($_POST['email']);

        // Validate email, Member ID, and Date format
        if (!validate_email($email)) {
            $_SESSION['message'] = "Invalid email format.";
            $_SESSION['msg_type'] = "danger";
        } elseif (!validate_member_id($memberID)) {
            $_SESSION['message'] = "Invalid Member ID format.";
            $_SESSION['msg_type'] = "danger";
        } elseif (!validate_date($birthday)) {
            $_SESSION['message'] = "Invalid date format. Please use YYYY-MM-DD or MM/DD/YYYY.";
            $_SESSION['msg_type'] = "danger";
        } else {
            try {
                $database->query("INSERT INTO member (member_id, first_name, last_name, birthday, email) VALUES ('$memberID', '$firstname', '$lastname', '$birthday', '$email')")
                    or die($database->error);
            
                $_SESSION['message'] = "Member added successfully!";
                $_SESSION['msg_type'] = "success";
            } catch (mysqli_sql_exception $e) {
                // Handle the MySQLi SQL exception for duplicate entry
                if ($e->getCode() == 1062) { // Error code for duplicate entry
                    $_SESSION['message'] = " Member with the same ID already exists.";
                    $_SESSION['msg_type'] = "danger";
                } else {
                    throw $e; // Re-throw the exception if it's not a duplicate entry error
                }
            }            
        }
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
}

if (isset($_POST['update'])) {
    // Update member
    $originalMemberID = sanitize_input($_POST['originalMemberID']);
    $memberID = sanitize_input($_POST['memberID']);
    $firstname = sanitize_input($_POST['firstname']);
    $lastname = sanitize_input($_POST['lastname']);
    $birthday = sanitize_input($_POST['birthday']);
    $email = sanitize_input($_POST['email']);

    // Validate email, Member ID, and Date format
    if (!validate_email($email)) {
        $_SESSION['message'] = "Invalid email format.";
        $_SESSION['msg_type'] = "danger";
    } elseif (!validate_member_id($memberID)) {
        $_SESSION['message'] = "Invalid Member ID format.";
        $_SESSION['msg_type'] = "danger";
    } elseif (!validate_date($birthday)) {
        $_SESSION['message'] = "Invalid date format. Please use YYYY-MM-DD or MM/DD/YYYY.";
        $_SESSION['msg_type'] = "danger";
    } else {
        // Check if the new memberID already exists (excluding the current member being edited)
        $checkQuery = $database->query("SELECT member_id FROM member WHERE member_id='$memberID' AND member_id<>'$originalMemberID'");
        if ($checkQuery->num_rows > 0) {
            $_SESSION['message'] = "Error: Member ID already exists.";
            $_SESSION['msg_type'] = "danger";
        } else {
            // Perform the update operation
            $database->query("UPDATE member SET member_id='$memberID', first_name='$firstname', last_name='$lastname', birthday='$birthday', email='$email' WHERE member_id='$originalMemberID'")
                or die($database->error);

            $_SESSION['message'] = "Member updated successfully!";
            $_SESSION['msg_type'] = "warning";
            header("Location: {$_SERVER['PHP_SELF']}");
            exit();
        }
    }
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

// Edit member - Populate form with existing data
if (isset($_GET['edit'])) {
    $editMemberID = sanitize_input($_GET['edit']);
    $editResult = $database->query("SELECT * FROM member WHERE member_id='$editMemberID'") or die($database->error);
    
    if ($editResult->num_rows == 1) {
        $editData = $editResult->fetch_assoc();
        $editMemberID = $editData['member_id'];
        $editFirstname = $editData['first_name'];
        $editLastname = $editData['last_name'];
        $editBirthday = $editData['birthday'];
        $editEmail = $editData['email'];
    } else {
        // Redirect to the main page if member not found
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
    }
    
    // Delete member
    if (isset($_GET['delete'])) {
        $memberID = sanitize_input($_GET['delete']);
        $database->query("DELETE FROM member WHERE member_id='$memberID'") or die($database->error);
    
        $_SESSION['message'] = "Member deleted successfully!";
        $_SESSION['msg_type'] = "danger";
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
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
    <style>
        body {
            background-image: url('lm.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        .center-title {
            text-align: center;
            color: #fff;
            margin-bottom: 30px;
            background-color: darkblue;
            padding: 10px;
            border-radius: 5px;
        }

        .error-message {
            color: red;
        }

        .form-group {
            text-align: left;
            margin-bottom: 30px;
        }

        .form-group input {
            width: 450px;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }

        .form-group label {
            font-weight: bold;
            font-size: 17px;
        }

        table {
            margin-top: 20px;
        }

        th,
        td {
            text-align: center;
        }

        th {
            background-color: #343a40;
            color: #ffffff;
        }

        .btn-warning,
        .btn-danger,
        .btn-secondary {
            padding: 5px 10px;
            margin-right: 5px;
        }

        .btn-warning:hover,
        .btn-danger:hover,
        .btn-secondary:hover {
            opacity: 0.8;
        }

        .btn-primary {
            background-color: #28a745;
            /* Green color */
            border: none;
        }

        .btn-primary:hover {
            background-color: #218838;
            /* Darker green color on hover */
        }

        .btn-secondary {
            background-color: #dc3545;
            /* Red color */
            border: none;
        }

        .btn-secondary:hover {
            background-color: #c82333;
            /* Darker red color on hover */
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">

        <h2 class="center-title display-4">Library Member Registration</h2>

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

        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" class="mx-auto col-lg-6">
            <div class="form-group">
                <label for="memberID">Member ID:</label>
                <input type="text" class="form-control" id="memberID" name="memberID" value="<?= isset($editMemberID) ? $editMemberID : '' ?>" <?= isset($editMemberID) ? 'readonly' : '' ?> required placeholder="Enter Member ID (e.g., M001)">
                <small class="error-message">
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add']) && !validate_member_id($_POST['memberID'])) echo "Invalid Member ID format. Example: M001"; ?>
                </small>
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
                <label for="birthday">Birthdate:</label>
                <input type="date" class="form-control" id="birthday" name="birthday" value="<?= isset($editBirthday) ? $editBirthday : '' ?>" required>
                <small class="error-message">
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add']) && !validate_date($_POST['birthday'])) echo "Invalid date format. Please use YYYY-MM-DD or MM/DD/YYYY."; ?>
                </small>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= isset($editEmail) ? $editEmail : '' ?>" required>
                <small class="error-message">
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add']) && !validate_email($_POST['email'])) echo "Invalid email format."; ?>
                </small>
            </div>

            <div class="button-container">
                <button type="submit" class="btn btn-primary" name="<?= isset($editMemberID) ? 'update' : 'add' ?>">
                    <?= isset($editMemberID) ? 'Update Member' : 'Add Member' ?>
                </button>
                <?php if (isset($editMemberID)) : ?>
                    <input type="hidden" name="originalMemberID" value="<?= $editMemberID ?>">
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
                <?php endif; ?>
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
                    <th>Action</th>
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
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?edit=<?= $row['member_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?delete=<?= $row['member_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this member?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>