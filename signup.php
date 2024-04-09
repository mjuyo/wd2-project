<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 20th, 2024
    Description: PHP Sign Up

****************/

    session_start();
    require('connect.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $full_name = $_POST['full_name'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (strlen($password) < 6) {
            $error = "The password must be 6 character long at least.";
        } elseif ($password !== $confirm_password) {
            $error = "The passwords don't match.";
        } else {
            $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $statement = $db->prepare("SELECT * FROM users WHERE username = ?");
            $statement->execute([$username]);
            $user_exists = $statement->fetch();

            if ($user_exists) {
                $error = "The username already exists, please choose a different username.";
            } else {
                $hash_password = password_hash($password, PASSWORD_DEFAULT);

                $statement = $db->prepare("INSERT INTO users (full_name, username, password) VALUES (?, ?)");
                $success = $statement->execute([$full_name, $username, $hash_password]);

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">    
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

    <div class="signin-container">
        <div class="signin-wrapper">
            <form action="signup.php" method="POST">
                <div class="back-and-img">
                    <a href="signin.php" class="back-button">
                        <i class="bi bi-arrow-left-circle"></i>
                    </a>
                    <div class="signin-img">
                        <img src="images/hutt_logo.png" >
                    </div>
                </div>

                <h2>Sign Up</h2>

                <?php if(isset($error)): ?>
                    <p class="error signin-error"><?= $error ?></p>
                <?php elseif(isset($_SESSION['message'])): ?>
                    <p class="success"><?= $_SESSION['message'] ?> <a href="signin.php">Sign in now.</a></p>
                <?php endif; ?>

                <div class="input-box">
                    <input type="text" id="full_name" name="full_name" placeholder="Name" required>
                </div>
                <div class="input-box">
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>
                <div class="input-box">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <div class="input-box">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                </div>

                <button type="submit" class="signin-signup-btn">Sign Up</button>

                <div class="register-link">
                    <p>Have account? <a href="signin.php">Sign in now.</a></p>
                </div>
            </form>
        </div>
    </div>
    <?php include('footer.php'); ?>
</body>
</html>