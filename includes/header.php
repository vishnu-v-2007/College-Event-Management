<?php
/**
 * College Event Management System
 * Common Page Header
 */

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/database.php';
}
require_once __DIR__ . '/auth.php';

// Determine relative root path for assets and links
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$isSubDir = preg_match('#/(student|admin)$#', $scriptDir);
$root = $isSubDir ? '../' : './';

$pageTitle = $pageTitle ?? 'College Event Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle); ?> | CEMS</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $root; ?>assets/css/style.css">
    <?php if (isset($isDashboard) && $isDashboard): ?>
    <link rel="stylesheet" href="<?= $root; ?>assets/css/dashboard.css">
    <?php endif; ?>
</head>
<body class="<?= (isset($isDashboard) && $isDashboard) ? 'dashboard-body' : ''; ?>">

