<?php
session_start();
require_once("db_connection.php");

// Function to sanitize user inputs
function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to validate Borrow ID format
function validate_borrow_id($borrowID)
{
    return preg_match('/^BR\d{3}$/', $borrowID);
}

// Function to validate Book ID format
function validate_book_id($bookID)
{
    return preg_match('/^B\d{3}$/', $bookID);
}

// Function to validate Member ID format
function validate_member_id($memberID)
{
    return preg_match('/^M\d{3}$/', $memberID);
}

// Function to validate Date format
function validate_date($date)
{
    $d1 = DateTime::createFromFormat('Y-m-d', $date);
    $d2 = DateTime::createFromFormat('m/d/Y', $date);
    return ($d1 && $d1->format('Y-m-d') === $date) || ($d2 && $d2->format('m/d/Y') === $date);
}

// Function to add borrow details
function add_borrow_details($borrowID, $bookID, $memberID, $borrowStatus, $modifiedDate)
{
    global $database;

    $database->query("INSERT INTO bookborrower (borrow_id, book_id, member_id, borrow_status, borrower_date_modified) VALUES ('$borrowID', '$bookID', '$memberID', '$borrowStatus', '$modifiedDate')")
        or die($database->error);

    $_SESSION['message'] = "Borrow details added successfully!";
    $_SESSION['msg_type'] = "success";
}

// Function to update borrow details
function update_borrow_details($originalBorrowID, $borrowID, $bookID, $memberID, $borrowStatus, $modifiedDate)
{
    global $database;

    $database->query("UPDATE bookborrower SET borrow_id='$borrowID', book_id='$bookID', member_id='$memberID', borrow_status='$borrowStatus', borrower_date_modified='$modifiedDate' WHERE borrow_id='$originalBorrowID'")
        or die($database->error);

    $_SESSION['message'] = "Borrow details updated successfully!";
    $_SESSION['msg_type'] = "warning";
}

// Function to display borrow details
function display_borrow_details()
{
    global $database;
    $result = $database->query("SELECT * FROM bookborrower") or die($database->error);
    return $result;
}

// Function to delete borrow details
function delete_borrow_details($borrowID)
{
    global $database;

    $database->query("DELETE FROM bookborrower WHERE borrow_id='$borrowID'") or die($database->error);

    $_SESSION['message'] = "Borrow detail deleted successfully!";
    $_SESSION['msg_type'] = "danger";
}

// CRUD Operations
// CRUD Operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        // Add new borrow details
        $borrowID = sanitize_input($_POST['borrowID']);
        $bookID = sanitize_input($_POST['bookID']);
        $memberID = sanitize_input($_POST['memberID']);
        $borrowStatus = sanitize_input($_POST['borrowStatus']);
        $modifiedDate = date('Y-m-d H:i:s'); // system date

        // Validate Borrow ID, Book ID, Member ID, and Date format
        if (!validate_borrow_id($borrowID)) {
            $_SESSION['message'] = "Invalid Borrow ID format.";
            $_SESSION['msg_type'] = "danger";
        } elseif (!validate_book_id($bookID)) {
            $_SESSION['message'] = "Invalid Book ID format.";
            $_SESSION['msg_type'] = "danger";
        } elseif (!validate_member_id($memberID)) {
            $_SESSION['message'] = "Invalid Member ID format.";
            $_SESSION['msg_type'] = "danger";
        } else {
            // Check if memberID exists in the member table
            $checkMemberQuery = "SELECT * FROM member WHERE member_id = '$memberID'";
            $checkMemberResult = $database->query($checkMemberQuery);

            // Check if bookID exists in the book table
            $checkBookQuery = "SELECT * FROM book WHERE book_id = '$bookID'";
            $checkBookResult = $database->query($checkBookQuery);

            if ($checkMemberResult->num_rows == 0) {
                $_SESSION['message'] = "Member ID does not exist.";
                $_SESSION['msg_type'] = "danger";
            } elseif ($checkBookResult->num_rows == 0) {
                $_SESSION['message'] = "Book ID does not exist.";
                $_SESSION['msg_type'] = "danger";
            } else {
                try {
                    // Both memberID and bookID exist, proceed with adding borrow details
                    add_borrow_details($borrowID, $bookID, $memberID, $borrowStatus, $modifiedDate);
                } catch (mysqli_sql_exception $e) {
                    // Handle the MySQLi SQL exception for duplicate entry
                    if ($e->getCode() == 1062) { // Error code for duplicate entry
                        $_SESSION['message'] = " Borrow ID already exist.";
                        $_SESSION['msg_type'] = "danger";
                    } else {
                        throw $e; // Re-throw the exception if it's not a duplicate entry error
                    }
                }
            }
        }
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
}


