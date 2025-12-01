<?php
session_start();
require 'includes/db_connect.php';

// 1. SECURITY: Only Participants
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    header("Location: index.php");
    exit();
}

$p_id = $_SESSION['user_id'];
$message = "";

// --- LOGIC 1: CREATE TEAM ---
if (isset($_POST['create_team'])) {
    $tname = trim($_POST['team_name']);
    
    $stmt = $conn->prepare("INSERT INTO teams (tname, leader) VALUES (?, ?)");
    $stmt->bind_param("si", $tname, $p_id);
    
    if($stmt->execute()) {
        $new_tid = $stmt->insert_id;
        // Add Leader to Team Members
        $stmt2 = $conn->prepare("INSERT INTO forms (p_id, t_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $p_id, $new_tid);
        $stmt2->execute();
        $message = "<script>alert('✅ Team Created Successfully!');</script>";
    } else {
        $message = "<script>alert('❌ Error: Could not create team.');</script>";
    }
}

// --- LOGIC 2: ADD MEMBER ---
if (isset($_POST['add_member'])) {
    $email = trim($_POST['email']);
    $tid = $_POST['team_id'];
    
    // Find User
    $stmt = $conn->prepare("SELECT participant_id FROM participants WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        $new_pid = $row['participant_id'];
        
        // Check if already in team
        $check = $conn->query("SELECT * FROM forms WHERE p_id = $new_pid AND t_id = $tid");
        if($check->num_rows == 0) {
            $stmt2 = $conn->prepare("INSERT INTO forms (p_id, t_id) VALUES (?, ?)");
            $stmt2->bind_param("ii", $new_pid, $tid);
            if($stmt2->execute()){
                 $message = "<script>alert('✅ Member Added Successfully!');</script>";
            }
        } else {
            $message = "<script>alert('⚠️ User is already in this team!');</script>";
        }
    } else {
        $message = "<script>alert('❌ User email not found!');</script>";
    }
}

// --- LOGIC 3: REGISTER TEAM FOR EVENT (UPDATED) ---
if (isset($_POST['reg_team'])) {
    $tid = $_POST['team_id'];
    $eid = $_POST['event_id'];
    
    // A. Check if *I* am already participating in this event via ANY team
    $check_dup_sql = "SELECT r.team_id 
                      FROM registrations r
                      JOIN forms f ON r.team_id = f.t_id
                      WHERE f.p_id = ? AND r.event_id = ?";
    $stmt_dup = $conn->prepare($check_dup_sql);
    $stmt_dup->bind_param("ii", $p_id, $eid);
    $stmt_dup->execute();
    $res_dup = $stmt_dup->get_result();

    if ($res_dup->num_rows > 0) {
        $message = "<script>alert('⛔ Registration Blocked: You are already participating in this event with another team!');</script>";
    } else {
        // B. Verify I am the Leader of the selected team
        $chk = $conn->query("SELECT leader FROM teams WHERE team_id = $tid");
        $team_data = $chk->fetch_assoc();
        
        if($team_data && $team_data['leader'] == $p_id) {
            
            // C. Check if this specific team is already registered (Double check)
            $team_dup = $conn->query("SELECT * FROM registrations WHERE team_id=$tid AND event_id=$eid");
            
            if($team_dup->num_rows == 0) {
                // D. Proceed to Register
                $stmt = $conn->prepare("INSERT INTO registrations (team_id, event_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $tid, $eid);
                if($stmt->execute()) {
                    $message = "<script>alert('✅ Team Registered Successfully!');</script>";
                }
            } else {
                $message = "<script>alert('⚠️ This team is already registered for this event!');</script>";
            }
        } else {
            $message = "<script>alert('❌ Only the Team Leader can register the team!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard | Invicta</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* SIDEBAR LAYOUT */
        body { display: flex; background: #1a1a2e; font-family: 'Segoe UI', sans-serif; margin: 0; min-height: 100vh; }
        
        .sidebar {
            width: 250px;
            background: #16213e;
            height: 100vh;
            position: fixed;
            padding: 20px;
            border-right: 1px solid #533483;
            box-sizing: border-box;
        }
        .logo { font-size: 24px; font-weight: bold; color: #e94560; margin-bottom: 40px; text-align: center; }
        .nav-links a { display: block; color: #a2a8d3; padding: 12px 15px; text-decoration: none; font-size: 16px; border-radius: 5px; transition: 0.3s; margin-bottom: 5px; }
        .nav-links a:hover, .nav-links a.active { background: #0f3460; color: #fff; border-left: 4px solid #e94560; }
        
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); box-sizing: border-box; }

        /* CARDS & FORMS */
        .card {
            background: #16213e;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #533483;
            margin-bottom: 30px;
        }
        
        h2 { color: #fff; border-bottom: 1px solid #533483; padding-bottom: 10px; margin-top: 0; }
        label { display: block; color: #a2a8d3; margin-bottom: 5px; margin-top: 15px; }
        
        .form-control {
            width: 100%;
            padding: 10px;
            background: #0f3460;
            border: 1px solid #533483;
            color: white;
            border-radius: 5px;
            box-sizing: border-box; 
        }
        
        .btn { 
            padding: 10px 20px; 
            background: #e94560; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            margin-top: 15px; 
            font-weight: bold;
        }
        .btn:hover { background: #c72c41; }

        /* TABLE */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #533483; color: #fff; }
        th { background: #0f3460; color: #e94560; }
        
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; width: 100%; }
        }
    </style>
</head>
<body>
    
    <?php if($message) echo $message; ?>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo">🚀 INVICTA</div>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="#my-teams" class="active">My Teams</a>
            <a href="#register">Register Event</a>
            <a href="#my-events">Registered Events</a>
            <a href="logout.php" style="margin-top: 20px; color: #e94560;">Logout</a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h1 style="color: #e94560;">Student Dashboard</h1>
        <p style="color: #a2a8d3;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></p>

        <!-- SECTION 1: CREATE & MANAGE TEAMS -->
        <div class="card" id="my-teams">
            <h2>Manage My Teams</h2>
            
            <form method="POST" style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 20px;">
                <div style="flex-grow: 1;">
                    <label>Create New Team</label>
                    <input type="text" name="team_name" class="form-control" placeholder="Enter Team Name" required>
                </div>
                <button type="submit" name="create_team" class="btn" style="margin-bottom: 0;">Create</button>
            </form>
            
            <table>
                <tr>
                    <th>Team Name</th>
                    <th>Role</th>
                    <th>Members</th>
                    <th>Add Member</th>
                </tr>
                <?php
                $sql = "SELECT t.team_id, t.tname, t.leader 
                        FROM teams t 
                        JOIN forms f ON t.team_id = f.t_id 
                        WHERE f.p_id = $p_id";
                $res = $conn->query($sql);
                
                if($res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) {
                        $isLeader = ($row['leader'] == $p_id);
                        echo "<tr>";
                        echo "<td><b style='color:#e94560'>" . htmlspecialchars($row['tname']) . "</b></td>";
                        echo "<td>" . ($isLeader ? '👑 Leader' : 'Member') . "</td>";
                        
                        // Members
                        echo "<td>";
                        $mem_sql = "SELECT p.name FROM participants p JOIN forms f ON p.participant_id = f.p_id WHERE f.t_id = " . $row['team_id'];
                        $mems = $conn->query($mem_sql);
                        while($m = $mems->fetch_assoc()) echo htmlspecialchars($m['name']) . ", ";
                        echo "</td>";
                        
                        // Add Member Action
                        echo "<td>";
                        if($isLeader) {
                            echo "<form method='POST' style='display:flex; gap:5px;'>
                                    <input type='hidden' name='team_id' value='".$row['team_id']."'>
                                    <input type='email' name='email' placeholder='Email' class='form-control' style='padding:5px; height:35px;' required>
                                    <button type='submit' name='add_member' class='btn' style='padding:5px 10px; margin:0; height:35px;'>+</button>
                                  </form>";
                        } else {
                            echo "<span style='color:#666;'>N/A</span>";
                        }
                        echo "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>You haven't joined any teams yet. Create one above!</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- SECTION 2: REGISTER FOR EVENTS -->
        <div class="card" id="register">
            <h2>Register for Event</h2>
            <p style="color: #a2a8d3; margin-bottom: 15px;">Select an event and the team you want to participate with.</p>
            
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    
                    <!-- 1. Select Event -->
                    <div>
                        <label>Select Event</label>
                        <select name="event_id" class="form-control" required>
                            <option value="">-- Choose Event --</option>
                            <?php
                            $evts = $conn->query("SELECT * FROM events");
                            while($e = $evts->fetch_assoc()) {
                                echo "<option value='".$e['event_id']."'>".$e['event_name']." (" . $e['event_date'] . ")</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- 2. Select Team -->
                    <div>
                        <label>Select Your Team</label>
                        <select name="team_id" class="form-control" required>
                            <option value="">-- Choose Team --</option>
                            <?php
                            // Only fetching teams where I am leader
                            $myTeams = $conn->query("SELECT team_id, tname FROM teams WHERE leader = $p_id");
                            if($myTeams->num_rows > 0) {
                                while($t = $myTeams->fetch_assoc()) {
                                    echo "<option value='".$t['team_id']."'>".$t['tname']."</option>";
                                }
                            } else {
                                echo "<option disabled>You must Create a Team first</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <button type="submit" name="reg_team" class="btn" style="width: 100%;">Confirm Registration</button>
            </form>
        </div>
        
        <!-- SECTION 3: MY REGISTRATIONS (UPDATED WITH SCORE) -->
        <div class="card" id="my-events">
            <h2>Registered Events & Scores</h2>
            <table>
                <tr>
                    <th>Event Name</th>
                    <th>Participating Team</th>
                    <th>Date</th>
                    <th>Score</th> <!-- NEW COLUMN -->
                </tr>
                <?php
                // Updated Query to fetch score
                $reg_sql = "SELECT e.event_name, e.event_date, t.tname, r.score 
                            FROM registrations r
                            JOIN events e ON r.event_id = e.event_id
                            JOIN teams t ON r.team_id = t.team_id
                            JOIN forms f ON t.team_id = f.t_id
                            WHERE f.p_id = $p_id
                            ORDER BY e.event_date";
                $regs = $conn->query($reg_sql);
                
                if($regs->num_rows > 0) {
                    while($r = $regs->fetch_assoc()) {
                        // Handle Score Display
                        $score_display = isset($r['score']) ? "<b style='color:#4cd137'>".$r['score']."</b>" : "<i style='color:#a2a8d3'>Pending</i>";
                        
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($r['event_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($r['tname']) . "</td>";
                        echo "<td>" . htmlspecialchars($r['event_date']) . "</td>";
                        echo "<td>" . $score_display . "</td>"; // SHOW SCORE
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No upcoming registrations.</td></tr>";
                }
                ?>
            </table>
        </div>

    </div>
</body>
</html>