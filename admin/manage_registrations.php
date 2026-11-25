<?php
/**
 * College Event Management System
 * Admin Manage Event Registrations (admin/manage_registrations.php)
 */

$pageTitle = 'Manage Registrations - Admin Portal';
$isDashboard = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

requireAdminLogin('login.php');
$admin = getLoggedInAdmin();
$pdo = getDBConnection();

// Handle Status Update (Approved / Pending / Rejected)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Security token invalid.');
        header('Location: manage_registrations.php');
        exit();
    }

    $regId = (int)($_POST['registration_id'] ?? 0);
    $newStatus = trim($_POST['new_status'] ?? '');

    if ($regId > 0 && in_array($newStatus, ['Approved', 'Pending', 'Rejected'])) {
        try {
            $stmt = $pdo->prepare("UPDATE registrations SET status = ? WHERE registration_id = ?");
            $stmt->execute([$newStatus, $regId]);
            setFlash('success', 'Registration status updated to "' . $newStatus . '".');
        } catch (Exception $e) {
            setFlash('danger', 'Error updating status: ' . $e->getMessage());
        }
    }
    header('Location: manage_registrations.php');
    exit();
}

// Handle Delete Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_registration') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Security token invalid.');
        header('Location: manage_registrations.php');
        exit();
    }

    $regId = (int)($_POST['registration_id'] ?? 0);
    $eventId = (int)($_POST['event_id'] ?? 0);

    if ($regId > 0) {
        try {
            $pdo->beginTransaction();
            $del = $pdo->prepare("DELETE FROM registrations WHERE registration_id = ?");
            $del->execute([$regId]);

            // Release seat back if event exists
            if ($eventId > 0) {
                $upd = $pdo->prepare("UPDATE events SET available_seats = available_seats + 1 WHERE event_id = ?");
                $upd->execute([$eventId]);
            }
            $pdo->commit();
            setFlash('success', 'Registration record deleted and seat returned to availability pool.');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            setFlash('danger', 'Error deleting registration: ' . $e->getMessage());
        }
    }
    header('Location: manage_registrations.php');
    exit();
}

// Filter and Search
$search = trim($_GET['search'] ?? '');
$eventFilter = (int)($_GET['event_id'] ?? 0);
$statusFilter = trim($_GET['status'] ?? '');

// Fetch list of events for filter dropdown
$allEvents = [];
try {
    $allEvents = $pdo->query("SELECT event_id, event_name FROM events ORDER BY event_name ASC")->fetchAll();
} catch (Exception $e) {}

