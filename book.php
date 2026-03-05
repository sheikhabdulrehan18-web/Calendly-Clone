<?php
require_once 'db.php';
 
if (!isset($_GET['u']) || !isset($_GET['date']) || !isset($_GET['time'])) {
    header("Location: index.php");
    exit();
}
 
$username = $_GET['u'];
$date = $_GET['date'];
$time_human = $_GET['time'];
$time = date('H:i:s', strtotime($time_human));
 
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$host = $stmt->fetch();
 
if (!$host) die("Host not found.");
 
$msg = '';
$success = false;
 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_booking'])) {
    $guest_name = trim($_POST['guest_name']);
    $guest_email = trim($_POST['guest_email']);
 
    if (empty($guest_name) || empty($guest_email)) {
        $msg = "Please provide your name and email.";
    } else {
        // Double check if slot is still available
        $stmt = $pdo->prepare("SELECT id FROM meetings WHERE host_id = ? AND meeting_date = ? AND meeting_time = ?");
        $stmt->execute([$host['id'], $date, $time]);
        if ($stmt->fetch()) {
            $msg = "Sorry, this slot has just been booked. Please pick another one.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO meetings (host_id, guest_name, guest_email, meeting_date, meeting_time) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$host['id'], $guest_name, $guest_email, $date, $time])) {
                $success = true;
            } else {
                $msg = "Could not book meeting. Try again.";
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
    <title>Confirm Booking - Calendly Clone</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #006bff;
            --bg: #f8fafe;
            --white: #ffffff;
            --text: #1a1a1a;
            --border: #e2e8f0;
        }
 
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
 
        .form-card {
            background: var(--white);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            border: 1px solid var(--border);
            text-align: center;
            animation: fadeIn 0.5s ease;
        }
 
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
 
        .meeting-summary {
            background: #f1f5f9;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: left;
        }
 
        .summary-item { margin-bottom: 10px; font-weight: 500; }
        .summary-item label { color: #64748b; font-size: 13px; display: block; }
 
        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
        input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 16px;
        }
 
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
            transition: 0.2s;
        }
        .btn:hover { background: #0056cc; transform: translateY(-1px); }
 
        .success-box { text-align: center; }
        .success-icon { font-size: 60px; color: #22c55e; margin-bottom: 20px; }
        .error { color: #ef4444; margin-bottom: 20px; font-weight: 500; }
    </style>
</head>
<body>
    <div class="form-card">
        <?php if ($success): ?>
            <div class="success-box">
                <div class="success-icon">✓</div>
                <h2>Booking Confirmed!</h2>
                <p style="color: #64748b; margin: 20px 0;">A meeting has been scheduled with <?php echo htmlspecialchars($host['full_name']); ?>.</p>
                <div class="meeting-summary">
                    <p><strong><?php echo date('l, F j, Y', strtotime($date)); ?></strong></p>
                    <p><strong><?php echo $time_human; ?> (30 min)</strong></p>
                </div>
                <a href="index.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Return Home</a>
            </div>
        <?php else: ?>
            <h2>Enter Details</h2>
            <p style="color: #64748b; margin-bottom: 30px;">Please share your information to finalize the booking.</p>
 
            <?php if ($msg): ?>
                <div class="error"><?php echo $msg; ?></div>
            <?php endif; ?>
 
            <div class="meeting-summary">
                <div class="summary-item">
                    <label>HOST</label>
                    <?php echo htmlspecialchars($host['full_name']); ?>
                </div>
                <div class="summary-item">
                    <label>DATE & TIME</label>
                    <?php echo date('M d, Y', strtotime($date)); ?> @ <?php echo $time_human; ?>
                </div>
            </div>
 
            <form method="POST">
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" name="guest_name" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <label>Your Email</label>
                    <input type="email" name="guest_email" placeholder="email@example.com" required>
                </div>
                <button type="submit" name="confirm_booking" class="btn">Confirm Meeting</button>
            </form>
 
            <a href="calendar.php?u=<?php echo $username; ?>" style="display: block; margin-top: 20px; color: #64748b; font-size: 14px; text-decoration: none;">Cancel</a>
        <?php endif; ?>
    </div>
</body>
</html>
