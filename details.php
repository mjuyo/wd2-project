<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 18th, 2024
    Description: PHP Details for bounties.

****************/

    require('connect.php');

    // Sanitize $_GET['bounty_id'] to ensure it is a integer
    $bounty_id = filter_input(INPUT_GET, 'bounty_id', FILTER_SANITIZE_NUMBER_INT);

    if($bounty_id === false || $bounty_id === null) {
        header("Location index.php");
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

    // If no post is found, redirects to index.php
    if($row === false) {
        header("Location: index.php");
        exit;
    }

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
        <header id="header">
            <h1 class="special-font"><a href="index.php">Galactic Bounties Network - <?= $row['title'] ?></a></h1>
            <h2><a href="index.php">Galactic Bounties Network - <?= $row['title'] ?></a></h2>
        </header>
        <?php include('nav.php'); ?>
        <main id="bounties-details">
            <h2><?= $row['title'] ?></h2>
            <div class="date-stamp">
                <small><?= date("F d, Y, h:i a", strtotime($row['bounty_date'])) . " - "?><a href="edit.php?bounty_id=<?= $row['bounty_id'] ?>">edit</a></small>
            </div>
            <div class="bounties-description">
                <?php if (!empty($row['image_path'])): ?>
                    <img src="<?= $row['image_path'] ?>" alt="<?= $row['title'] ?>">
                <?php elseif (empty($row['image_path'])): ?>
                    <div class="no-photo">No Photo</div>
                <?php endif ?>
                <p><strong>Description:</strong> <?= $row['description'] ?></p>
                <p><strong>Name:</strong> <?= $row['name'] ?></p>
                <p><strong>Species:</strong> <?= $row['species'] ?></p>
                <p><strong>Reward:</strong> <?= number_format($row['reward']) ?> <span class="special-font">$ </span></p>
                <p><strong>Status:</strong> <?= $row['status'] ?></p>
            </div>
        </main>
        <?php include('footer.php'); ?>
    </div>
</body>
</html>





