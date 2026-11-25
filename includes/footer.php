<?php
/**
 * College Event Management System
 * Common Footer
 */

$isSubDir = preg_match('#/(student|admin)$#', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')));
$root = $isSubDir ? '../' : './';
?>
<footer class="footer-custom">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h5><i class="bi bi-calendar2-check-fill text-info me-2"></i> College Event Management</h5>
                <p class="text-secondary small">
                    A comprehensive, full-stack college event management and registration portal designed to connect students with campus workshops, seminars, hackathons, and cultural fests.
                </p>
                <div class="d-flex gap-3 text-secondary mt-3 fs-5">
                    <a href="#" class="text-secondary"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-secondary"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-secondary"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-secondary"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="text-secondary"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h5>Quick Links</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= $root; ?>index.php">Home</a></li>
                    <li class="mb-2"><a href="<?= $root; ?>about.php">About Us</a></li>
                    <li class="mb-2"><a href="<?= $root; ?>events.php">Browse Events</a></li>
                    <li class="mb-2"><a href="<?= $root; ?>contact.php">Contact Support</a></li>
                    <li class="mb-2"><a href="<?= $root; ?>login.php">Student Portal</a></li>
                    <li class="mb-2"><a href="<?= $root; ?>admin/login.php">Admin Panel</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <h5>Event Categories</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= $root; ?>events.php?category=Workshops"><i class="bi bi-chevron-right me-1 text-info"></i> Workshops</a></li>
                    <li class="mb-2"><a href="<?= $root; ?>events.php?category=Technical+Events"><i class="bi bi-chevron-right me-1 text-info"></i> Technical Events</a></li>
                    <li class="mb-2"><a href="<?= $root; ?>events.php?category=Cultural+Events"><i class="bi bi-chevron-right me-1 text-info"></i> Cultural Events</a></li>
                    <li class="mb-2"><a href="<?= $root; ?>events.php?category=Sports+Events"><i class="bi bi-chevron-right me-1 text-info"></i> Sports Events</a></li>
                    <li class="mb-2"><a href="<?= $root; ?>events.php?category=Competitions"><i class="bi bi-chevron-right me-1 text-info"></i> Competitions</a></li>
                    <li class="mb-2"><a href="<?= $root; ?>events.php?category=Seminars"><i class="bi bi-chevron-right me-1 text-info"></i> Seminars & AI</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>Campus Contact</h5>
                <ul class="list-unstyled small text-secondary">
                    <li class="mb-2 d-flex gap-2">
                        <i class="bi bi-geo-alt-fill text-info mt-1"></i>
                        <span>Campus Center, Tech Innovation Block, MVJ Degree College, Bangalore - 560067</span>
                    </li>
                    <li class="mb-2 d-flex gap-2">
                        <i class="bi bi-telephone-fill text-info"></i>
                        <span>+91 (080) 2845-4567 / +91 98765 43210</span>
                    </li>
                    <li class="mb-2 d-flex gap-2">
                        <i class="bi bi-envelope-at-fill text-info"></i>
                        <span>events@college.edu</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="bi bi-clock-fill text-info"></i>
                        <span>Mon - Sat: 9:00 AM - 5:00 PM</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div class="text-secondary small">
                &copy; <?= date('Y'); ?> <strong>College Event Management System</strong>. All rights reserved.
            </div>
            <div class="text-secondary small">
                BCA (Data Science) Project | Developed with PHP, MySQL & Bootstrap 5
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= $root; ?>assets/js/script.js"></script>
</body>
</html>

