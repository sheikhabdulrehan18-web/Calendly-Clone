<?php
session_start();
require_once 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
$user_id = $_SESSION['user_id'];
$msg = '';
 
// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_availability'])) {
    // Clear existing
    $stmt = $pdo->prepare("DELETE FROM availability WHERE user_id = ?");
    $stmt->execute([$user_id]);
 
    $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    foreach ($days as $index => $day) {
        if (isset($_POST["active_$day"])) {
            $start = $_POST["start_$day"];
            $end = $_POST["end_$day"];
            $stmt = $pdo->prepare("INSERT INTO availability (user_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $index, $start, $end]);
        }
    }
    $msg = "Availability updated successfully!";
}
 
// Fetch current availability
$stmt = $pdo->prepare("SELECT * FROM availability WHERE user_id = ?");
$stmt->execute([$user_id]);
$current_avail = [];
while ($row = $stmt->fetch()) {
    $current_avail[$row['day_of_week']] = $row;
}
 
$days_map = [
    0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
];
$days_keys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Availability - Calendly Clone</title>
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
        body { background-color: var(--bg); color: var(--text); padding: 40px 20px; }
 
        .container { max-width: 800px; margin: 0 auto; }
 
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { font-size: 28px; font-weight: 700; }
 
        .card {
            background: var(--white);
            padding: 30px;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            animation: fadeIn 0.5s ease;
        }
 
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
 
        .day-row {
            display: grid;
            grid-template-columns: 150px 1fr 1fr 1fr;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid var(--border);
            gap: 20px;
        }
        .day-row:last-child { border-bottom: none; }
 
        .day-name { font-weight: 600; display: flex; align-items: center; gap: 10px; }
 
        input[type="time"] {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
        }
 
        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 18px; width: 18px;
            left: 3px; bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider { background-color: var(--primary); }
        input:checked + .slider:before { transform: translateX(20px); }
 
        .btn { padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; transition: 0.2s; display: inline-block; }
        .btn-primary { background: var(--primary); color: white; margin-top: 30px; width: 100%; }
        .btn-outline { border: 1px solid var(--border); color: var(--text); background: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
 
        .success-msg { background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500; }
 
        @media (max-width: 600px) {
            .day-row { grid-template-columns: 1fr; text-align: center; }
            .day-name { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Set Availability</h1>
            <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
        </div>
 
        <?php if ($msg): ?>
            <div class="success-msg"><?php echo $msg; ?></div>
        <?php endif; ?>
 
        <form method="POST" class="card">
            <?php foreach ($days_map as $idx => $name): ?>
                <?php 
                    $active = isset($current_avail[$idx]);
                    $start = $active ? $current_avail[$idx]['start_time'] : '09:00';
                    $end = $active ? $current_avail[$idx]['end_time'] : '17:00';
                    $key = $days_keys[$idx];
                ?>
                <div class="day-row">
                    <div class="day-name">
                        <label class="switch">
                            <input type="checkbox" name="active_<?php echo $key; ?>" <?php echo $active ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <?php echo $name; ?>
                    </div>
                    <div>
                        <input type="time" name="start_<?php echo $key; ?>" value="<?php echo date('H:i', strtotime($start)); ?>">
                    </div>
                    <div style="text-align: center; color: var(--text-light);">to</div>
                    <div>
                        <input type="time" name="end_<?php echo $key; ?>" value="<?php echo date('H:i', strtotime($end)); ?>">
                    </div>
                </div>
            <?php endforeach; ?>
 
            <button type="submit" name="update_availability" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</body>
</html>
 
