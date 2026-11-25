<?php
/**
 * College Event Management System
 * Database Connection Configuration
 * Technology: PHP PDO with MySQL / MariaDB (XAMPP Compatible)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials for XAMPP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'college_event_management');
define('DB_PORT', '3306');

// Application URL configuration
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Auto-detect base folder
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // If running in subfolder like /college_event_management/admin or /college_event_management
    $basePath = preg_replace('#/(admin|student|includes|config).*$#', '', $scriptDir);
    $basePath = rtrim($basePath, '/');
    define('BASE_URL', $protocol . $host . $basePath);
}

/**
 * Get PDO Database Connection
 * Handles connection, schema verification and seamless fallback
 * 
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // Try connecting to MySQL database
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // If database does not exist, try creating it on localhost
        try {
            $dsnNoDb = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
            $tempPdo = new PDO($dsnNoDb, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            
            // Connect to newly created database
            $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            // Execute SQL schema file to create tables and seed sample data
            $sqlFile = __DIR__ . '/../database/college_event_management.sql';
            if (file_exists($sqlFile)) {
                $sqlContent = file_get_contents($sqlFile);
                $pdo->exec($sqlContent);
            }
            return $pdo;
        } catch (PDOException $innerException) {
            // If MySQL is not running on XAMPP (e.g. MySQL service stopped),
            // provide seamless SQLite database fallback for demonstration / standalone testing
            $sqlitePath = __DIR__ . '/../database/college_event_management.sqlite';
            $needsSeed = !file_exists($sqlitePath) || filesize($sqlitePath) === 0;
            
            try {
                $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                
                if ($needsSeed) {
                    seedSqliteDatabase($pdo);
                }
                return $pdo;
            } catch (Exception $sqliteErr) {
                die("<div style='font-family:Arial,sans-serif;margin:40px auto;max-width:650px;padding:25px;border:1px solid #f5c2c7;background:#f8d7da;color:#842029;border-radius:8px;'>"
                    . "<h3 style='margin-top:0;'>Database Connection Error</h3>"
                    . "<p>Unable to connect to MySQL on <code>" . DB_HOST . ":" . DB_PORT . "</code>.</p>"
                    . "<p><strong>To resolve this in XAMPP:</strong></p>"
                    . "<ol style='padding-left:20px;'>"
                    . "<li>Open <strong>XAMPP Control Panel</strong>.</li>"
                    . "<li>Click <strong>Start</strong> next to <strong>Apache</strong> and <strong>MySQL</strong>.</li>"
                    . "<li>Open <strong>phpMyAdmin</strong> and import <code>database/college_event_management.sql</code>.</li>"
                    . "</ol>"
                    . "<hr style='border:0;border-top:1px solid #f5c2c7;margin:15px 0;'>"
                    . "<small>Technical Detail: " . htmlspecialchars($e->getMessage()) . "</small>"
                    . "</div>");
            }
        }
    }
}

/**
 * Seed SQLite database when MySQL is temporarily unavailable
 */
