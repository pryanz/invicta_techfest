<?php 
session_start();
require 'includes/db_connect.php'; 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Invicta 2025</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Sidebar & Layout */
        body { display: flex; background: #1a1a2e; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            width: 250px;
            background: #16213e;
            height: 100vh;
            position: fixed;
            padding: 20px;
            border-right: 1px solid #533483;
        }
        .logo { font-size: 24px; font-weight: bold; color: #e94560; margin-bottom: 40px; text-align: center; }
        .nav-links a { display: block; color: #a2a8d3; padding: 12px 15px; text-decoration: none; font-size: 16px; border-radius: 5px; transition: 0.3s; margin-bottom: 5px; }
        .nav-links a:hover, .nav-links a.active { background: #0f3460; color: #fff; padding-left: 20px; border-left: 4px solid #e94560; }
        
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); }
        
        /* Hero Section */
        .hero { text-align: center; margin-bottom: 60px; }
        .hero h1 { font-size: 4rem; margin: 0; color: #e94560; text-shadow: 0 0 20px rgba(233,69,96,0.5); }
        .hero p { font-size: 1.2rem; color: #a2a8d3; }
        
        .countdown { display: flex; justify-content: center; gap: 20px; margin-top: 30px; }
        .time-box { background: #0f3460; padding: 15px; border-radius: 8px; border: 1px solid #533483; min-width: 80px; }
        .time-box span { font-size: 2rem; font-weight: bold; display: block; color: #fff; }
        .time-box label { font-size: 0.8rem; color: #a2a8d3; text-transform: uppercase; }

        /* Grid & Cards */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        .card {
            background: #16213e;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #533483;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
        }
        .card:hover { transform: translateY(-5px); border-color: #e94560; box-shadow: 0 10px 20px rgba(0,0,0,0.3); }
        
        .card-img { height: 180px; background: #0f3460; position: relative; }
        .card-body { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .card-body h3 { margin: 0 0 10px 0; color: #fff; font-size: 1.4rem; }
        
        .btn { 
            display: block; 
            text-align: center;
            padding: 10px 20px; 
            background: #e94560; 
            color: white; 
            border-radius: 5px; 
            text-decoration: none; 
            font-size: 0.9rem; 
            margin-top: auto; /* Pushes button to bottom */
            font-weight: bold;
        }
        .btn:hover { background: #c72c41; }

        /* Event Details Styling */
        .event-meta {
            display: flex;
            justify-content: space-between;
            color: #fff;
            font-size: 0.9rem;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(83, 52, 131, 0.5);
        }
        .event-venue {
            color: #a2a8d3;
            font-size: 0.95rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; width: 100%; padding: 20px; }
        }
    </style>
</head>
<body>
    
    <div class="sidebar">
        <div class="logo">🚀 INVICTA</div>
        <div class="nav-links">
            <a href="#" class="active">Home</a>
            <a href="#events">Events</a>

            <!-- Show Accommodation tab ONLY for Participants -->
            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'participant'): ?>
                <a href="accommodation.php" style="color:#e94560;">🏠 Book Stay</a>
            <?php endif; ?>

            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard_<?php echo $_SESSION['role']; ?>.php" style="color: #4cd137;">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="index.php">Login / Register</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="main-content">
        
        <!-- HERO SECTION -->
        <div class="hero">
            <h1>INVICTA 2025</h1>
            <p>Innovate. Compete. Conquer.</p>
            
            <div class="countdown">
                <div class="time-box"><span id="days">00</span><label>Days</label></div>
                <div class="time-box"><span id="hours">00</span><label>Hours</label></div>
                <div class="time-box"><span id="minutes">00</span><label>Mins</label></div>
                <div class="time-box"><span id="seconds">00</span><label>Secs</label></div>
            </div>
            <p style="margin-top:20px; color:#a2a8d3;">Event Starts: Dec 10, 2025</p>
        </div>

        <!-- EVENTS SECTION -->
        <h2 id="events" style="border-bottom: 2px solid #e94560; display:inline-block; margin-bottom:30px; color: #fff;">Featured Events</h2>
        <div class="grid-container">
            <?php
            $sql = "SELECT * FROM events ORDER BY event_date ASC";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()):
                    // Image Logic
                    $imageFile = '';
                    if (!empty($row['image_path'])) {
                        $imageFile = 'assets/images/' . $row['image_path'];
                    }
                    $hasImage = (!empty($imageFile) && file_exists(__DIR__ . '/' . $imageFile));
            ?>
                <div class="card">
                    <div class="card-img">
                        <?php if ($hasImage): ?>
                            <img src="<?php echo $imageFile; ?>" alt="<?php echo htmlspecialchars($row['event_name']); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <div style="display:flex; align-items:center; justify-content:center; color:#533483; font-size:3rem; font-weight:bold; width:100%; height:100%; background:#e0e0e0;">
                                <?php echo strtoupper(substr($row['event_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($row['event_name']); ?></h3>
                        
                        <!-- UPDATED: Date & Time Display -->
                        <div class="event-meta">
                            <!-- Date: Dec 10, 2025 -->
                            <span>📅 <?php echo date('M d, Y', strtotime($row['event_date'])); ?></span>
                            <!-- Time: 02:00 PM (Handles SQL TIME type perfectly) -->
                            <span>⏰ <?php echo (!empty($row['event_time'])) ? date('h:i A', strtotime($row['event_time'])) : 'TBA'; ?></span>
                        </div>

                        <!-- UPDATED: Venue Display -->
                        <div class="event-venue">
                            📍 <?php echo htmlspecialchars($row['venue']); ?>
                        </div>

                        <p style="color:#a2a8d3; font-size:0.9rem; margin:10px 0; flex-grow: 1;">
                            <?php echo substr(htmlspecialchars($row['description']), 0, 80) . '...'; ?>
                        </p>

                        <!-- Action Button -->
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['role']=='participant'): ?>
                            <a href="dashboard_participant.php" class="btn">Register Team</a>
                        <?php elseif(!isset($_SESSION['user_id'])): ?>
                            <a href="index.php" class="btn" style="background:#533483;">Login to Join</a>
                        <?php else: ?>
                            <span style="color:#4cd137; text-align:center; display:block; margin-top:10px;">(Staff View)</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
                endwhile; 
            } else {
                echo "<p style='color:#a2a8d3'>No events scheduled yet.</p>";
            }
            ?>
        </div>

        <!-- ACCOMMODATION SECTION -->
        <h2 id="accommodation" style="border-bottom: 2px solid #e94560; display:inline-block; margin:60px 0 30px 0; color: #fff;">Accommodation Options</h2>
        <div class="grid-container">
            <?php
            // Logic: Join room_types + accommodation to calculate availability
            $sql_rooms = "SELECT room_type, cost, capacity, 
                          SUM(capacity - current_occupancy) as beds_left 
                          FROM accommodation 
                          GROUP BY room_type 
                          ORDER BY cost ASC";
            $rooms = $conn->query($sql_rooms);

            if ($rooms->num_rows > 0) {
                while($r = $rooms->fetch_assoc()):
            ?>
            <div class="card">
                <div class="card-body" style="text-align:center;">
                    <h3 style="color: #fff; margin-bottom: 10px;"><?php echo htmlspecialchars($r['room_type']); ?></h3>
                    
                    <p style="font-size:2rem; font-weight:bold; margin:10px 0; color: #4cd137;">
                        ₹<?php echo number_format($r['cost']); ?>
                    </p>
                    
                    <p style="color: #a2a8d3; margin-bottom: 15px;">
                        Capacity: <?php echo $r['capacity']; ?> Person(s) / Room
                    </p>

                    <?php if($r['beds_left'] > 0): ?>
                        <div style="display:inline-block; border: 1px solid #4cd137; color: #4cd137; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">
                            🟢 <?php echo $r['beds_left']; ?> Beds Available
                        </div>
                    <?php else: ?>
                        <div style="display:inline-block; border: 1px solid #e84118; color: #e84118; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">
                            🔴 SOLD OUT
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php 
                endwhile; 
            } else {
                echo "<p style='color: #a2a8d3;'>Accommodation details coming soon.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        var countDownDate = new Date("Dec 10, 2025 09:00:00").getTime();

        var x = setInterval(function() {
            var now = new Date().getTime();
            var distance = countDownDate - now;

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("days").innerHTML = days < 10 ? "0" + days : days;
            document.getElementById("hours").innerHTML = hours < 10 ? "0" + hours : hours;
            document.getElementById("minutes").innerHTML = minutes < 10 ? "0" + minutes : minutes;
            document.getElementById("seconds").innerHTML = seconds < 10 ? "0" + seconds : seconds;

            if (distance < 0) {
                clearInterval(x);
                document.querySelector(".countdown").innerHTML = "<h2 style='color:#4cd137'>EVENT STARTED!</h2>";
            }
        }, 1000);
    </script>
</body>
</html>