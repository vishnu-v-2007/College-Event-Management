<?php
/**
 * College Event Management System
 * Contact Us Page (contact.php)
 */

$pageTitle = 'Contact Us - Campus Event Support';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = getDBConnection();
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errorMessage = 'Security validation failed. Please try again.';
    } else {
        $name    = trim($_POST['name'] ?? '');
        $email   = strtolower(trim($_POST['email'] ?? ''));
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || strlen($name) < 2) {
            $errorMessage = 'Please provide your full name.';
        } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Please provide a valid email address.';
        } elseif (empty($subject) || strlen($subject) < 3) {
            $errorMessage = 'Please enter an inquiry subject (at least 3 characters).';
        } elseif (empty($message) || strlen($message) < 10) {
            $errorMessage = 'Please enter a detailed message (at least 10 characters).';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'Unread')");
                $stmt->execute([$name, $email, $subject, $message]);
                $successMessage = 'Thank you, ' . htmlspecialchars($name) . '! Your message has been submitted to the event committee. We will respond to your email shortly.';
            } catch (Exception $e) {
                $errorMessage = 'Unable to send message: ' . $e->getMessage();
            }
        }
    }
}
?>

<!-- Contact Hero -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #091931 0%, #0f2b5c 50%, #173b75 100%);">
    <div class="container py-2 text-center">
        <span class="badge bg-info text-dark px-3 py-2 text-uppercase fw-bold mb-2">Support & Inquiries</span>
        <h1 class="display-5 fw-bold text-white mb-2">Get in Touch With Us</h1>
        <p class="lead text-light opacity-90 max-w-700 mx-auto" style="max-width: 650px;">
            Have questions about event schedules, registration criteria, or volunteering? Reach out to the Event Management Committee.
        </p>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <h3 class="fw-bold text-primary mb-2">Send Us a Message</h3>
                    <p class="text-muted small mb-4">Fill out the form below and our coordination desk will get back to you.</p>

                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success d-flex align-items-center shadow-sm mb-4">
                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                            <div><?= $successMessage; ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert alert-danger d-flex align-items-center shadow-sm mb-4">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                            <div><?= e($errorMessage); ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="contact.php" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           placeholder="e.g. Diya Patel" required
                                           value="<?= isset($_POST['name']) && empty($successMessage) ? e($_POST['name']) : ''; ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="name@student.edu" required
                                           value="<?= isset($_POST['email']) && empty($successMessage) ? e($_POST['email']) : ''; ?>">
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-chat-left-dots"></i></span>
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           placeholder="e.g. Query regarding CodeFest registration deadline" required
                                           value="<?= isset($_POST['subject']) && empty($successMessage) ? e($_POST['subject']) : ''; ?>">
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="message" class="form-label">Your Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="5" 
                                          placeholder="Type your detailed inquiry or feedback here..." required><?= isset($_POST['message']) && empty($successMessage) ? e($_POST['message']) : ''; ?></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                                    <i class="bi bi-send-fill me-2"></i> Submit Inquiry
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Campus Information & Office Details -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                    <h4 class="fw-bold text-primary mb-4">Contact Information</h4>
                    
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary fs-4">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Campus Address</h6>
                            <p class="text-muted small mb-0">
                                Event Coordination Secretariat<br>
                                Tech Innovation Block, MVJ Degree College<br>
                                Near ITPL, Whitefield, Bangalore - 560067
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="p-3 rounded-circle bg-info bg-opacity-10 text-info fs-4">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Helpline & Queries</h6>
                            <p class="text-muted small mb-0">
                                <strong>General:</strong> +91 (080) 2845-4567<br>
                                <strong>Event Cell:</strong> +91 98765 43210
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="p-3 rounded-circle bg-success bg-opacity-10 text-success fs-4">
                            <i class="bi bi-envelope-check-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Email Desk</h6>
                            <p class="text-muted small mb-0">
                                events@college.edu<br>
                                support.cems@college.edu
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="p-3 rounded-circle bg-warning bg-opacity-10 text-warning fs-4">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Office Hours</h6>
                            <p class="text-muted small mb-0">
                                Monday to Saturday: 09:00 AM – 05:00 PM<br>
                                Sunday: Closed (Active on Event Days)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fast FAQs snippet -->
                <div class="p-4 bg-primary text-white rounded-4 shadow-sm">
                    <h5 class="fw-bold mb-2"><i class="bi bi-question-circle me-1"></i> Quick Assistance</h5>
                    <p class="small text-light opacity-90 mb-3">
                        Are you experiencing issues registering for an event? Check if you have created your student account first.
                    </p>
                    <a href="register.php" class="btn btn-outline-light btn-sm fw-bold">Student Registration</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

