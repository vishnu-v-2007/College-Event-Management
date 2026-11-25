<?php
/**
 * College Event Management System
 * Administrator Login (admin/login.php)
 */

$pageTitle = 'Administrator Login - Staff Portal';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// If already logged in as admin, redirect to admin dashboard
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please submit the form again.';
    } else {
        $usernameOrEmail = trim($_POST['username_or_email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($usernameOrEmail) || empty($password)) {
            $error = 'Please enter administrator username/email and password.';
        } else {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1");
                $stmt->execute([$usernameOrEmail, strtolower($usernameOrEmail)]);
                $admin = $stmt->fetch();

                if ($admin && verifyPassword($password, $admin['password'])) {
                    // Regenerate session and set admin authentication
                    session_regenerate_id(true);
                    $_SESSION['user_type']      = 'admin';
                    $_SESSION['admin_id']       = $admin['admin_id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email']    = $admin['email'];

                    setFlash('success', 'Welcome, Administrator ' . $admin['username'] . '!');
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid administrator credentials. Access restricted.';
                }
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="auth-card">
                    <div class="auth-header" style="background: linear-gradient(135deg, #0b1e3f 0%, #1e293b 100%);">
                        <i class="bi bi-shield-lock-fill display-5 mb-2 d-inline-block text-warning"></i>
                        <h2 class="h3 fw-bold text-white mb-1">Administrator Portal</h2>
                        <p class="text-light opacity-90 small mb-0">Authorized Event Committee Personnel Only</p>
                    </div>

                    <div class="auth-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger d-flex align-items-center shadow-sm" role="alert">
                                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                                <div class="small"><?= e($error); ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Demo Credentials Box for Evaluation -->
                        <div class="alert alert-warning border-0 p-3 rounded-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold small text-dark"><i class="bi bi-key-fill me-1"></i> Demo Admin Account:</span>
                                <button type="button" class="btn btn-xs btn-dark py-0 px-2 small" id="btnFillDemoAdmin" style="font-size:0.75rem;">
                                    Auto-Fill
                                </button>
                            </div>
                            <div class="small text-muted">
                                <strong>Username:</strong> <code id="demoAdminUser">admin</code><br>
                                <strong>Password:</strong> <code id="demoAdminPass">Admin@123</code>
                            </div>
                        </div>

                        <form method="POST" action="login.php" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">

                            <div class="mb-3">
                                <label for="username_or_email" class="form-label">Username or Admin Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill-lock"></i></span>
                                    <input type="text" class="form-control" id="username_or_email" name="username_or_email" 
                                           placeholder="admin or admin@college.edu" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="admin_password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="admin_password" name="password" 
                                           placeholder="Enter admin password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="admin_password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark w-100 py-2 fs-6 fw-bold shadow-sm" style="background:#0f2b5c;">
                                <i class="bi bi-shield-check me-1 text-warning"></i> Sign In to Admin Console
                            </button>
                        </form>

                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="text-muted small mb-0">
                                Looking for student login? <a href="../login.php" class="fw-bold text-primary">Student Portal</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btnFillDemoAdmin')?.addEventListener('click', function() {
    document.getElementById('username_or_email').value = 'admin';
    document.getElementById('admin_password').value = 'Admin@123';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

