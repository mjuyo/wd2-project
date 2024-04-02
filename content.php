<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 31st, 2024
    Description: PHP Content page

****************/

    session_start();

    if (!isset($_SESSION['is_admin'])) {
        // Default value for users who are not logged in or are not admins
        $_SESSION['is_admin'] = false; 
    }

    require('connect.php');

    // Sorting
    $sort_column = 'bounty_date';
    $sort_order = 'DESC';

    if (isset($_GET['sort'])) {
        $sort_value = $_GET['sort'];

        switch ($sort_value) {
            case 'name_ASC':
                $sort_column = 'name';
                $sort_order = 'ASC';
                break;
            case 'name_DESC':
                $sort_column = 'name';
                $sort_order = 'DESC';
                break;
            case 'reward_ASC':
                $sort_column = 'reward';
                $sort_order = 'ASC';
                break;
            case 'reward_DESC':
                $sort_column = 'reward';
                $sort_order = 'DESC';
                break;
            case 'bounty_date_ASC':
                $sort_column = 'bounty_date';
                $sort_order = 'ASC';
                break;
            case 'bounty_date_DESC':
                $sort_column = 'bounty_date';
                $sort_order = 'DESC';
                break;
        }
    }


    // Optional sorting
    $sort_options = array(
        'name_ASC' => 'Name from A to Z',
        'name_DESC' => 'Name from Z to A',
        'reward_ASC' => 'Reward from Lowest to Highest',
        'reward_DESC' => 'Reward from Highest to Lowest',
        'bounty_date_ASC' => 'Oldest to Newest',
        'bounty_date_DESC' => 'Newest to Oldest',
    );

    $current_sorting = isset($sort_options[$sort_column . '_' . $sort_order]) ? $sort_options[$sort_column . '_' . $sort_order] : 'Newest to Oldest';


    // Query from the database
    $query = "SELECT * FROM bounties ORDER BY $sort_column $sort_order";
    $statement = $db->prepare($query);
    $statement->execute();

    // Active tab
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
        <main id="all-bounties">
            
            <div class="sorting-wrapper">
                <div class="sorted-select">
                    

                    <?php if (isset($_SESSION['username'])) : ?>
                        <form method="GET" action="">
                            <select name="sort" onchange="this.form.submit()">
                                <option value="name_ASC" <?= $sort_column.'_'.$sort_order === 'name_ASC' ? 'selected' : '' ?>>Name A-Z</option>
                                <option value="name_DESC" <?= $sort_column.'_'.$sort_order === 'name_DESC' ? 'selected' : '' ?>>Name Z-A</option>
                                <option value="reward_ASC" <?= $sort_column.'_'.$sort_order === 'reward_ASC' ? 'selected' : '' ?>>Reward Low-High</option>
                                <option value="reward_DESC" <?= $sort_column.'_'.$sort_order === 'reward_DESC' ? 'selected' : '' ?>>Reward High-Low</option>
                                <option value="bounty_date_ASC" <?= $sort_column.'_'.$sort_order === 'bounty_date_ASC' ? 'selected' : '' ?>>Date Oldest-Newest</option>
                                <option value="bounty_date_DESC" <?= $sort_column.'_'.$sort_order === 'bounty_date_DESC' ? 'selected' : '' ?>>Date Newest-Oldest</option>
                            </select>
                        </form>
                        <!-- Optional --> 
                        <div class="sorted-by">
                            <p>Sorted by <?= $current_sorting ?></p>
                        </div>
                        <!-- End Optional -->
                    <?php endif ?>
                </div>

            </div>

            <section class="bounties-grid">
                <?php while($row = $statement->fetch()): ?>
                    <div class="bounties-post">
                        <h2><a href="details.php?bounty_id=<?= $row['bounty_id'] ?>"><?= $row['title'] ?></a></h2>
                        <div class="date-stamp">
                            <small><?= date("F d, Y, h:i a", strtotime($row['bounty_date'])) ?></small>
                        </div>
                        <?php if ($_SESSION['is_admin'] === true): ?>
                            <a href="edit_bounty.php?bounty_id=<?= $row['bounty_id'] ?>">edit</a>
                        <?php endif ?>
                        <div class="bounties-content">
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
                    </div>
                <?php endwhile ?>
            </section>
        </main>
        <?php include('footer.php'); ?>
    </div>
</body>
</html>