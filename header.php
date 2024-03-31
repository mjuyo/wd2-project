<div class="header">
    <div class="logo">
        <a href="index.php">
            <img src="images/hutt_logo.png" >
            <div class="title">
                <h2 class="special-font">Galactic Bounties Network</h2>
                <h2>Galactic Bounties Network</h2>
            </div>
        </a>
    </div>
    <div class="search-bar">
        <input type="search" placeholder="Search...">
    </div>
    <div class="auth-links">
        <!-- Check if user is signed in and display appropriate link -->
        <?php if (isset($_SESSION['username'])): ?>
            <div class="user-welcome">
                <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <label for="toggle">
                        <p>&#9776;</p>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                          <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                          <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                        </svg>
                    </label>
                    <input type="checkbox" id="toggle" />

                <ul id="menuList">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="signout.php">Sign Out</a></li>
                </ul>
            </div>
        <?php else: ?>
            <div class="">
                
            </div>    
                <a href="signin.php">Sign In</a>
        <?php endif; ?>
    </div>
</div>

<div id="navbar">
    <ul id="menu">
        <li><a href="index.php" class="<?= is_active('index.php') ?>">Home</a></li>
        <li><a href="bounty.php" class="<?= is_active('bounty.php') ?>">New Bounty</a></li>
    </ul>
</div>