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
    <title></title>
</head>
<body>
    <div id="wrapper">
        <div id="header">
            <h1><a href="index.php">Galactic Bounties Network - GBN</a></h1>
        </div>
        <?php include('nav.php'); ?>
        <div id="all-blogs">
            <div class="blog-post">
                <?php while($row = $statement->fetch()): ?>
                <div class="paragraph">
                    <h2><a href="show.php?id=<?= $row['id'] ?>"><?= $row['title'] ?></a></h2>
                    <div class="date-stamp">
                        <small><?= date("F d, Y, h:i a", strtotime($row['bounty_date'])) . " - "?><a href="edit.php?id=<?= $row['id'] ?>">edit</a></small>
                    </div>
                    <div class="blog-content">
                        <p><?= $row['description'] ?></p>
                    </div>
                </div>
                <?php endwhile ?>
            </div>
        </div>
        <!-- <?php include('footer.php'); ?> -->
    </div>


</body>
</html>