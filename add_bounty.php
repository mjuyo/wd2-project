<?php

/*******w******** 
    
    Name: Marco Juyo
    Date: March 17th, 2024
    Description: PHP New Bounties.

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

    // Post logic

    $image_path = null;
    $error = '';
    $image_filename = '';
    $new_image_path = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title']) && isset($_POST['description'])) {
        // Sanitize user input
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $species = filter_input(INPUT_POST, 'species', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $reward = filter_input(INPUT_POST, 'reward', FILTER_SANITIZE_NUMBER_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (isset($_FILES['bounty_image']) && $_FILES['bounty_image']['error'] === 0) {
            $image_filename        = $_FILES['bounty_image']['name'];
            $temporary_image_path  = $_FILES['bounty_image']['tmp_name'];
            $new_image_path        = file_upload_path($image_filename);

            if (!file_is_an_image($temporary_image_path, $new_image_path)) {
                $error = "The file extension is not valid, please upload an image file.";


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

        if(empty($error)) {
            if(strlen($title) < 1 || strlen($description) < 1) {
                $error = 'Title and content must have at least 1 character';
            } else {
                // Build the parameterized SQL query and bind to the sanitized values
                $query = "INSERT INTO bounties (title, description, name, species, reward, status, image_path) VALUES(:title, :description, :name, :species, :reward, :status, :image_path)";
                $statement = $db->prepare($query);

                // Bind values to the parameters
                $statement->bindValue(':title', $title);
                $statement->bindValue(':description', $description);
                $statement->bindValue(':name', $name);
                $statement->bindValue(':species', $species);
                $statement->bindValue(':reward', $reward, PDO::PARAM_INT);
                $statement->bindValue(':status', $status);
                $statement->bindValue(':image_path', $new_image_path);

                // Execute the Post
                if(!$statement->execute()){
                    $error = "Failed to add the bounty to the database.";
                } else {
                    header("Location: content.php");
                    exit;
                }
            }
        }
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
    <link rel="stylesheet" href="styles.css">
    <title>GBN</title>
</head>
<body>
    <div class="wrapper">
        <?php include('header.php'); ?>
        <div id="bounties-form-wrapper">
            <?php if(!empty($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php endif ?>
            <form method="post" action="add_bounty.php" class="bounty-form" enctype="multipart/form-data">
                <fieldset>
                    <!-- <legend>New Bounty</legend> -->
                    <div>
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" />
                    </div>
                    
                    <div>
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" />
                    </div>
                    <div>
                        <label for="species">Species</label>
                        <input type="text" id="species" name="species" value="<?= isset($_POST['species']) ? htmlspecialchars($_POST['species']) : '' ?>" />
                    </div>
                    <div>
                        <label for="reward">Reward</label>
                        <input type="number" id="reward" name="reward" value="<?= isset($_POST['reward']) ? htmlspecialchars($_POST['reward']) : '' ?>" />
                    </div>
                    <div>
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
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
                        <label for="bounty_image">Bounty Image: </label>
                        <input type="file" name="bounty_image" id="bounty_image" />
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