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


    // Post logic

    $image_path = null;
    $error = '';
    $image_filename = '';
    $new_image_path = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title']) && isset($_POST['description'])) {
        // Sanitize user input
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        // $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = $_POST['description']; // Sanitation modified to use WYSIWYG
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $reward = filter_input(INPUT_POST, 'reward', FILTER_SANITIZE_NUMBER_INT);
        $species_id = filter_input(INPUT_POST, 'species_id', FILTER_SANITIZE_NUMBER_INT);
        $status_id = filter_input(INPUT_POST, 'status_id', FILTER_SANITIZE_NUMBER_INT);
        $difficulty_id = filter_input(INPUT_POST, 'difficulty_id', FILTER_SANITIZE_NUMBER_INT);


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
                $query = "INSERT INTO bounties (title, description, name, species_id, status_id, difficulty_id, reward, image_path) VALUES (:title, :description, :name, :species_id, :status_id, :difficulty_id, :reward, :image_path)";

                $statement = $db->prepare($query);

                // Binding
                $statement->bindValue(':title', $title);
                $statement->bindValue(':description', $description);
                $statement->bindValue(':name', $name);
                $statement->bindValue(':species_id', $species_id, PDO::PARAM_INT);
                $statement->bindValue(':status_id', $status_id, PDO::PARAM_INT);
                $statement->bindValue(':difficulty_id', $difficulty_id, PDO::PARAM_INT);
                $statement->bindValue(':reward', $reward, PDO::PARAM_INT);
                $statement->bindValue(':image_path', $image_path);


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
    <div class="wrapper">
        <?php include('header.php'); ?>
        <div class="bounties-container">
            <div class="bounties-form-wrapper">
                <?php if(!empty($error)): ?>
                    <p class="error"><?= $error ?></p>
                <?php endif ?>
                <form method="post" action="add_bounty.php" class="bounty-form" enctype="multipart/form-data">
                    <legend>New Bounty</legend>
                    <div>
                        <input type="text" id="title" name="title" value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" placeholder="Title" />
                    </div>
                    
                    <div>
                        <input type="text" id="name" name="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" placeholder="Name" />
                    </div>
                    <div>
                        <select id="species_id" name="species_id">
                            <option value="">Select a species</option>
                            <?php foreach (fetchSpecies($db) as $specie): ?>
                                <option value="<?= htmlspecialchars($specie['species_id']) ?>"><?= htmlspecialchars($specie['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select id="status_id" name="status_id">
                            <option value="">Select status</option>
                            <?php foreach (fetchStatus($db) as $status): ?>
                                <option value="<?= htmlspecialchars($status['status_id']) ?>"><?= htmlspecialchars($status['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select id="difficulty_id" name="difficulty_id">
                            <option value="">Select difficulty</option>
                            <?php foreach (fetchDifficulty($db) as $difficulty): ?>
                                <option value="<?= htmlspecialchars($difficulty['difficulty_id']) ?>"><?= htmlspecialchars($difficulty['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <input type="number" id="reward" name="reward" value="<?= isset($_POST['reward']) ? htmlspecialchars($_POST['reward']) : '' ?>" placeholder="Reward" />
                    </div>
                    <div>
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="2"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                    </div>

                    <div>
                        <label for="bounty_image">Bounty Image</label>
                        <input type="file" name="bounty_image" id="bounty_image" />
                    </div>
                    <div class="button-post">
                        <input type="submit" name="command" value="Post" />
                    </div>
                </form>
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