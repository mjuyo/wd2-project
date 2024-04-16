<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 19th, 2024
    Description: PHP edit and update for bounties.

****************/

    session_start();

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: signin.php');
        exit;
    }

    require('connect.php');
    // require('authenticate.php');
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

    // Fetch species table
    function fetchSpecies($db) {
        $query = "SELECT species_id, name FROM species";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch status table
    function fetchStatus($db) {
        $query = "SELECT status_id, name FROM status";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch difficulty table
    function fetchDifficulty($db) {
        $query = "SELECT difficulty_id, name FROM difficulty";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Delete bounty post if id is present in POST
    if($_POST && isset($_POST['bounty_id'], $_POST['action'])) {

        $bounty_id = filter_input(INPUT_POST, 'bounty_id', FILTER_SANITIZE_NUMBER_INT);

        // Check if there is a request to delete the image
        $removeImage = isset($_POST['remove_image']);
        if ($removeImage && $bounty_id) {
            // Get the current image filename from the database
            $queryGetImage = "SELECT image_path FROM bounties WHERE bounty_id = :bounty_id";
            $statementGetImage = $db->prepare($queryGetImage);
            $statementGetImage->bindValue(':bounty_id', $bounty_id, PDO::PARAM_INT);
            $statementGetImage->execute();
            $row = $statementGetImage->fetch(PDO::FETCH_ASSOC);
            $currentImage = $row['image_path'];

            // Delete the image file from the file system
            if (!empty($currentImage)) {
                $imagePath = $currentImage;
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Remove the image file
                }
            }

            // Update the database to remove the image path
            $queryRemoveImage = "UPDATE bounties SET image_path = NULL WHERE bounty_id = :bounty_id";
            $statementRemoveImage = $db->prepare($queryRemoveImage);
            $statementRemoveImage->bindValue(':bounty_id', $bounty_id, PDO::PARAM_INT);
            $statementRemoveImage->execute();
        }


        $image_path = null;
        $error = '';
        $image_filename = '';
        $new_image_path = '';

        // Edit blog post if title, content and id are present in POST
        if($_POST && isset($_POST['title']) && isset($_POST['description'])) {
            // Sanitize user input to escape HTML entities and filter out dangerous characters
            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $reward = filter_input(INPUT_POST, 'reward', FILTER_SANITIZE_NUMBER_INT);
            $species_id = filter_input(INPUT_POST, 'species_id', FILTER_SANITIZE_NUMBER_INT);
            $status_id = filter_input(INPUT_POST, 'status_id', FILTER_SANITIZE_NUMBER_INT);
            $difficulty_id = filter_input(INPUT_POST, 'difficulty_id', FILTER_SANITIZE_NUMBER_INT);
            // $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $description = $_POST['description']; // Sanitation modified to use WYSIWYG

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
                    // Validate if ID is integer, if not, redirect to content.php
                    header("Location: content.php");
                    exit;
                } else {
                    // Build the parameterized SQL query and bind to the above sanitized values
                    $query = "UPDATE bounties SET title = :title, description = :description, name = :name, species_id = :species_id, reward = :reward, status_id = :status_id, difficulty_id = :difficulty_id" . ($image_path ? ", image_path = :image_path" : "") . " WHERE bounty_id = :bounty_id";
                    $statement = $db->prepare($query);

                    $statement->bindValue(':bounty_id', $bounty_id, PDO::PARAM_INT);
                    $statement->bindValue(':title', $title);
                    $statement->bindValue(':description', $description);
                    $statement->bindValue(':name', $name);
                    $statement->bindValue(':species_id', $species_id, PDO::PARAM_INT);
                    $statement->bindValue(':reward', $reward, PDO::PARAM_INT);
                    $statement->bindValue(':status_id', $status_id, PDO::PARAM_INT);
                    $statement->bindValue(':difficulty_id', $difficulty_id, PDO::PARAM_INT);

                    if ($image_path) {
                        $statement->bindValue(':image_path', $image_path);
                    }

                    // Execute the INSERT
                    if(!$statement->execute()) {
                        $error = "Failed to update the bounty in the database.";
                    } else {
                        header("Location: content.php");
                        exit;
                    }                
                }


            }
        }
    }

    if (isset($_GET['bounty_id'])) {
        // Retrieve to be edited, if id GET parameter is in URL
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
    <script src="https://cdn.tiny.cloud/1/hbxpe7vn0nqmviarr6ittv43ay4nx0lsa3y9jvroi479oyh7/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;500&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/aurebesh" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles.css">
    <title>GBN</title>
</head>
<body>
    <!-- Remember that alternative syntax is good and html inside php is bad -->
    <div class="wrapper">
        
        <?php include('header.php'); ?>
        <div class="bounties-container">
            <div class="bounties-form-wrapper">
                <?php if($bounty_id): ?>
                    <?php if(!empty($error)): ?>
                        <p class="error"><?= $error ?></p>
                    <?php endif ?>
                    <form method="post" action="edit_bounty.php?bounty_id=<?= $bounties['bounty_id'] ?>" class="bounty-form" enctype="multipart/form-data">
                            <legend>Edit Post</legend>
                            <div>
                                <input type="hidden" name="bounty_id" value="<?= $bounties['bounty_id'] ?>">
                            </div>
                            <div>
                                <label for="title">Title</label>
                                <input type="text" id="title" name="title" value="<?= $bounties['title'] ?>">
                            </div>
                            <div>
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" value="<?= $bounties['name'] ?>"/>
                            </div>
                            <div>
                                <label for="species_id">Species</label>
                                <select id="species_id" name="species_id">
                                    <?php foreach (fetchSpecies($db) as $specie): ?>
                                        <option value="<?= htmlspecialchars($specie['species_id']) ?>" <?= $specie['species_id'] == $bounties['species_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($specie['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="status_id">Status</label>
                                <select id="status_id" name="status_id">
                                    <?php foreach (fetchStatus($db) as $status): ?>
                                        <option value="<?= htmlspecialchars($status['status_id']) ?>" <?= $status['status_id'] == $bounties['status_id'] ? 'selected' : '' ?>><?= htmlspecialchars($status['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="difficulty_id">Difficulty</label>
                                <select id="difficulty_id" name="difficulty_id">
                                    <?php foreach (fetchDifficulty($db) as $difficulty): ?>
                                        <option value="<?= htmlspecialchars($difficulty['difficulty_id']) ?>" <?= $difficulty['difficulty_id'] == $bounties['difficulty_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($difficulty['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="reward">Reward</label>
                                <input type="number" id="reward" name="reward" value="<?= $bounties['reward'] ?>"/>
                            </div>
                            <div>
                                <label for="description">Description</label>
                                <textarea id="description" name="description"><?= $bounties['description'] ?></textarea>
                            </div>
                            <div>
                                <label for="bounty_image">Bounty Image</label>
                                <input type="file" id="bounty_image" name="bounty_image" />
                            </div>
                            
                            <!-- Remove image checkbox-->
                            <?php if (!empty($bounties['image_path'])): ?>
                                <div class="remove-image">
                                    <label for="remove_image">Remove Image? </label>
                                    <input type="checkbox" id="remove_image" name="remove_image">
                                </div>
                            <?php endif; ?>
                            <!-- Remove image checkbox-->

                            <div class="button-update">
                                <input type="submit" name="action" value="Update">
                                <!-- <input type="submit" name="action" value="Delete"> -->
                                <!-- <a href="delete_bounty.php?bounty_id=<?= $bounties['bounty_id'] ?>">Delete</a> -->
                            </div>
                    </form>
                    <form method="post" action="delete_bounty.php" class="delete-form" enctype="multipart/form-data">
                        <div class="button-delete">
                            <input type="hidden" name="bounty_id" value="<?= $bounties['bounty_id'] ?>">
                            <input type="hidden" name="redirect_to" value="content.php">
                            <input type="submit" name="action" value="Delete">
                        </div>
                    </form>
                <?php endif ?>
            </div>
        </div>

        <?php include('footer.php'); ?>
    </div>

    <!-- WYSIWYG script-->
    <script>
          tinymce.init({
            selector: '#description'
          });
    </script>
</body>
</html>