<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 20th, 2024
    Description: PHP Sign In

****************/

    session_start();
    require('connect.php');

    $signin_error = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signin'])) {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = $_POST['password'];

        $statement = $db->prepare('SELECT * FROM users WHERE username = ?');
        $statement->execute([$username]);
        $users = $statement->fetch();

        if ($users && password_verify($password, $users['password'])) {
            // Common session for all users
            $_SESSION['user_id'] = $users['user_id'];
            $_SESSION['username'] = $users['username'];
            $_SESSION['full_name'] = $users['full_name'];

            // Check if the user is admin
            if (!empty($users['is_admin'])) {
                $_SESSION['is_admin'] = true;
                header('Location: index.php'); //Later to redirect to deashboard
            } else {
                $_SESSION['is_admin'] = false;
                header('Location: index.php');
            }
            exit();
            
        } else {
            $signin_error = "Incorrect username or password.";
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
    <title>User Sign In</title>
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
            <form action="signin.php" method="POST">
                <div class="signin-img">
                    <img src="images/hutt_logo.png" >
                </div>

                <h2>Sign in</h2>

                <?php if (!empty($signin_error)): ?>
                    <p class="error signin-error"><?= $signin_error ?></p>
                <?php endif; ?>

                <input type="hidden" name="signin">

                <div class="input-box">
                    <input type="text" id="username" name="username" placeholder="Username" required>
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="input-box">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <i class="bi bi-lock-fill"></i>
                </div>

                <button type="submit" class="signin-signup-btn">Sign In</button>

                <div class="register-link">
                    <p>No account? <a href="signup.php">Create yours now.</a></p>
                </div>
            </form>
        </div>
    </div>
    <?php include('footer.php'); ?>
</body>
</html>


