<<?php 
    session_start();

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: signin.php');
        exit;
    }

    require('connect.php');

    if (isset($_GET['user_id'])) {
        $user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);

        // Prepare and execute DELETE statement
        $delete_query = "DELETE FROM users WHERE user_id = :user_id";
        $stmt = $db->prepare($delete_query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Redirect to dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            // Handle error
            $error_message = "Error deleting user.";
        }
    }

?>
