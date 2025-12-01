<?php 
// Enable error reporting to see file upload errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'includes/db_connect.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'club') {
    header("Location: index.php");
    exit();
}

$my_club_id = $_SESSION['user_id'];
$message = "";

// 2. HANDLE FORM SUBMISSION
if (isset($_POST['add_event'])) {
    
    $name = trim($_POST['name']);
    $coord_id = $_POST['coordinator_id']; 
    $judge_id = $_POST['judge_id'];
    $date = $_POST['date'];
    $venue = trim($_POST['venue']);
    $desc = trim($_POST['desc']);

    // --- IMAGE UPLOAD LOGIC (FIXED) ---
    $image_path = ""; 
    
    // Check if file was sent
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        
        // Use ABSOLUTE PATH to prevent "folder not found" errors
        $target_dir = __DIR__ . "/assets/images/";
        
        // Auto-create folder if missing
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                die("❌ FATAL ERROR: Failed to create 'assets/images/' folder. Check your permissions.");
            }
        }
        
        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if(in_array($file_ext, $allowed)) {
            // Generate unique name
            $new_filename = time() . "_" . uniqid() . "." . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            // Try to move file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $new_filename; // ✅ SUCCESS
            } else {
                $message = "<script>alert('❌ Error: Failed to move file. Is the folder writable?');</script>";
            }
        } else {
            $message = "<script>alert('❌ Error: Only JPG, PNG, and GIF files allowed.');</script>";
        }
    } else {
        // Debugging: Why was no file uploaded?
        $upload_err = $_FILES['image']['error'];
        if ($upload_err != 0 && $upload_err != 4) { // 4 means "No file selected"
            $message = "<script>alert('❌ Upload Error Code: $upload_err');</script>";
        }
    }

    // --- DATABASE INSERT ---
    // Only proceed if we have an image (or remove this check if image is optional)
    if (!empty($image_path) || empty($message)) {
        
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO events (event_name, event_date, venue, description, club_id, coordinator_id, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssiis", $name, $date, $venue, $desc, $my_club_id, $coord_id, $image_path);
            
            if ($stmt->execute()) {
                $new_event_id = $conn->insert_id; 

                // Assign Judge
                if (!empty($judge_id)) {
                    $stmt2 = $conn->prepare("INSERT INTO event_judges (event_id, judge_id) VALUES (?, ?)");
                    $stmt2->bind_param("ii", $new_event_id, $judge_id);
                    $stmt2->execute();
                }

                $conn->commit();
                $message = "<script>alert('✅ Event Created Successfully!'); window.location.href='dashboard_club.php';</script>";
            } else {
                throw new Exception($stmt->error);
            }

        } catch (Exception $e) {
            $conn->rollback();
            $message = "<script>alert('Database Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Club Admin | Invicta</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { display: block; background: #1a1a2e; }
        .main-content { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .navbar {
            background: #16213e;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #533483;
        }

        .card {
            background: #16213e;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #533483;
            margin-bottom: 40px;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #533483; color: #fff; vertical-align: middle; }
        th { background: #0f3460; color: #e94560; }

        .form-control { width: 100%; margin-bottom: 15px; }
        .btn { width: auto; padding: 12px 25px; background: #e94560; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #c72c41; }
        
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }

        input[type="file"] {
            background: #0f3460;
            padding: 10px;
            border: 1px solid #533483;
            border-radius: 5px;
            color: #a2a8d3;
        }
    </style>
</head>
<body>
    
    <?php if($message) echo $message; ?>

    <nav class="navbar">
        <div style="font-size: 1.5rem; font-weight: bold; color: #e94560;">⚡ CLUB DASHBOARD</div>
        <div class="nav-links">
            <a href="home.php">View Site</a>
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
                    <label style="color:#a2a8d3">Assign Judge</label>
                    <select name="judge_id" class="form-control">
                        <option value="">-- Select Judge --</option>
                        <?php
                        $judges = $conn->query("SELECT judge_id, name FROM judges");
                        while($j = $judges->fetch_assoc()) {
                            echo "<option value='".$j['judge_id']."'>".$j['name']."</option>";
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

                <div class="full-width">
                    <label style="color:#a2a8d3">Event Banner</label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
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

        <h3 style="color: #a2a8d3; border-bottom: 1px solid #533483; padding-bottom: 10px;">My Events</h3>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Event</th>
                    <th>Coordinator</th>
                    <th>Judge</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT e.*, c.name as coord_name, j.name as judge_name 
                        FROM events e 
                        LEFT JOIN coordinators c ON e.coordinator_id = c.coordinator_id 
                        LEFT JOIN event_judges ej ON e.event_id = ej.event_id 
                        LEFT JOIN judges j ON ej.judge_id = j.judge_id
                        WHERE e.club_id = $my_club_id 
                        ORDER BY e.event_date DESC";
                $res = $conn->query($sql);
                
                if($res->num_rows > 0) {
                    while($row = $res->fetch_assoc()): 
                        // Retrieval Logic
                        $img_filename = $row['image_path'];
                        $img_src = "assets/images/" . $img_filename;
                        
                        // Check if file actually exists on server
                        $has_img = (!empty($img_filename) && file_exists($img_src));
                    ?>
                    <tr>
                        <td>
                            <?php if($has_img): ?>
                                <img src="<?php echo $img_src; ?>" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #533483;">
                            <?php else: ?>
                                <span style="color:#666; font-size:0.8rem;">No Img</span>
                            <?php endif; ?>
                        </td>
                        <td><b style="color: #e94560;"><?php echo htmlspecialchars($row['event_name']); ?></b></td>
                        <td><?php echo htmlspecialchars($row['coord_name']); ?></td>
                        <td><?php echo $row['judge_name'] ? htmlspecialchars($row['judge_name']) : '-'; ?></td>
                        <td><?php echo date("M d", strtotime($row['event_date'])); ?></td>
                    </tr>
                    <?php endwhile; 
                } else {
                    echo "<tr><td colspan='5'>No events found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
