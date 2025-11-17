<nav class="site-nav" aria-label="Primary">
        <div class="wrapper">
            <div class="brand-block">
                <!-- AI generated logo -->
                <img src="images/logo.jpg" alt="Nexora IT Solutions logo">
                <span>Nexora IT Solutions</span>
            </div>
            <div class="menu-links">
                <a <?= ($currentPage === 'index') ? 'class="active"' : ''; ?> href="index.php">Home</a>
                <a <?= ($currentPage === 'jobs') ? 'class="active"' : ''; ?> href="jobs.php">Jobs</a>
                <a <?= ($currentPage === 'apply') ? 'class="active"' : ''; ?> href="apply.php">Apply</a>
                <a <?= ($currentPage === 'about') ? 'class="active"' : ''; ?> href="about.php">About</a>
                <a <?= ($currentPage === 'manage') ? 'class="active"' : ''; ?> href="manage.php">HR System</a>
                <a <?= ($currentPage === 'enhancements') ? 'class="active"' : ''; ?> href="enhancements.php">Enhancements</a>
            </div>
        </div>
    </nav>