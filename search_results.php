<?php
    session_start();
    require('connect.php');

    $searchQuery = $_GET['query'] ?? '';
    $results = [];

    if (!empty($searchQuery)) {
        $query = "SELECT b.*, sp.name AS species_name 
                  FROM bounties b 
                  LEFT JOIN species sp ON b.species_id = sp.species_id  
                  WHERE b.title LIKE :searchQuery 
                     OR b.name LIKE :searchQuery 
                     OR sp.name LIKE :searchQuery";

        $statement = $db->prepare($query);
        $searchTerm = '%' . $searchQuery . '%';
        $statement->bindValue(':searchQuery', $searchTerm);
        $statement->execute();
        $results = $statement->fetchAll();
    }

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
        <main class="search-results">
            <h1>Search Results for "<?= htmlspecialchars($searchQuery) ?>"</h1>
            <ul>
                <?php foreach ($results as $row): ?>
                    <li>
                        <a href="details.php?bounty_id=<?= $row['bounty_id'] ?>">
                            <?= htmlspecialchars($row['title']) ?> (<?= htmlspecialchars($row['species_name']) ?>)
                        </a>
                    </li>
                <?php endforeach; ?>
                <?php if (count($results) === 0): ?>
                    <li>No results found.</li>
                <?php endif; ?>
            </ul>
        </main>
    </div>
</body>
</html>
