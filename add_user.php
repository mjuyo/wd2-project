<?php 

/*******w******** 
    
    Name: Marco Juyo
    Date: March 31st, 2024
    Description: PHP Add user

****************/

    session_start();

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: signin.php');
        exit;
    }

    require('connect.php');

    // Add users
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

                $statement = $db->prepare("INSERT INTO users (full_name, username, password) VALUES (?, ?, ?)");
                $success = $statement->execute([$full_name, $username, $hash_password]);

                if ($success) {
                    $_SESSION['message'] = "The account was created.";
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Sign up failed, try again.";
                }
            }

        }
    }


    function is_active($link) {
        // Get the current page file name
        $current_page = basename($_SERVER['PHP_SELF']); 
        return $link == $current_page ? 'active' : '';
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
	<title>Admin Dashboard</title>
</head>
<body>
	<div class="wrapper">
        <?php include('header.php'); ?>
        
        <main class="dashboard">
            <h1><a href="dashboard.php">Admin Dashboard</a></h1>

            <?php if(isset($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php elseif(isset($_SESSION['message'])): ?>
                <p class="success"><?= $_SESSION['message'] ?></p>
            <?php endif; ?>
            
            <h2>Add User</h2>
            <form action="add_user.php" method="POST">
                <div>
                    <input type="text" id="full_name" name="full_name" placeholder="Name" required>
                </div>
                <div>
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>
                <div>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <div>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                </div>
                <div>
                    <input type="submit" value="Add User">
                </div>
            </form>

        </main>

        <?php include('footer.php'); ?>
    </div>
</body>
</html>