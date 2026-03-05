<?php
session_start();
require_once 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$username = $_SESSION['username'];
 
// Handle cancel meeting
if (isset($_GET['cancel'])) {
    $meeting_id = (int)$_GET['cancel'];
    $stmt = $pdo->prepare("DELETE FROM meetings WHERE id = ? AND host_id = ?");
    $stmt->execute([$meeting_id, $user_id]);
    header("Location: dashboard.php?msg=cancelled");
    exit();
}
 
// Fetch upcoming meetings (today or future)
$stmt = $pdo->prepare("SELECT * FROM meetings WHERE host_id = ? AND meeting_date >= CURDATE() ORDER BY meeting_date ASC, meeting_time ASC");
$stmt->execute([$user_id]);
$upcoming_meetings = $stmt->fetchAll();
 
// Fetch past meetings
$stmt = $pdo->prepare("SELECT * FROM meetings WHERE host_id = ? AND meeting_date < CURDATE() ORDER BY meeting_date DESC, meeting_time DESC");
$stmt->execute([$user_id]);
$past_meetings = $stmt->fetchAll();
 
$public_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/calendar.php?u=$username";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Calendly Clone</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #006bff;
            --bg: #f8fafe;
            --white: #ffffff;
            --text: #1a1a1a;
            --text-light: #64748b;
            --border: #e2e8f0;
            --danger: #ef4444;
        }
 
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg); color: var(--text); }
 
        .navbar {
            background: var(--white);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }
 
        .logo { font-size: 20px; font-weight: 700; color: var(--primary); text-decoration: none; }
        .logo span { color: var(--text); }
 
        .nav-right { display: flex; align-items: center; gap: 20px; }
        .user-info { font-weight: 500; }
 
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
 
        .header-box {
            background: var(--white);
            padding: 30px;
            border-radius: 16px;
            border: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
 
        .booking-link-box h3 { font-size: 16px; color: var(--text-light); margin-bottom: 10px; }
        .booking-link-box p { font-size: 18px; font-weight: 600; color: var(--primary); }
 
        .btn { padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; transition: 0.2s; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-outline { border: 1px solid var(--border); color: var(--text); background: white; }
        .btn-danger { background: #fee2e2; color: var(--danger); }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
 
        .tabs { display: flex; gap: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--border); }
        .tab { padding: 10px 20px; cursor: pointer; color: var(--text-light); font-weight: 500; border-bottom: 2px solid transparent; }
        .tab.active { color: var(--primary); border-bottom-color: var(--primary); }
 
        .meeting-list { display: grid; gap: 15px; }
        .meeting-card {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.2s;
        }
        .meeting-card:hover { transform: scale(1.01); box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
 
        .meeting-info h4 { font-size: 18px; margin-bottom: 5px; }
        .meeting-info p { color: var(--text-light); font-size: 14px; }
        .meeting-time { font-weight: 600; color: var(--primary); margin-top: 5px; }
 
        .empty-state { text-align: center; padding: 60px; color: var(--text-light); }
 
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .badge-upcoming { background: #dcfce7; color: #166534; }
        .badge-past { background: #f1f5f9; color: #475569; }
 
        @media (max-width: 600px) {
            .header-box { flex-direction: column; text-align: center; gap: 20px; }
            .meeting-card { flex-direction: column; text-align: center; gap: 15px; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="logo">Calendly<span>Clone</span></a>
        <div class="nav-right">
            <span class="user-info">Hi, <?php echo htmlspecialchars($full_name); ?></span>
            <a href="availability.php" class="btn btn-outline">My Availability</a>
            <a href="logout.php" class="btn btn-outline" style="color: var(--danger);">Logout</a>
        </div>
    </nav>
 
    <div class="container">
        <div class="header-box">
            <div class="booking-link-box">
                <h3>Your Public Booking Link</h3>
                <p id="linkText"><?php echo $public_link; ?></p>
            </div>
            <button onclick="copyLink()" class="btn btn-primary">Copy Link</button>
        </div>
 
        <div class="tabs">
            <div class="tab active" onclick="showTab('upcoming')">Upcoming</div>
            <div class="tab" onclick="showTab('past')">Past</div>
        </div>
 
        <div id="upcoming" class="meeting-list">
            <?php if (empty($upcoming_meetings)): ?>
                <div class="empty-state">No upcoming meetings scheduled.</div>
            <?php else: ?>
                <?php foreach ($upcoming_meetings as $m): ?>
                    <div class="meeting-card">
                        <div class="meeting-info">
                            <span class="badge badge-upcoming">Confirmed</span>
                            <h4>Meeting with <?php echo htmlspecialchars($m['guest_name']); ?></h4>
                            <p><?php echo htmlspecialchars($m['guest_email']); ?></p>
                            <p class="meeting-time"><?php echo date('M d, Y', strtotime($m['meeting_date'])); ?> @ <?php echo date('h:i A', strtotime($m['meeting_time'])); ?> (30 min)</p>
                        </div>
                        <a href="?cancel=<?php echo $m['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this meeting?')">Cancel</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
 
        <div id="past" class="meeting-list" style="display: none;">
            <?php if (empty($past_meetings)): ?>
                <div class="empty-state">No past meetings.</div>
            <?php else: ?>
                <?php foreach ($past_meetings as $m): ?>
                    <div class="meeting-card">
                        <div class="meeting-info">
                            <span class="badge badge-past">Completed</span>
                            <h4>Meeting with <?php echo htmlspecialchars($m['guest_name']); ?></h4>
                            <p><?php echo htmlspecialchars($m['guest_email']); ?></p>
                            <p class="meeting-time"><?php echo date('M d, Y', strtotime($m['meeting_date'])); ?> @ <?php echo date('h:i A', strtotime($m['meeting_time'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
 
    <script>
        function showTab(tabId) {
            document.getElementById('upcoming').style.display = 'none';
            document.getElementById('past').style.display = 'none';
            document.getElementById(tabId).style.display = 'grid';
 
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            event.currentTarget.classList.add('active');
        }
 
        function copyLink() {
            const link = document.getElementById('linkText').innerText;
            navigator.clipboard.writeText(link).then(() => {
                alert('Link copied to clipboard!');
            });
        }
    </script>
</body>
</html>
 
Syntax highlighting powered by GeSHi
Help
