<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    header("Location: index.php");
    exit();
}

$p_id = $_SESSION['user_id'];

// 1. CHECK EXISTING BOOKING (Join 3 tables to get details)
$sql_check = "SELECT b.*, a.room_number, rt.type_name, rt.cost 
              FROM bookings b 
              JOIN accommodation a ON b.room_id = a.room_id 
              JOIN room_types rt ON a.type_id = rt.type_id
              WHERE b.participant_id = $p_id";
$res_check = $conn->query($sql_check);
$existing_booking = $res_check->fetch_assoc();

// 2. FETCH ROOM TYPES (Only show categories that have at least 1 empty bed)
// We sum up the capacity vs occupancy to see if the whole category is sold out
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
</head>
<body>
    <!-- Navbar Code Here... -->

    <div class="main-content" style="max-width:800px; margin:40px auto;">
        
        <?php if ($existing_booking): ?>
            <div class="card" style="border-left: 5px solid #4cd137;">
                <h2>✅ Room Allocated: <?php echo $existing_booking['room_number']; ?></h2>
                <p><strong>Type:</strong> <?php echo $existing_booking['type_name']; ?></p>
                <p><strong>Check-in:</strong> <?php echo $existing_booking['checkin_date']; ?></p>
                <button onclick="window.print()" class="btn">Download Ticket</button>
            </div>
        <?php else: ?>
            
            <div class="card" style="padding:30px;">
                <h2>Book Accommodation</h2>
                <form action="book_room.php" method="POST">
                    
                    <div style="margin-bottom:15px;">
                        <label style="color:#a2a8d3;">Select Room Category</label>
                        <select name="type_id" class="form-control" required>
                            <option value="">-- Choose Type --</option>
                            <?php while($t = $types_res->fetch_assoc()): ?>
                                <option value="<?php echo $t['type_id']; ?>">
                                    <?php echo $t['type_name']; ?> (₹<?php echo $t['cost']; ?>) 
                                    - <?php echo $t['beds_available']; ?> beds left
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div style="display:flex; gap:10px;">
                        <input type="date" name="checkin" class="form-control" required>
                        <input type="date" name="checkout" class="form-control" required>
                    </div>

                    <button type="submit" name="book_room" class="btn" style="margin-top:20px; width:100%;">Confirm Booking</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>