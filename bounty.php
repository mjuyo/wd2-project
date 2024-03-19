<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 17th, 2024
    Description: PHP New Bounties.

****************/

    require('connect.php');
    require('authenticate.php');

    if ($_POST && isset($_POST['title']) && isset($_POST['description'])) {
        // Sanitize user input
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $species = filter_input(INPUT_POST, 'species', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $reward = filter_input(INPUT_POST, 'reward', FILTER_SANITIZE_NUMBER_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


        if(strlen($title) < 1 || strlen($description) < 1) {
            $error = 'Title and content must have at least 1 character';
        } else {
            // Build the parameterized SQL query and bind to the sanitized values
            $query = "INSERT INTO bounties (title, description, name, species, reward, status) VALUES(:title, :description, :name, :species, :reward, :status)";
            $statement = $db->prepare($query);

            // Bind values to the parameters
            $statement->bindValue(':title', $title);
            $statement->bindValue(':description', $description);
            $statement->bindValue(':name', $name);
            $statement->bindValue(':species', $species);
            $statement->bindValue(':reward', $reward, PDO::PARAM_INT);
            $statement->bindValue(':status', $status);

            // Execute the Post
            if($statement->execute()){
                header("Location: index.php");
                exit;
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
    <title>GBN</title>
</head>
<body>
    <div id="wrapper">
        <div id="header">
            <h1><a href="index.php">Galactic Bounty Network</a></h1>
        </div>
        <?php include('nav.php'); ?>
        <div id="all-bounties">
            <?php if(!empty($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php endif ?>
            <form method="post" action="bounty.php" enctype="multipart/form-data">
                <fieldset>
                    <legend>New Bounty</legend>
                    <div>
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" />
                    </div>
                    <div>
                        <label for="description">Description</label>
                        <textarea id="description" name="description"></textarea>
                    </div>
                    <div>
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" />
                    </div>
                    <div>
                        <label for="species">Species</label>
                        <input type="text" id="species" name="species" />
                    </div>
                    <div>
                        <label for="reward">Reward</label>
                        <input type="number" id="reward" name="reward" />
                    </div>
                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div>
                        <label for="bounty_image">Bounty Image</label>
                        <input type="file" id="bounty_image" name="bounty_image" />
                    </div>
                    <div class="button-post">
                        <input type="submit" name="command" value="Post" />
                    </div>
                </fieldset>
            </form>
        </div>
        <?php include('footer.php'); ?>
    </div>

</body>
</html>