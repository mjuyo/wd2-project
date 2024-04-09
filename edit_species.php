<?php 

    session_start();

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: signin.php');
        exit;
    }

    require('connect.php');

    $errors = [];

    // Check if the species ID is present and valid
    $species_id = filter_input(INPUT_GET, 'species_id', FILTER_SANITIZE_NUMBER_INT);
    if (!$species_id) {
        $errors[] = "Invalid species ID.";
    } else {
        // Fetch the species current data
        $query = "SELECT name, homeworld FROM species WHERE species_id = :species_id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':species_id', $species_id, PDO::PARAM_INT);
        $stmt->execute();
        $species = $stmt->fetch();

        // If the form is submitted, process the form data
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $homeworld = filter_input(INPUT_POST, 'homeworld', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Prepare the update statement
            $update_query = "UPDATE species SET name = :name, homeworld = :homeworld WHERE species_id = :species_id";
            $update_stmt = $db->prepare($update_query);
            
            $update_stmt->bindValue(':species_id', $species_id, PDO::PARAM_INT);
            $update_stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $update_stmt->bindValue(':homeworld', $homeworld, PDO::PARAM_STR);

            // Execute the update statement
            if ($update_stmt->execute()) {
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = "Error updating species.";
            }
        }
    }

    function is_active($link) {
        // Get the current page file name
        $current_page = basename($_SERVER['PHP_SELF']); 
        return $link == $current_page ? 'active' : '';
    } 
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">  
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;500&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/aurebesh" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <title>Edit Species - Admin Dashboard</title>
</head>
<body>
    <div class="wrapper">
        <?php include('header.php'); ?>

        <main class="dashboard">
            <h1><a href="dashboard.php">Admin Dashboard</a></h1>

            <?php foreach ($errors as $error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>

            <h2>Edit Species</h2>
            <form method="post" action="edit_species.php?species_id=<?= htmlspecialchars($species_id) ?>">
                <div>
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($species['name'] ?? '') ?>" required>
                </div>

                <div>
                    <label for="homeworld">Homeworld:</label>
                    <input type="text" id="homeworld" name="homeworld" value="<?= htmlspecialchars($species['homeworld'] ?? '') ?>" required>
                </div>

                <div>
                    <input type="submit" value="Update Species">
                </div>
            </form>
        </main>

        <?php include('footer.php'); ?>
    </div>
</body>
</html>
