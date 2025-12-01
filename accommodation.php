<?php
session_start();
require 'includes/db_connect.php';

// 1. SECURITY: Only Participants can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    header("Location: index.php");
    exit();
}

$p_id = $_SESSION['user_id'];

// 2. CHECK EXISTING BOOKING
// Join 3 tables to get details: Bookings -> Accommodation -> Room Types
$sql_check = "SELECT b.*, a.room_number, rt.type_name, rt.cost 
            FROM bookings b 
            JOIN accommodation a ON b.room_id = a.room_id 
            JOIN room_types rt ON a.type_id = rt.type_id
            WHERE b.participant_id = $p_id";
$res_check = $conn->query($sql_check);
$existing_booking = $res_check->fetch_assoc();

// 3. FETCH ROOM TYPES
// Group by type to show categories (e.g. "Triple Sharing")
$types_sql = "SELECT rt.type_id, rt.type_name, rt.cost, 
              (SUM(rt.capacity) - SUM(a.current_occupancy)) as beds_available
              FROM room_types rt
              JOIN accommodation a ON rt.type_id = a.type_id
              GROUP BY rt.type_id
              HAVING beds_available > 0";
$types_res = $conn->query($types_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Accommodation | Invicta</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* SIDEBAR LAYOUT (Matches Home & Dashboard) */
        body { 
            display: flex; 
            background: #1a1a2e; 
            font-family: 'Segoe UI', sans-serif; 
            margin: 0; 
            min-height: 100vh; 
        }
        
        .sidebar {
            width: 250px;
            background: #16213e;
            height: 100vh;
            position: fixed;
            padding: 20px;
            border-right: 1px solid #533483;
            box-sizing: border-box;
        }
        .logo { 
            font-size: 24px; 
            font-weight: bold; 
            color: #e94560; 
            margin-bottom: 40px; 
            text-align: center; 
        }
        
        .nav-links a { 
            display: block; 
            color: #a2a8d3; 
            padding: 12px 15px; 
            text-decoration: none; 
            font-size: 16px; 
            border-radius: 5px; 
            transition: 0.3s; 
            margin-bottom: 5px; 
        }
        .nav-links a:hover, .nav-links a.active { 
            background: #0f3460; 
            color: #fff; 
            border-left: 4px solid #e94560; 
        }
        
        /* MAIN CONTENT AREA */
        .main-content { 
            margin-left: 250px; 
            padding: 40px; 
            width: calc(100% - 250px); 
            box-sizing: border-box; 
            max-width: 1000px; 
        }

        /* CARD STYLES */
        .card { 
            background: #16213e; 
            padding: 30px; 
            border-radius: 12px; 
            border: 1px solid #533483; 
            margin-bottom: 40px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        
        /* Form Styling */
        label { 
            display: block; 
            margin-bottom: 8px; 
            color: #a2a8d3; 
            font-weight: 500; 
        }
        .form-control { 
            width: 100%; 
            padding: 12px; 
            background: #0f3460; 
            border: 1px solid #533483; 
            color: white; 
            border-radius: 5px; 
            box-sizing: border-box; 
        }
        .btn { 
            width: 100%; 
            padding: 15px; 
            background: #e94560; 
            color: white; 
            border: none; 
            font-size: 1.1rem; 
            border-radius: 5px; 
            cursor: pointer; 
            margin-top: 20px; 
        }
        .btn:hover { background: #c72c41; }

        /* Grid for Dates */
        .date-row { 
            display: flex; 
            gap: 20px; 
            margin-top: 20px; 
        }
        .date-col { flex: 1; }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; width: 100%; padding: 20px; }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo">🚀 INVICTA</div>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="#" class="active">Accommodation</a>
            <a href="dashboard_participant.php">Dashboard</a>
            <a href="logout.php" style="margin-top: 20px; color: #e94560;">Logout</a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <h1 style="color: #e94560; margin-top: 0;">Accommodation Booking</h1>
        <p style="color: #a2a8d3; margin-bottom: 30px;">Secure your stay for the duration of the fest.</p>

        <?php if ($existing_booking): ?>
            <!-- BOOKING CONFIRMED TICKET -->
            <div class="card" style="border-left: 5px solid #4cd137;">
                <h2 style="color: #4cd137; margin-top: 0;">✅ Room Allocated: <?php echo $existing_booking['room_number']; ?></h2>
                <div style="font-size: 1.1rem; line-height: 1.8; color: #fff;">
                    <p><strong>Category:</strong> <?php echo $existing_booking['type_name']; ?></p>
                    <p><strong>Cost:</strong> ₹<?php echo $existing_booking['cost']; ?></p>
                    <p><strong>Check-in:</strong> <?php echo date("d M Y", strtotime($existing_booking['checkin_date'])); ?></p>
                    <p><strong>Check-out:</strong> <?php echo date("d M Y", strtotime($existing_booking['checkout_date'])); ?></p>
                </div>
                <button onclick="window.print()" class="btn" style="background:#533483; width: auto; padding: 10px 30px;">Download Ticket</button>
            </div>
        <?php else: ?>
            
            <!-- BOOKING FORM -->
            <div class="card">
                <h2 style="color: #fff; border-bottom: 1px solid #533483; padding-bottom: 15px; margin-top: 0;">Book Your Stay</h2>
                
                <form action="book_room.php" method="POST">
                    
                    <!-- Room Selection -->
                    <div style="margin-top: 20px;">
                        <label>Select Room Category</label>
                        <select name="type_id" class="form-control" required style="cursor: pointer;">
                            <option value="">-- Choose Room Type --</option>
                            <?php while($t = $types_res->fetch_assoc()): ?>
                                <option value="<?php echo $t['type_id']; ?>">
                                    <?php echo $t['type_name']; ?> (₹<?php echo $t['cost']; ?>) 
                                    - <?php echo $t['beds_available']; ?> beds left
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Date Selection (Side by Side) -->
                    <div class="date-row">
                        <div class="date-col">
                            <label>Check-in Date</label>
                            <input type="date" name="checkin" class="form-control" min="2025-12-10" required>
                        </div>
                        <div class="date-col">
                            <label>Check-out Date</label>
                            <input type="date" name="checkout" class="form-control" min="2025-12-10" required>
                        </div>
                    </div>

                    <button type="submit" name="book_room" class="btn">Confirm Booking</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
