<?php
/**
 * College Event Management System
 * Admin Add New Event (admin/add_event.php)
 */

$pageTitle = 'Add New Event - Admin Portal';
$isDashboard = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

requireAdminLogin('login.php');
$admin = getLoggedInAdmin();
$pdo = getDBConnection();

$errors = [];
$formData = [
    'event_name'      => '',
    'description'     => '',
    'category'        => 'Workshops',
    'event_date'      => date('Y-m-d', strtotime('+7 days')),
    'event_time'      => '10:00',
    'venue'           => '',
    'total_seats'     => 60,
    'available_seats' => 60
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please try again.';
    } else {
        $formData['event_name']      = trim($_POST['event_name'] ?? '');
        $formData['description']     = trim($_POST['description'] ?? '');
        $formData['category']        = trim($_POST['category'] ?? '');
        $formData['event_date']      = trim($_POST['event_date'] ?? '');
        $formData['event_time']      = trim($_POST['event_time'] ?? '');
        $formData['venue']           = trim($_POST['venue'] ?? '');
        $formData['total_seats']     = (int)($_POST['total_seats'] ?? 60);
        $formData['available_seats'] = (int)($_POST['available_seats'] ?? $formData['total_seats']);

        if (empty($formData['event_name']) || strlen($formData['event_name']) < 3) {
            $errors[] = 'Event title must be at least 3 characters.';
        }
        if (empty($formData['description']) || strlen($formData['description']) < 10) {
            $errors[] = 'Description must be at least 10 characters.';
        }
        if (empty($formData['category'])) {
            $errors[] = 'Please select an event category.';
        }
        if (empty($formData['event_date'])) {
            $errors[] = 'Please specify the event date.';
        }
        if (empty($formData['event_time'])) {
            $errors[] = 'Please specify the event start time.';
        }
        if (empty($formData['venue'])) {
            $errors[] = 'Venue location is required.';
        }
        if ($formData['total_seats'] <= 0) {
            $errors[] = 'Total seats must be greater than zero.';
        }
        if ($formData['available_seats'] < 0 || $formData['available_seats'] > $formData['total_seats']) {
            $formData['available_seats'] = $formData['total_seats'];
        }

        // Handle Event Image Upload
        $imageFilename = 'web_dev_workshop.jpg'; // default fallback
        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmp  = $_FILES['event_image']['tmp_name'];
            $fileName = $_FILES['event_image']['name'];
            $fileSize = $_FILES['event_image']['size'];
            $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($fileExt, $allowedExtensions)) {
                $errors[] = 'Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.';
            } elseif ($fileSize > 3 * 1024 * 1024) {
                $errors[] = 'Image size exceeds maximum limit of 3MB.';
            } else {
                $newFilename = 'event_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                $targetDir = __DIR__ . '/../uploads/events/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                if (move_uploaded_file($fileTmp, $targetDir . $newFilename)) {
                    $imageFilename = $newFilename;
                } else {
                    $errors[] = 'Failed to upload event image. Check directory permissions.';
                }
            }
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO events (event_name, description, category, event_date, event_time, venue, total_seats, available_seats, event_image)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $formData['event_name'],
                    $formData['description'],
                    $formData['category'],
                    $formData['event_date'],
                    $formData['event_time'],
                    $formData['venue'],
                    $formData['total_seats'],
                    $formData['available_seats'],
                    $imageFilename
                ]);

                setFlash('success', 'Event "' . $formData['event_name'] . '" published successfully!');
                header('Location: manage_events.php');
                exit();
            } catch (Exception $e) {
                $errors[] = 'Database insertion error: ' . $e->getMessage();
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
                <a class="nav-link" href="manage_events.php">
                    <i class="bi bi-calendar3"></i> Manage Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="add_event.php">
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
                <h1 class="topbar-title">Create New College Event</h1>
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
                    <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please resolve the following errors:</h6>
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
                        <i class="bi bi-plus-square text-primary"></i> Event Details Form
                    </h3>
                </div>
                <div class="content-card-body">
                    <form method="POST" action="add_event.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">

                        <div class="row g-3">
                            <!-- Event Name -->
                            <div class="col-md-8">
                                <label for="event_name" class="form-label">Event Name / Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="event_name" name="event_name" 
                                       placeholder="e.g. Artificial Intelligence Masterclass 2026" 
                                       value="<?= e($formData['event_name']); ?>" required>
                            </div>

                            <!-- Category -->
                            <div class="col-md-4">
                                <label for="category" class="form-label">Event Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="Workshops" <?= $formData['category'] === 'Workshops' ? 'selected' : ''; ?>>Workshops</option>
                                    <option value="Technical Events" <?= $formData['category'] === 'Technical Events' ? 'selected' : ''; ?>>Technical Events</option>
                                    <option value="Competitions" <?= $formData['category'] === 'Competitions' ? 'selected' : ''; ?>>Competitions</option>
                                    <option value="Seminars" <?= $formData['category'] === 'Seminars' ? 'selected' : ''; ?>>Seminars</option>
                                    <option value="Cultural Events" <?= $formData['category'] === 'Cultural Events' ? 'selected' : ''; ?>>Cultural Events</option>
                                    <option value="Sports Events" <?= $formData['category'] === 'Sports Events' ? 'selected' : ''; ?>>Sports Events</option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label">Event Description & Agenda <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="4" 
                                          placeholder="Provide a thorough overview of the event, learning outcomes, and requirements..." required><?= e($formData['description']); ?></textarea>
                            </div>

                            <!-- Date -->
                            <div class="col-md-4">
                                <label for="event_date" class="form-label">Event Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="event_date" name="event_date" 
                                       value="<?= e($formData['event_date']); ?>" required>
                            </div>

                            <!-- Time -->
                            <div class="col-md-4">
                                <label for="event_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="event_time" name="event_time" 
                                       value="<?= e($formData['event_time']); ?>" required>
                            </div>

                            <!-- Venue -->
                            <div class="col-md-4">
                                <label for="venue" class="form-label">Venue Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="venue" name="venue" 
                                       placeholder="e.g. Lab 4, Tech Block or Auditorium" 
                                       value="<?= e($formData['venue']); ?>" required>
                            </div>

                            <!-- Total Seats -->
                            <div class="col-md-4">
                                <label for="total_seats" class="form-label">Total Seating Capacity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="total_seats" name="total_seats" 
                                       min="1" max="1000" value="<?= e($formData['total_seats']); ?>" required>
                            </div>

                            <!-- Available Seats -->
                            <div class="col-md-4">
                                <label for="available_seats" class="form-label">Available Seats <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="available_seats" name="available_seats" 
                                       min="0" max="1000" value="<?= e($formData['available_seats']); ?>" required>
                                <div class="form-text small">Typically equal to Total Seats for new events</div>
                            </div>

                            <!-- Image Upload -->
                            <div class="col-md-4">
                                <label for="event_image" class="form-label">Banner Image (JPG/PNG/WEBP)</label>
                                <input type="file" class="form-control" id="event_image" name="event_image" accept="image/*">
                                <div class="form-text small">Max 3MB (Optional: default banner applied)</div>
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-cloud-arrow-up me-1"></i> Publish Event
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

