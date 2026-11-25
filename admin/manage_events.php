<?php
/**
 * College Event Management System
 * Admin Manage Events (admin/manage_events.php)
 */

$pageTitle = 'Manage Events - Admin Portal';
$isDashboard = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

requireAdminLogin('login.php');
$admin = getLoggedInAdmin();
$pdo = getDBConnection();

// Handle Delete Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_event') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Security token invalid. Please try again.');
        header('Location: manage_events.php');
        exit();
    }

    $eventId = (int)($_POST['event_id'] ?? 0);
    if ($eventId > 0) {
        try {
            // Fetch event to get image filename
            $stmt = $pdo->prepare("SELECT event_image, event_name FROM events WHERE event_id = ?");
            $stmt->execute([$eventId]);
            $ev = $stmt->fetch();

            if ($ev) {
                // Delete event (foreign key cascade deletes registrations)
                $del = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
                $del->execute([$eventId]);

                // Delete image if custom upload
                if (!empty($ev['event_image']) && !str_starts_with($ev['event_image'], 'assets/')) {
                    $imgPath = __DIR__ . '/../uploads/events/' . $ev['event_image'];
                    if (file_exists($imgPath) && !in_array($ev['event_image'], ['web_dev_workshop.jpg', 'python_workshop.jpg', 'coding_competition.jpg', 'data_science_seminar.jpg', 'cultural_fest.jpg', 'sports_meet.jpg', 'technical_quiz.jpg', 'entrepreneurship_summit.jpg'])) {
                        @unlink($imgPath);
                    }
                }

                setFlash('success', 'Event "' . $ev['event_name'] . '" deleted successfully.');
            }
        } catch (Exception $e) {
            setFlash('danger', 'Error deleting event: ' . $e->getMessage());
        }
    }
    header('Location: manage_events.php');
    exit();
}

// Search and filter parameters
$search = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');

$query = "
    SELECT e.*, COUNT(r.registration_id) AS total_registered
    FROM events e
    LEFT JOIN registrations r ON e.event_id = r.event_id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (e.event_name LIKE ? OR e.venue LIKE ? OR e.description LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if (!empty($categoryFilter)) {
    $query .= " AND e.category = ?";
    $params[] = $categoryFilter;
}

$query .= " GROUP BY e.event_id ORDER BY e.event_date ASC";

$events = [];
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll();
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
                <a class="nav-link active" href="manage_events.php">
                    <i class="bi bi-calendar3"></i> Manage Events
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

    <!-- Main Content -->
    <div class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="topbar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="topbar-title">Manage College Events</h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="add_event.php" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add New Event
                </a>
            </div>
        </header>

        <main class="dashboard-content">
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']); ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= e($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Search & Filter Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-3">
                    <form method="GET" action="manage_events.php" class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search event by name or venue..." value="<?= e($search); ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <option value="Workshops" <?= $categoryFilter === 'Workshops' ? 'selected' : ''; ?>>Workshops</option>
                                <option value="Technical Events" <?= $categoryFilter === 'Technical Events' ? 'selected' : ''; ?>>Technical Events</option>
                                <option value="Cultural Events" <?= $categoryFilter === 'Cultural Events' ? 'selected' : ''; ?>>Cultural Events</option>
                                <option value="Sports Events" <?= $categoryFilter === 'Sports Events' ? 'selected' : ''; ?>>Sports Events</option>
                                <option value="Seminars" <?= $categoryFilter === 'Seminars' ? 'selected' : ''; ?>>Seminars</option>
                                <option value="Competitions" <?= $categoryFilter === 'Competitions' ? 'selected' : ''; ?>>Competitions</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm px-3 flex-grow-1">Filter</button>
                            <a href="manage_events.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Events Table -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3 class="content-card-title">
                        <i class="bi bi-calendar-check text-primary"></i> Event List (<?= count($events); ?>)
                    </h3>
                    <a href="add_event.php" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add Event
                    </a>
                </div>
                <div class="content-card-body p-0">
                    <?php if (!empty($events)): ?>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Thumbnail</th>
                                        <th>Event Name</th>
                                        <th>Category</th>
                                        <th>Date & Venue</th>
                                        <th>Capacity / Seats</th>
                                        <th>Registrations</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $ev): ?>
                                        <?php
                                            $thumb = 'uploads/events/' . ($ev['event_image'] ?? 'web_dev_workshop.jpg');
                                            if (!file_exists(__DIR__ . '/../' . $thumb)) {
                                                $thumb = 'assets/images/web_dev_workshop.jpg';
                                            }
                                            $isFull = ((int)$ev['available_seats'] <= 0);
                                        ?>
                                        <tr>
                                            <td style="width: 70px;">
                                                <img src="../<?= $thumb; ?>" alt="" class="rounded-2 shadow-sm" style="width: 60px; height: 42px; object-fit: cover;">
                                            </td>
                                            <td>
                                                <strong><?= e($ev['event_name']); ?></strong><br>
                                                <small class="text-muted text-truncate d-inline-block" style="max-width: 240px;"><?= e($ev['description']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= e($ev['category']); ?></span>
                                            </td>
                                            <td>
                                                <div><i class="bi bi-calendar3 text-primary me-1"></i> <?= formatDate($ev['event_date']); ?></div>
                                                <small class="text-muted"><i class="bi bi-geo-alt text-danger me-1"></i> <?= e($ev['venue']); ?></small>
                                            </td>
                                            <td>
                                                <div class="small fw-semibold <?= $isFull ? 'text-danger' : 'text-success'; ?>">
                                                    <?= e($ev['available_seats']); ?> / <?= e($ev['total_seats']); ?> Available
                                                </div>
                                                <div class="progress mt-1" style="height: 6px; width: 100px;">
                                                    <?php 
                                                        $booked = (int)$ev['total_seats'] - (int)$ev['available_seats'];
                                                        $pct = ($ev['total_seats'] > 0) ? round(($booked / $ev['total_seats']) * 100) : 0;
                                                    ?>
                                                    <div class="progress-bar <?= $pct > 80 ? 'bg-danger' : 'bg-primary'; ?>" style="width: <?= $pct; ?>%;"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="manage_registrations.php?event_id=<?= $ev['event_id']; ?>" class="badge bg-primary text-white text-decoration-none">
                                                    <?= $ev['total_registered']; ?> Registered
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="edit_event.php?id=<?= $ev['event_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Event">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <form method="POST" action="manage_events.php" class="d-inline">
                                                        <input type="hidden" name="action" value="delete_event">
                                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
                                                        <input type="hidden" name="event_id" value="<?= $ev['event_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                data-confirm="Are you sure you want to delete '<?= e($ev['event_name']); ?>'? All associated registrations will also be removed." title="Delete Event">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x display-4 text-muted d-block mb-3"></i>
                            <h5>No events match your criteria</h5>
                            <p class="text-muted small">Try adjusting your search terms or create a new event.</p>
                            <a href="add_event.php" class="btn btn-primary btn-sm px-4">Create New Event</a>
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

