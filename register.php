<?php
require_once('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve user input
    $user_id = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Validate user ID format using regular expression
    if (!preg_match('/^U\d{3}$/', $user_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid User ID format.']);
        exit;
    }

    // Validate password length
    if (strlen($password) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters.']);
        exit;
    }

    // Check if the username already exists
    $checkUsernameQuery = "SELECT * FROM user WHERE username = '$username'";
    $result = $database->query($checkUsernameQuery);
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
        exit;
    }

    // Check if the email already exists
    $checkEmailQuery = "SELECT * FROM user WHERE email = '$email'";
    $result = $database->query($checkEmailQuery);
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
        exit;
    }

    // Hash the password before storing it in the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user details into the database
    $insertUserQuery = "INSERT INTO user (user_id, email, first_name, last_name, username, password)
                        VALUES ('$user_id', '$email', '$first_name', '$last_name', '$username', '$hashedPassword')";

    if ($database->query($insertUserQuery) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Registration successful.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error occurred during registration.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

// Close the database connection
$database->close();

?>
