<?php
/**
 * College Event Management System
 * About Us Page (about.php)
 */

$pageTitle = 'About Us - Institution & Event Management Committee';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- About Hero Section -->
<section class="bg-primary text-white py-5" style="background: linear-gradient(135deg, #0a1e40 0%, #0f2b5c 60%, #1e3a8a 100%);">
    <div class="container py-3">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-info text-dark px-3 py-2 text-uppercase fw-bold mb-3">Institutional Overview</span>
                <h1 class="display-5 fw-bold text-white mb-3">About College Event Management</h1>
                <p class="lead text-light opacity-90 mb-0">
                    Committed to fostering academic brilliance, technical prowess, leadership qualities, and vibrant cultural engagement across the collegiate community.
                </p>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <i class="bi bi-mortarboard-fill text-info" style="font-size: 6rem; opacity: 0.85;"></i>
            </div>
        </div>
    </div>
</section>

<!-- College Introduction & Overview -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <span class="badge bg-primary px-3 py-2 text-uppercase fw-bold mb-2">Introduction</span>
                <h2 class="fw-bold mb-3">A Hub of Innovation, Knowledge & Student Leadership</h2>
                <p class="text-muted">
                    Our college stands as a premier educational institution dedicated to producing industry-ready graduates, agile researchers, and socially conscious citizens. The <strong>College Event Management System (CEMS)</strong> was architected to replace outdated paper notices and fragmented spreadsheets with a modern, automated digital portal.
                </p>
                <p class="text-muted">
                    Through CEMS, students from Computer Applications, Engineering, Science, and Management can discover upcoming campus opportunities, review seating capacities, enroll with a single click, and securely manage their participation passes.
                </p>
                <div class="p-3 bg-light rounded-3 border-start border-4 border-primary mt-4">
                    <p class="fst-italic mb-0 text-dark">
                        "Extracurricular and co-curricular events form the crucible in which theoretical classroom concepts transform into real-world collaboration, team dynamics, and problem-solving excellence."
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/cultural_fest.jpg" alt="College Campus Life" class="img-fluid rounded-4 shadow">
            </div>
        </div>
    </div>
</section>

<!-- Vision & Mission -->
<section class="py-5 bg-light border-top border-bottom">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary fs-3">
                            <i class="bi bi-eye-fill"></i>
                        </div>
                        <h3 class="h4 fw-bold mb-0">Our Vision</h3>
                    </div>
                    <p class="text-muted mb-0">
                        To evolve as a center of educational excellence that nurtures curiosity, creative thinking, technological acumen, and community leadership through vibrant campus interactions, industry seminars, and inter-collegiate championships.
                    </p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 rounded-circle bg-info bg-opacity-10 text-info fs-3">
                            <i class="bi bi-bullseye"></i>
                        </div>
                        <h3 class="h4 fw-bold mb-0">Our Mission</h3>
                    </div>
                    <ul class="text-muted mb-0 ps-3">
                        <li class="mb-2">Deliver high-impact hands-on technical workshops in emerging software paradigms.</li>
                        <li class="mb-2">Encourage inter-disciplinary participation in arts, literature, dance, and theatre.</li>
                        <li class="mb-2">Promote physical fitness, sportsmanship, and teamwork through annual athletics tournaments.</li>
                        <li>Equip students with professional networking, public speaking, and event leadership skills.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Event Management Committee & Student Leadership -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary px-3 py-2 text-uppercase fw-bold mb-2">Coordination Council</span>
            <h2 class="section-title">Event Management Committee</h2>
            <p class="section-subtitle">
                Dedicated faculty advisors and student coordinators working tirelessly to organize world-class campus events.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="card text-center border-0 shadow-sm p-4 rounded-4 h-100">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; background: #e0f2fe; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #0284c7;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Dr. S. K. Narayanan</h5>
                    <p class="text-primary small fw-semibold mb-2">Chief Faculty Advisor</p>
                    <p class="text-muted small mb-0">Department of Computer Applications</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card text-center border-0 shadow-sm p-4 rounded-4 h-100">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; background: #fce7f3; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #db2777;">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Prof. Meera Sharma</h5>
                    <p class="text-primary small fw-semibold mb-2">Cultural Secretary</p>
                    <p class="text-muted small mb-0">Dean of Student Affairs</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card text-center border-0 shadow-sm p-4 rounded-4 h-100">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #16a34a;">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Coach Rajesh Kumar</h5>
                    <p class="text-primary small fw-semibold mb-2">Sports Convener</p>
                    <p class="text-muted small mb-0">Physical Education Directorate</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card text-center border-0 shadow-sm p-4 rounded-4 h-100">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; background: #fef3c7; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #d97706;">
                        <i class="bi bi-laptop"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Arjun M. & Sneha P.</h5>
                    <p class="text-primary small fw-semibold mb-2">Lead Student Coordinators</p>
                    <p class="text-muted small mb-0">BCA Data Science Final Year</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Participate Section -->
<section class="py-5 bg-light border-top">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Why Participate in Campus Events?</h2>
            <p class="section-subtitle">Enhance your academic portfolio, acquire micro-credentials, and make lasting collegiate memories.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-3 shadow-sm h-100">
                    <div class="fs-2 text-primary mb-3"><i class="bi bi-file-earmark-check-fill"></i></div>
                    <h4 class="h5 fw-bold">Verified Certificates</h4>
                    <p class="text-muted small mb-0">Earn verified digital certificates and credentials upon successful workshop participation to strengthen your resume.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-3 shadow-sm h-100">
                    <div class="fs-2 text-info mb-3"><i class="bi bi-diagram-3-fill"></i></div>
                    <h4 class="h5 fw-bold">Industry Networking</h4>
                    <p class="text-muted small mb-0">Connect directly with alumni mentors, tech entrepreneurs, hackathon judges, and industry guest lecturers.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-3 shadow-sm h-100">
                    <div class="fs-2 text-warning mb-3"><i class="bi bi-award-fill"></i></div>
                    <h4 class="h5 fw-bold">Prizes & Recognition</h4>
                    <p class="text-muted small mb-0">Win trophies, medals, cash scholarships, and departmental honors in annual competitions and hackathons.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

