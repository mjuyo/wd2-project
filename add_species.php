<?php 

/*******w******** 
    
    Name: Marco Juyo
    Date: April 7th, 2024
    Description: PHP Add Species

****************/

    session_start();

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: signin.php');
        exit;
    }

    require('connect.php');

    // Add species
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $species_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $homeworld = filter_input(INPUT_POST, 'homeworld', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $statement = $db->prepare("SELECT * FROM species WHERE name = ?");
        $statement->execute([$species_name]);
        $species_exists = $statement->fetch();

        if ($species_exists) {
            $error = "The species already exists, please enter a different name.";
        } else {
            $statement = $db->prepare("INSERT INTO species (name, homeworld) VALUES (?, ?)");
            $success = $statement->execute([$species_name, $homeworld]);

            if ($success) {
                $_SESSION['message'] = "New species added successfully.";
                header("Location: add_species.php");
                exit();
            } else {
                $error = "Failed to add species, please try again.";
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
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;500&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/aurebesh" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
	<title>Add Species - Admin Dashboard</title>
</head>
<body>
	<div class="wrapper">
        <?php include('header.php'); ?>
        
        <main class="dashboard">
            <h1><a href="dashboard.php">Admin Dashboard</a></h1>

            <?php if(isset($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php elseif(isset($_SESSION['message'])): ?>
                <p class="success"><?= $_SESSION['message'] ?></p>
            <?php endif; ?>
            
            <h2>Add Species</h2>
            <form action="add_species.php" method="POST">
                <div>
                    <input type="text" id="name" name="name" placeholder="Species Name" required>
                </div>
                <div>
                    <input type="text" id="homeworld" name="homeworld" placeholder="Homeworld" required>
                </div>
                <div>
                    <input type="submit" value="Add Species">
                </div>
            </form>

        </main>

        <?php include('footer.php'); ?>
    </div>
</body>
</html>