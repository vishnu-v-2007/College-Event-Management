<<<<<<< HEAD
# COLLEGE EVENT MANAGEMENT SYSTEM (CEMS)
## Full-Stack Web Development Project | BCA (Data Science)

**Domain:** Full-Stack Web Development  
**Academic Level:** Basic to Intermediate  
**Course:** Bachelor of Computer Applications (BCA - Data Science)  
**Technologies:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5, Bootstrap Icons, PHP 8, MySQL  
**Development & Runtime Environment:** Google Antigravity, Visual Studio Code, XAMPP (Apache + MySQL / MariaDB)

---

## 1. PROJECT OVERVIEW

The **College Event Management System (CEMS)** is an end-to-end, fully functional, responsive collegiate web portal designed to modernize campus event administration. The system allows students to explore upcoming campus events, view real-time seating availability, register with automated duplicate prevention, manage their academic profiles, and access printable official digital event passes.

A separate, secured **Administrator Dashboard** empowers faculty advisors and event coordinators to manage student records, maintain event schedules using complete CRUD operations, upload event banner graphics, monitor registrations, review approval workflows, handle public support inquiries, and analyze student engagement via interactive **Chart.js** data visualizations.

---

## 2. KEY FEATURES & MODULES

### Student Features (Public & Student Portal)
- **Home Page (`index.php`):** Hero banner, institutional highlights, live event counters, featured events, category cards, and responsive navigation.
- **About Us (`about.php`):** Institutional mission, vision, event committee faculty advisors, and student coordinator council.
- **Events Catalog (`events.php`):** Dynamic catalog fetched from MySQL with real-time keyword search, category filtering (Workshops, Technical, Cultural, Sports, Seminars, Competitions), seat badges, and details modals.
- **Student Registration (`register.php`):** Full input validation, uniqueness checks on Student ID and email, and password hashing using Bcrypt (`password_hash`).
- **Student Login (`login.php`):** Secure credential verification (`password_verify`), session handling, session ID regeneration, and one-click demo credentials auto-fill.
- **Student Dashboard (`student/dashboard.php`):** Welcome greeting, profile card, KPI metric counters (Total Registered, Approved Passes, Pending, Available Events), recent registrations list, and recommended events.
- **Event Registration Engine (`events.php` & `student/event_register.php`):** Transactional reservation with atomic seat decrements, duplicate registration prevention via database composite unique constraints, and instant pass generation.
- **My Registrations & Passes (`student/my_events.php`):** Full registration history with status pills, cancellation with automatic seat refund, and printable digital event passes with formatted barcode strings.
- **Profile Management (`student/profile.php`):** Edit contact number, department, course, year of study, and update account password.
- **Contact Support (`contact.php`):** Inquiry form storing messages directly in MySQL with status tracking (Unread, Read, Replied).

### Administrator Features (Staff Portal)
- **Admin Authentication (`admin/login.php`):** Dedicated staff gateway with session isolation and demo login helper.
- **Admin Dashboard (`admin/dashboard.php`):** High-level KPI summary cards (Students, Events, Total Registrations, Pending Reviews), interactive Chart.js bar charts (Registrations per Event) and doughnut charts (Events by Category), and recent activity tables.
- **Event Management (`admin/manage_events.php`, `add_event.php`, `edit_event.php`):** Complete CRUD suite to create, read, update, and delete events with date/time pickers, venue locations, seat quotas, and image banner uploads.
- **Student Management (`admin/manage_students.php`):** Directory of enrolled students, department filtering, search bar, student registration history modals, and record deletion.
- **Registration Management (`admin/manage_registrations.php`):** Review all registrations, filter by event or status, live status transitions (Approved, Pending, Rejected), print/export feature, and seat-refund cancellation.
- **Contact Messages (`admin/contact_messages.php`):** Support inbox with full message view modals, status updates, mailto reply links, and deletion.

---

## 3. PROJECT FOLDER STRUCTURE

