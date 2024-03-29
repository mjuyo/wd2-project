<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 19th, 2024
    Description: PHP edit and update for bounties.

****************/

    require('connect.php');
    require('authenticate.php');
    include __DIR__ . '/php-image-resize-master/lib/ImageResize.php';
    include __DIR__ . '/php-image-resize-master/lib/ImageResizeException.php';

    use \Gumlet\ImageResize;

    function file_upload_path($original_filename, $upload_subfolder_name = 'uploads') {
       // $current_folder = dirname(__FILE__);
       
       $path_segments = [$upload_subfolder_name, basename($original_filename)];
       
       return join(DIRECTORY_SEPARATOR, $path_segments);
    }

    function file_is_an_image($temporary_path, $new_path) {
        $allowed_mime_types      = ['image/gif', 'image/jpeg', 'image/png'];
        $allowed_file_extensions = ['gif', 'jpg', 'jpeg', 'png'];
        
        $file_info = getimagesize($temporary_path);

        if ($file_info) {
            $actual_file_extension   = pathinfo($new_path, PATHINFO_EXTENSION);
            $actual_mime_type        = $file_info['mime'];
            
            $file_extension_is_valid = in_array(strtolower($actual_file_extension), $allowed_file_extensions);
            $mime_type_is_valid      = in_array($actual_mime_type, $allowed_mime_types);
            
            return $file_extension_is_valid && $mime_type_is_valid;
        } else {
            return false;
        }
    }


    // Delete bounty post if id is present in POST
    if($_POST && isset($_POST['bounty_id'])) {
        
        if($_POST['action'] == 'Delete'){
            // Delete post
            $bounty_id = filter_input(INPUT_POST, 'bounty_id', FILTER_SANITIZE_NUMBER_INT);
            $query = "DELETE FROM bounties WHERE bounty_id = :bounty_id";
            $statement = $db->prepare($query);
            $statement->bindValue(':bounty_id', $bounty_id, PDO::PARAM_INT);
            $statement->execute();

            header("Location: index.php");
            exit;
        }

        $image_path = null;
        $error = '';
        $image_filename = '';
        $new_image_path = '';


        // Edit blog post if title, content and id are present in POST
        if($_POST && isset($_POST['title']) && isset($_POST['description']) && isset($_POST['bounty_id'])) {
            // Sanitize user input to escape HTML entities and filter out dangerous characters
            $bounty_id = filter_input(INPUT_POST, 'bounty_id', FILTER_SANITIZE_NUMBER_INT);
            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $species = filter_input(INPUT_POST, 'species', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $reward = filter_input(INPUT_POST, 'reward', FILTER_SANITIZE_NUMBER_INT);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $image_upload_detected = isset($_FILES['bounty_image']) && ($_FILES['bounty_image']['error'] === 0);

            if ($image_upload_detected) {
                $image_filename        = $_FILES['bounty_image']['name'];
                $temporary_image_path  = $_FILES['bounty_image']['tmp_name'];
                $new_image_path        = file_upload_path($image_filename);

                if (!file_is_an_image($temporary_image_path, $new_image_path)) {
                    $error = "The file extension is not valid, please upload an image file.";
                    echo $error;

                } else {
                    if (move_uploaded_file($temporary_image_path, $new_image_path)) {
                        
                        $image_path = $new_image_path;

                        // Resizing
                        $image = new ImageResize($new_image_path);
                        $image->crop(250, 250, true, ImageResize::CROPTOP);
                        $image->save($new_image_path);           
                    }
                }
            }

            if (empty($error)) {
                // Check if the title and content have at least 1 character
                if(strlen($title) < 1 || strlen($description) < 1) {
                    $error = 'Title and description must have at least 1 character';
                } elseif($bounty_id === false) {
                    // Validate if ID is integer, if not, redirect to index.php
                    header("Location: index.php");
                    exit;
                } else {
                    // Build the parameterized SQL query and bind to the above sanitized values
                    $query = "UPDATE bounties SET title = :title, description = :description, name = :name, species = :species, reward = :reward, status = :status" . ($image_path ? ", image_path = :image_path" : "") . " WHERE bounty_id = :bounty_id";
                    $statement = $db->prepare($query);

                    $statement->bindValue(':bounty_id', $bounty_id, PDO::PARAM_INT);
                    $statement->bindValue(':title', $title);
                    $statement->bindValue(':description', $description);
                    $statement->bindValue(':name', $name);
                    $statement->bindValue(':species', $species);
                    $statement->bindValue(':reward', $reward, PDO::PARAM_INT);
                    $statement->bindValue(':status', $status);

                    if ($image_path) {
                        $statement->bindValue(':image_path', $image_path);
                    }

                    // Execute the INSERT
                    if(!$statement->execute()) {
                        $error = "Failed to update the bounty in the database.";
                    } else {
                        header("Location: index.php");
                        exit;
                    }                
                }


            }
        }
    }

    if (isset($_GET['bounty_id'])) {
        // Retrieve blog to be edited, if id GET parameter is in URL
        // Sanitize the id. Like above but this time from INPUT_GET
        $bounty_id = filter_input(INPUT_GET, 'bounty_id', FILTER_SANITIZE_NUMBER_INT);
        
        // Validate if ID is integer, if not, redirect to index.php
        if($bounty_id === false || $bounty_id === null) {
            header("Location: index.php");
            exit;
        }

        // Build the parametirized SQL query using the filtered id
        $query = "SELECT * FROM bounties WHERE bounty_id = :bounty_id";
        $statement = $db->prepare($query);
        $statement->bindValue(':bounty_id', $bounty_id, PDO::PARAM_INT);

        // Execute the SELECT and fetch the single row returned
        $statement->execute();
        $bounties = $statement->fetch();

        if ($bounties === false) {
            // No bounty post found for the given ID, redirect to index.php
            header("Location: index.php");
            exit;
        }

    } else {
        $bounty_id = false; // False if we are not UPDATING or SELECTING
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
    <link rel="stylesheet" href="styles.css">
    <title>GBN</title>
</head>
<body>
    <!-- Remember that alternative syntax is good and html inside php is bad -->
    <div class="wrapper">
        <div id="header">
            <h1><a href="index.php">Galactic Bounties Network - Edit</a></h1>
        </div>
        <?php include('nav.php'); ?>
        <div id="bounties-form">
            <?php if($bounty_id): ?>
                <?php if(!empty($error)): ?>
                    <p class="error"><?= $error ?></p>
                <?php endif ?>
                <form method="post" action="edit.php?bounty_id=<?= $bounties['bounty_id'] ?>" enctype="multipart/form-data">
                    <fieldset>
                        <legend>Edit Post</legend>
                        <div>
                            <input type="hidden" name="bounty_id" value="<?= $bounties['bounty_id'] ?>">
                        </div>
                        <div>
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" value="<?= $bounties['title'] ?>">
                        </div>
                        <div>
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?= $bounties['description'] ?></textarea>
                        </div>
                        <div>
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="<?= $bounties['name'] ?>"/>
                        </div>
                        <div>
                            <label for="species">Species</label>
                            <input type="text" id="species" name="species" value="<?= $bounties['species'] ?>"/>
                        </div>
                        <div>
                            <label for="reward">Reward</label>
                            <input type="number" id="reward" name="reward" value="<?= $bounties['reward'] ?>"/>
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


                        <div class="button-edit">
                            <input type="submit" name="action" value="Update">
                            <input type="submit" name="action" value="Delete">
                        </div>
                    </fieldset>
                </form>
            <?php endif ?>
        </div>
        <?php include('footer.php'); ?>
    </div>
</body>
</html>