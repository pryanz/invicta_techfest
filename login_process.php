<?php
session_start();
require 'includes/db_connect.php';

if (isset($_POST['btn_login'])) {
    $role = $_POST['role'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $table = "";
    $id_column = "";
    
    switch ($role) {
        case 'participant': $table = "participants"; $id_column = "participant_id"; break;
        case 'club':        $table = "clubs";        $id_column = "club_id";        break;
        case 'judge':       $table = "judges";       $id_column = "judge_id";       break;
        default: die("Invalid Role Selected");
    }

    $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($password === $row['password']) {
            
            $_SESSION['user_id'] = $row[$id_column];
            $_SESSION['role'] = $role;
            $_SESSION['name'] = ($role == 'club') ? $row['club_name'] : $row['name'];

            // DEFINE REDIRECT TARGET BASED ON ROLE
            $redirect_page = "dashboard_" . $role . ".php";

            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Login Successful</title>
                <style>
                    body { background-color: #1a1a2e; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif; text-align: center; }
                    .box { background: #16213e; padding: 40px; border-radius: 10px; border: 2px solid #4cd137; }
                    h1 { color: #4cd137; }
                </style>
            </head>
            <body>
                <div class="box">
                    <h1>Login Successful!</h1>
                    <p>Welcome, <b>' . htmlspecialchars($_SESSION['name']) . '</b>.</p>
                    <p>Loading your dashboard...</p>
                </div>
                <script>
                    setTimeout(function() {
                        // DYNAMIC REDIRECT HERE
                        window.location.href = "' . $redirect_page . '";
                    }, 1500);
                </script>
            </body>
            </html>';
            exit();

        } else {
            echo "<script>alert('Incorrect Password!'); window.location='index.php';</script>";
        }
    } else {
        echo "<script>alert('User not found.'); window.location='index.php';</script>";
    }
} else {
    header("Location: index.php");
    exit();
}
?>