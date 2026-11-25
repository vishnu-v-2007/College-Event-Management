<?php
/**
 * College Event Management System
 * Student My Registrations & Digital Passes (student/my_events.php)
 */

$pageTitle = 'My Event Registrations & Passes';
$isDashboard = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

requireStudentLogin('../login.php');
$student = getLoggedInStudent();
$pdo = getDBConnection();

// Handle Registration Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_registration') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Security token invalid. Please try again.');
        header('Location: my_events.php');
        exit();
    }

    $regId = (int)($_POST['registration_id'] ?? 0);
    $eventId = (int)($_POST['event_id'] ?? 0);

    if ($regId > 0 && $eventId > 0) {
        try {
            $pdo->beginTransaction();

            // Verify registration belongs to this student
            $check = $pdo->prepare("SELECT * FROM registrations WHERE registration_id = ? AND student_id = ?");
            $check->execute([$regId, $student['student_id']]);
            if ($check->fetch()) {
                // Delete registration
                $del = $pdo->prepare("DELETE FROM registrations WHERE registration_id = ?");
                $del->execute([$regId]);

                // Increment available seats
                $upd = $pdo->prepare("UPDATE events SET available_seats = available_seats + 1 WHERE event_id = ?");
                $upd->execute([$eventId]);

                $pdo->commit();
                setFlash('success', 'Event registration cancelled successfully. Seat has been released.');
            } else {
                $pdo->rollBack();
                setFlash('danger', 'Invalid registration record.');
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            setFlash('danger', 'Cancellation error: ' . $e->getMessage());
        }
    }
    header('Location: my_events.php');
    exit();
}

