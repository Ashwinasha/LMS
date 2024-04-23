<?php
// check_login.php

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve username and password from the POST data
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Perform basic validation (you should implement more robust validation)
    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit();
    }

    // Replace these credentials with your actual database credentials
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $dbname = "library_system";

    // Create a connection
    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    // Check connection
    if ($conn->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection error']);
        exit();
    }

    // Use parameterized query to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM user WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);

    // Execute the query
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Successful login
        echo json_encode(['status' => 'success']);
    } else {
        // Failed login
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
