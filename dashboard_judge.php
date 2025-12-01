<?php
session_start();
require 'includes/db_connect.php';

// 1. SECURITY: Strict Judge Only Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'judge') {
    header("Location: index.php");
    exit();
}

$j_id = $_SESSION['user_id'];
$message = "";

// 2. HANDLE SCORE UPDATE
if (isset($_POST['update_score'])) {
    $team_id = $_POST['team_id'];
    $event_id = $_POST['event_id'];
    $score = intval($_POST['score']); 

    $stmt = $conn->prepare("UPDATE registrations SET score = ? WHERE team_id = ? AND event_id = ?");
    $stmt->bind_param("iii", $score, $team_id, $event_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert success'>✅ Score updated for Team #$team_id</div>";
    } else {
        $message = "<div class='alert error'>❌ Error updating score.</div>";
    }
}

// 3. PERSIST SELECTED EVENT
$selected_event_id = isset($_REQUEST['event_id']) ? $_REQUEST['event_id'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Judge Grading Panel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #1a1a2e; display: block; }
        .main-content { max-width: 1000px; margin: 40px auto; padding: 20px; }
        
        /* Navbar */
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
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #533483;
            margin-bottom: 30px;
        }

        /* Grading Table */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #533483; color: #fff; vertical-align: middle; }
        th { background: #0f3460; color: #e94560; }
        tr:hover { background: rgba(255,255,255,0.05); }

        /* Inputs */
        .event-select {
            width: 100%;
            padding: 15px;
            background: #0f3460;
            color: white;
            border: 1px solid #533483;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
        }
        .score-input {
            width: 80px;
            padding: 8px;
            background: #1a1a2e;
            border: 1px solid #e94560;
            color: #fff;
            text-align: center;
            font-weight: bold;
            border-radius: 4px;
        }
        .btn-update {
            padding: 8px 15px;
            background: #e94560;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-update:hover { background: #c72c41; }

        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .success { background: rgba(76, 209, 55, 0.2); border: 1px solid #4cd137; color: #4cd137; }
        .error { background: rgba(232, 65, 24, 0.2); border: 1px solid #e84118; color: #e84118; }
        
        /* Logout Button Style */
        .btn-logout {
            background: #e94560; 
            padding: 8px 15px; 
            border-radius: 5px; 
            color: white; 
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-logout:hover { background: #c72c41; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div style="font-size: 1.5rem; font-weight: bold; color: #e94560;">⚖️ JUDGE PANEL</div>
        
        <!-- LOGOUT WITH CONFIRMATION -->
        <a href="logout.php" class="btn-logout" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </nav>

    <div class="main-content">
        
        <?php if($message) echo $message; ?>

        <!-- STEP 1: SELECT EVENT -->
        <div class="card">
            <h3 style="color:#a2a8d3; margin-bottom: 10px;">Select Event to Grade</h3>
            <form method="GET">
                <select name="event_id" class="event-select" onchange="this.form.submit()">
                    <option value="0">-- Choose Assigned Event --</option>
                    <?php
                    // Logic: Get events linked to this Judge via event_judges table
                    $sql = "SELECT e.event_id, e.event_name, e.event_date 
                            FROM events e 
                            JOIN event_judges ej ON e.event_id = ej.event_id 
                            WHERE ej.judge_id = $j_id";
                    $res = $conn->query($sql);
                    while($row = $res->fetch_assoc()) {
                        $sel = ($row['event_id'] == $selected_event_id) ? 'selected' : '';
                        echo "<option value='".$row['event_id']."' $sel>".$row['event_name']." (" . $row['event_date'] . ")</option>";
                    }
                    ?>
                </select>
            </form>
        </div>

        <!-- STEP 2: GRADING TABLE (Only shows if event selected) -->
        <?php if($selected_event_id > 0): ?>
            
            <?php
            // Get Event Name for Title
            $evt_q = $conn->query("SELECT event_name FROM events WHERE event_id = $selected_event_id");
            $evt_name = $evt_q->fetch_assoc()['event_name'];
            ?>

            <div class="card">
                <h2 style="color: #fff; border-bottom: 1px solid #533483; padding-bottom: 15px; margin-bottom: 0;">
                    Grading: <span style="color: #e94560;"><?php echo htmlspecialchars($evt_name); ?></span>
                </h2>
                
                <table>
                    <thead>
                        <tr>
                            <th>Team ID</th>
                            <th>Team Name</th>
                            <th>Score (0-100)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Logic: Get Teams registered for this specific event
                        // Join 'teams' with 'registrations'
                        $teams_sql = "SELECT t.team_id, t.tname, r.score 
                                      FROM teams t 
                                      JOIN registrations r ON t.team_id = r.team_id 
                                      WHERE r.event_id = $selected_event_id 
                                      ORDER BY t.tname ASC";
                        $teams = $conn->query($teams_sql);

                        if($teams->num_rows > 0) {
                            while($t = $teams->fetch_assoc()):
                            ?>
                            <tr>
                                <td>#<?php echo $t['team_id']; ?></td>
                                <td style="font-weight:bold; font-size:1.1rem;"><?php echo htmlspecialchars($t['tname']); ?></td>
                                
                                <!-- Individual Form for each row -->
                                <form method="POST">
                                    <td>
                                        <input type="hidden" name="team_id" value="<?php echo $t['team_id']; ?>">
                                        <input type="hidden" name="event_id" value="<?php echo $selected_event_id; ?>">
                                        <input type="number" name="score" class="score-input" 
                                               value="<?php echo $t['score'] ? $t['score'] : 0; ?>" 
                                               min="0" max="100" required>
                                    </td>
                                    <td>
                                        <button type="submit" name="update_score" class="btn-update">Update</button>
                                    </td>
                                </form>
                            </tr>
                            <?php endwhile; 
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center; padding:30px; color:#a2a8d3;'>No teams have registered for this event yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>