<?php
/**
 * College Event Management System
 * Student Registration (register.php)
 */

$pageTitle = 'Student Registration - Create Your Account';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// If already logged in, redirect to dashboard
if (isStudentLoggedIn()) {
    header('Location: student/dashboard.php');
    exit();
}

$errors = [];
$formData = [
    'student_id' => '',
    'name'       => '',
    'email'      => '',
    'phone'      => '',
    'department' => '',
    'course'     => '',
    'year'       => '3rd Year'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid or expired. Please submit again.';
    }

    $formData['student_id'] = strtoupper(trim($_POST['student_id'] ?? ''));
    $formData['name']       = trim($_POST['name'] ?? '');
    $formData['email']      = strtolower(trim($_POST['email'] ?? ''));
    $formData['phone']      = trim($_POST['phone'] ?? '');
    $formData['department'] = trim($_POST['department'] ?? '');
    $formData['course']     = trim($_POST['course'] ?? '');
    $formData['year']       = trim($_POST['year'] ?? '');
    $password               = $_POST['password'] ?? '';
    $confirmPassword        = $_POST['confirm_password'] ?? '';

    // Field Validations
    if (empty($formData['student_id'])) {
        $errors[] = 'Student ID / Roll Number is required.';
    } elseif (strlen($formData['student_id']) < 4 || strlen($formData['student_id']) > 30) {
        $errors[] = 'Student ID must be between 4 and 30 characters.';
    }

    if (empty($formData['name'])) {
        $errors[] = 'Full name is required.';
    } elseif (strlen($formData['name']) < 3) {
        $errors[] = 'Full name must contain at least 3 characters.';
    }

    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid college or personal email address is required.';
    }

    if (empty($formData['phone']) || !preg_match('/^[0-9+\-\s]{8,15}$/', $formData['phone'])) {
        $errors[] = 'A valid phone number (8-15 digits) is required.';
    }

    if (empty($formData['department'])) {
        $errors[] = 'Department is required.';
    }

    if (empty($formData['course'])) {
        $errors[] = 'Course / Degree is required.';
    }

    if (empty($formData['year'])) {
        $errors[] = 'Year of study is required.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    // Database duplicate check
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();

            // Check duplicate email
            $checkEmail = $pdo->prepare("SELECT student_id FROM students WHERE email = ?");
            $checkEmail->execute([$formData['email']]);
            if ($checkEmail->fetch()) {
                $errors[] = 'An account with email address "' . htmlspecialchars($formData['email']) . '" is already registered.';
            }

            // Check duplicate student_id
            $checkId = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ?");
            $checkId->execute([$formData['student_id']]);
            if ($checkId->fetch()) {
                $errors[] = 'Student ID "' . htmlspecialchars($formData['student_id']) . '" is already registered.';
            }

            // If no duplicates, insert student
            if (empty($errors)) {
                $hashedPassword = hashPassword($password);
                $insStmt = $pdo->prepare("
                    INSERT INTO students (student_id, name, email, phone, department, course, year, password)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $insStmt->execute([
                    $formData['student_id'],
                    $formData['name'],
                    $formData['email'],
                    $formData['phone'],
                    $formData['department'],
                    $formData['course'],
                    $formData['year'],
                    $hashedPassword
                ]);

                setFlash('success', 'Registration successful! You can now log in with your email and password.');
                header('Location: login.php?registered=1&email=' . urlencode($formData['email']));
                exit();
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<div class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="auth-card">
                    <div class="auth-header">
                        <i class="bi bi-person-plus-fill display-5 mb-2 d-inline-block text-info"></i>
                        <h2 class="h3 fw-bold text-white mb-1">Student Registration</h2>
                        <p class="text-light opacity-90 small mb-0">Create your official account to register for collegiate events</p>
                    </div>

                    <div class="auth-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger shadow-sm">
                                <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-octagon-fill me-1"></i> Please fix the following errors:</h6>
                                <ul class="mb-0 ps-3 small">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= e($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="register.php" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">

                            <div class="row g-3">
                                <!-- Student ID -->
                                <div class="col-md-6">
                                    <label for="student_id" class="form-label">Student ID / Roll No. <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-card-heading"></i></span>
                                        <input type="text" class="form-control" id="student_id" name="student_id" 
                                               placeholder="e.g. BCA2026011" value="<?= e($formData['student_id']); ?>" required>
                                    </div>
                                    <div class="form-text small">Official university registration number</div>
                                </div>

                                <!-- Full Name -->
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               placeholder="e.g. Rohan Verma" value="<?= e($formData['name']); ?>" required>
                                    </div>
                                </div>

                                <!-- Email Address -->
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="student@college.edu" value="<?= e($formData['email']); ?>" required>
                                    </div>
                                </div>

                                <!-- Phone Number -->
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               placeholder="e.g. 9876543210" value="<?= e($formData['phone']); ?>" required>
                                    </div>
                                </div>

                                <!-- Department -->
                                <div class="col-md-4">
                                    <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                    <select class="form-select" id="department" name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="Computer Applications" <?= $formData['department'] === 'Computer Applications' ? 'selected' : ''; ?>>Computer Applications</option>
                                        <option value="Computer Science & Engineering" <?= $formData['department'] === 'Computer Science & Engineering' ? 'selected' : ''; ?>>Computer Science & Engg</option>
                                        <option value="Information Technology" <?= $formData['department'] === 'Information Technology' ? 'selected' : ''; ?>>Information Technology</option>
                                        <option value="Data Science & AI" <?= $formData['department'] === 'Data Science & AI' ? 'selected' : ''; ?>>Data Science & AI</option>
                                        <option value="Electronics & Communication" <?= $formData['department'] === 'Electronics & Communication' ? 'selected' : ''; ?>>Electronics & Communication</option>
                                        <option value="Management Studies" <?= $formData['department'] === 'Management Studies' ? 'selected' : ''; ?>>Management Studies</option>
                                    </select>
                                </div>

                                <!-- Course -->
                                <div class="col-md-4">
                                    <label for="course" class="form-label">Course / Degree <span class="text-danger">*</span></label>
                                    <select class="form-select" id="course" name="course" required>
                                        <option value="">Select Course</option>
                                        <option value="BCA (Data Science)" <?= $formData['course'] === 'BCA (Data Science)' ? 'selected' : ''; ?>>BCA (Data Science)</option>
                                        <option value="BCA (General)" <?= $formData['course'] === 'BCA (General)' ? 'selected' : ''; ?>>BCA (General)</option>
                                        <option value="B.Tech CSE" <?= $formData['course'] === 'B.Tech CSE' ? 'selected' : ''; ?>>B.Tech CSE</option>
                                        <option value="B.Sc IT" <?= $formData['course'] === 'B.Sc IT' ? 'selected' : ''; ?>>B.Sc IT</option>
                                        <option value="B.Sc Data Science" <?= $formData['course'] === 'B.Sc Data Science' ? 'selected' : ''; ?>>B.Sc Data Science</option>
                                        <option value="MCA" <?= $formData['course'] === 'MCA' ? 'selected' : ''; ?>>MCA</option>
                                        <option value="BBA" <?= $formData['course'] === 'BBA' ? 'selected' : ''; ?>>BBA</option>
                                    </select>
                                </div>

                                <!-- Year of Study -->
                                <div class="col-md-4">
                                    <label for="year" class="form-label">Year of Study <span class="text-danger">*</span></label>
                                    <select class="form-select" id="year" name="year" required>
                                        <option value="1st Year" <?= $formData['year'] === '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                                        <option value="2nd Year" <?= $formData['year'] === '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                                        <option value="3rd Year" <?= $formData['year'] === '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                                        <option value="4th Year" <?= $formData['year'] === '4th Year' ? 'selected' : ''; ?>>4th Year</option>
                                    </select>
                                </div>

                                <!-- Password -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Min. 6 characters" required minlength="6">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text small">Must be at least 6 characters</div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               placeholder="Re-enter password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold shadow-sm">
                                    <i class="bi bi-check-circle me-1"></i> Register Student Account
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="text-muted small mb-0">
                                Already have an account? <a href="login.php" class="fw-bold text-primary">Login Here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

