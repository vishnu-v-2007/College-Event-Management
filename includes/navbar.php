<?php
/**
 * College Event Management System
 * Main Navigation Bar
 */

$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isSubDir = preg_match('#/(student|admin)$#', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')));
$root = $isSubDir ? '../' : './';
$student = getLoggedInStudent();
$admin = getLoggedInAdmin();
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= $root; ?>index.php">
            <i class="bi bi-calendar2-check-fill"></i>
            <span>CEMS <span class="d-none d-sm-inline text-info fw-normal fs-6">| College Events</span></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'index.php' && !$isSubDir) ? 'active' : ''; ?>" href="<?= $root; ?>index.php">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'about.php') ? 'active' : ''; ?>" href="<?= $root; ?>about.php">
                        <i class="bi bi-info-circle me-1"></i> About Us
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'events.php') ? 'active' : ''; ?>" href="<?= $root; ?>events.php">
                        <i class="bi bi-calendar-event me-1"></i> Browse Events
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript === 'contact.php') ? 'active' : ''; ?>" href="<?= $root; ?>contact.php">
                        <i class="bi bi-envelope me-1"></i> Contact
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if (isStudentLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2" type="button" id="studentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 text-info"></i>
                            <span><?= e($student['name']); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="studentMenu">
                            <li><h6 class="dropdown-header text-primary"><i class="bi bi-mortarboard me-1"></i> Student ID: <?= e($student['student_id']); ?></h6></li>
                            <li><a class="dropdown-item" href="<?= $root; ?>student/dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= $root; ?>student/my_events.php"><i class="bi bi-ticket-perforated me-2"></i> My Registrations</a></li>
                            <li><a class="dropdown-item" href="<?= $root; ?>student/profile.php"><i class="bi bi-person-gear me-2"></i> Edit Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $root; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php elseif (isAdminLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn btn-warning dropdown-toggle d-flex align-items-center gap-2 text-dark fw-bold" type="button" id="adminMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>Admin: <?= e($admin['username']); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="adminMenu">
                            <li><a class="dropdown-item" href="<?= $root; ?>admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Admin Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= $root; ?>admin/manage_events.php"><i class="bi bi-calendar3 me-2"></i> Manage Events</a></li>
                            <li><a class="dropdown-item" href="<?= $root; ?>admin/manage_registrations.php"><i class="bi bi-clipboard-check me-2"></i> Manage Registrations</a></li>
                            <li><a class="dropdown-item" href="<?= $root; ?>admin/manage_students.php"><i class="bi bi-people me-2"></i> Manage Students</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $root; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Admin Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= $root; ?>login.php" class="btn btn-outline-light btn-sm px-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Student Login
                    </a>
                    <a href="<?= $root; ?>register.php" class="btn btn-info btn-sm text-dark fw-semibold px-3">
                        <i class="bi bi-person-plus me-1"></i> Register
                    </a>
                    <a href="<?= $root; ?>admin/login.php" class="btn btn-dark btn-sm border-secondary text-light px-2" title="Admin Portal">
                        <i class="bi bi-shield-lock"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Alerts Container -->
<?php $flash = getFlash(); if ($flash): ?>
<div class="container mt-3">
    <div class="alert alert-<?= e($flash['type']); ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-<?= ($flash['type'] === 'success') ? 'check-circle-fill' : (($flash['type'] === 'danger') ? 'exclamation-triangle-fill' : 'info-circle-fill'); ?> me-2"></i>
        <?= e($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>

