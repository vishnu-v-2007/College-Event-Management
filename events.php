<?php
/**
 * College Event Management System
 * Events Listing & Registration (events.php)
 */

$pageTitle = 'Campus Events - Browse & Register';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = getDBConnection();
$student = getLoggedInStudent();

// Handle direct registration action if submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    requireStudentLogin('login.php?redirect=events.php');
    
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token. Please try again.');
        header('Location: events.php');
        exit();
    }

    $eventId = (int)($_POST['event_id'] ?? 0);
    $studentId = $student['student_id'];

    if ($eventId <= 0) {
        setFlash('danger', 'Invalid event selected.');
        header('Location: events.php');
        exit();
    }

    try {
        // 1. Check if event exists and get seat count
        $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch();

        if (!$event) {
            setFlash('danger', 'Selected event does not exist.');
            header('Location: events.php');
            exit();
        }

        // 2. Check if student is already registered
        $checkStmt = $pdo->prepare("SELECT * FROM registrations WHERE student_id = ? AND event_id = ?");
        $checkStmt->execute([$studentId, $eventId]);
        if ($checkStmt->fetch()) {
            setFlash('warning', 'You are already registered for "' . $event['event_name'] . '". Check your dashboard.');
            header('Location: events.php');
            exit();
        }

        // 3. Check seat availability
        if ((int)$event['available_seats'] <= 0) {
            setFlash('danger', 'Registration failed: All seats for "' . $event['event_name'] . '" are full.');
            header('Location: events.php');
            exit();
        }

        // 4. Begin transaction to insert registration and decrement available seats
        $pdo->beginTransaction();

        $insStmt = $pdo->prepare("INSERT INTO registrations (student_id, event_id, status) VALUES (?, ?, 'Approved')");
        $insStmt->execute([$studentId, $eventId]);

        $updStmt = $pdo->prepare("UPDATE events SET available_seats = available_seats - 1 WHERE event_id = ? AND available_seats > 0");
        $updStmt->execute([$eventId]);

        $pdo->commit();

        setFlash('success', 'Registration successful! You have reserved a seat for "' . $event['event_name'] . '".');
        header('Location: student/my_events.php');
        exit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        setFlash('danger', 'Registration error: ' . $e->getMessage());
        header('Location: events.php');
        exit();
    }
}

// Fetch all events from database
$allEvents = [];
try {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
    $allEvents = $stmt->fetchAll();
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
}

