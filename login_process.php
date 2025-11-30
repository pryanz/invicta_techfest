<?php
session_start();
require 'includes/db_connect.php';

if (isset($_POST['btn_login'])) {
    $role = $_POST['role'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Select Table based on Role
    $table = "";
    $id_column = "";
    
    switch ($role) {
        case 'participant':
            $table = "participants";
            $id_column = "participant_id";
            break;
        case 'coordinator':
            $table = "coordinators";
            $id_column = "coordinator_id";
            break;
        case 'mentor':
            $table = "mentors";
            $id_column = "mentor_id";
            break;
        case 'club':
            $table = "clubs";
            $id_column = "club_id";
            break;
        default:
            die("Invalid Role Selected");
    }

    // 2. Check Database
    $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        
        // 3. Verify Password (Direct comparison for now)
        if ($password === $row['password']) {
            
            // --- LOGIN SUCCESS ---
            $_SESSION['user_id'] = $row[$id_column];
            $_SESSION['role'] = $role;
            $_SESSION['name'] = ($role == 'club') ? $row['club_name'] : $row['name'];

            // 4. DISPLAY WELCOME MESSAGE & REDIRECT
            // We use simple HTML/CSS to make it look nice while waiting
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Login Successful</title>
                <style>
                    body {
                        background-color: #1a1a2e;
                        color: #ffffff;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        font-family: sans-serif;
                        text-align: center;
                    }
                    .box {
                        background: #16213e;
                        padding: 40px;
                        border-radius: 10px;
                        border: 2px solid #4cd137;
                        box-shadow: 0 0 20px rgba(76, 209, 55, 0.3);
                    }
                    h1 { color: #4cd137; }
                </style>
            </head>
            <body>
                <div class="box">
                    <h1>✅ Login Successful!</h1>
                    <p>Welcome back, <b>' . htmlspecialchars($_SESSION['name']) . '</b>.</p>
                    <p>Redirecting you to the Home Page...</p>
                </div>
                
                <script>
                    setTimeout(function() {
                        window.location.href = "home.php";
                    }, 2000); // 2 seconds delay
                </script>
            </body>
            </html>';
            exit();

        } else {
            echo "<script>alert('Incorrect Password!'); window.location='index.php';</script>";
        }
    } else {
        echo "<script>alert('User not found. Please register first.'); window.location='register.php';</script>";
    }
} else {
    // If user tries to open this file directly without clicking button
    header("Location: index.php");
    exit();
}
?>