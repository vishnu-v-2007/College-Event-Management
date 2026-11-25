<?php
/**
 * College Event Management System
 * Home Page (index.php)
 */

$pageTitle = 'Home - Explore & Register for Campus Events';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = getDBConnection();

// Fetch summary stats
$totalEvents = 0;
$totalStudents = 0;
$totalRegistrations = 0;
try {
    $totalEvents = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalRegistrations = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
} catch (Exception $e) {}

// Fetch up to 6 featured upcoming events
$upcomingEvents = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM events ORDER BY event_date ASC LIMIT 6");
    $stmt->execute();
    $upcomingEvents = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<!-- Hero Banner Section -->
<section class="hero-section text-center text-lg-start">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="hero-badges mb-3">
                    <span class="badge text-white"><i class="bi bi-mortarboard-fill me-1 text-info"></i> Department of Computer Applications</span>
                    <span class="badge text-white"><i class="bi bi-stars me-1 text-warning"></i> Academic Year 2026-2027</span>
                </div>
                <h1 class="display-4 fw-bold mb-3">
                    Discover, Participate & <span class="text-info">Excel</span> in College Events
                </h1>
                <p class="lead mb-4 text-light opacity-90">
                    Welcome to the official <strong>College Event Management System</strong>. Explore technical workshops, hackathons, academic seminars, sports meets, and cultural festivals all in one unified digital campus platform.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="events.php" class="btn btn-info btn-lg px-4 text-dark fw-bold shadow-sm">
                        <i class="bi bi-compass me-2"></i> Explore Events
                    </a>
                    <?php if (!isStudentLoggedIn()): ?>
                    <a href="register.php" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                        <i class="bi bi-person-plus me-2"></i> Register Now
                    </a>
                    <?php else: ?>
                    <a href="student/dashboard.php" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                        <i class="bi bi-speedometer2 me-2"></i> My Dashboard
                    </a>
                    <?php endif; ?>
                    <a href="about.php" class="btn btn-link text-light text-decoration-none d-flex align-items-center">
                        Learn More <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="p-3 bg-white bg-opacity-10 rounded-4 shadow-lg border border-light border-opacity-25">
                    <img src="assets/images/web_dev_workshop.jpg" alt="Campus Event Highlight" class="img-fluid rounded-3 shadow">
                    <div class="mt-3 text-start px-2">
                        <span class="badge bg-warning text-dark mb-1">Featured Event</span>
                        <h6 class="text-white mb-0 fw-bold">Annual Tech & Cultural Symposium 2026</h6>
                        <small class="text-info"><i class="bi bi-geo-alt me-1"></i> Main Auditorium & Lab Complex</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Statistics Bar -->
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="bi bi-calendar2-event"></i>
                    </div>
                    <div class="stat-number"><?= $totalEvents > 0 ? $totalEvents : '8+'; ?></div>
                    <div class="stat-label">Campus Events</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <div class="stat-icon" style="background:#e0e7ff; color:#4338ca;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stat-number"><?= $totalStudents > 0 ? $totalStudents : '250+'; ?></div>
                    <div class="stat-label">Enrolled Students</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <div class="stat-icon" style="background:#dcfce7; color:#15803d;">
                        <i class="bi bi-ticket-perforated-fill"></i>
                    </div>
                    <div class="stat-number"><?= $totalRegistrations > 0 ? $totalRegistrations : '120+'; ?></div>
                    <div class="stat-label">Registrations Done</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box">
                    <div class="stat-icon" style="background:#fef3c7; color:#b45309;">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <div class="stat-number">6</div>
                    <div class="stat-label">Event Categories</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Upcoming Events Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary px-3 py-2 text-uppercase fw-bold mb-2">Campus Calendar</span>
            <h2 class="section-title">Upcoming College Events</h2>
            <p class="section-subtitle">
                Join exciting technical workshops, sports tournaments, hackathons, and cultural fests. Reserve your seat before registration closes!
            </p>
        </div>

        <div class="row g-4">
            <?php if (!empty($upcomingEvents)): ?>
                <?php foreach ($upcomingEvents as $event): ?>
                    <?php
                        $badgeClass = match($event['category']) {
                            'Workshops' => 'badge-workshop',
                            'Technical Events' => 'badge-tech',
                            'Cultural Events' => 'badge-cultural',
                            'Sports Events' => 'badge-sports',
                            'Seminars' => 'badge-seminar',
                            'Competitions' => 'badge-competition',
                            default => 'bg-secondary'
                        };
                        $isFull = ((int)$event['available_seats'] <= 0);
                        $imagePath = 'uploads/events/' . ($event['event_image'] ?? 'web_dev_workshop.jpg');
                        if (!file_exists(__DIR__ . '/' . $imagePath)) {
                            $imagePath = 'assets/images/web_dev_workshop.jpg';
                        }
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="event-card">
                            <div class="event-image-wrapper">
                                <img src="<?= $imagePath; ?>" alt="<?= e($event['event_name']); ?>">
                                <span class="event-category-badge <?= $badgeClass; ?>"><?= e($event['category']); ?></span>
                                <span class="event-seats-badge <?= $isFull ? 'bg-danger text-white' : 'bg-success text-white'; ?>">
                                    <?= $isFull ? 'Seats Full' : e($event['available_seats']) . ' Seats Left'; ?>
                                </span>
                            </div>
                            <div class="event-card-body">
                                <h3 class="event-card-title"><?= e($event['event_name']); ?></h3>
                                <p class="event-card-desc"><?= e($event['description']); ?></p>
                                <ul class="event-meta-list">
                                    <li>
                                        <i class="bi bi-calendar3"></i>
                                        <span><?= formatDate($event['event_date']); ?> at <?= formatTime($event['event_time']); ?></span>
                                    </li>
                                    <li>
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span><?= e($event['venue']); ?></span>
                                    </li>
                                </ul>
                                <div class="mt-auto d-flex gap-2">
                                    <?php if ($isFull): ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="bi bi-slash-circle me-1"></i> Registration Closed
                                        </button>
                                    <?php elseif (isStudentLoggedIn()): ?>
                                        <a href="events.php#event-<?= $event['event_id']; ?>" class="btn btn-primary w-100">
                                            <i class="bi bi-pencil-square me-1"></i> Register Now
                                        </a>
                                    <?php else: ?>
                                        <a href="login.php?redirect=events.php" class="btn btn-primary w-100">
                                            <i class="bi bi-box-arrow-in-right me-1"></i> Login to Register
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted fs-5">No upcoming events found at this time.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="events.php" class="btn btn-outline-primary btn-lg px-5">
                View All College Events <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Event Categories Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-info text-dark px-3 py-2 text-uppercase fw-bold mb-2">Explore By Domain</span>
            <h2 class="section-title">Event Categories</h2>
            <p class="section-subtitle">
                Choose from a diverse spectrum of student activities spanning coding, cultural expression, athletics, and academic research.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <a href="events.php?category=Technical+Events" class="text-decoration-none">
                    <div class="category-card">
                        <div class="category-icon-circle">
                            <i class="bi bi-cpu-fill"></i>
                        </div>
                        <h4 class="h5 mb-2">Technical Events</h4>
                        <p class="text-muted small mb-0">Hackathons, coding sprints, robotics exhibits, and modern software design challenges.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6">
                <a href="events.php?category=Workshops" class="text-decoration-none">
                    <div class="category-card">
                        <div class="category-icon-circle" style="background:#f3e8ff; color:#9333ea;">
                            <i class="bi bi-laptop-fill"></i>
                        </div>
                        <h4 class="h5 mb-2">Hands-on Workshops</h4>
                        <p class="text-muted small mb-0">Full-stack web engineering, Python data science, cloud architectures, and machine learning labs.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6">
                <a href="events.php?category=Cultural+Events" class="text-decoration-none">
                    <div class="category-card">
                        <div class="category-icon-circle" style="background:#fce7f3; color:#db2777;">
                            <i class="bi bi-music-note-beamed"></i>
                        </div>
                        <h4 class="h5 mb-2">Cultural Events</h4>
                        <p class="text-muted small mb-0">Annual cultural festival, musical concerts, street plays, dance battles, and art exhibitions.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6">
                <a href="events.php?category=Sports+Events" class="text-decoration-none">
                    <div class="category-card">
                        <div class="category-icon-circle" style="background:#dcfce7; color:#16a34a;">
                            <i class="bi bi-trophy-fill"></i>
                        </div>
                        <h4 class="h5 mb-2">Sports Tournaments</h4>
                        <p class="text-muted small mb-0">Inter-departmental cricket, football, basketball, badminton, track and field championships.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6">
                <a href="events.php?category=Seminars" class="text-decoration-none">
                    <div class="category-card">
                        <div class="category-icon-circle" style="background:#fef3c7; color:#d97706;">
                            <i class="bi bi-mic-fill"></i>
                        </div>
                        <h4 class="h5 mb-2">Seminars & Talks</h4>
                        <p class="text-muted small mb-0">Keynotes from industry leaders, tech researchers, entrepreneurs, and esteemed alumni.</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6">
                <a href="events.php?category=Competitions" class="text-decoration-none">
                    <div class="category-card">
                        <div class="category-icon-circle" style="background:#fee2e2; color:#dc2626;">
                            <i class="bi bi-fire"></i>
                        </div>
                        <h4 class="h5 mb-2">Competitions & Quizzes</h4>
                        <p class="text-muted small mb-0">High-stakes algorithmic duels, technical quizzes, debate tournaments, and case study pitches.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- About College Brief Section -->