// Update borrow details
if (isset($_POST['update'])) {
    $originalBorrowID = sanitize_input($_POST['originalBorrowID']);
    $borrowID = sanitize_input($_POST['borrowID']);
    $bookID = sanitize_input($_POST['bookID']);
    $memberID = sanitize_input($_POST['memberID']);
    $borrowStatus = sanitize_input($_POST['borrowStatus']);
    $modifiedDate = date('Y-m-d H:i:s'); // system date

    // Validate Borrow ID, Book ID, Member ID, and Date format
    if (!validate_borrow_id($borrowID)) {
        $_SESSION['message'] = "Invalid Borrow ID format.";
        $_SESSION['msg_type'] = "danger";
    } elseif (!validate_book_id($bookID)) {
        $_SESSION['message'] = "Invalid Book ID format.";
        $_SESSION['msg_type'] = "danger";
    } elseif (!validate_member_id($memberID)) {
        $_SESSION['message'] = "Invalid Member ID format.";
        $_SESSION['msg_type'] = "danger";
    } else {
        // Check if memberID exists in the member table
        $checkMemberQuery = "SELECT * FROM member WHERE member_id = '$memberID'";
        $checkMemberResult = $database->query($checkMemberQuery);

        // Check if bookID exists in the book table
        $checkBookQuery = "SELECT * FROM book WHERE book_id = '$bookID'";
        $checkBookResult = $database->query($checkBookQuery);

        if ($checkMemberResult->num_rows == 0) {
            $_SESSION['message'] = "Member ID does not exist.";
            $_SESSION['msg_type'] = "danger";
        } elseif ($checkBookResult->num_rows == 0) {
            $_SESSION['message'] = "Book ID does not exist.";
            $_SESSION['msg_type'] = "danger";
        } else {
            // Both memberID and bookID exist, proceed with updating borrow details
            update_borrow_details($originalBorrowID, $borrowID, $bookID, $memberID, $borrowStatus, $modifiedDate);
        }
    }
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}


// Delete borrow details
if (isset($_GET['delete'])) {
    $borrowID = sanitize_input($_GET['delete']);
    delete_borrow_details($borrowID);
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

// Edit borrow details - Populate form with existing data
if (isset($_GET['edit'])) {
    $editBorrowID = sanitize_input($_GET['edit']);
    $editResult = $database->query("SELECT * FROM bookborrower WHERE borrow_id='$editBorrowID'") or die($database->error);

    if ($editResult->num_rows == 1) {
        $editData = $editResult->fetch_assoc();
        $editBorrowID = $editData['borrow_id'];
        $editBookID = $editData['book_id'];
        $editMemberID = $editData['member_id'];
        $editBorrowStatus = $editData['borrow_status'];
        // $editModifiedDate = $editData['borrower_date_modified']; // You may choose to display or use this date
    } else {
        // Redirect to the main page if borrow details not found
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Library Borrow Details</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<?php
    include("admin.php");
?>
<body>

    <div class="container">
        <h2 class="center-title display-5">Library Borrow Details</h2>

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
            <label for="borrowID">Borrow ID:</label>
            <input type="text" class="form-control" id="borrowID" name="borrowID" value="<?php echo isset($editBorrowID) ? $editBorrowID : ''; ?>" <?php echo isset($editBorrowID) ? 'readonly' : ''; ?> required>
            <small class="error-message">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add']) && !validate_member_id($_POST['memberID'])) echo "Invalid Member ID format. Example: M001"; ?>
            </small>
        </div>

            <div class="form-group">
                <label for="bookID">Book ID:</label>
                <input type="text" class="form-control" id="bookID" name="bookID" value="<?php echo isset($editBookID) ? $editBookID : ''; ?>" required>
                <small class="error-message">
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add']) && !validate_member_id($_POST['memberID'])) echo "Invalid Member ID format. Example: M001"; ?>
                </small>
            </div>
            <div class="form-group">
                <label for="memberID">Member ID:</label>
                <input type="text" class="form-control" id="memberID" name="memberID" value="<?php echo isset($editMemberID) ? $editMemberID : ''; ?>" required>
                <small class="error-message">
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add']) && !validate_member_id($_POST['memberID'])) echo "Invalid Member ID format. Example: M001"; ?>
                </small>
            </div>
            <div class="form-group">
                <label for="borrowStatus">Borrow Status:</label>
                <select class="form-control" id="borrowStatus" name="borrowStatus" required>
                    <option value="Borrowed" <?php echo (isset($editBorrowStatus) && $editBorrowStatus == 'Borrowed') ? 'selected' : ''; ?>>Borrowed</option>
                    <option value="Available" <?php echo (isset($editBorrowStatus) && $editBorrowStatus == 'Available') ? 'selected' : ''; ?>>Available</option>
                </select>
            </div>
            <div class="button-container">
                <button type="submit" class="btn btn-warning" name="<?= isset($editBorrowID) ? 'update' : 'add' ?>">
                    <?= isset($editBorrowID) ? 'Update Info' : 'Add Info ' ?>
                </button>
                <?php if (isset($editBorrowID)) : ?>
                    <input type="hidden" name="originalBorrowID" value="<?= $editBorrowID ?>">
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-danger" style="margin-left: 10px;">Cancel</a>
                <?php endif; ?>
            </div>


        </form>
        <br>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Borrow ID</th>
                    <th>Book ID</th>
                    <th>Member ID</th>
                    <th>Borrow Status</th>
                    <th>Date Modified</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $database->query("SELECT * FROM bookborrower") or die($database->error);

                while ($row = $result->fetch_assoc()) :
                ?>
                    <tr>
                        <td><?php echo $row['borrow_id']; ?></td>
                        <td><?php echo $row['book_id']; ?></td>
                        <td><?php echo $row['member_id']; ?></td>
                        <td><?php echo $row['borrow_status']; ?></td>
                        <td><?php echo $row['borrower_date_modified']; ?></td>
                        <td>
                            <a href="<?php echo $_SERVER['PHP_SELF'] . '?edit=' . $row['borrow_id']; ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo $_SERVER['PHP_SELF'] . '?delete=' . $row['borrow_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    </div>
    </div>



</body>

</html>
