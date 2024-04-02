<?php 
    session_start();

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: signin.php');
        exit;
    }

    require('connect.php');

    if($_POST && isset($_POST['bounty_id']) && $_POST['action'] == 'Delete'){
        $bounty_id = filter_input(INPUT_POST, 'bounty_id', FILTER_SANITIZE_NUMBER_INT);

        // Delete comment
        $del_comments_query = "DELETE FROM comments WHERE bounty_id = :bounty_id";
        $del_comments_stmt = $db->prepare($del_comments_query);
        $del_comments_stmt->bindValue(':bounty_id', $bounty_id, PDO::PARAM_INT);
        $del_comments_stmt->execute();

        // Delete bounty
        $delete_query = "DELETE FROM bounties WHERE bounty_id = :bounty_id";
        $stmt = $db->prepare($delete_query);
        $stmt->bindValue(':bounty_id', $bounty_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Redirect
            $redirect_to = filter_input(INPUT_POST, 'redirect_to', FILTER_SANITIZE_STRING);
        
            header('Location: ' . (!empty($redirect_to) ? $redirect_to : 'content.php'));
            exit;
        } else {
            // Handle error
            $error = "Error deleting bounty.";
        }
    }

?>