<section class="py-5 bg-light border-top">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <img src="assets/images/data_science_seminar.jpg" alt="About College Campus" class="img-fluid rounded-4 shadow">
            </div>
            <div class="col-lg-6">
                <span class="badge bg-primary px-3 py-2 text-uppercase fw-bold mb-2">About Our Institution</span>
                <h2 class="fw-bold mb-3">Empowering Students Through Holistic Campus Activities</h2>
                <p class="text-muted mb-3">
                    At our institution, learning goes beyond lecture halls and textbooks. The College Event Management System (CEMS) provides students with a streamlined digital avenue to register for extracurricular, co-curricular, and technical opportunities.
                </p>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <span class="fw-semibold">Automated Registration</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <span class="fw-semibold">Real-Time Seat Tracking</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <span class="fw-semibold">Digital Event Passes</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <span class="fw-semibold">Admin Oversight & Analytics</span>
                        </div>
                    </div>
                </div>
                <a href="about.php" class="btn btn-primary px-4">
                    Read Institutional Mission <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-5 text-center text-white" style="background: linear-gradient(135deg, #0a1e40 0%, #1e40af 100%);">
    <div class="container py-4">
        <h2 class="display-6 fw-bold text-white mb-3">Ready to Be Part of the Next Big Event?</h2>
        <p class="lead text-light opacity-90 max-w-700 mx-auto mb-4" style="max-width:650px;">
            Join over 1,000+ enthusiastic students participating in campus seminars, workshops, and sports tournaments every semester.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <?php if (!isStudentLoggedIn()): ?>
            <a href="register.php" class="btn btn-info btn-lg px-4 text-dark fw-bold shadow">
                Create Student Account
            </a>
            <a href="login.php" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                Student Sign In
            </a>
            <?php else: ?>
            <a href="events.php" class="btn btn-info btn-lg px-4 text-dark fw-bold shadow">
                Browse Active Events
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

