<?php
/**
 * College Event Management System
 * Admin Manage Students (admin/manage_students.php)
 */

$pageTitle = 'Manage Students - Admin Portal';
$isDashboard = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

requireAdminLogin('login.php');
$admin = getLoggedInAdmin();
$pdo = getDBConnection();

// Handle Delete Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_student') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: manage_students.php');
        exit();
    }

    $studentId = trim($_POST['student_id'] ?? '');
    if (!empty($studentId)) {
        try {
            // Delete student (cascade deletes registrations)
            $del = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
            $del->execute([$studentId]);

            setFlash('success', 'Student record "' . htmlspecialchars($studentId) . '" deleted successfully.');
        } catch (Exception $e) {
            setFlash('danger', 'Error deleting student: ' . $e->getMessage());
        }
    }
    header('Location: manage_students.php');
    exit();
}

// Search & Filter parameters
$search = trim($_GET['search'] ?? '');
$deptFilter = trim($_GET['department'] ?? '');

$query = "
    SELECT s.*, COUNT(r.registration_id) as total_events_registered
    FROM students s
    LEFT JOIN registrations r ON s.student_id = r.student_id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (s.student_id LIKE ? OR s.name LIKE ? OR s.email LIKE ? OR s.phone LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if (!empty($deptFilter)) {
    $query .= " AND s.department = ?";
    $params[] = $deptFilter;
}

$query .= " GROUP BY s.student_id ORDER BY s.created_at DESC";

$students = [];
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
}