```
college_event_management/
│
├── index.php                         # Home page with hero, stats, upcoming events
├── about.php                         # Institutional vision, mission, committee
├── events.php                        # Dynamic events catalog with search & filter
├── contact.php                       # Contact form & campus contact details
├── register.php                      # Student registration with validation
├── login.php                         # Student login with demo credentials
├── logout.php                        # Secure session destruction & logout
├── README.md                         # Project documentation & execution guide
│
├── student/                          # Student Portal Subdirectory
│   ├── dashboard.php                 # Student dashboard with KPIs & events
│   ├── profile.php                   # Profile editor & password change
│   ├── my_events.php                 # Registered events & digital ticket passes
│   └── event_register.php            # Direct event registration processor
│
├── admin/                            # Administrator Portal Subdirectory
│   ├── login.php                     # Admin login gateway
│   ├── dashboard.php                 # Admin console with Chart.js analytics
│   ├── manage_events.php             # Event CRUD list, search, and delete
│   ├── add_event.php                 # Create event form with image upload
│   ├── edit_event.php                # Edit event details & seat capacities
│   ├── manage_students.php           # Student directory & registration modals
│   ├── manage_registrations.php      # Registration approval / rejection workflow
│   └── contact_messages.php          # Support inquiries inbox & email replies
│
├── config/                           # Database & App Configuration
│   └── database.php                  # PDO MySQL connection & fallback logic
│
├── includes/                         # Modular Component Partials
│   ├── header.php                    # Common HTML head, styles, and page titles
│   ├── navbar.php                    # Responsive navigation bar with role menus
│   ├── footer.php                    # Campus footer, social links, and scripts
│   └── auth.php                      # Auth helpers, CSRF tokens & input sanitization
│
├── assets/                           # Static Web Assets
│   ├── css/
│   │   ├── style.css                 # Main theme styles (Dark/Light Blue scheme)
│   │   └── dashboard.css             # Sidebar & dashboard portal styles
│   ├── js/
│   │   └── script.js                 # Search, filtering, validation, modals, print
│   └── images/
│       └── logo.png                  # CEMS institutional branding logo
│
├── uploads/                          # Dynamic File Storage
│   └── events/                       # Uploaded & sample event banner images
│
├── database/                         # Database Migration & Schema
│   └── college_event_management.sql  # Full MySQL schema & 10+ sample records
│
└── documentation/                    # Academic Documentation & Evidence
    ├── project_report.docx           # Complete 28-chapter formal project report
    └── screenshots/                  # 14 High-resolution application screenshots
        ├── 01_home_page.png
        ├── 02_about_page.png
        ├── 03_events_page.png
        ├── 04_student_registration.png
        ├── 05_student_login.png
        ├── 06_student_dashboard.png
        ├── 07_event_registration.png
        ├── 08_my_registrations.png
        ├── 09_contact_page.png
        ├── 10_admin_login.png
        ├── 11_admin_dashboard.png
        ├── 12_manage_events.png
        ├── 13_manage_students.png
        └── 14_manage_registrations.png
```

---

## 4. DATABASE CONFIGURATION & SAMPLE DATA

### Database Name: `college_event_management`
The database includes 5 relational tables normalized to 3NF:
1. `students` — Stores student profile, academic program, and hashed password.
2. `admins` — Stores administrator accounts.
3. `events` — Stores event titles, descriptions, categories, dates, venues, and seat quotas.
4. `registrations` — Links students to events with unique constraint on `(student_id, event_id)`.
5. `contact_messages` — Stores user inquiries and resolution statuses.

### Default Login Credentials for Demonstration:

| Role | Username / Email | Password | Access Portal |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` *(or `admin@college.edu`)* | `Admin@123` | `http://localhost/college_event_management/admin/login.php` |
| **Sample Student 1** | `aarav.sharma@student.edu` | `Student@123` | `http://localhost/college_event_management/login.php` |
| **Sample Student 2** | `diya.patel@student.edu` | `Student@123` | `http://localhost/college_event_management/login.php` |
| **Sample Student 3** | `rohan.verma@student.edu` | `Student@123` | `http://localhost/college_event_management/login.php` |

*(Note: Both login pages feature one-click **"Auto-Fill Demo"** buttons for evaluator convenience).*

---

## 5. STEP-BY-STEP EXECUTION GUIDE USING XAMPP

Follow these steps to run the application on your computer using XAMPP:

