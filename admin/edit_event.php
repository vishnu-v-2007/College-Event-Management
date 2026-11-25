<?php
/**
 * College Event Management System
 * Admin Edit Event (admin/edit_event.php)
 */

$pageTitle = 'Edit Event - Admin Portal';
$isDashboard = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

requireAdminLogin('login.php');
$admin = getLoggedInAdmin();
$pdo = getDBConnection();

$eventId = (int)($_GET['id'] ?? 0);
if ($eventId <= 0) {
    setFlash('danger', 'Invalid event selected for editing.');
    header('Location: manage_events.php');
    exit();
}

// Fetch current event record
$event = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();
} catch (Exception $e) {}

if (!$event) {
    setFlash('danger', 'Event not found in database.');
    header('Location: manage_events.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid. Please try again.';
    } else {
        $eventName      = trim($_POST['event_name'] ?? '');
        $description    = trim($_POST['description'] ?? '');
        $category       = trim($_POST['category'] ?? '');
        $eventDate      = trim($_POST['event_date'] ?? '');
        $eventTime      = trim($_POST['event_time'] ?? '');
        $venue          = trim($_POST['venue'] ?? '');
        $totalSeats     = (int)($_POST['total_seats'] ?? 0);
        $availableSeats = (int)($_POST['available_seats'] ?? 0);

        if (empty($eventName) || strlen($eventName) < 3) {
            $errors[] = 'Event title must be at least 3 characters.';
        }
        if (empty($description) || strlen($description) < 10) {
            $errors[] = 'Description must be at least 10 characters.';
        }
        if (empty($category)) {
            $errors[] = 'Please select a valid category.';
        }
        if (empty($eventDate)) {
            $errors[] = 'Date is required.';
        }
        if (empty($eventTime)) {
            $errors[] = 'Time is required.';
        }
        if (empty($venue)) {
            $errors[] = 'Venue location is required.';
        }
        if ($totalSeats <= 0) {
            $errors[] = 'Total seats must be greater than zero.';
        }
        if ($availableSeats < 0 || $availableSeats > $totalSeats) {
            $errors[] = 'Available seats cannot exceed total seats or be negative.';
        }

        $imageFilename = $event['event_image'];
        // Check if new image is uploaded
        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmp  = $_FILES['event_image']['tmp_name'];
            $fileName = $_FILES['event_image']['name'];
            $fileSize = $_FILES['event_image']['size'];
            $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($fileExt, $allowedExtensions)) {
                $errors[] = 'Allowed image extensions: JPG, JPEG, PNG, WEBP.';
            } elseif ($fileSize > 3 * 1024 * 1024) {
                $errors[] = 'Image exceeds 3MB limit.';
            } else {
                $newFilename = 'event_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                $targetDir = __DIR__ . '/../uploads/events/';
                if (move_uploaded_file($fileTmp, $targetDir . $newFilename)) {
                    $imageFilename = $newFilename;
                } else {
                    $errors[] = 'Failed to upload new image file.';
                }
            }
        }

        if (empty($errors)) {
            try {
                $updStmt = $pdo->prepare("
                    UPDATE events
                    SET event_name = ?, description = ?, category = ?, event_date = ?, 
                        event_time = ?, venue = ?, total_seats = ?, available_seats = ?, event_image = ?
                    WHERE event_id = ?
                ");
                $updStmt->execute([
                    $eventName,
                    $description,
                    $category,
                    $eventDate,
                    $eventTime,
                    $venue,
                    $totalSeats,
                    $availableSeats,
                    $imageFilename,
                    $eventId
                ]);

                setFlash('success', 'Event "' . $eventName . '" updated successfully!');
                header('Location: manage_events.php');
                exit();
            } catch (Exception $e) {
                $errors[] = 'Database update error: ' . $e->getMessage();
            }
        }
    }
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

    <!-- Main Content Area -->
    <div class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="topbar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="topbar-title">Edit Event: <?= e($event['event_name']); ?></h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="manage_events.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Events
                </a>
            </div>
        </header>

        <main class="dashboard-content">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger shadow-sm mb-4">
                    <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Error updating event:</h6>
                    <ul class="mb-0 ps-3 small">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <div class="content-card-header">
                    <h3 class="content-card-title">
                        <i class="bi bi-pencil-square text-primary"></i> Update Event Information
                    </h3>
                    <span class="badge bg-light text-muted border font-monospace">Event ID: #<?= $event['event_id']; ?></span>
                </div>
                <div class="content-card-body">
                    <form method="POST" action="edit_event.php?id=<?= $eventId; ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="event_name" class="form-label">Event Name / Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="event_name" name="event_name" 
                                       value="<?= e($_POST['event_name'] ?? $event['event_name']); ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="category" class="form-label">Event Category <span class="text-danger">*</span></label>
                                <?php $catVal = $_POST['category'] ?? $event['category']; ?>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="Workshops" <?= $catVal === 'Workshops' ? 'selected' : ''; ?>>Workshops</option>
                                    <option value="Technical Events" <?= $catVal === 'Technical Events' ? 'selected' : ''; ?>>Technical Events</option>
                                    <option value="Competitions" <?= $catVal === 'Competitions' ? 'selected' : ''; ?>>Competitions</option>
                                    <option value="Seminars" <?= $catVal === 'Seminars' ? 'selected' : ''; ?>>Seminars</option>
                                    <option value="Cultural Events" <?= $catVal === 'Cultural Events' ? 'selected' : ''; ?>>Cultural Events</option>
                                    <option value="Sports Events" <?= $catVal === 'Sports Events' ? 'selected' : ''; ?>>Sports Events</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Event Description & Agenda <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?= e($_POST['description'] ?? $event['description']); ?></textarea>
                            </div>

                            <div class="col-md-4">
                                <label for="event_date" class="form-label">Event Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="event_date" name="event_date" 
                                       value="<?= e($_POST['event_date'] ?? $event['event_date']); ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="event_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="event_time" name="event_time" 
                                       value="<?= e($_POST['event_time'] ?? substr($event['event_time'], 0, 5)); ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="venue" class="form-label">Venue Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="venue" name="venue" 
                                       value="<?= e($_POST['venue'] ?? $event['venue']); ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="total_seats" class="form-label">Total Seating Capacity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="total_seats" name="total_seats" 
                                       min="1" max="1000" value="<?= e($_POST['total_seats'] ?? $event['total_seats']); ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="available_seats" class="form-label">Available Seats <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="available_seats" name="available_seats" 
                                       min="0" max="1000" value="<?= e($_POST['available_seats'] ?? $event['available_seats']); ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="event_image" class="form-label">Replace Image (Optional)</label>
                                <input type="file" class="form-control" id="event_image" name="event_image" accept="image/*">
                                <div class="form-text small">Current: <?= e($event['event_image'] ?? 'Default'); ?></div>
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-save me-1"></i> Save Changes
                                </button>
                                <a href="manage_events.php" class="btn btn-outline-secondary px-4">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>