// Fetch current student's registered event IDs for quick status check
$registeredEventIds = [];
if (isStudentLoggedIn()) {
    try {
        $regStmt = $pdo->prepare("SELECT event_id FROM registrations WHERE student_id = ?");
        $regStmt->execute([$student['student_id']]);
        $registeredEventIds = $regStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {}
}

// Filter category from URL if preset
$filterCategory = isset($_GET['category']) ? trim($_GET['category']) : 'all';
?>

<!-- Events Hero Banner -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #091931 0%, #0f2b5c 50%, #173b75 100%);">
    <div class="container py-2 text-center">
        <span class="badge bg-info text-dark px-3 py-2 text-uppercase fw-bold mb-2">College Calendar</span>
        <h1 class="display-5 fw-bold text-white mb-2">Explore Campus Events</h1>
        <p class="lead text-light opacity-90 max-w-700 mx-auto" style="max-width: 650px;">
            Discover, filter, and register for technical workshops, coding challenges, cultural festivals, and athletic tournaments.
        </p>
    </div>
</section>

<!-- Filter & Search Section -->
<section class="py-4 bg-white border-bottom shadow-sm sticky-lg-top" style="top: 68px; z-index: 1000;">
    <div class="container">
        <div class="row g-3 align-items-center">
            <!-- Search Bar -->
            <div class="col-lg-5 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="eventSearchInput" class="form-control border-start-0" placeholder="Search event by name, keyword or venue...">
                </div>
            </div>

            <!-- Category Filter Buttons -->
            <div class="col-lg-7 col-md-6">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <button class="btn btn-sm event-filter-btn <?= ($filterCategory === 'all') ? 'btn-primary active' : 'btn-outline-primary'; ?>" data-category="all">
                        All Events
                    </button>
                    <button class="btn btn-sm event-filter-btn <?= ($filterCategory === 'Workshops') ? 'btn-primary active' : 'btn-outline-primary'; ?>" data-category="Workshops">
                        Workshops
                    </button>
                    <button class="btn btn-sm event-filter-btn <?= ($filterCategory === 'Technical Events') ? 'btn-primary active' : 'btn-outline-primary'; ?>" data-category="Technical Events">
                        Technical
                    </button>
                    <button class="btn btn-sm event-filter-btn <?= ($filterCategory === 'Competitions') ? 'btn-primary active' : 'btn-outline-primary'; ?>" data-category="Competitions">
                        Competitions
                    </button>
                    <button class="btn btn-sm event-filter-btn <?= ($filterCategory === 'Seminars') ? 'btn-primary active' : 'btn-outline-primary'; ?>" data-category="Seminars">
                        Seminars
                    </button>
                    <button class="btn btn-sm event-filter-btn <?= ($filterCategory === 'Cultural Events') ? 'btn-primary active' : 'btn-outline-primary'; ?>" data-category="Cultural Events">
                        Cultural
                    </button>
                    <button class="btn btn-sm event-filter-btn <?= ($filterCategory === 'Sports Events') ? 'btn-primary active' : 'btn-outline-primary'; ?>" data-category="Sports Events">
                        Sports
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Events Grid Section -->
<section class="py-5 bg-light">
    <div class="container">
        <!-- Live No Results Found Message -->
        <div id="noEventsFound" class="alert alert-warning text-center py-5 shadow-sm" style="display: none;">
            <i class="bi bi-exclamation-triangle-fill fs-2 d-block mb-2 text-warning"></i>
            <h5>No matching events found!</h5>
            <p class="text-muted mb-0">Try adjusting your search keywords or clearing the category filter.</p>
        </div>

        <div class="row g-4" id="eventsGrid">
            <?php if (!empty($allEvents)): ?>
                <?php foreach ($allEvents as $event): ?>
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
                        $isRegistered = in_array($event['event_id'], $registeredEventIds);
                        $imagePath = 'uploads/events/' . ($event['event_image'] ?? 'web_dev_workshop.jpg');
                        if (!file_exists(__DIR__ . '/' . $imagePath)) {
                            $imagePath = 'assets/images/web_dev_workshop.jpg';
                        }
                    ?>
                    <div class="col-lg-4 col-md-6 event-item" 
                         id="event-<?= $event['event_id']; ?>"
                         data-category="<?= e($event['category']); ?>"
                         data-title="<?= e($event['event_name']); ?>"
                         data-desc="<?= e($event['description']); ?>"
                         data-venue="<?= e($event['venue']); ?>"
                         style="<?= ($filterCategory !== 'all' && $filterCategory !== $event['category']) ? 'display:none;' : ''; ?>">
                        
                        <div class="event-card">
                            <div class="event-image-wrapper">
                                <img src="<?= $imagePath; ?>" alt="<?= e($event['event_name']); ?>">
                                <span class="event-category-badge <?= $badgeClass; ?>"><?= e($event['category']); ?></span>
                                
                                <?php if ($isRegistered): ?>
                                    <span class="event-seats-badge bg-primary text-white">
                                        <i class="bi bi-check2-circle me-1"></i> Registered
                                    </span>
                                <?php elseif ($isFull): ?>
                                    <span class="event-seats-badge bg-danger text-white">Seats Full</span>
                                <?php else: ?>
                                    <span class="event-seats-badge bg-success text-white">
                                        <?= e($event['available_seats']); ?> / <?= e($event['total_seats']); ?> Seats
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="event-card-body">
                                <h3 class="event-card-title"><?= e($event['event_name']); ?></h3>
                                <p class="event-card-desc"><?= e($event['description']); ?></p>

                                <ul class="event-meta-list">
                                    <li>
                                        <i class="bi bi-calendar3"></i>
                                        <span><strong>Date:</strong> <?= formatDate($event['event_date']); ?></span>
                                    </li>
                                    <li>
                                        <i class="bi bi-clock"></i>
                                        <span><strong>Time:</strong> <?= formatTime($event['event_time']); ?></span>
                                    </li>
                                    <li>
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span><strong>Venue:</strong> <?= e($event['venue']); ?></span>
                                    </li>
                                </ul>

                                <div class="mt-auto d-flex gap-2">
                                    <!-- View Details Modal Trigger -->
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" 
                                            data-bs-toggle="modal" data-bs-target="#eventDetailsModal"
                                            data-title="<?= e($event['event_name']); ?>"
                                            data-desc="<?= e($event['description']); ?>"
                                            data-category="<?= e($event['category']); ?>"
                                            data-date="<?= formatDate($event['event_date']); ?>"
                                            data-time="<?= formatTime($event['event_time']); ?>"
                                            data-venue="<?= e($event['venue']); ?>"
                                            data-seats="<?= e($event['available_seats']) . ' of ' . e($event['total_seats']); ?>"
                                            data-image="<?= $imagePath; ?>">
                                        <i class="bi bi-info-circle"></i> Details
                                    </button>

                                    <!-- Registration Actions -->
                                    <?php if ($isRegistered): ?>
                                        <a href="student/my_events.php" class="btn btn-success btn-sm flex-grow-1">
                                            <i class="bi bi-check-circle-fill me-1"></i> View Ticket Pass
                                        </a>
                                    <?php elseif ($isFull): ?>
                                        <button class="btn btn-secondary btn-sm flex-grow-1" disabled>
                                            <i class="bi bi-slash-circle me-1"></i> Seats Full
                                        </button>
                                    <?php elseif (isStudentLoggedIn()): ?>
                                        <button type="button" class="btn btn-primary btn-sm flex-grow-1"
                                                data-bs-toggle="modal" data-bs-target="#registerModal"
                                                data-event-id="<?= $event['event_id']; ?>"
                                                data-event-name="<?= e($event['event_name']); ?>"
                                                data-event-date="<?= formatDate($event['event_date']); ?> at <?= formatTime($event['event_time']); ?>"
                                                data-event-venue="<?= e($event['venue']); ?>"
                                                data-event-seats="<?= e($event['available_seats']); ?>">
                                            <i class="bi bi-pencil-square me-1"></i> Register Now
                                        </button>
                                    <?php else: ?>
                                        <a href="login.php?redirect=events.php" class="btn btn-primary btn-sm flex-grow-1">
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
                    <p class="text-muted fs-5">No events found in the database.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Registration Confirmation Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="POST" action="events.php">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
                <input type="hidden" name="event_id" id="modalEventId" value="">

                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="registerModalLabel">
                        <i class="bi bi-calendar2-check me-2"></i> Confirm Event Registration
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">You are registering with your student credentials:</p>
                    
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <h5 class="text-primary fw-bold mb-1" id="modalEventName">Event Title</h5>
                        <p class="small text-muted mb-2"><i class="bi bi-calendar-event me-1"></i> <span id="modalEventDate">Date</span></p>
                        <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i> <span id="modalEventVenue">Venue</span></p>
                        <p class="small text-success mb-0 fw-semibold"><i class="bi bi-people me-1"></i> <span id="modalEventSeats">0</span> seats currently available</p>
                    </div>

                    <?php if (isStudentLoggedIn()): ?>
                    <div class="card border-0 bg-primary bg-opacity-10 p-3 rounded-3">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-person-badge-fill text-primary fs-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold"><?= e($student['name']); ?></h6>
                                <small class="text-muted">ID: <?= e($student['student_id']); ?> | <?= e($student['course']); ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="modal-footer border-0 bg-light py-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Confirm & Reserve Seat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="detailTitle">Event Name</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-5">
                        <img id="detailImage" src="assets/images/web_dev_workshop.jpg" alt="Event preview" class="img-fluid rounded-3 shadow-sm mb-3">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="mb-2"><strong>Category:</strong> <span id="detailCategory" class="badge bg-primary">Category</span></div>
                            <div class="mb-2"><strong>Date:</strong> <span id="detailDate">Date</span></div>
                            <div class="mb-2"><strong>Time:</strong> <span id="detailTime">Time</span></div>
                            <div class="mb-2"><strong>Venue:</strong> <span id="detailVenue">Venue</span></div>
                            <div><strong>Seats:</strong> <span id="detailSeats" class="text-success fw-bold">Seats</span></div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h5 class="fw-bold text-primary mb-2">Event Overview & Syllabus</h5>
                        <p id="detailDesc" class="text-muted leading-relaxed"></p>
                        
                        <h6 class="fw-bold mt-4 mb-2"><i class="bi bi-shield-check text-success me-1"></i> Participant Guidelines</h6>
                        <ul class="text-muted small ps-3">
                            <li class="mb-1">Bring your valid College Student ID card to the venue.</li>
                            <li class="mb-1">Laptops required for hands-on coding workshops.</li>
                            <li class="mb-1">Certificates will be awarded to registered participants upon completion.</li>
                            <li>Report 15 minutes before the scheduled start time.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

