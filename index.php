<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Invicta 2025</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #1a1a2e;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .auth-card {
            background: #16213e;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 1px solid #533483;
        }
        .auth-card h1 {
            color: #e94560;
            margin-bottom: 10px;
            margin-top: 0;
        }
        .auth-card p {
            color: #a2a8d3;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            color: #a2a8d3;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            background: #0f3460;
            border: 1px solid #533483;
            border-radius: 6px;
            color: #fff;
            font-size: 1rem;
            box-sizing: border-box; 
        } 
        
        .form-control:focus {
            outline: none;
            border-color: #e94560;
        }
        select.form-control {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #e94560;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
            font-weight: bold;
        }
        .btn-login:hover {
            background: #c72c41;
        }
        .links {
            margin-top: 20px;
            font-size: 0.9rem;
        }
        .links a {
            color: #e94560;
            text-decoration: none;
            font-weight: bold;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .guest-link {
            display: block;
            margin-top: 15px;
            color: #a2a8d3;
            font-size: 0.85rem;
            text-decoration: none;
            opacity: 0.8;
            transition: 0.3s;
        }
        .guest-link:hover {
            opacity: 1;
            color: #fff;
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <h1>🚀 INVICTA 2025</h1>
        <p>Select your role to continue</p>

        <form action="login_process.php" method="POST">
            
            <div class="form-group">
                <label for="role">Login Type:</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="" disabled selected>Select Role...</option>
                    <option value="participant">Participant (Student)</option>
                    <option value="coordinator">Event Coordinator</option>
                    <option value="mentor">Faculty Mentor</option>
                    <option value="club">Club Admin</option>
                    <option value="judge">Judge</option>
                </select>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" name="btn_login" class="btn-login">Login</button>
        </form>

        <div class="links">
            <p style="color:#a2a8d3; margin: 0;">New here? <a href="register.php">Register as Participant</a></p>
        </div>

        <a href="home.php" class="guest-link">Continue as Guest (View Events) &rarr;</a>
    </div>

</body>
</html>