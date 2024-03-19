<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 18th, 2024
    Description: PHP Details for bounties.

****************/

    require('connect.php');

    // Sanitize $_GET['id'] to ensure it is a integer
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    if($id === false || $id === null) {
        header("Location index.php");
        exit;
    }

    // Build and prepare SQL String with :id placeholder parameter
    $query = "SELECT * FROM bounties WHERE bounty_id = :id LIMIT 1";
    $statement = $db->prepare($query);


    // Bind the :id parameter in the query to the sanitized
    // $id specifying a binding=type of integer
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
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
    <link rel="stylesheet" href="main.css">
    <title>Galactic Bounties Network</title>
</head>
<body>
    <!-- Remember that alternative syntax is good and html inside php is bad -->
    <div id="wrapper">
        <div id="header">
            <h1><a href="index.php">Galactic Bounties Network - <?= $row['title'] ?></a></h1>
        </div>
        <?php include('nav.php'); ?>
        <div id="all-bounties">
            <div class="bounties-post">
                <h2><?= $row['title'] ?></h2>
                <div class="date-stamp">
                    <small><?= date("F d, Y, h:i a", strtotime($row['bounty_date'])) . " - "?><a href="edit.php?id=<?= $row['bounty_id'] ?>">edit</a></small>
                </div>
                <div class="bounties-content">
                    <p><?= nl2br($row['description']) ?></p>
                    <p><strong>Name:</strong> <?= htmlspecialchars($row['name']) ?></p>
                    <p><strong>Species:</strong> <?= htmlspecialchars($row['species']) ?></p>
                    <p><strong>Reward:</strong> <?= htmlspecialchars($row['reward']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($row['status']) ?></p>
                </div>
            </div>
        </div>
        <?php include('footer.php'); ?>
    </div>
</body>
</html>





