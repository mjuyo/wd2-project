<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 18th, 2024
    Description: PHP Details for bounties.

****************/

    session_start();

    if (!isset($_SESSION['is_admin'])) {
        // Default value for users who are not logged in or are not admins
        $_SESSION['is_admin'] = false; 
    }

    require('connect.php');

    // Sanitize $_GET['bounty_id'] to ensure it is a integer
    $bounty_id = filter_input(INPUT_GET, 'bounty_id', FILTER_SANITIZE_NUMBER_INT);

    if($bounty_id === false || $bounty_id === null) {
        header("Location content.php");
        exit;
    }

    // Build and prepare SQL String with :bounty_id placeholder parameter
    $query = "SELECT * FROM bounties WHERE bounty_id = :bounty_id LIMIT 1";
    $statement = $db->prepare($query);


    // Bind the :id parameter in the query to the sanitized
    // $id specifying a binding=type of integer
    $statement->bindValue(':bounty_id', $bounty_id, PDO::PARAM_INT);
    $statement->execute();

    // Fetch the row selected by primary key id
    $row = $statement->fetch();

    // If no post is found, redirects to content.php
    if($row === false) {
        header("Location: content.php");
        exit;
    }


    // Handle comment submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_text'])) {
        
        if (isset($_GET['comment_submitted']) && $_GET['comment_submitted']) {
            $comment_submitted = true;
        } else {
            $comment_submitted = false;
        }

        if ($_POST['captcha'] !== $_SESSION['captcha']) {
            $error = "Incorrect CAPTCHA, please try again.";
        } else {
            $comment_text = filter_input(INPUT_POST, 'comment_text', FILTER_SANITIZE_STRING);
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING) ?: 'Anonymous';  // Default to 'Anonymous' if not provided

            // Insert comment and username into the database
            $insertQuery = "INSERT INTO comments (bounty_id, username, comment_text) VALUES (:bounty_id, :username, :comment_text)";
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->bindValue(':bounty_id', $bounty_id, PDO::PARAM_INT);
            $insertStmt->bindValue(':username', $username, PDO::PARAM_STR);
            $insertStmt->bindValue(':comment_text', $comment_text, PDO::PARAM_STR);
            
            if ($insertStmt->execute()) {
                header("Location: details.php?bounty_id=$bounty_id&comment_submitted=true");
                exit;
            }
        }
    }

    // Fetching existing comments for the bounty
    $commentsQuery = "SELECT comment_id, username, comment_text, comment_date FROM comments WHERE bounty_id = :bounty_id ORDER BY comment_date DESC";
    $commentsStmt = $db->prepare($commentsQuery);
    $commentsStmt->bindValue(':bounty_id', $bounty_id, PDO::PARAM_INT);
    $commentsStmt->execute();
    $comments = $commentsStmt->fetchAll();

    function is_active($link) {
        // Get the current page file name
        $current_page = basename($_SERVER['PHP_SELF']); 
        return $link == $current_page ? 'active' : '';
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;500&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/aurebesh" rel="stylesheet">

    <link rel="stylesheet" href="styles.css">
    <title>GBN</title>
</head>
<body>
    <!-- Remember that alternative syntax is good and html inside php is bad -->
    <div class="wrapper">

        <?php include('header.php'); ?>
        <div class="details-wrapper">
            <main id="bounties-details">
                <h2><?= $row['title'] ?></h2>
                <div class="date-stamp">
                    <small><?= date("F d, Y, h:i a", strtotime($row['bounty_date'])) ?></small>
                </div>
                <?php if ($_SESSION['is_admin'] === true): ?>
                    <a href="edit_bounty.php?bounty_id=<?= $row['bounty_id'] ?>">edit</a>
                <?php endif ?>
                <div class="bounties-description">
                    <?php if (!empty($row['image_path'])): ?>
                        <img src="<?= $row['image_path'] ?>" alt="<?= $row['title'] ?>">
                    <?php elseif (empty($row['image_path'])): ?>
                        <div class="no-photo">No Photo</div>
                    <?php endif; ?>
                    <p><strong>Description:</strong> <?= $row['description'] ?></p>
                    <p><strong>Name:</strong> <?= $row['name'] ?></p>
                    <p><strong>Species:</strong> <?= $row['species'] ?></p>
                    <p><strong>Reward:</strong> <?= number_format($row['reward']) ?> <span class="special-font">$ </span></p>
                    <p><strong>Status:</strong> <?= $row['status'] ?></p>
                </div>
            </main>
            <div class="comments-section">
                <h2>New Intel</h2>
                <form method="POST" action="details.php?bounty_id=<?= $bounty_id ?>">
                    <div>
                        <!-- <label for="username">Name</label> -->
                        <input type="text" id="username" name="username" placeholder="Name" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" />
                    </div>
                    <div>
                        <!-- <label for="comment_text">Comment</label> -->
                        <textarea name="comment_text" placeholder="Enter your comments or intel" rows="4" required><?= htmlspecialchars($_POST['comment_text'] ?? '') ?></textarea>
                    </div>
                    <div class="captcha">
                        <img src="captcha.php" alt="CAPTCHA" />
                        <input type="text" name="captcha" placeholder="Enter CAPTCHA" required />
                        <?php if (!empty($error)): ?>
                            <div class="error"><?= htmlspecialchars($error) ?></div>
                        <?php endif ?>
                    </div>
                    <button type="submit">Add Comment</button>
                </form>
            </div>
            <div class="comments-list">
                <h2>Intels</h2>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <p class="intel-by">Intel by <strong><?= htmlspecialchars($comment['username']) ?></strong><p>
                        <p><?= htmlspecialchars($comment['comment_text']) ?></p>
                        <small>On <?= date("F d, Y, h:i a", strtotime($comment['comment_date'])) ?></small>
                        <?php if ($_SESSION['is_admin'] === true): ?>
                            <a href="moderate.php?comment_id=<?= $comment['comment_id'] ?>">Delete</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php include('footer.php'); ?>
    </div>
</body>
</html>





