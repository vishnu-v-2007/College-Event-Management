<?php
/**
 * College Event Management System
 * Admin Manage Contact Inquiries (admin/contact_messages.php)
 */

$pageTitle = 'Contact Inquiries - Admin Portal';
$isDashboard = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

requireAdminLogin('login.php');
$admin = getLoggedInAdmin();
$pdo = getDBConnection();

// Handle Status Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Security token invalid.');
        header('Location: contact_messages.php');
        exit();
    }

    $msgId = (int)($_POST['message_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    if ($msgId > 0 && in_array($status, ['Unread', 'Read', 'Replied'])) {
        try {
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE message_id = ?");
            $stmt->execute([$status, $msgId]);
            setFlash('success', 'Message status updated to "' . $status . '".');
        } catch (Exception $e) {
            setFlash('danger', 'Error updating status: ' . $e->getMessage());
        }
    }
    header('Location: contact_messages.php');
    exit();
}

// Handle Delete Message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_message') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Security token invalid.');
        header('Location: contact_messages.php');
        exit();
    }

    $msgId = (int)($_POST['message_id'] ?? 0);
    if ($msgId > 0) {
        try {
            $del = $pdo->prepare("DELETE FROM contact_messages WHERE message_id = ?");
            $del->execute([$msgId]);
            setFlash('success', 'Message deleted successfully.');
        } catch (Exception $e) {
            setFlash('danger', 'Error deleting message: ' . $e->getMessage());
        }
    }
    header('Location: contact_messages.php');
    exit();
}

// Fetch all messages
$messages = [];
try {
    $messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
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
                <a class="nav-link" href="manage_students.php">
                    <i class="bi bi-people"></i> Manage Students
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="contact_messages.php">
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
                <h1 class="topbar-title">Student & Public Inquiries</h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary fs-6 px-3 py-2">Total Inquiries: <?= count($messages); ?></span>
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
                        <i class="bi bi-inbox-fill text-primary"></i> Support Inquiries Inbox
                    </h3>
                </div>
                <div class="content-card-body p-0">
                    <?php if (!empty($messages)): ?>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Sender Name & Email</th>
                                        <th>Subject</th>
                                        <th>Message Preview</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $msg): ?>
                                        <tr id="msg-<?= $msg['message_id']; ?>">
                                            <td>
                                                <small class="text-muted"><?= formatDate($msg['created_at']); ?></small>
                                            </td>
                                            <td>
                                                <strong><?= e($msg['name']); ?></strong><br>
                                                <a href="mailto:<?= e($msg['email']); ?>" class="small text-muted text-decoration-none">
                                                    <i class="bi bi-envelope me-1"></i> <?= e($msg['email']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <strong class="text-dark"><?= e($msg['subject']); ?></strong>
                                            </td>
                                            <td>
                                                <div class="small text-muted text-truncate" style="max-width: 280px;">
                                                    <?= e($msg['message']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-pill <?= strtolower($msg['status']); ?>">
                                                    <?= e($msg['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <!-- View Message Modal Trigger -->
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" data-bs-target="#msgModal<?= $msg['message_id']; ?>" title="Read Full Message">
                                                        <i class="bi bi-eye"></i>
                                                    </button>

                                                    <!-- Delete Message Form -->
                                                    <form method="POST" action="contact_messages.php" class="d-inline">
                                                        <input type="hidden" name="action" value="delete_message">
                                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
                                                        <input type="hidden" name="message_id" value="<?= $msg['message_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                data-confirm="Are you sure you want to delete this inquiry?" title="Delete Message">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- Message Detail Modal -->
                                                <div class="modal fade" id="msgModal<?= $msg['message_id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                                            <div class="modal-header bg-primary text-white border-0 py-3">
                                                                <h5 class="modal-title fw-bold">
                                                                    <i class="bi bi-envelope-open me-2"></i> Inquiry Details
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <div class="mb-3">
                                                                    <h6 class="fw-bold text-dark mb-1"><?= e($msg['subject']); ?></h6>
                                                                    <small class="text-muted">
                                                                        From: <strong><?= e($msg['name']); ?></strong> (<?= e($msg['email']); ?>) on <?= formatDate($msg['created_at']); ?>
                                                                    </small>
                                                                </div>
                                                                <div class="p-3 bg-light rounded-3 border mb-3">
                                                                    <p class="mb-0 text-dark small" style="white-space: pre-wrap;"><?= e($msg['message']); ?></p>
                                                                </div>

                                                                <form method="POST" action="contact_messages.php" class="d-flex align-items-center gap-2">
                                                                    <input type="hidden" name="action" value="update_status">
                                                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
                                                                    <input type="hidden" name="message_id" value="<?= $msg['message_id']; ?>">
                                                                    <label class="form-label mb-0 small fw-bold">Update Status:</label>
                                                                    <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                                                        <option value="Unread" <?= $msg['status'] === 'Unread' ? 'selected' : ''; ?>>Unread</option>
                                                                        <option value="Read" <?= $msg['status'] === 'Read' ? 'selected' : ''; ?>>Read</option>
                                                                        <option value="Replied" <?= $msg['status'] === 'Replied' ? 'selected' : ''; ?>>Replied</option>
                                                                    </select>
                                                                </form>
                                                            </div>
                                                            <div class="modal-footer border-0 bg-light py-2 d-flex justify-content-between">
                                                                <a href="mailto:<?= e($msg['email']); ?>?subject=Re: <?= urlencode($msg['subject']); ?>" class="btn btn-sm btn-primary">
                                                                    <i class="bi bi-reply-fill me-1"></i> Reply via Email
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
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
                            <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                            <h5>No inquiries found</h5>
                            <p class="text-muted small">The support messages inbox is empty.</p>
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

