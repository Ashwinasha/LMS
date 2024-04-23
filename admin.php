<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <title>Admin Panel</title>
    <style>
        body {
            background: url('lm.jpg') center center fixed;
        }

        .navbar {
            background-color: #00008b;
            padding: 10px;
        }

        .navbar-brand {
            margin-right: 0;
            font-size: x-large;
        }

        .navbar-toggler {
            margin-left: 0;
        }

        .navbar-nav {
            text-align: center;
            width: 100%;
        }

        .navbar-nav .nav-item {
            width: 100%;
        }

        .navbar-nav .nav-link {
            padding: 1rem;
            font-size: x-large;
            cursor: pointer;
            /* Add cursor pointer for better user experience */
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #850e05;">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" onclick="loadPage('userfeaure.php')">User</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="loadPage('books.php')">Books</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="loadPage('member.php')">Member</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="loadPage('category.php')">Book Category</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="loadPage('borrow.php')">Book Borrow</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="logout()">Log out</a>
                </li>
            </ul>
        </div>
    </nav>

    <script>
        function loadPage(targetUrl) {
            // Use AJAX to load the content from the target URL
            $.ajax({
                url: targetUrl,
                type: 'GET',
                success: function(data) {
                    // Replace the content of the body with the loaded content
                    $('body').html(data);
                },
                error: function() {
                    alert('Error loading page');
                }
            });
        }

        function logout() {
            // Redirect to index.html
            window.location.href = 'index.html';
        }
    </script>

</body>

</html>
