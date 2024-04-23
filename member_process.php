<?php
session_start();
require_once("db_connection.php");

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
        header("Location: member.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        // Update member
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
            // Perform the update operation
            $database->query("UPDATE member SET first_name='$firstname', last_name='$lastname', birthday='$birthday', email='$email' WHERE member_id='$memberID'")
                or die($database->error);

            $_SESSION['message'] = "Member updated successfully!";
            $_SESSION['msg_type'] = "success";
        }

        // Redirect back to the form page
        header("Location: member.php");
        exit();
    }
}

// Retrieve member information for editing
if (isset($_GET['edit'])) {
    $memberID = sanitize_input($_GET['edit']);
    $result = $database->query("SELECT * FROM member WHERE member_id='$memberID'");

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $firstname = $row['first_name'];
        $lastname = $row['last_name'];
        $birthday = $row['birthday'];
        $email = $row['email'];
    }
}

if (isset($_GET['delete'])) {
    $memberID = sanitize_input($_GET['delete']);
    $database->query("DELETE FROM member WHERE member_id='$memberID'") or die($database->error);

    $_SESSION['message'] = "Member deleted successfully!";
    $_SESSION['msg_type'] = "danger";
    header("Location: member.php");
    exit();
}

?>

<!-- HTML Form for Editing -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
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
    <h2 class="center-title display-5">Edit Member</h2>

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
            <input type="text" class="form-control" id="memberID" name="memberID" value="<?php echo $memberID; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="firstname">First Name:</label>
            <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $firstname; ?>"
                   required>
        </div>
        <div class="form-group">
            <label for="lastname">Last Name:</label>
            <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $lastname; ?>"
                   required>
        </div>
        <div class="form-group">
            <label for="birthday">Birthdate:</label>
            <input type="date" class="form-control" id="birthday" name="birthday" value="<?php echo $birthday; ?>"
                   required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
        </div>

        <div class="button-container">
            <button type="submit" class="btn btn-warning" name="update">Update Member</button>
            <a href="member.php" class="btn btn-danger">Close</a>
        </div>
    </form>
</div>
</body>
</html>