### Step 1: Install & Launch XAMPP
1. Download and install **XAMPP** (with PHP 8+ and MySQL) from [apachefriends.org](https://www.apachefriends.org/).
2. Open the **XAMPP Control Panel**.
3. Click the **Start** button next to **Apache**.
4. Click the **Start** button next to **MySQL**.
5. Ensure both modules turn green and show active port numbers (80/443 for Apache, 3306 for MySQL).

### Step 2: Copy Project Files to `htdocs`
1. Navigate to your XAMPP installation directory (typically `C:\xampp\htdocs\`).
2. Copy the entire `college_event_management` folder into `C:\xampp\htdocs\`.
3. The root index file should be located at:  
   `C:\xampp\htdocs\college_event_management\index.php`

### Step 3: Import the MySQL Database
1. Open your web browser and go to: `http://localhost/phpmyadmin/`
2. Click on the **Databases** tab at the top.
3. In the "Create database" field, type: `college_event_management` and click **Create**.
4. Select the newly created `college_event_management` database from the left sidebar.
5. Click on the **Import** tab in the top menu.
6. Click **Choose File** (Browse) and select the SQL file located at:  
   `C:\xampp\htdocs\college_event_management\database\college_event_management.sql`
7. Scroll to the bottom and click **Import** (or **Go**).
8. A success message will appear confirming all 5 tables and sample records have been created.

### Step 4: Open and Test the Application in Your Browser
- **Public Home Page:**  
  `http://localhost/college_event_management/index.php`
- **Events Catalog:**  
  `http://localhost/college_event_management/events.php`
- **Student Registration:**  
  `http://localhost/college_event_management/register.php`
- **Student Login:**  
  `http://localhost/college_event_management/login.php`
- **Administrator Login:**  
  `http://localhost/college_event_management/admin/login.php`
- **Administrator Dashboard:**  
  `http://localhost/college_event_management/admin/dashboard.php`

---

## 6. TESTING EVIDENCE (TEST CASES TC01 – TC22)

All 22 test cases specified in the project requirements were verified and passed:

| Test ID | Test Scenario | Steps | Expected Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC01** | Open Home Page | Navigate to `index.php` | Page loads with hero, live stats, and upcoming events | **PASS** |
| **TC02** | Navigate to About Us | Click "About Us" link | Institutional vision, mission, and committee display | **PASS** |
| **TC03** | Display Available Events | Navigate to `events.php` | All 8 events load dynamically from MySQL | **PASS** |
| **TC04** | Register New Student | Fill form with unique ID & email | Account created with Bcrypt hash; redirect to login | **PASS** |
| **TC05** | Check Duplicate Student Reg | Enter existing email / Student ID | Duplicate rejected with clear error alert | **PASS** |
| **TC06** | Login with Valid Credentials | Input valid email and password | Authenticated; redirected to student dashboard | **PASS** |
| **TC07** | Login with Invalid Credentials| Input wrong password | Error message: "Invalid email or password" | **PASS** |
| **TC08** | Open Student Dashboard | Visit `student/dashboard.php` | Dashboard shows KPI cards & recent registrations | **PASS** |
| **TC09** | Update Student Profile | Edit phone/dept in `profile.php` | Database record updated; session refreshed | **PASS** |
| **TC10** | Register for an Event | Click "Register Now" on event | Registration saved; seat inventory decremented by 1 | **PASS** |
| **TC11** | Prevent Duplicate Event Reg | Attempt second registration | System rejects duplicate with warning notice | **PASS** |
| **TC12** | Check Event Seat Availability | View event when seats = 0 | Badge displays "Seats Full"; button locked | **PASS** |
| **TC13** | Admin Login | Submit `admin` / `Admin@123` | Admin authenticated; redirected to admin dashboard | **PASS** |
| **TC14** | Add New Event | Submit form with banner image | Event inserted; image saved in `uploads/events/` | **PASS** |
| **TC15** | Edit Existing Event | Modify event venue & capacity | Changes immediately reflected on public pages | **PASS** |
| **TC16** | Delete Event | Click delete in `manage_events` | Event & associated registrations cascade deleted | **PASS** |
| **TC17** | View Student Records | Open `manage_students.php` | Filter by department; inspect registration modal | **PASS** |
| **TC18** | Manage Event Registrations | Toggle status to Approved/Rejected | Status updated in DB; reflected on student pass | **PASS** |
| **TC19** | Submit Contact Form | Submit message on `contact.php` | Message stored in `contact_messages`; alert shown | **PASS** |
| **TC20** | User / Admin Logout | Click "Sign Out" | Session destroyed; redirected to index page | **PASS** |
| **TC21** | Test Mobile Responsiveness | View on 375px - 768px viewports | Layout adapts fluidly; mobile menu functions | **PASS** |
| **TC22** | Test Database Connectivity | Verify PDO connection & query | PDO prepared statements execute reliably | **PASS** |

---

## 7. PROJECT DOCUMENTATION & SCREENSHOTS

- **Formal Academic Report:** Located at `documentation/project_report.docx`. Formatted with all 28 required academic chapters, including cover page, editable certificate, declaration, acknowledgement, abstract, system architecture, DFDs, ER diagrams, database schemas, implementation details, testing matrix, and references.
- **Visual Evidence:** All 14 high-resolution interface screenshots are stored in `documentation/screenshots/`.

---

Developed for **BCA (Data Science)** Academic Submission.

=======
# College-Event-Management
A web-based College Event Management System to manage college events, registrations and student participation.
>>>>>>> 09369d7af113c015d4db3874c9683ee28a25eef0
