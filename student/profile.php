<?php
/**
 * College Event Management System
 * Student Profile Management (student/profile.php)
 */

$pageTitle = 'My Profile';
$isDashboard = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

requireStudentLogin('../login.php');
$sessionStudent = getLoggedInStudent();
$pdo = getDBConnection();

$errors = [];
$successMsg = '';

// Handle Profile Details Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please try again.';
    } else {
        $name       = trim($_POST['name'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $course     = trim($_POST['course'] ?? '');
        $year       = trim($_POST['year'] ?? '');

        if (empty($name) || strlen($name) < 3) {
            $errors[] = 'Full name must be at least 3 characters.';
        }
        if (empty($phone) || !preg_match('/^[0-9+\-\s]{8,15}$/', $phone)) {
            $errors[] = 'Please enter a valid contact phone number.';
        }
        if (empty($department)) {
            $errors[] = 'Department cannot be empty.';
        }
        if (empty($course)) {
            $errors[] = 'Course cannot be empty.';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE students 
                    SET name = ?, phone = ?, department = ?, course = ?, year = ? 
                    WHERE student_id = ?
                ");
                $stmt->execute([$name, $phone, $department, $course, $year, $sessionStudent['student_id']]);

                // Update session variables
                $_SESSION['student_name']   = $name;
                $_SESSION['student_dept']   = $department;
                $_SESSION['student_course'] = $course;
                $_SESSION['student_year']   = $year;

                $successMsg = 'Profile information updated successfully!';
            } catch (Exception $e) {
                $errors[] = 'Update error: ' . $e->getMessage();
            }
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please try again.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword)) {
            $errors[] = 'Current password is required.';
        }
        if (empty($newPassword) || strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New password confirmation does not match.';
        }

        if (empty($errors)) {
            try {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password FROM students WHERE student_id = ?");
                $stmt->execute([$sessionStudent['student_id']]);
                $currHash = $stmt->fetchColumn();

                if ($currHash && verifyPassword($currentPassword, $currHash)) {
                    $newHash = hashPassword($newPassword);
                    $upd = $pdo->prepare("UPDATE students SET password = ? WHERE student_id = ?");
                    $upd->execute([$newHash, $sessionStudent['student_id']]);
                    $successMsg = 'Password changed successfully!';
                } else {
                    $errors[] = 'Incorrect current password provided.';
                }
            } catch (Exception $e) {
                $errors[] = 'Password update error: ' . $e->getMessage();
            }
        }
    }
}

// Fetch fresh student record
$student = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$sessionStudent['student_id']]);
    $student = $stmt->fetch() ?: $sessionStudent;
} catch (Exception $e) {
    $student = $sessionStudent;
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
                <?= strtoupper(substr($student['name'] ?? 'S', 0, 1)); ?>
            </div>
            <div class="user-info text-truncate" style="max-width: 160px;">
                <div class="name text-truncate"><?= e($student['name']); ?></div>
                <div class="role text-truncate"><?= e($student['student_id']); ?></div>
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
                <a class="nav-link" href="my_events.php">
                    <i class="bi bi-ticket-perforated"></i> My Registrations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../events.php">
                    <i class="bi bi-calendar-event"></i> Browse All Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="profile.php">
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
                <h1 class="topbar-title">Student Profile Settings</h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </header>

        <main class="dashboard-content">
            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= e($successMsg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger shadow-sm mb-4">
                    <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Attention:</h6>
                    <ul class="mb-0 ps-3 small">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Profile Details Form -->
                <div class="col-lg-8">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3 class="content-card-title">
                                <i class="bi bi-person-lines-fill text-primary"></i> Edit Personal & Academic Details
                            </h3>
                        </div>
                        <div class="content-card-body">
                            <form method="POST" action="profile.php" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="update_profile">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Student ID / Roll No.</label>
                                        <input type="text" class="form-control bg-light" value="<?= e($student['student_id']); ?>" readonly disabled>
                                        <div class="form-text small">Student ID cannot be modified once registered</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control bg-light" value="<?= e($student['email']); ?>" readonly disabled>
                                        <div class="form-text small">Official login email address</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?= e($student['name']); ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?= e($student['phone']); ?>" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                        <select class="form-select" id="department" name="department" required>
                                            <option value="Computer Applications" <?= $student['department'] === 'Computer Applications' ? 'selected' : ''; ?>>Computer Applications</option>
                                            <option value="Computer Science & Engineering" <?= $student['department'] === 'Computer Science & Engineering' ? 'selected' : ''; ?>>Computer Science & Engg</option>
                                            <option value="Information Technology" <?= $student['department'] === 'Information Technology' ? 'selected' : ''; ?>>Information Technology</option>
                                            <option value="Data Science & AI" <?= $student['department'] === 'Data Science & AI' ? 'selected' : ''; ?>>Data Science & AI</option>
                                            <option value="Electronics & Communication" <?= $student['department'] === 'Electronics & Communication' ? 'selected' : ''; ?>>Electronics & Communication</option>
                                            <option value="Management Studies" <?= $student['department'] === 'Management Studies' ? 'selected' : ''; ?>>Management Studies</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="course" class="form-label">Course <span class="text-danger">*</span></label>
                                        <select class="form-select" id="course" name="course" required>
                                            <option value="BCA (Data Science)" <?= $student['course'] === 'BCA (Data Science)' ? 'selected' : ''; ?>>BCA (Data Science)</option>
                                            <option value="BCA (General)" <?= $student['course'] === 'BCA (General)' ? 'selected' : ''; ?>>BCA (General)</option>
                                            <option value="B.Tech CSE" <?= $student['course'] === 'B.Tech CSE' ? 'selected' : ''; ?>>B.Tech CSE</option>
                                            <option value="B.Sc IT" <?= $student['course'] === 'B.Sc IT' ? 'selected' : ''; ?>>B.Sc IT</option>
                                            <option value="B.Sc Data Science" <?= $student['course'] === 'B.Sc Data Science' ? 'selected' : ''; ?>>B.Sc Data Science</option>
                                            <option value="MCA" <?= $student['course'] === 'MCA' ? 'selected' : ''; ?>>MCA</option>
                                            <option value="BBA" <?= $student['course'] === 'BBA' ? 'selected' : ''; ?>>BBA</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="year" class="form-label">Year of Study <span class="text-danger">*</span></label>
                                        <select class="form-select" id="year" name="year" required>
                                            <option value="1st Year" <?= $student['year'] === '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                                            <option value="2nd Year" <?= $student['year'] === '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                                            <option value="3rd Year" <?= $student['year'] === '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                                            <option value="4th Year" <?= $student['year'] === '4th Year' ? 'selected' : ''; ?>>4th Year</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                                            <i class="bi bi-save me-1"></i> Save Profile Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Change Password Form -->
                <div class="col-lg-4">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3 class="content-card-title">
                                <i class="bi bi-shield-lock text-primary"></i> Change Password
                            </h3>
                        </div>
                        <div class="content-card-body">
                            <form method="POST" action="profile.php" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="change_password">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                    <div class="form-text small">At least 6 characters</div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>

                                <button type="submit" class="btn btn-warning w-100 fw-bold text-dark mt-2">
                                    <i class="bi bi-key me-1"></i> Update Password
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border text-center">
                        <small class="text-muted">
                            <i class="bi bi-calendar-check me-1"></i> Member Since: <?= formatDate($student['created_at'] ?? date('Y-m-d')); ?>
                        </small>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>

