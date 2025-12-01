<?php require 'includes/db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Invicta 2025</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">🚀 INVICTA</div>
        <div class="nav-links">
            <a href="#" class="active">Home</a>
            <a href="#events">Events</a>
            <a href="#accommodation">Stay</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard_<?php echo $_SESSION['role']; ?>.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="index.php">Login / Register</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="main-content">
        <div class="hero">
            <h1 style="color: #e94560;">INVICTA 2025</h1>
            <p>Innovate. Compete. Conquer.</p>
            
            <div class="countdown">
                <div class="time-box"><span id="days">00</span><label>Days</label></div>
                <div class="time-box"><span id="hours">00</span><label>Hours</label></div>
                <div class="time-box"><span id="minutes">00</span><label>Mins</label></div>
                <div class="time-box"><span id="seconds">00</span><label>Secs</label></div>
            </div>
            <p style="margin-top:20px; color:#a2a8d3;">Event Starts: Dec 10, 2025</p>
        </div>

        <h2 id="events" style="border-bottom: 2px solid var(--accent); display:inline-block; margin-bottom:20px;">Featured Events</h2>
        <div class="grid-container">
            <?php
            $sql = "SELECT * FROM events ";
            $result = $conn->query($sql);
            while($row = $result->fetch_assoc()):
            ?>
                <div class="card">
                    <div class="card-img">
                        <?php
                        // Prefer an uploaded image if available, otherwise show the first letter
                        $imageFile = '';
                        if (!empty($row['image_path'])) {
                            $imageFile = 'assets/images/' . basename($row['image_path']);
                            $fsPath = __DIR__ . '/' . $imageFile;
                        }

                        if (!empty($imageFile) && file_exists($fsPath)) : ?>
                            <img src="<?php echo $imageFile; ?>" alt="<?php echo htmlspecialchars($row['event_name']); ?>" style="width:100%; height:100%; object-fit:cover; display:block;">
                        <?php else: ?>
                            <div style="display:flex; align-items:center; justify-content:center; color:#533483; font-size:3rem; font-weight:bold; width:100%; height:100%;">
                                <?php echo strtoupper(substr($row['event_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3><?php echo $row['event_name']; ?></h3>
                        <p style="color:#a2a8d3; font-size:0.9rem; margin:10px 0;"><?php echo $row['description']; ?></p>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            
                            <small><?php echo date('M d, h:i A', strtotime($row['event_date'])); ?></small>
                        </div>
                        <?php if(isset($_SESSION['participant_id']) && $_SESSION['role']=='participant'): ?>
                            <a href="dashboard_participant.php" class="btn">Register Team</a>
                        <?php else: ?>
                            <a href="auth.php" class="btn" style="background:#533483;">Login to Join</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <h2 id="accommodation" style="border-bottom: 2px solid var(--accent); display:inline-block; margin:40px 0 20px 0;">Accommodation</h2>
        <div class="grid-container">
            <?php
            $rooms = $conn->query("SELECT * FROM accommodation");
            while($r = $rooms->fetch_assoc()):
            ?>
            <div class="card">
                <div class="card-body" style="text-align:center;">
                    <h3 style="color:var(--light);"><?php echo $r['room_type']; ?></h3>
                    <p style="font-size:1.5rem; font-weight:bold; margin:10px 0;">₹<?php echo $r['cost_per_night']; ?></p>
                    <p><?php echo $r['location']; ?></p>
                    <small>Capacity: <?php echo $r['capacity']; ?> Person(s)</small>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        // Set the date we're counting down to (Dec 10, 2025)
        var countDownDate = new Date("Dec 10, 2025 09:00:00").getTime();

        var x = setInterval(function() {
            var now = new Date().getTime();
            var distance = countDownDate - now;

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("days").innerHTML = days;
            document.getElementById("hours").innerHTML = hours;
            document.getElementById("minutes").innerHTML = minutes;
            document.getElementById("seconds").innerHTML = seconds;

            if (distance < 0) {
                clearInterval(x);
                document.querySelector(".countdown").innerHTML = "EVENT STARTED!";
            }
        }, 1000);
    </script>
</body>
</html>