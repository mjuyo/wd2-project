<header class="header">
    <div class="header-container">
        <!-- Logo -->
        <a href="index.php" class="logo">
            <img src="images/hutt_logo.png" >
            <div class="title">
                <h2 class="special-font">GBN</h2>
            </div>
        </a>

        <!-- Nav Bar -->
        <ul class="navbar">
            <li><a href="index.php" class="<?= is_active('index.php') ?>">Home</a></li>
            <li><a href="content.php" class="<?= is_active('content.php') ?>">Bounties</a></li>
        </ul>

        
        <!-- Search bar-->
        <form action="search_results.php" method="GET">
            <div class="search-bar">
                <input type="search" name="query" placeholder="Search...">
                <?php if (isset($_GET['species_id'])): ?>
                    <input type="hidden" name="species_id" value="<?= htmlspecialchars($_GET['species_id']) ?>">
                <?php endif; ?>
                <?php if (isset($_GET['status_id'])): ?>
                    <input type="hidden" name="status_id" value="<?= htmlspecialchars($_GET['status_id']) ?>">
                <?php endif; ?>
                <?php if (isset($_GET['difficulty_id'])): ?>
                    <input type="hidden" name="difficulty_id" value="<?= htmlspecialchars($_GET['difficulty_id']) ?>">
                <?php endif; ?>
                <i class="bi bi-search"></i>
            </div>
        </form>

        <!-- User login info -->
        <div class="login-info">
            <!-- Check if user is signed in and display appropriate link -->
            <?php if (isset($_SESSION['username'])): ?>
                <div class="user-welcome">
                    <span>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></span>              
                    <div class="dropdown">
                      <button class="dropbtn">
                        <i class="bi bi-list"></i>
                        <i class="bi bi-person-circle"></i>
                      </button>
                      <div class="dropdown-content">
                        <a href="dashboard.php">Dashboard</a>
                        <a href="signout.php">Sign Out</a>
                      </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="signin-btn">
                    <a href="signin.php">Sign In</a>
                </div>    
            <?php endif; ?>
        </div>
    </div>
</header>

