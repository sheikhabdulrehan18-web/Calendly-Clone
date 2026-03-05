<?php
require_once 'db.php';
 
if (!isset($_GET['u'])) {
    die("User not found.");
}
 
$username = $_GET['u'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$host = $stmt->fetch();
 
if (!$host) {
    die("User not found.");
}
 
// Helper to get available slots for a date
function getAvailableSlots($pdo, $host_id, $date) {
    $day_of_week = date('w', strtotime($date));
 
    // Get host availability for this day
    $stmt = $pdo->prepare("SELECT * FROM availability WHERE user_id = ? AND day_of_week = ?");
    $stmt->execute([$host_id, $day_of_week]);
    $avail = $stmt->fetch();
 
    if (!$avail) return [];
 
    $start = strtotime($avail['start_time']);
    $end = strtotime($avail['end_time']);
    $slots = [];
 
    // Get existing meetings for this date
    $stmt = $pdo->prepare("SELECT meeting_time FROM meetings WHERE host_id = ? AND meeting_date = ?");
    $stmt->execute([$host_id, $date]);
    $booked = $stmt->fetchAll(PDO::FETCH_COLUMN);
 
    $current = $start;
    while ($current + (30 * 60) <= $end) {
        $time = date('H:i:s', $current);
        if (!in_array($time, $booked)) {
            $slots[] = date('h:i A', $current);
        }
        $current += (30 * 60); // 30 min intervals
    }
    return $slots;
}
 
// For AJAX requests to get slots
if (isset($_GET['date_ajax'])) {
    header('Content-Type: application/json');
    echo json_encode(getAvailableSlots($pdo, $host['id'], $_GET['date_ajax']));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a meeting with <?php echo htmlspecialchars($host['full_name']); ?></title>
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
 
        .booking-container {
            background: var(--white);
            max-width: 900px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            display: flex;
            overflow: hidden;
            border: 1px solid var(--border);
            animation: fadeIn 0.8s ease;
        }
 
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
 
        .host-sidebar {
            width: 300px;
            padding: 40px;
            border-right: 1px solid var(--border);
            background: #fafbfc;
        }
 
        .host-sidebar h2 { font-size: 24px; margin-bottom: 20px; }
        .host-sidebar .duration { color: #64748b; font-weight: 600; font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
 
        .calendar-section { flex: 1; padding: 40px; }
 
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; text-align: center; }
        .day-header { font-weight: 500; font-size: 12px; color: #64748b; padding-bottom: 10px; }
        .day {
            padding: 15px 5px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: 600;
            transition: 0.2s;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .day:hover:not(.disabled) { background: var(--bg); color: var(--primary); }
        .day.active { background: var(--primary); color: white; }
        .day.disabled { color: #cbd5e1; cursor: default; }
 
        .slots-modal {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(4px);
        }
 
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 16px;
            width: 100%;
            max-width: 400px;
            max-height: 80vh;
            overflow-y: auto;
        }
 
        .slot-btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid var(--primary);
            color: var(--primary);
            background: transparent;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .slot-btn:hover { background: var(--primary); color: white; }
 
        .btn-close { float: right; cursor: pointer; color: #64748b; }
 
        @media (max-width: 768px) {
            .booking-container { flex-direction: column; }
            .host-sidebar { width: 100%; border-right: none; border-bottom: 1px solid var(--border); }
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <div class="host-sidebar">
            <p style="color: #64748b; font-size: 14px; margin-bottom: 10px;">HOST</p>
            <h2><?php echo htmlspecialchars($host['full_name']); ?></h2>
            <div class="duration">⏱ 30 min</div>
            <p style="color: #64748b;">Schedule a quick call or meeting in my available slots.</p>
        </div>
 
        <div class="calendar-section">
            <div class="calendar-header">
                <h3 id="monthYear"></h3>
                <div style="display: flex; gap: 10px;">
                    <button onclick="prevMonth()" class="btn-slot" style="padding: 5px 10px; cursor: pointer;">&lt;</button>
                    <button onclick="nextMonth()" class="btn-slot" style="padding: 5px 10px; cursor: pointer;">&gt;</button>
                </div>
            </div>
            <div class="calendar-grid" id="calendarGrid">
                <!-- Header -->
                <div class="day-header">SUN</div><div class="day-header">MON</div><div class="day-header">TUE</div><div class="day-header">WED</div>
                <div class="day-header">THU</div><div class="day-header">FRI</div><div class="day-header">SAT</div>
                <!-- Days will be generated by JS -->
            </div>
        </div>
    </div>
 
    <div class="slots-modal" id="slotsModal">
        <div class="modal-content">
            <span class="btn-close" onclick="closeModal()">✕</span>
            <h3 id="selectedDateText" style="margin-bottom: 20px;">Select a Time</h3>
            <div id="slotsContainer"></div>
        </div>
    </div>
 
    <script>
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();
        let selectedDate = null;
 
        function renderCalendar() {
            const grid = document.getElementById('calendarGrid');
            const monthYear = document.getElementById('monthYear');
 
            // Clear previous days
            const dayHeaders = document.querySelectorAll('.day-header');
            grid.innerHTML = '';
            dayHeaders.forEach(h => grid.appendChild(h));
 
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const today = new Date();
            today.setHours(0,0,0,0);
 
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            monthYear.innerText = `${monthNames[currentMonth]} ${currentYear}`;
 
            // Padding
            for (let i = 0; i < firstDay; i++) {
                const div = document.createElement('div');
                grid.appendChild(div);
            }
 
            for (let i = 1; i <= daysInMonth; i++) {
                const div = document.createElement('div');
                div.className = 'day';
                div.innerText = i;
 
                const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                const dateObj = new Date(currentYear, currentMonth, i);
 
                if (dateObj < today) {
                    div.classList.add('disabled');
                } else {
                    div.onclick = () => selectDay(dateStr, div);
                }
                grid.appendChild(div);
            }
        }
 
        async function selectDay(date, element) {
            document.querySelectorAll('.day').forEach(d => d.classList.remove('active'));
            element.classList.add('active');
            selectedDate = date;
 
            const response = await fetch(`calendar.php?u=<?php echo $username; ?>&date_ajax=${date}`);
            const slots = await response.json();
 
            const container = document.getElementById('slotsContainer');
            container.innerHTML = '';
 
            document.getElementById('selectedDateText').innerText = `Times for ${new Date(date).toDateString()}`;
 
            if (slots.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #64748b;">No slots available for this day.</p>';
            } else {
                slots.forEach(slot => {
                    const btn = document.createElement('button');
                    btn.className = 'slot-btn';
                    btn.innerText = slot;
                    btn.onclick = () => {
                        window.location.href = `book.php?u=<?php echo $username; ?>&date=${date}&time=${slot}`;
                    };
                    container.appendChild(btn);
                });
            }
            document.getElementById('slotsModal').style.display = 'flex';
        }
 
        function closeModal() { document.getElementById('slotsModal').style.display = 'none'; }
        function prevMonth() { currentMonth--; if(currentMonth < 0) { currentMonth = 11; currentYear--; } renderCalendar(); }
        function nextMonth() { currentMonth++; if(currentMonth > 11) { currentMonth = 0; currentYear++; } renderCalendar(); }
 
        renderCalendar();
    </script>
</body>
</html>
 
