<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>

<style>
.header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    padding: 15px 0;
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo-text {
    font-size: 24px;
    font-weight: 600;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-decoration: none;
}

.nav-links {
    display: flex;
    gap: 30px;
    align-items: center;
}

.nav-link {
    color: #666;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    padding: 8px 16px;
    border-radius: 8px;
}

.nav-link:hover {
    color: #764ba2;
    background: rgba(118, 75, 162, 0.1);
}

.nav-link.active {
    color: #fff;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.user-menu {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-name {
    color: #666;
    font-weight: 500;
}

.logout-btn {
    color: #ff6b6b;
    text-decoration: none;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    background: rgba(255, 107, 107, 0.1);
}

@media (max-width: 768px) {
    .nav-links {
        display: none;
    }
    
    .nav-links.active {
        display: flex;
        flex-direction: column;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .menu-toggle {
        display: block;
        font-size: 24px;
        cursor: pointer;
    }
}
</style>

<header class="header">
    <div class="nav-container">
        <a href="index.php" class="logo-text">Virtual Study Buddy</a>
        
        <?php if ($is_logged_in): ?>
            <nav class="nav-links">
                <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                <a href="study_planner.php" class="nav-link <?php echo $current_page == 'study_planner.php' ? 'active' : ''; ?>">Study Planner</a>
                <a href="upload_notes.php" class="nav-link <?php echo $current_page == 'upload_notes.php' ? 'active' : ''; ?>">Upload Notes</a>
                <a href="studybuddy.php" class="nav-link <?php echo $current_page == 'studybuddy.php' ? 'active' : ''; ?>">StudyBuddy AI</a>
            </nav>
            
            <div class="user-menu">
                <span class="user-name">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        <?php else: ?>
            <nav class="nav-links">
                <a href="login.php" class="nav-link <?php echo $current_page == 'login.php' ? 'active' : ''; ?>">Login</a>
                <a href="signup.php" class="nav-link <?php echo $current_page == 'signup.php' ? 'active' : ''; ?>">Sign Up</a>
            </nav>
        <?php endif; ?>
    </div>
</header>

<!-- Add spacing after fixed header -->
<div style="height: 80px;"></div> 