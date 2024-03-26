<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 16th, 2024
    Description: PHP Home page for project

****************/

    require('connect.php');

    $query = "SELECT * FROM bounties ORDER BY bounty_date DESC";

    $statement = $db->prepare($query);

    $statement->execute();

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
    <title>GBN</title>
</head>
<body>
    <div id="wrapper">
        <header id="header">
            <h1><a href="index.php">Galactic Bounties Network</a></h1>
        </header>
        <?php include('nav.php'); ?>
        <main id="all-bounties">
            <section class="bounties-grid">
                <?php while($row = $statement->fetch()): ?>
                    <div class="bounties-post">
                        <h2><a href="details.php?bounty_id=<?= $row['bounty_id'] ?>"><?= $row['title'] ?></a></h2>
                        <div class="date-stamp">
                            <small><?= date("F d, Y, h:i a", strtotime($row['bounty_date'])) . " - "?><a href="edit.php?bounty_id=<?= $row['bounty_id'] ?>">edit</a></small>
                        </div>
                        <div class="bounties-content">
                            <?php if (!empty($row['image_path'])): ?>
                                <img src="<?= $row['image_path'] ?>" alt="<?= $row['title'] ?>">
                            <?php elseif (empty($row['image_path'])): ?>
                                <img src="images/hutt_logo.jpg" alt="No Image">
                            <?php endif ?>
                            <p><strong>Description:</strong> <?= $row['description'] ?></p>
                            <p><strong>Name:</strong> <?= $row['name'] ?></p>
                            <p><strong>Species:</strong> <?= $row['species'] ?></p>
                            <p><strong>Reward:</strong> <?= $row['reward'] ?> <span class="special-font">$ </span></p>
                            <p><strong>Status:</strong> <?= $row['status'] ?></p>
                        </div>
                    </div>
                <?php endwhile ?>
            </section>
        </main>
        <?php include('footer.php'); ?>
    </div>
</body>
</html>