// Fetch all registrations grouped by student for quick modal inspection
$studentRegistrations = [];
try {
    $rstmt = $pdo->query("
        SELECT r.student_id, r.registration_id, r.status, r.registration_date, e.event_name, e.event_date
        FROM registrations r
        JOIN events e ON r.event_id = e.event_id
        ORDER BY r.registration_date DESC
    ");
    while ($row = $rstmt->fetch()) {
        $studentRegistrations[$row['student_id']][] = $row;
    }
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
                <a class="nav-link" href="manage_registrations.php">
                    <i class="bi bi-clipboard-check"></i> Registrations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="manage_students.php">
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
                <h1 class="topbar-title">Manage Registered Students</h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary fs-6 px-3 py-2">Total Students: <?= count($students); ?></span>
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
                    <form method="GET" action="manage_students.php" class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search by name, student ID, email or phone..." value="<?= e($search); ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <select name="department" class="form-select form-select-sm">
                                <option value="">All Departments</option>
                                <option value="Computer Applications" <?= $deptFilter === 'Computer Applications' ? 'selected' : ''; ?>>Computer Applications</option>
                                <option value="Computer Science & Engineering" <?= $deptFilter === 'Computer Science & Engineering' ? 'selected' : ''; ?>>Computer Science & Engg</option>
                                <option value="Information Technology" <?= $deptFilter === 'Information Technology' ? 'selected' : ''; ?>>Information Technology</option>
                                <option value="Data Science & AI" <?= $deptFilter === 'Data Science & AI' ? 'selected' : ''; ?>>Data Science & AI</option>
                                <option value="Electronics & Communication" <?= $deptFilter === 'Electronics & Communication' ? 'selected' : ''; ?>>Electronics & Communication</option>
                                <option value="Management Studies" <?= $deptFilter === 'Management Studies' ? 'selected' : ''; ?>>Management Studies</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm px-3 flex-grow-1">Filter</button>
                            <a href="manage_students.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Students Table -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3 class="content-card-title">
                        <i class="bi bi-people-fill text-primary"></i> Enrolled Students Directory (<?= count($students); ?>)
                    </h3>
                </div>
                <div class="content-card-body p-0">
                    <?php if (!empty($students)): ?>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Full Name & Contact</th>
                                        <th>Department</th>
                                        <th>Course & Year</th>
                                        <th>Registered Events</th>
                                        <th>Enrolled Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $st): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark border font-monospace fs-6">
                                                    <?= e($st['student_id']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= e($st['name']); ?></strong><br>
                                                <small class="text-muted"><i class="bi bi-envelope me-1"></i> <?= e($st['email']); ?></small><br>
                                                <small class="text-muted"><i class="bi bi-telephone me-1"></i> <?= e($st['phone']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= e($st['department']); ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= e($st['course']); ?></div>
                                                <small class="text-muted"><?= e($st['year']); ?></small>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" data-bs-target="#studentModal<?= preg_replace('/[^a-zA-Z0-9]/', '', $st['student_id']); ?>">
                                                    <i class="bi bi-ticket-perforated me-1"></i> <?= $st['total_events_registered']; ?> Events
                                                </button>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= formatDate($st['created_at']); ?></small>
                                            </td>
                                            <td>
                                                <form method="POST" action="manage_students.php" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_student">
                                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
                                                    <input type="hidden" name="student_id" value="<?= e($st['student_id']); ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            data-confirm="Are you sure you want to delete student '<?= e($st['name']); ?>' (<?= e($st['student_id']); ?>)? All their event registrations will be removed." title="Delete Student Record">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>

                                                <!-- Student Events History Modal -->
                                                <?php $modalId = preg_replace('/[^a-zA-Z0-9]/', '', $st['student_id']); ?>
                                                <div class="modal fade" id="studentModal<?= $modalId; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                                            <div class="modal-header bg-primary text-white border-0 py-3">
                                                                <h5 class="modal-title fw-bold">
                                                                    <i class="bi bi-person-lines-fill me-2"></i> Student Details & Registration History
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <div class="bg-light p-3 rounded-3 border mb-4">
                                                                    <div class="row g-2 small">
                                                                        <div class="col-md-6"><strong>Name:</strong> <?= e($st['name']); ?></div>
                                                                        <div class="col-md-6"><strong>Student ID:</strong> <code><?= e($st['student_id']); ?></code></div>
                                                                        <div class="col-md-6"><strong>Email:</strong> <?= e($st['email']); ?></div>
                                                                        <div class="col-md-6"><strong>Phone:</strong> <?= e($st['phone']); ?></div>
                                                                        <div class="col-md-6"><strong>Department:</strong> <?= e($st['department']); ?></div>
                                                                        <div class="col-md-6"><strong>Course / Year:</strong> <?= e($st['course']); ?> (<?= e($st['year']); ?>)</div>
                                                                    </div>
                                                                </div>

                                                                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-calendar-event me-1"></i> Registered Events</h6>
                                                                <?php if (!empty($studentRegistrations[$st['student_id']])): ?>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered">
                                                                            <thead class="table-light">
                                                                                <tr>
                                                                                    <th>Reg ID</th>
                                                                                    <th>Event Name</th>
                                                                                    <th>Event Date</th>
                                                                                    <th>Registration Date</th>
                                                                                    <th>Status</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php foreach ($studentRegistrations[$st['student_id']] as $sreg): ?>
                                                                                    <tr>
                                                                                        <td>#REG-<?= str_pad($sreg['registration_id'], 5, '0', STR_PAD_LEFT); ?></td>
                                                                                        <td><strong><?= e($sreg['event_name']); ?></strong></td>
                                                                                        <td><?= formatDate($sreg['event_date']); ?></td>
                                                                                        <td><?= formatDate($sreg['registration_date']); ?></td>
                                                                                        <td>
                                                                                            <span class="status-pill <?= strtolower($sreg['status']); ?>">
                                                                                                <?= e($sreg['status']); ?>
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                <?php endforeach; ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <p class="text-muted small mb-0">This student has not registered for any events yet.</p>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="modal-footer border-0 bg-light py-2">
                                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
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
                        <div class="text-center py-5">
                            <i class="bi bi-people display-4 text-muted d-block mb-3"></i>
                            <h5>No student records found</h5>
                            <p class="text-muted small">No students matched the active search or filter criteria.</p>
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

