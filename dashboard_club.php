<?php 
// 1. ENABLE ERROR REPORTING (So we see the actual error)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// 2. CHECK FILE PATHS
if (!file_exists('includes/db_connect.php')) {
    die("❌ CRITICAL ERROR: Could not find 'includes/db_connect.php'. Check your folder structure.");
}
require 'includes/db_connect.php';

// 3. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'club') {
    die("❌ ACCESS DENIED: You are not logged in as a Club. Current Role: " . ($_SESSION['role'] ?? 'None'));
}

$my_club_id = $_SESSION['user_id'];
$message = "";

// 4. HANDLE FORM SUBMISSION DEBUGGING
if (isset($_POST['add_event'])) {
    echo "<div style='background:#f8d7da; padding:10px; border:1px solid red;'>";
    echo "<h3>🔍 DEBUGGING FORM SUBMISSION</h3>";
    
    $name = trim($_POST['name']);
    $coord_id = $_POST['coordinator_id']; 
    $date = $_POST['date'];
    $venue = trim($_POST['venue']);
    $desc = trim($_POST['desc']);

    echo "Data Received: Name=$name, Date=$date, Venue=$venue, Coord=$coord_id<br>";

    // --- IMAGE UPLOAD DEBUG ---
    $image_path = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "assets/images/";
        
        // Try to create directory
        if (!file_exists($target_dir)) {
            echo "Attempting to create folder '$target_dir'...<br>";
            if (!mkdir($target_dir, 0777, true)) {
                die("❌ ERROR: Failed to create 'assets/images/' folder. Check permissions.");
            }
        }
        
        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = time() . "_" . uniqid() . "." . $file_ext;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $new_filename)) {
            $image_path = $new_filename;
            echo "✅ Image uploaded successfully: $new_filename<br>";
        } else {
            echo "⚠️ Image upload failed (Check folder permissions).<br>";
        }
    } else {
        echo "ℹ️ No image uploaded or upload error.<br>";
    }

    // --- DATABASE INSERT DEBUG ---
    $sql = "INSERT INTO events (event_name, event_date, venue, description, club_id, coordinator_id, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        // THIS IS USUALLY WHERE IT FAILS
        die("❌ SQL PREPARE FAILED: " . $conn->error . "<br><b>Hint:</b> Check if your 'events' table actually has the 'image_path' column.");
    }

    $stmt->bind_param("ssssiis", $name, $date, $venue, $desc, $my_club_id, $coord_id, $image_path);
    
    if($stmt->execute()) {
        echo "<h3 style='color:green'>✅ SUCCESS! Event created.</h3>";
        echo "<script>setTimeout(()=>window.location.href='dashboard_club.php', 2000);</script>"; // Refresh after 2s
    } else {
        die("❌ SQL EXECUTE FAILED: " . $stmt->error);
    }
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Club Admin (Debug Mode)</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { display: block; background: #1a1a2e; }
        .main-content { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .navbar { background: #16213e; padding: 15px 30px; display: flex; justify-content: space-between; border-bottom: 2px solid #533483; }
        .card { background: #16213e; padding: 30px; border-radius: 12px; border: 1px solid #533483; margin-bottom: 40px; }
        .form-control { width: 100%; margin-bottom: 15px; }
        .btn { padding: 12px 25px; background: #e94560; color: white; border: none; cursor: pointer; }
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }
    </style>
</head>
<body>
    
    <nav class="navbar">
        <div style="font-size: 1.5rem; font-weight: bold; color: #e94560;">⚡ CLUB DASHBOARD (DEBUG)</div>
        <div class="nav-links">
            <a href="logout.php" style="background: #e94560; padding: 8px 15px; border-radius: 5px;">Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 20px; color: #e94560;">Create New Event</h3>
            
            <form method="POST" class="grid-form" enctype="multipart/form-data">
                
                <div class="full-width">
                    <label style="color:#a2a8d3">Event Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div>
                    <label style="color:#a2a8d3">Assign Coordinator</label>
                    <select name="coordinator_id" class="form-control" required>
                        <option value="">-- Select Student Head --</option>
                        <?php
                        $coords = $conn->query("SELECT coordinator_id, name FROM coordinators");
                        while($c = $coords->fetch_assoc()) {
                            echo "<option value='".$c['coordinator_id']."'>".$c['name']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label style="color:#a2a8d3">Event Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>

                <div>
                    <label style="color:#a2a8d3">Venue</label>
                    <input type="text" name="venue" class="form-control" required>
                </div>

                <div>
                    <label style="color:#a2a8d3">Event Banner</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                
                <div class="full-width">
                    <label style="color:#a2a8d3">Description</label>
                    <textarea name="desc" class="form-control" style="height: 100px;"></textarea>
                </div>
                
                <div class="full-width">
                    <button type="submit" name="add_event" class="btn">Create Event</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>