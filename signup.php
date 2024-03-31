<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 20th, 2024
    Description: PHP Sign Up

****************/

    session_start();
    require('connect.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (strlen($password) < 6) {
            $error = "The password must be 6 character long at least.";
        } elseif ($password !== $confirm_password) {
            $error = "The passwords don't match.";
        } else {
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $statement = $db->prepare("SELECT * FROM users WHERE username = ?");
            $statement->execute([$username]);
            $user_exists = $statement->fetch();

            if ($user_exists) {
                $error = "The username already exists, please choose a different username.";
            } else {
                $hash_password = password_hash($password, PASSWORD_DEFAULT);

                $statement = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $success = $statement->execute([$username, $hash_password]);

                if ($success) {
                    $_SESSION['message'] = "The account was created.";
                    // header("Location: signin.php");
                    // exit();
                } else {
                    $error = "Sign up failed, try again.";
                }
            }

        }
    }

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;500&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/aurebesh" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <title>User Sign Up</title>
</head>
<body>

    <div class="logo-signin">
        <a href="index.php">
            <div class="title">
                <h2 class="special-font">Galactic Bounties Network</h2>
                <h2>Galactic Bounties Network</h2>
            </div>
        </a>
    </div>

    <div class="signin-wrapper">
        <div class="signin-container">
            <a href="signin.php" class="back-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                </svg>
            </a>
            <div class="signin-img">
                <img src="images/hutt_logo.png" >
            </div>

            <h2>Sign Up</h2>
            <?php if(isset($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php elseif(isset($_SESSION['message'])): ?>
                <p class="success"><?= $_SESSION['message'] ?> <a href="signin.php">Sign in now.</a></p>
            <?php endif; ?>

            <form action="signup.php" method="POST">
                <div>
                    <!-- <label for="username">Username:</label> -->
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>
                <div>
                    <!-- <label for="password">Password:</label> -->
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <div>
                    <!-- <label for="confirm_password">Confirm Password:</label> -->
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                </div>
                <div>
                    <input type="submit" value="Sign Up">
                </div>
            </form>
        </div>
    </div>
    <?php include('footer.php'); ?>
</body>
</html>