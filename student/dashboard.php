<?php
/**
 * College Event Management System
 * Student Dashboard (student/dashboard.php)
 */

$pageTitle = 'Student Dashboard';
$isDashboard = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

requireStudentLogin('../login.php');
$student = getLoggedInStudent();
$pdo = getDBConnection();

// Fetch up-to-date student profile from DB
$currentStudent = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student['student_id']]);
    $currentStudent = $stmt->fetch() ?: $student;
} catch (Exception $e) {
    $currentStudent = $student;
}

// Fetch Student Stats
$totalRegistered = 0;
$approvedRegistrations = 0;
$pendingRegistrations = 0;
$totalAvailableEvents = 0;

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE student_id = ?");
    $stmt->execute([$student['student_id']]);
    $totalRegistered = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE student_id = ? AND status = 'Approved'");
    $stmt->execute([$student['student_id']]);
    $approvedRegistrations = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE student_id = ? AND status = 'Pending'");
    $stmt->execute([$student['student_id']]);
    $pendingRegistrations = (int)$stmt->fetchColumn();

    $totalAvailableEvents = (int)$pdo->query("SELECT COUNT(*) FROM events WHERE available_seats > 0")->fetchColumn();
} catch (Exception $e) {}

// Fetch Recent Registrations with Event details
$recentRegistrations = [];
try {
    $stmt = $pdo->prepare("
        SELECT r.registration_id, r.registration_date, r.status,
               e.event_id, e.event_name, e.category, e.event_date, e.event_time, e.venue, e.event_image
        FROM registrations r
        JOIN events e ON r.event_id = e.event_id
        WHERE r.student_id = ?
        ORDER BY r.registration_date DESC
        LIMIT 5
    ");
    $stmt->execute([$student['student_id']]);
    $recentRegistrations = $stmt->fetchAll();
} catch (Exception $e) {}

// Fetch 3 recommended upcoming events not yet registered by this student
$recommendedEvents = [];
try {
    $stmt = $pdo->prepare("
        SELECT * FROM events 
        WHERE event_id NOT IN (SELECT event_id FROM registrations WHERE student_id = ?)
          AND available_seats > 0
        ORDER BY event_date ASC
        LIMIT 3
    ");
    $stmt->execute([$student['student_id']]);
    $recommendedEvents = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<div class="dashboard-wrapper">
    <!-- Student Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-calendar2-check-fill"></i>
            <span>CEMS Student</span>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">
                <?= strtoupper(substr($currentStudent['name'] ?? 'S', 0, 1)); ?>
            </div>
            <div class="user-info text-truncate" style="max-width: 160px;">
                <div class="name text-truncate"><?= e($currentStudent['name']); ?></div>
                <div class="role text-truncate"><?= e($currentStudent['student_id']); ?></div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-header">Student Menu</li>
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="my_events.php">
                    <i class="bi bi-ticket-perforated"></i> My Registrations
                    <?php if ($totalRegistered > 0): ?>
                        <span class="badge bg-primary rounded-pill ms-auto"><?= $totalRegistered; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../events.php">
                    <i class="bi bi-calendar-event"></i> Browse All Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="profile.php">
                    <i class="bi bi-person-gear"></i> My Profile
                </a>
            </li>

            <li class="menu-header">Quick Links</li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php">
                    <i class="bi bi-house-door"></i> College Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../contact.php">
                    <i class="bi bi-question-circle"></i> Need Help?
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="../logout.php" class="btn btn-outline-danger w-100 btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="dashboard-main">
        <!-- Top Navbar -->
        <header class="dashboard-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="topbar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="topbar-title">Student Dashboard</h1>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="../events.php" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Register for Event
                </a>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle text-primary"></i>
                        <span class="d-none d-md-inline"><?= e($currentStudent['name']); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> Profile Settings</a></li>
                        <li><a class="dropdown-item" href="my_events.php"><i class="bi bi-ticket me-2"></i> My Passes</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Body Content -->
        <main class="dashboard-content">
            <!-- Flash Message -->
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']); ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= e($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Welcome Greeting Banner -->
            <div class="card border-0 rounded-4 shadow-sm mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #0f2b5c 0%, #1e40af 100%);">
                <div class="card-body p-4 p-md-5 position-relative">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <span class="badge bg-info text-dark px-3 py-1 text-uppercase fw-bold mb-2">Student Portal</span>
                            <h2 class="display-6 fw-bold mb-2">Welcome, <?= e($currentStudent['name']); ?>!</h2>
                            <p class="text-light opacity-90 mb-3">
                                <strong><?= e($currentStudent['course'] ?? 'BCA'); ?></strong> &bull; <?= e($currentStudent['department'] ?? 'Computer Applications'); ?> (<?= e($currentStudent['year'] ?? '3rd Year'); ?>) &bull; ID: <code><?= e($currentStudent['student_id']); ?></code>
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="my_events.php" class="btn btn-light btn-sm fw-bold px-3">
                                    <i class="bi bi-qr-code me-1"></i> View Registered Passes
                                </a>
                                <a href="../events.php" class="btn btn-outline-light btn-sm fw-semibold px-3">
                                    <i class="bi bi-compass me-1"></i> Browse Upcoming Events
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center d-none d-lg-block">
                            <i class="bi bi-mortarboard display-2 text-info opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Metric Cards -->
            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="metric-card">
                        <div class="metric-icon blue">
                            <i class="bi bi-ticket-perforated-fill"></i>
                        </div>
                        <div class="metric-info">
                            <div class="value"><?= $totalRegistered; ?></div>
                            <div class="label">Total Registered</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="metric-card">
                        <div class="metric-icon green">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="metric-info">
                            <div class="value"><?= $approvedRegistrations; ?></div>
                            <div class="label">Approved Passes</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="metric-card">
                        <div class="metric-icon amber">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="metric-info">
                            <div class="value"><?= $pendingRegistrations; ?></div>
                            <div class="label">Pending Approval</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="metric-card">
                        <div class="metric-icon purple">
                            <i class="bi bi-calendar2-week-fill"></i>
                        </div>
                        <div class="metric-info">
                            <div class="value"><?= $totalAvailableEvents; ?></div>
                            <div class="label">Available Events</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Registrations Table -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="content-card mb-0 h-100">
                        <div class="content-card-header">
                            <h3 class="content-card-title">
                                <i class="bi bi-clock-history text-primary"></i> My Recent Registrations
                            </h3>
                            <a href="my_events.php" class="btn btn-outline-primary btn-sm">View All</a>
                        </div>
                        <div class="content-card-body p-0">
                            <?php if (!empty($recentRegistrations)): ?>
                                <div class="table-responsive">
                                    <table class="table-custom">
                                        <thead>
                                            <tr>
                                                <th>Event Name</th>
                                                <th>Date & Venue</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentRegistrations as $reg): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= e($reg['event_name']); ?></strong><br>
                                                        <small class="text-muted">Registered: <?= formatDate($reg['registration_date']); ?></small>
                                                    </td>
                                                    <td>
                                                        <small><i class="bi bi-calendar3 me-1 text-primary"></i> <?= formatDate($reg['event_date']); ?></small><br>
                                                        <small class="text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i> <?= e($reg['venue']); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border"><?= e($reg['category']); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="status-pill <?= strtolower($reg['status']); ?>">
                                                            <i class="bi bi-circle-fill" style="font-size:0.5rem;"></i>
                                                            <?= e($reg['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="my_events.php#pass-<?= $reg['registration_id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-qr-code"></i> Pass
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5 px-3">
                                    <i class="bi bi-calendar-x display-4 text-muted d-block mb-3"></i>
                                    <h5>No event registrations yet!</h5>
                                    <p class="text-muted small mb-3">Explore the college calendar and register for upcoming competitions and workshops.</p>
                                    <a href="../events.php" class="btn btn-primary btn-sm px-4">Browse Campus Events</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Profile Quick Widget -->
                <div class="col-lg-4">
                    <div class="content-card mb-4">
                        <div class="content-card-header">
                            <h3 class="content-card-title">
                                <i class="bi bi-person-badge text-primary"></i> Student Profile
                            </h3>
                            <a href="profile.php" class="btn btn-link btn-sm p-0 text-decoration-none">Edit</a>
                        </div>
                        <div class="content-card-body text-center">
                            <div class="user-avatar mx-auto mb-3" style="width: 72px; height: 72px; font-size: 1.8rem;">
                                <?= strtoupper(substr($currentStudent['name'] ?? 'S', 0, 1)); ?>
                            </div>
                            <h5 class="fw-bold mb-1"><?= e($currentStudent['name']); ?></h5>
                            <p class="text-muted small mb-3"><code><?= e($currentStudent['student_id']); ?></code></p>
                            
                            <ul class="list-group list-group-flush text-start small mb-3">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Email:</span>
                                    <span class="fw-semibold text-truncate ms-2"><?= e($currentStudent['email']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Phone:</span>
                                    <span class="fw-semibold"><?= e($currentStudent['phone']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Department:</span>
                                    <span class="fw-semibold text-truncate ms-2"><?= e($currentStudent['department']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Course / Year:</span>
                                    <span class="fw-semibold"><?= e($currentStudent['course']); ?> (<?= e($currentStudent['year']); ?>)</span>
                                </li>
                            </ul>

                            <a href="profile.php" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-gear me-1"></i> Update Profile Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommended Events for this student -->
            <?php if (!empty($recommendedEvents)): ?>
                <div class="content-card">
                    <div class="content-card-header">
                        <h3 class="content-card-title">
                            <i class="bi bi-stars text-warning"></i> Recommended Events For You
                        </h3>
                        <a href="../events.php" class="btn btn-outline-primary btn-sm">Explore All</a>
                    </div>
                    <div class="content-card-body">
                        <div class="row g-3">
                            <?php foreach ($recommendedEvents as $ev): ?>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-primary"><?= e($ev['category']); ?></span>
                                            <small class="text-success fw-bold"><?= e($ev['available_seats']); ?> seats left</small>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1"><?= e($ev['event_name']); ?></h6>
                                        <p class="text-muted small mb-2"><i class="bi bi-calendar3 me-1"></i> <?= formatDate($ev['event_date']); ?></p>
                                        <p class="text-muted small mb-3 text-truncate"><i class="bi bi-geo-alt me-1"></i> <?= e($ev['venue']); ?></p>
                                        <div class="mt-auto">
                                            <a href="../events.php#event-<?= $ev['event_id']; ?>" class="btn btn-primary btn-sm w-100">
                                                <i class="bi bi-pencil-square me-1"></i> Quick Register
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>

