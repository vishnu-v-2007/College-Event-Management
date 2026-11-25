<?php
/**
 * College Event Management System
 * Administrator Dashboard (admin/dashboard.php)
 */

$pageTitle = 'Admin Dashboard';
$isDashboard = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

requireAdminLogin('login.php');
$admin = getLoggedInAdmin();
$pdo = getDBConnection();

// Summary Metrics
$totalStudents = 0;
$totalEvents = 0;
$totalRegistrations = 0;
$upcomingEvents = 0;
$pendingRegistrations = 0;
$unreadMessages = 0;

try {
    $totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalEvents = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $totalRegistrations = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
    $upcomingEvents = (int)$pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= date('now') OR event_date >= CURDATE()")->fetchColumn();
    $pendingRegistrations = (int)$pdo->query("SELECT COUNT(*) FROM registrations WHERE status = 'Pending'")->fetchColumn();
    $unreadMessages = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'Unread'")->fetchColumn();
} catch (Exception $e) {}

// Chart Data 1: Registrations per Event
$chartEvents = [];
$chartRegCounts = [];
try {
    $stmt = $pdo->query("
        SELECT e.event_name, COUNT(r.registration_id) as reg_count
        FROM events e
        LEFT JOIN registrations r ON e.event_id = r.event_id
        GROUP BY e.event_id, e.event_name
        ORDER BY reg_count DESC
        LIMIT 6
    ");
    $chartRows = $stmt->fetchAll();
    foreach ($chartRows as $cr) {
        $chartEvents[] = (strlen($cr['event_name']) > 18) ? substr($cr['event_name'], 0, 18) . '...' : $cr['event_name'];
        $chartRegCounts[] = (int)$cr['reg_count'];
    }
} catch (Exception $e) {}

// Chart Data 2: Events by Category
$categoryLabels = [];
$categoryCounts = [];
try {
    $stmt = $pdo->query("
        SELECT category, COUNT(*) as cat_count
        FROM events
        GROUP BY category
    ");
    $catRows = $stmt->fetchAll();
    foreach ($catRows as $crow) {
        $categoryLabels[] = $crow['category'];
        $categoryCounts[] = (int)$crow['cat_count'];
    }
} catch (Exception $e) {}

// Recent Registrations (latest 6)
$recentRegistrations = [];
try {
    $stmt = $pdo->query("
        SELECT r.registration_id, r.registration_date, r.status,
               s.student_id, s.name as student_name, s.department, s.course,
               e.event_id, e.event_name, e.event_date
        FROM registrations r
        JOIN students s ON r.student_id = s.student_id
        JOIN events e ON r.event_id = e.event_id
        ORDER BY r.registration_date DESC
        LIMIT 6
    ");
    $recentRegistrations = $stmt->fetchAll();
} catch (Exception $e) {}

// Recent Contact Messages (latest 4)
$recentMessages = [];
try {
    $stmt = $pdo->query("
        SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 4
    ");
    $recentMessages = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<div class="dashboard-wrapper">
    <!-- Admin Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-shield-check"></i>
            <span>CEMS Admin</span>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <?= strtoupper(substr($admin['username'] ?? 'A', 0, 1)); ?>
            </div>
            <div class="user-info text-truncate" style="max-width: 160px;">
                <div class="name text-truncate"><?= e($admin['username']); ?></div>
                <div class="role text-warning">System Administrator</div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-header">Administration</li>
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_events.php">
                    <i class="bi bi-calendar3"></i> Manage Events
                    <span class="badge bg-primary rounded-pill ms-auto"><?= $totalEvents; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="add_event.php">
                    <i class="bi bi-calendar-plus"></i> Add New Event
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_registrations.php">
                    <i class="bi bi-clipboard-check"></i> Registrations
                    <?php if ($pendingRegistrations > 0): ?>
                        <span class="badge bg-warning text-dark rounded-pill ms-auto"><?= $pendingRegistrations; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_students.php">
                    <i class="bi bi-people"></i> Manage Students
                    <span class="badge bg-secondary rounded-pill ms-auto"><?= $totalStudents; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="contact_messages.php">
                    <i class="bi bi-chat-dots"></i> Contact Messages
                    <?php if ($unreadMessages > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-auto"><?= $unreadMessages; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="menu-header">Public Portal</li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> View Website
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="../logout.php" class="btn btn-outline-danger w-100 btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i> Admin Logout
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="topbar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="topbar-title">Administrator Console</h1>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="add_event.php" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Create Event
                </a>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle border" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-shield-lock-fill text-warning me-1"></i> <?= e($admin['username']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="manage_events.php"><i class="bi bi-calendar3 me-2"></i> Events</a></li>
                        <li><a class="dropdown-item" href="manage_registrations.php"><i class="bi bi-clipboard me-2"></i> Registrations</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="dashboard-content">
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']); ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= e($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Metrics Overview Cards -->
            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="metric-card">
                        <div class="metric-icon blue">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="metric-info">
                            <div class="value"><?= $totalStudents; ?></div>
                            <div class="label">Total Students</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="metric-card">
                        <div class="metric-icon indigo">
                            <i class="bi bi-calendar-event-fill"></i>
                        </div>
                        <div class="metric-info">
                            <div class="value"><?= $totalEvents; ?></div>
                            <div class="label">Total Events</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="metric-card">
                        <div class="metric-icon green">
                            <i class="bi bi-clipboard-data-fill"></i>
                        </div>
                        <div class="metric-info">
                            <div class="value"><?= $totalRegistrations; ?></div>
                            <div class="label">Total Registrations</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="metric-card">
                        <div class="metric-icon amber">
                            <i class="bi bi-hourglass-top"></i>
                        </div>
                        <div class="metric-info">
                            <div class="value"><?= $pendingRegistrations; ?></div>
                            <div class="label">Pending Review</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visual Charts Row -->
            <div class="row g-4 mb-4">
                <div class="col-lg-7">
                    <div class="content-card h-100 mb-0">
                        <div class="content-card-header">
                            <h3 class="content-card-title">
                                <i class="bi bi-bar-chart-fill text-primary"></i> Registrations Per Event
                            </h3>
                            <span class="badge bg-light text-muted border">Top Events</span>
                        </div>
                        <div class="content-card-body">
                            <canvas id="registrationsChart" style="max-height: 280px; width:100%;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="content-card h-100 mb-0">
                        <div class="content-card-header">
                            <h3 class="content-card-title">
                                <i class="bi bi-pie-chart-fill text-info"></i> Events by Category
                            </h3>
                            <span class="badge bg-light text-muted border">Distribution</span>
                        </div>
                        <div class="content-card-body d-flex align-items-center justify-content-center">
                            <canvas id="categoriesChart" style="max-height: 260px; max-width: 260px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Registrations & Inquiries Row -->
            <div class="row g-4">
                <!-- Recent Registrations -->
                <div class="col-lg-8">
                    <div class="content-card mb-0">
                        <div class="content-card-header">
                            <h3 class="content-card-title">
                                <i class="bi bi-card-checklist text-primary"></i> Recent Event Registrations
                            </h3>
                            <a href="manage_registrations.php" class="btn btn-outline-primary btn-sm">Manage All</a>
                        </div>
                        <div class="content-card-body p-0">
                            <?php if (!empty($recentRegistrations)): ?>
                                <div class="table-responsive">
                                    <table class="table-custom">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Event Name</th>
                                                <th>Registered</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentRegistrations as $reg): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= e($reg['student_name']); ?></strong><br>
                                                        <small class="text-muted font-monospace"><?= e($reg['student_id']); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 200px;">
                                                            <?= e($reg['event_name']); ?>
                                                        </div>
                                                        <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> <?= formatDate($reg['event_date']); ?></small>
                                                    </td>
                                                    <td>
                                                        <small><?= formatDate($reg['registration_date']); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="status-pill <?= strtolower($reg['status']); ?>">
                                                            <i class="bi bi-circle-fill" style="font-size:0.5rem;"></i>
                                                            <?= e($reg['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="manage_registrations.php?search=<?= urlencode($reg['student_id']); ?>" class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">No student registrations recorded yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Contact Messages -->
                <div class="col-lg-4">
                    <div class="content-card mb-0">
                        <div class="content-card-header">
                            <h3 class="content-card-title">
                                <i class="bi bi-envelope-paper text-primary"></i> Support Inquiries
                            </h3>
                            <a href="contact_messages.php" class="btn btn-outline-primary btn-sm">View All</a>
                        </div>
                        <div class="content-card-body p-0">
                            <?php if (!empty($recentMessages)): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recentMessages as $msg): ?>
                                        <a href="contact_messages.php#msg-<?= $msg['message_id']; ?>" class="list-group-item list-group-item-action p-3">
                                            <div class="d-flex w-100 justify-content-between mb-1">
                                                <h6 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 170px;"><?= e($msg['name']); ?></h6>
                                                <small class="text-muted"><?= formatDate($msg['created_at']); ?></small>
                                            </div>
                                            <p class="mb-1 small text-dark fw-semibold text-truncate"><?= e($msg['subject']); ?></p>
                                            <p class="mb-1 small text-muted text-truncate"><?= e($msg['message']); ?></p>
                                            <span class="badge <?= $msg['status'] === 'Unread' ? 'bg-danger' : 'bg-light text-muted border'; ?> small">
                                                <?= e($msg['status']); ?>
                                            </span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">No contact messages received.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Registrations per Event Bar Chart
    const ctxBar = document.getElementById('registrationsChart');
    if (ctxBar) {
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartEvents); ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?= json_encode($chartRegCounts); ?>,
                    backgroundColor: 'rgba(2, 132, 199, 0.85)',
                    borderColor: '#0284c7',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // 2. Events by Category Doughnut Chart
    const ctxPie = document.getElementById('categoriesChart');
    if (ctxPie) {
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($categoryLabels); ?>,
                datasets: [{
                    data: <?= json_encode($categoryCounts); ?>,
                    backgroundColor: [
                        '#0284c7', '#ec4899', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                }
            }
        });
    }
});
</script>
</body>
</html>

