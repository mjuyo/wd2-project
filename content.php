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

    // Calculate relative time
    function time_elapsed_string($datetime, $full = false) {
        date_default_timezone_set('America/Winnipeg');
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'y',
            'm' => 'm',
            'w' => 'w',
            'd' => 'd',
            'h' => 'h',
            'i' => 'm',
            's' => 's',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . $v;
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) : 'just now';
    }


    // Fetch species, status, and difficulty for filters
    $speciesStmt = $db->query("SELECT species_id, name FROM species");
    $speciesList = $speciesStmt->fetchAll();

    $statusStmt = $db->query("SELECT status_id, name FROM status");
    $statusList = $statusStmt->fetchAll();

    $difficultyStmt = $db->query("SELECT difficulty_id, name FROM difficulty");
    $difficultyList = $difficultyStmt->fetchAll();

    // Build query
    $query = "
            SELECT b.*, 
                sp.name AS species_name,
                st.name AS status_name,
                df.name AS difficulty_name
            FROM bounties b 
            LEFT JOIN species sp ON b.species_id = sp.species_id
            LEFT JOIN status st ON b.status_id = st.status_id
            LEFT JOIN difficulty df ON b.difficulty_id = df.difficulty_id";

    // Build the WHERE clause based on filters
    $whereClauses = [];
    $bindings = [];

    if (!empty($_GET['species_id'])) {
        $whereClauses[] = 'b.species_id = :species_id';
        $bindings[':species_id'] = $_GET['species_id'];
    }

    if (!empty($_GET['status_id'])) {
        $whereClauses[] = 'b.status_id = :status_id';
        $bindings[':status_id'] = $_GET['status_id'];
    }

    if (!empty($_GET['difficulty_id'])) {
        $whereClauses[] = 'b.difficulty_id = :difficulty_id';
        $bindings[':difficulty_id'] = $_GET['difficulty_id'];
    }

    // Search query
    $searchQuery = $_GET['query'] ?? '';

    if (!empty($searchQuery)) {
        $whereClauses[] = "(b.title LIKE :searchQuery OR b.name LIKE :searchQuery OR sp.name LIKE :searchQuery)";
        $bindings[':searchQuery'] = '%' . $searchQuery . '%';
    }

    if (!empty($whereClauses)) {
        $query .= ' WHERE ' . implode(' AND ', $whereClauses);
    }

    // Count query
    $countQuery = "SELECT COUNT(*) AS total_count
                   FROM bounties b 
                   LEFT JOIN species sp ON b.species_id = sp.species_id
                   LEFT JOIN status st ON b.status_id = st.status_id
                   LEFT JOIN difficulty df ON b.difficulty_id = df.difficulty_id";

    if (!empty($whereClauses)) {
        $countQuery .= ' WHERE ' . implode(' AND ', $whereClauses);
    }

    $countStmt = $db->prepare($countQuery);
    foreach ($bindings as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $countResult = $countStmt->fetch();
    $totalCount = $countResult['total_count'];

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
    // ORDER BY
    $query .= " ORDER BY $sort_column $sort_order";


    $statement = $db->prepare($query);
    foreach ($bindings as $key => $value) {
        $statement->bindValue($key, $value);
    }
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles.css">
    <title>GBN</title>
</head>
<body>
    <div class="wrapper">
        <?php include('header.php'); ?>
        <main id="all-bounties">
            
            <div class="categories-sort-add">
                <!-- Sorting -->
                <div class="sorting-wrapper">
                    <div class="sorted-select">
<!--                         <div class="sorted-by">
                            <p>Sorted by </p>
                        </div>
 -->
                        <?php if (isset($_SESSION['username'])) : ?>
                            <form method="GET" action="">
                                <select name="sort" onchange="this.form.submit()">
                                    <option value="name_ASC" <?= $sort_column.'_'.$sort_order === 'name_ASC' ? 'selected' : '' ?>>Name A-Z</option>
                                    <option value="name_DESC" <?= $sort_column.'_'.$sort_order === 'name_DESC' ? 'selected' : '' ?>>Name Z-A</option>
                                    <option value="reward_ASC" <?= $sort_column.'_'.$sort_order === 'reward_ASC' ? 'selected' : '' ?>>Reward Low-High</option>
                                    <option value="reward_DESC" <?= $sort_column.'_'.$sort_order === 'reward_DESC' ? 'selected' : '' ?>>Reward High-Low</option>
                                    <option value="bounty_date_ASC" <?= $sort_column.'_'.$sort_order === 'bounty_date_ASC' ? 'selected' : '' ?>>Oldest-Newest</option>
                                    <option value="bounty_date_DESC" <?= $sort_column.'_'.$sort_order === 'bounty_date_DESC' ? 'selected' : '' ?>>Newest-Oldest</option>
                                </select>
                            </form>
                        <?php endif ?>
                    </div>
                </div>

                <!-- Categories -->
                <form action="content.php" method="GET">
                    <select name="species_id" onchange="this.form.submit()">
                        <option value="">Select Species</option>
                        <?php foreach ($speciesList as $species): ?>
                            <option value="<?= $species['species_id'] ?>" <?= (isset($_GET['species_id']) && $_GET['species_id'] == $species['species_id']) ? 'selected' : '' ?>><?= htmlspecialchars($species['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="status_id" onchange="this.form.submit()">
                        <option value="">Select Status</option>
                        <?php foreach ($statusList as $status): ?>
                            <option value="<?= $status['status_id'] ?>" <?= (isset($_GET['status_id']) && $_GET['status_id'] == $status['status_id']) ? 'selected' : '' ?>><?= htmlspecialchars($status['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="difficulty_id" onchange="this.form.submit()">
                        <option value="">Select Difficulty</option>
                        <?php foreach ($difficultyList as $difficulty): ?>
                            <option value="<?= $difficulty['difficulty_id'] ?>" <?= (isset($_GET['difficulty_id']) && $_GET['difficulty_id'] == $difficulty['difficulty_id']) ? 'selected' : '' ?>><?= htmlspecialchars($difficulty['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <!-- <input type="hidden" name="query" value="<?= $searchQuery?> "> -->
                </form>

                <!-- Add new bounty -->
                <div class="add-bounty-button">
                    <?php if ($_SESSION['is_admin'] === true) : ?>
                        <a href="add_bounty.php" class="button">+ Add Bounty</a>
                    <?php endif; ?>
                </div>                        
            </div>
            <div class="count-results">
                Total Results: <?= htmlspecialchars($totalCount) ?>
            </div>

            <!-- All content -->
            <section class="bounties-grid">
                <?php while($row = $statement->fetch()): ?>
                    <div class="bounties-post">
                        <div class="bounties-photo">
                            <?php if (!empty($row['image_path'])): ?>
                                <img src="<?= $row['image_path'] ?>" alt="<?= $row['title'] ?>">
                            <?php elseif (empty($row['image_path'])): ?>
                                <div class="no-photo">No Photo</div>
                            <?php endif ?>
                            <?php if ($_SESSION['is_admin'] === true): ?>
                                <div class="bounty-edit-content">
                                    <a href="edit_bounty.php?bounty_id=<?= $row['bounty_id'] ?>">edit</a>
                                </div>
                            <?php endif ?>
                        </div>
                        <div class="bounties-header">
                            <h2><a href="details.php?bounty_id=<?= $row['bounty_id'] ?>"><?= $row['short_title'] ?></a></h2>
                            <div class="date-stamp">
                                <small title="<?= date("F d, Y, h:i a", strtotime($row['bounty_date'])) ?>" ><?= " • " . time_elapsed_string($row['bounty_date']) ?></small>
                            </div>
                            
                        </div>
                        <div class="bounties-content">
                            <p><?= $row['description'] ?></p>
                            <p><strong>Name:</strong> <?= $row['name'] ?></p>
                            <p><strong>Species:</strong> <?= $row['species_name'] ?></p>
                            <p><strong>Difficulty:</strong> <?= $row['difficulty_name'] ?></p>
                            <p><strong>Reward:</strong> <?= number_format($row['reward']) ?> <span class="special-font">$ </span></p>
                            <p><strong>Status:</strong> <?= $row['status_name'] ?></p>
                        </div>
                    </div>
                <?php endwhile ?>
            </section>
        </main>
        <?php include('footer.php'); ?>
    </div>
</body>
</html>