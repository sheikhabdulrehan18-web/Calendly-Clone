<?php
session_start();
require_once 'db.php';
 
$error = '';
$success = '';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
 
    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if username or email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Username or Email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$full_name, $username, $email, $hashed_password])) {
                $user_id = $pdo->lastInsertId();
 
                // Initialize default availability (9 AM - 5 PM for weekdays)
                for ($i = 1; $i <= 5; $i++) {
                    $stmt = $pdo->prepare("INSERT INTO availability (user_id, day_of_week, start_time, end_time) VALUES (?, ?, '09:00:00', '17:00:00')");
                    $stmt->execute([$user_id, $i]);
                }
 
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $full_name;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Calendly Clone</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #006bff;
            --bg: #f8fafe;
            --card: #ffffff;
            --text: #1a1a1a;
            --border: #e2e8f0;
        }
 
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
 
        .auth-card {
            background: var(--card);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 107, 255, 0.05);
            width: 100%;
            max-width: 450px;
            border: 1px solid var(--border);
            animation: fadeIn 0.5s ease;
        }
 
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
 
        .logo { font-size: 24px; font-weight: 700; color: var(--primary); text-decoration: none; display: block; text-align: center; margin-bottom: 30px; }
        .logo span { color: var(--text); }
 
        h2 { text-align: center; margin-bottom: 30px; font-size: 24px; font-weight: 700; }
 
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #4a5568; }
        input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.2s;
        }
        input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0, 107, 255, 0.1); }
 
        .btn {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }
        .btn:hover { background: #0056cc; }
 
        .error { background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center; }
 
        .footer-text { text-align: center; margin-top: 25px; color: #718096; font-size: 14px; }
        .footer-text a { color: var(--primary); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="auth-card">
        <a href="index.php" class="logo">Calendly<span>Clone</span></a>
        <h2>Create your account</h2>
 
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
 
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="johndoe123" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="john@example.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Min 6 characters" required>
            </div>
            <button type="submit" name="signup" class="btn">Sign Up</button>
        </form>
 
        <p class="footer-text">Already have an account? <a href="login.php">Log In</a></p>
    </div>
</body>
</html>
 