function seedSqliteDatabase(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            admin_id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS students (
            student_id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT NOT NULL,
            department TEXT NOT NULL,
            course TEXT NOT NULL,
            year TEXT NOT NULL,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS events (
            event_id INTEGER PRIMARY KEY AUTOINCREMENT,
            event_name TEXT NOT NULL,
            description TEXT NOT NULL,
            category TEXT NOT NULL,
            event_date TEXT NOT NULL,
            event_time TEXT NOT NULL,
            venue TEXT NOT NULL,
            total_seats INTEGER NOT NULL DEFAULT 60,
            available_seats INTEGER NOT NULL DEFAULT 60,
            event_image TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS registrations (
            registration_id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id TEXT NOT NULL,
            event_id INTEGER NOT NULL,
            registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status TEXT NOT NULL DEFAULT 'Approved',
            UNIQUE(student_id, event_id),
            FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
            FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS contact_messages (
            message_id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            subject TEXT NOT NULL,
            message TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'Unread',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Seed Admin
    $checkAdmin = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    if ($checkAdmin == 0) {
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['admin', 'admin@college.edu', '$2y$10$tM6nJbX5Y.8cQ0OqZtN8xuxzJk8G4QZ/Fz1q8sX8L2fJ5cM3tFvK6']);
    }

    // Seed Students
    $checkStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    if ($checkStudents == 0) {
        $students = [
            ['BCA2026001', 'Aarav Sharma', 'aarav.sharma@student.edu', '9876543210', 'Computer Applications', 'BCA (Data Science)', '3rd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'],
            ['BCA2026002', 'Diya Patel', 'diya.patel@student.edu', '9876543211', 'Computer Applications', 'BCA (Data Science)', '3rd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'],
            ['BCA2026003', 'Rohan Verma', 'rohan.verma@student.edu', '9876543212', 'Computer Science & Engineering', 'B.Tech CSE', '2nd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'],
            ['BCA2026004', 'Ananya Iyer', 'ananya.iyer@student.edu', '9876543213', 'Information Technology', 'B.Sc IT', '3rd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'],
            ['BCA2026005', 'Vikram Singh', 'vikram.singh@student.edu', '9876543214', 'Computer Applications', 'BCA (General)', '2nd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'],
            ['BCA2026006', 'Priya Nair', 'priya.nair@student.edu', '9876543215', 'Data Science & AI', 'B.Sc Data Science', '1st Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'],
            ['BCA2026007', 'Karthik Rao', 'karthik.rao@student.edu', '9876543216', 'Computer Applications', 'BCA (Data Science)', '2nd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'],
            ['BCA2026008', 'Sneha Kulkarni', 'sneha.k@student.edu', '9876543217', 'Electronics & Communication', 'B.Tech ECE', '4th Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'],
            ['BCA2026009', 'Rahul Mehta', 'rahul.mehta@student.edu', '9876543218', 'Management Studies', 'BBA', '2nd Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5'],
            ['BCA2026010', 'Pooja Joshi', 'pooja.joshi@student.edu', '9876543219', 'Computer Applications', 'MCA', '1st Year', '$2y$10$wK1mHbV4Y.7bQ9OpZsM7wuxyJk7G3QZ/Ez0p7rW7K1eI4bL2sEuJ5']
        ];
        $ins = $pdo->prepare("INSERT INTO students (student_id, name, email, phone, department, course, year, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($students as $st) {
            $ins->execute($st);
        }
    }

    // Seed Events
    $checkEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    if ($checkEvents == 0) {
        $events = [
            [1, 'Web Development Workshop', 'Hands-on intensive masterclass on modern full-stack web engineering using HTML5, Bootstrap 5, PHP, and REST APIs. Students will build and deploy a dynamic web portal from scratch.', 'Workshops', '2026-12-05', '10:00:00', 'Seminar Hall A, Tech Block', 60, 56, 'web_dev_workshop.jpg'],
            [2, 'Python Programming Workshop', 'Comprehensive crash course focusing on data structures, algorithmic problem solving, object-oriented concepts, and practical scripting for data analysis.', 'Workshops', '2026-12-08', '11:00:00', 'Computer Lab 3, CS Department', 50, 47, 'python_workshop.jpg'],
            [3, 'Coding Competition: CodeFest 2026', 'Inter-departmental speed coding and debugging championship. Compete against top programmers across multiple rounds of algorithmic challenges and win exciting cash prizes.', 'Competitions', '2026-12-12', '09:30:00', 'Central Computing Centre', 80, 75, 'coding_competition.jpg'],
            [4, 'Data Science & AI Seminar', 'Keynote address by industry experts on the future of predictive analytics, large language models, generative AI in industry, and career pathways for BCA/B.Tech graduates.', 'Seminars', '2026-12-15', '14:00:00', 'APJ Abdul Kalam Auditorium', 150, 145, 'data_science_seminar.jpg'],
            [5, 'Annual Cultural Fest: Tarang 2026', 'Grand annual collegiate cultural fiesta featuring battle of the bands, classical & contemporary dance competitions, theatrical performances, fashion show, and celebrity night.', 'Cultural Events', '2026-12-20', '16:00:00', 'Open Air Amphitheatre', 300, 296, 'cultural_fest.jpg'],
            [6, 'Annual Inter-College Sports Meet', 'High-energy track & field athletics, inter-house football, basketball tournament, badminton championship, and table tennis showdown with awards and medals.', 'Sports Events', '2026-12-23', '08:00:00', 'University Sports Complex', 120, 117, 'sports_meet.jpg'],
            [7, 'Technical Quiz: TechWhiz 2026', 'Test your command of computer science fundamentals, emerging tech trends, cyber security, IT trivia, and rapid-fire buzzer rounds with certificates of excellence.', 'Technical Events', '2026-12-28', '11:30:00', 'Conference Room 2', 40, 38, 'technical_quiz.jpg'],
            [8, 'Entrepreneurship & Startup Summit', 'Interact with successful startup founders, venture capitalists, and incubator mentors. Learn how to pitch tech ideas, secure seed funding, and build scalable ventures.', 'Seminars', '2027-01-08', '10:30:00', 'Innovation & Incubation Hub', 100, 98, 'entrepreneurship_summit.jpg']
        ];
        $insEv = $pdo->prepare("INSERT INTO events (event_id, event_name, description, category, event_date, event_time, venue, total_seats, available_seats, event_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($events as $ev) {
            $insEv->execute($ev);
        }
    }

    // Seed Registrations
    $checkReg = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
    if ($checkReg == 0) {
        $regs = [
            [1, 'BCA2026001', 1, '2026-11-15 09:12:00', 'Approved'],
            [2, 'BCA2026001', 4, '2026-11-15 09:15:20', 'Approved'],
            [3, 'BCA2026002', 1, '2026-11-15 10:05:10', 'Approved'],
            [4, 'BCA2026002', 3, '2026-11-15 10:20:45', 'Pending'],
            [5, 'BCA2026003', 2, '2026-11-16 11:30:00', 'Approved'],
            [6, 'BCA2026003', 3, '2026-11-16 11:35:12', 'Approved'],
            [7, 'BCA2026004', 1, '2026-11-16 14:10:00', 'Approved'],
            [8, 'BCA2026004', 5, '2026-11-16 14:15:30', 'Approved'],
            [9, 'BCA2026005', 6, '2026-11-17 08:45:00', 'Approved'],
            [10, 'BCA2026006', 4, '2026-11-17 09:00:22', 'Pending'],
            [11, 'BCA2026007', 7, '2026-11-17 10:14:50', 'Approved'],
            [12, 'BCA2026008', 8, '2026-11-17 12:00:00', 'Approved']
        ];
        $insReg = $pdo->prepare("INSERT INTO registrations (registration_id, student_id, event_id, registration_date, status) VALUES (?, ?, ?, ?, ?)");
        foreach ($regs as $rg) {
            $insReg->execute($rg);
        }
    }

    // Seed Contact Messages
    $checkMsg = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
    if ($checkMsg == 0) {
        $msgs = [
            [1, 'Prof. Ramesh Kulkarni', 'ramesh.k@externalcollege.edu', 'Inter-College Fest Participation Query', 'Greetings! Our college department would like to send a team of 15 students to participate in CodeFest 2026. Please share the delegation guidelines.', 'Unread', '2026-11-16 10:30:00'],
            [2, 'Neha Singhania', 'neha.s@student.edu', 'Participation Certificate for Web Workshop', 'Hello Sir/Madam, will hardcopy participation certificates and digital badges be provided after completing the Web Development Workshop?', 'Read', '2026-11-16 14:22:10'],
            [3, 'Kunal Deshmukh', 'kunal.d@alumni.org', 'Guest Speaker Invitation at Tech Summit', 'I am an alumnus working as Senior Cloud Architect. I would love to mentor students during the upcoming Entrepreneurship Summit.', 'Read', '2026-11-17 09:15:00'],
            [4, 'Siddharth Roy', 'siddharth.r@student.edu', 'Query Regarding Sports Meet Schedule', 'Could you please confirm if the 100m sprint heats will be conducted in the morning session of the Sports Meet?', 'Unread', '2026-11-17 11:40:00'],
            [5, 'Divya Sharma', 'divya.sharma@gmail.com', 'Volunteering Opportunities for Cultural Fest', 'I would like to apply as a student coordinator/volunteer for Tarang 2026. Whom should I contact on campus?', 'Replied', '2026-11-17 13:05:00']
        ];
        $insMsg = $pdo->prepare("INSERT INTO contact_messages (message_id, name, email, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($msgs as $m) {
            $insMsg->execute($m);
        }
    }
}

