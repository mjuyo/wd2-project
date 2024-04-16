<?php
    session_start();
    require('connect.php');

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: signin.php');
        exit;
    }

    if (isset($_GET['comment_id'])) {
        $comment_id = filter_input(INPUT_GET, 'comment_id', FILTER_SANITIZE_NUMBER_INT);
        $bounty_id = filter_input(INPUT_GET, 'bounty_id', FILTER_SANITIZE_NUMBER_INT);

        $query = "DELETE FROM comments WHERE comment_id = :comment_id";
        $statement = $db->prepare($query);
        $statement->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
        
        if ($statement->execute()) {
            // Redirect back to the comments page
            header("Location: details.php?bounty_id=" . $bounty_id); 
            exit;
        }        
    }
?>
