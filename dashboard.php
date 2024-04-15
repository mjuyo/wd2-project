<?php 

/*******w******** 
    
    Name: Marco Juyo
    Date: March 31st, 2024
    Description: PHP Admin Dashboard

****************/

    session_start();

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: signin.php');
        exit;
    }

    require('connect.php');

    // Fetch users
    $user_query = "SELECT user_id, username, full_name, user_date, is_admin FROM users ORDER BY user_date DESC";
    $user_stmt = $db->prepare($user_query);
    $user_stmt->execute();
    $users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);


    // Fetch bounties
    $bounty_query = "SELECT bounty_id, title, name, reward, bounty_date FROM bounties ORDER BY bounty_date DESC";
    $bounty_stmt = $db->prepare($bounty_query);
    $bounty_stmt->execute();
    $bounties = $bounty_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Species
    $species_query = "SELECT species_id, name, homeworld FROM species";
    $species_stmt = $db->prepare($species_query);
    $species_stmt->execute();
    $species = $species_stmt->fetchAll(PDO::FETCH_ASSOC);


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
	<title>Admin Dashboard</title>
</head>
<body>
	<div class="wrapper">
        <?php include('header.php'); ?>
        
        <main class="dashboard">
            <h1>Admin Dashboard</h1>
            <div class="dashboard-content">
	            <!-- Bounties Section -->
	            <div class="dashboard-section">
	                <div class="dashboard-title">
	                	<h2>Bounties</h2>
	                	<a href="add_bounty.php" class="button">+ Add Bounty</a>
	                </div>
	                <!-- Bounties Table -->
	                <table>
	                    <!-- Table Headers -->
	                    <thead>
	                        <tr>
	                            <th>Name</th>
	                            <th>Reward</th>
	                            <th>Date</th>
	                            <th>Actions</th>
	                        </tr>
	                    </thead>
	                    <!-- Table Body -->
	                    <tbody>
	                        <?php foreach ($bounties as $bounty): ?>
	                        <tr>
	                            <td><?= htmlspecialchars($bounty['name']) ?></td>
	                            <td><?= number_format($bounty['reward']) ?> <span class="special-font">$ </span></td>
	                            <td><?= date("F d, Y, H:i", strtotime($bounty['bounty_date'])) ?></td>
	                            <td>
	                                <a href="edit_bounty.php?bounty_id=<?= $bounty['bounty_id'] ?>">Edit</a> |
	                                <!-- <a href="delete_bounty.php?bounty_id=<?= $bounty['bounty_id'] ?>">Delete</a> -->
	                                <form method="post" action="delete_bounty.php" enctype="multipart/form-data">
			                            <input type="hidden" name="bounty_id" value="<?= $bounty['bounty_id'] ?>">
			                            <input type="hidden" name="redirect_to" value="dashboard.php">
			                            <input type="submit" name="action" value="Delete" class="btn btn-del-small">
			                        </form>
	                            </td>
	                        </tr>
	                        <?php endforeach; ?>
	                    </tbody>
	                </table>
	            </div>

	            <!-- Users Section -->
	            <div class="dashboard-section">
	                <div class="dashboard-title">
		                <h2>Users</h2>
		                <a href="add_user.php" class="button">+ Add User</a>
	                </div>
	                <!-- Users Table -->
	                <table>
	                    <!-- Table Headers -->
	                    <thead>
	                        <tr>
	                        	<th>Name</th>
	                            <th>Username</th>
	                            <th>Role</th>
	                            <th>Creation Date</th>
	                            <th>Actions</th>
	                        </tr>
	                    </thead>
	                    <!-- Table Body -->
	                    <tbody>
	                        <?php foreach ($users as $user): ?>
	                        <tr>
	                        	<td><?= htmlspecialchars($user['full_name']) ?></td>
	                            <td><?= htmlspecialchars($user['username']) ?></td>
	                            <td><?= htmlspecialchars($user['is_admin']) ? 'Admin' : 'User' ?></td>
	                            <td><?= date("F d, Y, h:i a", strtotime($user['user_date'])) ?></td>
	                            <td>
	                                <a href="edit_user.php?user_id=<?= $user['user_id'] ?>">Edit</a> |
	                                <a href="delete_user.php?user_id=<?= $user['user_id'] ?>">Delete</a>
	                            </td>
	                        </tr>
	                        <?php endforeach; ?>
	                    </tbody>
	                </table>
	            </div>


	            <!-- Categories Section -->
	            <div class="dashboard-section">
	                <div class="dashboard-title">
		                <h2>Categories</h2>
		                <a href="add_species.php" class="button">+ Add Species</a>
	                </div>
	                <!-- Species Table -->
	                <table>
	                    <!-- Table Headers -->
	                    <thead>
	                        <tr>
	                        	<th>Species</th>
	                            <th>Planet</th>
	                            <th>Actions</th>
	                        </tr>
	                    </thead>
	                    <!-- Table Body -->
	                    <tbody>
	                        <?php foreach ($species as $species): ?>
	                        <tr>
	                        	<td><?= htmlspecialchars($species['name']) ?></td>
	                        	<td><?= htmlspecialchars($species['homeworld']) ?></td>
	                            <td>
	                                <a href="edit_species.php?species_id=<?= $species['species_id'] ?>">Edit</a>
	                            </td>
	                        </tr>
	                        <?php endforeach; ?>
	                    </tbody>
	                </table>
	            </div>
            </div>

        </main>
        <?php include('footer.php'); ?>
    </div>
</body>
</html>