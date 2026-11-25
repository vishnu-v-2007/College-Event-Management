<?php
/**
 * College Event Management System
 * Student Login (login.php)
 */

$pageTitle = 'Student Login - Access Portal';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// If already logged in, redirect
if (isStudentLoggedIn()) {
    header('Location: student/dashboard.php');
    exit();
}

$error = '';
$redirectUrl = $_GET['redirect'] ?? 'student/dashboard.php';
$emailValue = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please try logging in again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $redirectUrl = $_POST['redirect'] ?? 'student/dashboard.php';

        if (empty($email) || empty($password)) {
            $error = 'Please enter both your registered email and password.';
        } else {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $student = $stmt->fetch();

                if ($student && verifyPassword($password, $student['password'])) {
                    // Password verified! Create student session
                    session_regenerate_id(true);
                    $_SESSION['user_type']      = 'student';
                    $_SESSION['student_id']     = $student['student_id'];
                    $_SESSION['student_name']   = $student['name'];
                    $_SESSION['student_email']  = $student['email'];
                    $_SESSION['student_dept']   = $student['department'];
                    $_SESSION['student_course'] = $student['course'];
                    $_SESSION['student_year']   = $student['year'];

                    setFlash('success', 'Welcome back, ' . $student['name'] . '!');
                    header('Location: ' . $redirectUrl);
                    exit();
                } else {
                    $error = 'Invalid email address or password. Please verify your credentials.';
                }
            } catch (Exception $e) {
                $error = 'Authentication error: ' . $e->getMessage();
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
                    <div class="auth-header">
                        <i class="bi bi-person-circle display-5 mb-2 d-inline-block text-info"></i>
                        <h2 class="h3 fw-bold text-white mb-1">Student Portal Login</h2>
                        <p class="text-light opacity-90 small mb-0">Sign in to manage registrations and view passes</p>
                    </div>

                    <div class="auth-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger d-flex align-items-center shadow-sm" role="alert">
                                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                                <div class="small"><?= e($error); ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Demo Credentials Box -->
                        <div class="alert alert-info border-0 p-3 rounded-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold small text-primary"><i class="bi bi-lightbulb-fill me-1"></i> Demo Student Account:</span>
                                <button type="button" class="btn btn-xs btn-primary py-0 px-2 small" id="btnFillDemoStudent" style="font-size:0.75rem;">
                                    Auto-Fill
                                </button>
                            </div>
                            <div class="small text-muted">
                                <strong>Email:</strong> <code id="demoEmail">aarav.sharma@student.edu</code><br>
                                <strong>Password:</strong> <code id="demoPass">Student@123</code>
                            </div>
                        </div>

                        <form method="POST" action="login.php" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
                            <input type="hidden" name="redirect" value="<?= e($redirectUrl); ?>">

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="student@college.edu" value="<?= e($emailValue); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="password" class="form-label mb-0">Password <span class="text-danger">*</span></label>
                                </div>
                                <div class="input-group mt-1">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter your password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe">
                                <label class="form-check-label small text-muted" for="rememberMe">Remember my login</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Student Portal
                            </button>
                        </form>

                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="text-muted small mb-2">
                                New student? <a href="register.php" class="fw-bold text-primary">Register Here</a>
                            </p>
                            <p class="text-muted small mb-0">
                                College Staff or Admin? <a href="admin/login.php" class="text-secondary">Administrator Login</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btnFillDemoStudent')?.addEventListener('click', function() {
    document.getElementById('email').value = 'aarav.sharma@student.edu';
    document.getElementById('password').value = 'Student@123';
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

