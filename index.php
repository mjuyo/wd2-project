<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 16th, 2024
    Description: PHP Home page

****************/

    session_start();

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
    <div class="wrapper">
        <?php include('header.php'); ?>
        
        <main class="landing">
            <section class="hero">
                <h1>Welcome to the Galactic Bounties Network</h1>
                <p>The ultimate hub for intergalactic bounty hunters to find, track, and capture the most sought-after targets in the galaxy.</p>
                <a href="signup.php" class="btn btn-primary">Join the Hunt</a>
            </section>

            <!-- Disclaimer Section -->
            <section class="disclaimer">
                <p>Disclaimer: We are not associated with the Galactic Empire but are open to receiving bounties from them as well.</p>
            </section>
        </main>

        <?php include('footer.php'); ?>
    </div>
</body>
</html>