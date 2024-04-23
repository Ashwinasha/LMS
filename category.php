<?php
session_start();
require_once("db_connection.php");

// Function to sanitize user inputs
function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to validate Category ID format
function validate_category_id($categoryID)
{
    return preg_match('/^C\d{3}$/', $categoryID);
}

// CRUD Operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        // Add new book category
        $categoryID = sanitize_input($_POST['categoryID']);
        $categoryName = sanitize_input($_POST['categoryName']);
        $dateModified = date('Y-m-d H:i:s');

        // Validate Category ID format
        if (!validate_category_id($categoryID)) {
            $_SESSION['message'] = "Invalid Category ID format. Example: C001";
            $_SESSION['msg_type'] = "danger";
        } else {
            try {
                $database->query("INSERT INTO bookcategory (category_id, category_name, date_modified) VALUES ('$categoryID', '$categoryName', '$dateModified')")
                    or die($database->error);

                $_SESSION['message'] = "Book category added successfully!";
                $_SESSION['msg_type'] = "success";
            } catch (mysqli_sql_exception $e) {
                // Handle the MySQLi SQL exception for duplicate entry
                if ($e->getCode() == 1062) { // Error code for duplicate entry
                    $_SESSION['message'] = " Book category with the same ID already exists.";
                    $_SESSION['msg_type'] = "danger";
                } else {
                    throw $e; // Re-throw the exception if it's not a duplicate entry error
                }
            }
        }
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }

    if (isset($_POST['update'])) {
        // Update book category
        $originalCategoryID = sanitize_input($_POST['originalCategoryID']);
        $categoryID = sanitize_input($_POST['categoryID']);
        $categoryName = sanitize_input($_POST['categoryName']);
        $dateModified = date('Y-m-d H:i:s');

        // Validate Category ID format
        if (!validate_category_id($categoryID)) {
            $_SESSION['message'] = "Invalid Category ID format. Example: C001";
            $_SESSION['msg_type'] = "danger";
        } else {
            $database->query("UPDATE bookcategory SET category_id='$categoryID', category_name='$categoryName', date_modified='$dateModified' WHERE category_id='$originalCategoryID'")
                or die($database->error);

            $_SESSION['message'] = "Book category updated successfully!";
            $_SESSION['msg_type'] = "warning";
        }
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
}

// Delete book category
if (isset($_GET['delete'])) {
    $categoryID = sanitize_input($_GET['delete']);
    $database->query("DELETE FROM bookcategory WHERE category_id='$categoryID'") or die($database->error);

    $_SESSION['message'] = "Book category deleted successfully!";
    $_SESSION['msg_type'] = "danger";
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

// Edit book category - Populate form with existing data
if (isset($_GET['edit'])) {
    $editCategoryID = sanitize_input($_GET['edit']);
    $editResult = $database->query("SELECT * FROM bookcategory WHERE category_id='$editCategoryID'") or die($database->error);

    if ($editResult->num_rows == 1) {
        $editData = $editResult->fetch_assoc();
        $editCategoryID = $editData['category_id'];
        $editCategoryName = $editData['category_Name'];
    } else {
        // Redirect to the main page if category not found
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
    <title>Library Book Category Registration</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<?php
    include("admin.php");
?>
<body>
    <div class="container">

        <h2 class="center-title display-5">Library Book Category Registration</h2>

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
            <label for="categoryID">Category ID:</label>
            <input type="text" class="form-control" id="categoryID" name="categoryID" 
                value="<?= isset($editCategoryID) ? $editCategoryID : '' ?>" 
                <?= isset($editCategoryID) ? 'readonly' : '' ?> required>
            <small class="error-message">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add']) && !validate_category_id($_POST['categoryID'])) echo "Invalid Category ID format. Example: C001"; ?>
            </small>
        </div>

            <div class="form-group">
                <label for="categoryName">Category Name:</label>
                <input type="text" class="form-control" id="categoryName" name="categoryName" value="<?= isset($editCategoryName) ? $editCategoryName : '' ?>" required>
            </div>

            <div class="button-container">
                <button type="submit" class="btn btn-warning" name="<?= isset($editCategoryID) ? 'update' : 'add' ?>">
                    <?= isset($editCategoryID) ? 'Update Category' : 'Add Category' ?>
                </button>
                <?php if (isset($editCategoryID)) : ?>
                    <input type="hidden" name="originalCategoryID" value="<?= $editCategoryID ?>">
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-danger" style="margin-left: 10px;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>

        <br>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Category ID</th>
                    <th>Category Name</th>
                    <th>Date Modified</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $database->query("SELECT * FROM bookcategory") or die($database->error);

                while ($row = $result->fetch_assoc()) :
                ?>
                    <tr>
                        <td><?= $row['category_id'] ?></td>
                        <td><?= $row['category_Name'] ?></td>
                        <td><?= $row['date_modified'] ?></td>
                        <td>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?edit=<?= $row['category_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?delete=<?= $row['category_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
