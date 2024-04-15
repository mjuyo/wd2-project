<?php 

    session_start();

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: signin.php');
        exit;
    }

    require('connect.php');

    $errors = [];

    // Check if the user ID is present and valid
    $user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    if (!$user_id) {
        // Handle the case where there is no valid ID
        $errors[] = "Invalid user ID.";
    } else {
        // Fetch the user's current data
        $query = "SELECT username, full_name FROM users WHERE user_id = :user_id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch();

        // If the form is submitted, process the form data
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize and validate input data
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $password = $_POST['password']; // Only set if password is being changed

            // Prepare the update statement
            if (empty($password)) {
                // Update without changing the password
                $update_query = "UPDATE users SET username = :username, full_name = :full_name WHERE user_id = :user_id";
                $update_stmt = $db->prepare($update_query);
            } else {
                // Hash the new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                // Update and change the password
                $update_query = "UPDATE users SET username = :username, full_name = :full_name, password = :password WHERE user_id = :user_id";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
            }

            $update_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $update_stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $update_stmt->bindValue(':full_name', $full_name, PDO::PARAM_STR);

            // Execute the update statement
            if ($update_stmt->execute()) {
                // Redirect to user list or confirmation page
                header('Location: dashboard.php');
                exit;
            } else {
                // Handle error
                $errors[] = "Error updating user.";
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
    <meta charset="UTF-8">  
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;500&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/aurebesh" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles.css">
    <title>Edit User - Admin Dashboard</title>
</head>
<body>
    <div class="wrapper">
        <?php include('header.php'); ?>

        <main class="dashboard">
            <h1><a href="dashboard.php">Admin Dashboard</a></h1>

            <?php foreach ($errors as $error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>

            <h2>Edit User</h2>
            <form method="post" action="edit_user.php?user_id=<?= htmlspecialchars($user_id) ?>">
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div>
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>

                <div>
                    <label for="password">New Password (leave blank to not change):</label>
                    <input type="password" id="password" name="password">
                </div>

                <div>
                    <input type="submit" value="Update User">
                </div>
            </form>
        </main>

        <?php include('footer.php'); ?>
    </div>
</body>
</html>