$query = "
    SELECT r.registration_id, r.registration_date, r.status,
           s.student_id, s.name as student_name, s.email as student_email, s.phone as student_phone, s.department, s.course,
           e.event_id, e.event_name, e.event_date, e.event_time, e.venue, e.category
    FROM registrations r
    JOIN students s ON r.student_id = s.student_id
    JOIN events e ON r.event_id = e.event_id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (s.name LIKE ? OR s.student_id LIKE ? OR s.email LIKE ? OR e.event_name LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($eventFilter > 0) {
    $query .= " AND e.event_id = ?";
    $params[] = $eventFilter;
}

if (!empty($statusFilter)) {
    $query .= " AND r.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY r.registration_date DESC";

$registrations = [];
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $registrations = $stmt->fetchAll();
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
}
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
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_events.php">
                    <i class="bi bi-calendar3"></i> Manage Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="add_event.php">
                    <i class="bi bi-calendar-plus"></i> Add New Event
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="manage_registrations.php">
                    <i class="bi bi-clipboard-check"></i> Registrations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_students.php">
                    <i class="bi bi-people"></i> Manage Students
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="contact_messages.php">
                    <i class="bi bi-chat-dots"></i> Contact Messages
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
                <h1 class="topbar-title">Manage Event Registrations</h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print();">
                    <i class="bi bi-printer me-1"></i> Print / Export
                </button>
            </div>
        </header>

        <main class="dashboard-content">
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']); ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= e($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filter & Search Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-3">
                    <form method="GET" action="manage_registrations.php" class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search student name, ID or event..." value="<?= e($search); ?>">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <select name="event_id" class="form-select form-select-sm">
                                <option value="">Filter by Event</option>
                                <?php foreach ($allEvents as $ae): ?>
                                    <option value="<?= $ae['event_id']; ?>" <?= ($eventFilter === (int)$ae['event_id']) ? 'selected' : ''; ?>>
                                        <?= e($ae['event_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="Approved" <?= $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm px-3 flex-grow-1">Filter</button>
                            <a href="manage_registrations.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Registrations Table -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3 class="content-card-title">
                        <i class="bi bi-clipboard2-check text-primary"></i> Registration Records (<?= count($registrations); ?>)
                    </h3>
                </div>
                <div class="content-card-body p-0">
                    <?php if (!empty($registrations)): ?>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Reg ID</th>
                                        <th>Student Information</th>
                                        <th>Event Details</th>
                                        <th>Date & Venue</th>
                                        <th>Registered Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registrations as $rg): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark border font-monospace">
                                                    #REG-<?= str_pad($rg['registration_id'], 5, '0', STR_PAD_LEFT); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= e($rg['student_name']); ?></strong><br>
                                                <small class="text-muted font-monospace"><i class="bi bi-card-heading me-1"></i><?= e($rg['student_id']); ?></small><br>
                                                <small class="text-muted"><i class="bi bi-envelope me-1"></i><?= e($rg['student_email']); ?></small>
                                            </td>
                                            <td>
                                                <strong class="text-primary"><?= e($rg['event_name']); ?></strong><br>
                                                <span class="badge bg-secondary"><?= e($rg['category']); ?></span>
                                            </td>
                                            <td>
                                                <small><i class="bi bi-calendar3 me-1 text-primary"></i> <?= formatDate($rg['event_date']); ?></small><br>
                                                <small class="text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i> <?= e($rg['venue']); ?></small>
                                            </td>
                                            <td>
                                                <small><?= formatDate($rg['registration_date']); ?></small>
                                            </td>
                                            <td>
                                                <span class="status-pill <?= strtolower($rg['status']); ?> mb-1 d-inline-block">
                                                    <i class="bi bi-circle-fill" style="font-size:0.5rem;"></i>
                                                    <?= e($rg['status']); ?>
                                                </span>

                                                <!-- Quick Status Switch Form -->
                                                <form method="POST" action="manage_registrations.php" class="d-flex gap-1 mt-1">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
                                                    <input type="hidden" name="registration_id" value="<?= $rg['registration_id']; ?>">
                                                    
                                                    <?php if ($rg['status'] !== 'Approved'): ?>
                                                        <button type="submit" name="new_status" value="Approved" class="btn btn-xs btn-success py-0 px-2" style="font-size:0.75rem;" title="Approve Registration">
                                                            Approve
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($rg['status'] !== 'Rejected'): ?>
                                                        <button type="submit" name="new_status" value="Rejected" class="btn btn-xs btn-danger py-0 px-2" style="font-size:0.75rem;" title="Reject Registration">
                                                            Reject
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($rg['status'] !== 'Pending'): ?>
                                                        <button type="submit" name="new_status" value="Pending" class="btn btn-xs btn-warning py-0 px-2" style="font-size:0.75rem;" title="Mark as Pending">
                                                            Pending
                                                        </button>
                                                    <?php endif; ?>
                                                </form>
                                            </td>
                                            <td>
                                                <form method="POST" action="manage_registrations.php" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_registration">
                                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
                                                    <input type="hidden" name="registration_id" value="<?= $rg['registration_id']; ?>">
                                                    <input type="hidden" name="event_id" value="<?= $rg['event_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            data-confirm="Are you sure you want to delete this registration? Seat will be refunded." title="Delete Registration Record">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-clipboard-x display-4 text-muted d-block mb-3"></i>
                            <h5>No registrations found</h5>
                            <p class="text-muted small">No registration records match the current filter parameters.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>

