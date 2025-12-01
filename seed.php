<?php
// seed.php - POPULATE DATABASE WITH DUMMY DATA
require 'includes/db_connect.php';

// CSS for nice output
echo "<style>body{background:#1a1a2e; color:#fff; font-family:sans-serif; padding:20px;} .success{color:#4cd137;} .error{color:#e84118;} .info{color:#a2a8d3;}</style>";
echo "<h1>🌱 Seeding Database...</h1>";

// 1. CLEANUP (Wipe existing data to prevent duplicates)
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$tables = [
    'registrations', 'event_judges', 'forms', 'bookings', 'teams', 'events', 
    'accommodation', 'room_types', 'judges', 'coordinators', 'clubs', 'mentors', 'participants', 'sponsors'
];

foreach ($tables as $table) {
    if ($conn->query("TRUNCATE TABLE $table")) {
        echo "<div class='info'>Cleared table: $table</div>";
    } else {
        echo "<div class='error'>Error clearing $table: " . $conn->error . "</div>";
    }
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "<hr>";

// ======================================================
// 2. INSERT INDEPENDENT DATA (Level 1)
// ======================================================

// --- A. PARTICIPANTS ---
// Password for everyone is '12345'
$sql = "INSERT INTO participants (name, email, password, phone, college, department, year) VALUES 
('Rahul Sharma', 'rahul@test.com', '12345', '9876543210', 'IIT Bombay', 'CSE', '3rd'),
('Priya Verma', 'priya@test.com', '12345', '9123456780', 'NIT Trichy', 'ECE', '2nd'),
('Amit Kumar', 'amit@test.com', '12345', '9988776655', 'BITS Pilani', 'MECH', '4th'),
('Sara Ali', 'sara@test.com', '12345', '8877665544', 'VIT', 'CSE', '1st')";

if($conn->query($sql)) echo "<div class='success'>✅ Added Participants (User: rahul@test.com / 12345)</div>";
else echo "<div class='error'>❌ Participants Error: " . $conn->error . "</div>";

// --- B. MENTORS ---
$sql = "INSERT INTO mentors (name, email, password, department, phone, designation) VALUES 
('Dr. Anjali Gupta', 'anjali@faculty.edu', '12345', 'CSE', '9876500001', 'Assistant Professor'),
('Prof. Vikram Singh', 'vikram@faculty.edu', '12345', 'ECE', '9876500002', 'HOD')";

if($conn->query($sql)) echo "<div class='success'>✅ Added Mentors (User: anjali@faculty.edu / 12345)</div>";

// --- C. COORDINATORS ---
$sql = "INSERT INTO coordinators (name, email, password, phone) VALUES 
('Rohan Das', 'rohan@coord.com', '12345', '7778889990'),
('Sneha Roy', 'sneha@coord.com', '12345', '6665554440')";

if($conn->query($sql)) echo "<div class='success'>✅ Added Coordinators (User: rohan@coord.com / 12345)</div>";

// --- D. CLUBS ---
$sql = "INSERT INTO clubs (club_name, email, password, description) VALUES 
('Coding Club', 'code@club.com', '12345', 'For the love of algorithms and coffee.'),
('Robotics Club', 'robo@club.com', '12345', 'Building the future, one bot at a time.')";

if($conn->query($sql)) echo "<div class='success'>✅ Added Clubs (User: code@club.com / 12345)</div>";

// --- E. JUDGES ---
$sql = "INSERT INTO judges (name, affiliation, expertise, email, phone, password) VALUES 
('Sundar Pichai', 'Google', 'Software Engineering', 'sundar@google.com', '1010101010', '12345'),
('Elon Musk', 'SpaceX', 'Robotics & AI', 'elon@spacex.com', '2020202020', '12345')";

if($conn->query($sql)) echo "<div class='success'>✅ Added Judges (User: sundar@google.com / 12345)</div>";

// --- F. ROOM TYPES (Accommodation Menu) ---
$sql = "INSERT INTO room_types (type_name, cost, capacity) VALUES 
('Dormitory (Non-AC)', 500.00, 4),
('Double Sharing (AC)', 1200.00, 2),
('Single Luxury', 2500.00, 1)";

if($conn->query($sql)) echo "<div class='success'>✅ Added Room Types</div>";

// --- G. SPONSORS ---
$sql = "INSERT INTO sponsors (organization_name, phone, email) VALUES 
('Red Bull', '1112223333', 'sponsor@redbull.com'),
('GitHub', '4445556666', 'community@github.com')";

if($conn->query($sql)) echo "<div class='success'>✅ Added Sponsors</div>";


// ======================================================
// 3. INSERT DEPENDENT DATA (Level 2)
// ======================================================

// --- H. ACCOMMODATION (Inventory) ---
// Room IDs will be 1, 2, 3, 4, 5...
$sql = "INSERT INTO accommodation (room_number, type_id, current_occupancy) VALUES 
('H-101', 1, 0), -- Dorm
('H-102', 1, 0), -- Dorm
('A-201', 2, 0), -- Double
('A-202', 2, 0), -- Double
('VIP-1', 3, 0)"; 

if($conn->query($sql)) echo "<div class='success'>✅ Added Physical Rooms (Inventory)</div>";

// --- I. EVENTS ---
// Note: We leave image_path NULL or put a placeholder if you have one
$sql = "INSERT INTO events (event_name, event_date, event_time, venue, description, club_id, coordinator_id, image_path) VALUES 
('Hackathon 2025', '2025-12-10', '09:00:00', 'Main Auditorium', '24-hour coding marathon to solve real-world problems.', 1, 1, NULL),
('RoboWar', '2025-12-11', '14:00:00', 'Open Ground', 'Battle of the bots. May the best metal win.', 2, 2, NULL),
('Code Debugging', '2025-12-12', '10:00:00', 'Computer Lab 3', 'Find the bug, win the mug.', 1, 1, NULL)";

if($conn->query($sql)) echo "<div class='success'>✅ Added Events</div>";


// ======================================================
// 4. INSERT LINKING DATA (Level 3 - The Glue)
// ======================================================

// --- J. TEAMS ---
// Leader 1 (Rahul) -> Mentor 1
// Leader 2 (Priya) -> Mentor 2
$sql = "INSERT INTO teams (tname, leader, mentor) VALUES 
('Code Warriors', 1, 1), 
('Mecha Titans', 2, 2)";

if($conn->query($sql)) echo "<div class='success'>✅ Added Teams</div>";

// --- K. FORMS (Team Members) ---
// Team 1 Members: Rahul(1), Amit(3)
// Team 2 Members: Priya(2), Sara(4)
$sql = "INSERT INTO forms (p_id, t_id) VALUES 
(1, 1), (3, 1), 
(2, 2), (4, 2)";

if($conn->query($sql)) echo "<div class='success'>✅ Added Team Members</div>";

// --- L. REGISTRATIONS (Teams -> Events) ---
// Code Warriors -> Hackathon (ID 1)
// Mecha Titans -> RoboWar (ID 2)
$sql = "INSERT INTO registrations (team_id, event_id, score) VALUES 
(1, 1, NULL),
(2, 2, 85)"; // Pre-assign a score to test Judge Dashboard

if($conn->query($sql)) echo "<div class='success'>✅ Registered Teams for Events</div>";

// --- M. EVENT JUDGES ---
// Sundar(1) judges Hackathon(1)
// Elon(2) judges RoboWar(2)
$sql = "INSERT INTO event_judges (event_id, judge_id) VALUES 
(1, 1),
(2, 2)";

if($conn->query($sql)) echo "<div class='success'>✅ Assigned Judges to Events</div>";

// --- N. BOOKINGS ---
// Rahul(1) books Room 1 (Dorm H-101)
$sql = "INSERT INTO bookings (participant_id, room_id, checkin_date, checkout_date) VALUES 
(1, 1, '2025-12-10', '2025-12-12')";

// Update occupancy for that room
$conn->query("UPDATE accommodation SET current_occupancy = 1 WHERE room_id = 1");

if($conn->query($sql)) echo "<div class='success'>✅ Created Accommodation Booking</div>";

echo "<hr><h2>🎉 Database Seeded Successfully!</h2>";
echo "<p><a href='index.php' style='color:#4cd137; font-size:1.2rem;'>Go to Login Page &rarr;</a></p>";
?>