// Fetch all registrations of current student
$registrations = [];
try {
    $stmt = $pdo->prepare("
        SELECT r.registration_id, r.registration_date, r.status,
               e.event_id, e.event_name, e.category, e.event_date, e.event_time, e.venue, e.event_image
        FROM registrations r
        JOIN events e ON r.event_id = e.event_id
        WHERE r.student_id = ?
        ORDER BY e.event_date ASC
    ");
    $stmt->execute([$student['student_id']]);
    $registrations = $stmt->fetchAll();
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
}

// Student profile for pass rendering
$currStudent = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student['student_id']]);
    $currStudent = $stmt->fetch() ?: $student;
} catch (Exception $e) {
    $currStudent = $student;
}
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
                <?= strtoupper(substr($currStudent['name'] ?? 'S', 0, 1)); ?>
            </div>
            <div class="user-info text-truncate" style="max-width: 160px;">
                <div class="name text-truncate"><?= e($currStudent['name']); ?></div>
                <div class="role text-truncate"><?= e($currStudent['student_id']); ?></div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-header">Student Menu</li>
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="my_events.php">
                    <i class="bi bi-ticket-perforated"></i> My Registrations
                    <span class="badge bg-primary rounded-pill ms-auto"><?= count($registrations); ?></span>
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

    <!-- Main Content Area -->
    <div class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="topbar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="topbar-title">My Event Registrations</h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="../events.php" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Register More Events
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

            <div class="content-card">
                <div class="content-card-header">
                    <h3 class="content-card-title">
                        <i class="bi bi-ticket-detailed text-primary"></i> All Registered Events (<?= count($registrations); ?>)
                    </h3>
                </div>
                <div class="content-card-body p-0">
                    <?php if (!empty($registrations)): ?>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Registration ID</th>
                                        <th>Event Details</th>
                                        <th>Date & Venue</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registrations as $reg): ?>
                                        <tr id="pass-<?= $reg['registration_id']; ?>">
                                            <td>
                                                <span class="badge bg-light text-dark border font-monospace">
                                                    #REG-<?= str_pad($reg['registration_id'], 5, '0', STR_PAD_LEFT); ?>
                                                </span><br>
                                                <small class="text-muted"><?= formatDate($reg['registration_date']); ?></small>
                                            </td>
                                            <td>
                                                <strong><?= e($reg['event_name']); ?></strong>
                                            </td>
                                            <td>
                                                <div><i class="bi bi-calendar3 me-1 text-primary"></i> <?= formatDate($reg['event_date']); ?></div>
                                                <small class="text-muted"><i class="bi bi-clock me-1"></i> <?= formatTime($reg['event_time']); ?></small><br>
                                                <small class="text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i> <?= e($reg['venue']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= e($reg['category']); ?></span>
                                            </td>
                                            <td>
                                                <span class="status-pill <?= strtolower($reg['status']); ?>">
                                                    <i class="bi bi-circle-fill" style="font-size:0.5rem;"></i>
                                                    <?= e($reg['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <!-- View Pass Modal Trigger -->
                                                    <button type="button" class="btn btn-sm btn-info text-dark fw-semibold" 
                                                            data-bs-toggle="modal" data-bs-target="#ticketModal<?= $reg['registration_id']; ?>">
                                                        <i class="bi bi-qr-code me-1"></i> Pass
                                                    </button>

                                                    <!-- Cancel Registration Form -->
                                                    <form method="POST" action="my_events.php" class="d-inline">
                                                        <input type="hidden" name="action" value="cancel_registration">
                                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
                                                        <input type="hidden" name="registration_id" value="<?= $reg['registration_id']; ?>">
                                                        <input type="hidden" name="event_id" value="<?= $reg['event_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                data-confirm="Are you sure you want to cancel your registration for '<?= e($reg['event_name']); ?>'? Your seat will be freed.">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- Ticket Modal for each Registration -->
                                                <div class="modal fade" id="ticketModal<?= $reg['registration_id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                                            <div class="modal-header bg-primary text-white border-0 py-3">
                                                                <h5 class="modal-title fw-bold">
                                                                    <i class="bi bi-ticket-perforated-fill me-2"></i> Official Digital Event Pass
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4" id="printArea<?= $reg['registration_id']; ?>">
                                                                <div class="event-pass-card">
                                                                    <div class="pass-stamp"><?= strtoupper($reg['status']); ?></div>
                                                                    <div class="text-center mb-3">
                                                                        <h6 class="text-uppercase tracking-wider text-muted small fw-bold mb-1">CEMS &bull; MVJ DEGREE COLLEGE</h6>
                                                                        <h4 class="fw-bold text-primary mb-1"><?= e($reg['event_name']); ?></h4>
                                                                        <span class="badge bg-primary"><?= e($reg['category']); ?></span>
                                                                    </div>
                                                                    <hr class="my-3">
                                                                    <div class="row g-2 small mb-3">
                                                                        <div class="col-6">
                                                                            <strong class="text-muted d-block">Student Name:</strong>
                                                                            <span class="fw-bold text-dark"><?= e($currStudent['name']); ?></span>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <strong class="text-muted d-block">Student ID:</strong>
                                                                            <span class="fw-bold font-monospace"><?= e($currStudent['student_id']); ?></span>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <strong class="text-muted d-block">Date:</strong>
                                                                            <span><?= formatDate($reg['event_date']); ?></span>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <strong class="text-muted d-block">Time:</strong>
                                                                            <span><?= formatTime($reg['event_time']); ?></span>
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <strong class="text-muted d-block">Venue:</strong>
                                                                            <span><?= e($reg['venue']); ?></span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="p-3 bg-white rounded-3 border text-center">
                                                                        <div class="font-monospace text-primary fw-bold fs-6 mb-1">
                                                                            *REG-<?= str_pad($reg['registration_id'], 6, '0', STR_PAD_LEFT); ?>-<?= $reg['event_id']; ?>*
                                                                        </div>
                                                                        <small class="text-muted d-block">Present this digital pass at the entrance desk.</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-0 bg-light py-2">
                                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                                <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="printTicketPass('printArea<?= $reg['registration_id']; ?>')">
                                                                    <i class="bi bi-printer me-1"></i> Print / Save Pass
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 px-3">
                            <i class="bi bi-ticket-perforated display-3 text-muted d-block mb-3"></i>
                            <h5 class="fw-bold">No event registrations found</h5>
                            <p class="text-muted small mb-4">You have not registered for any events yet. Check out the college event schedule!</p>
                            <a href="../events.php" class="btn btn-primary px-4 fw-semibold">
                                <i class="bi bi-compass me-1"></i> Browse Upcoming Events
                            </a>
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

