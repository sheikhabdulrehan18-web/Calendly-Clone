<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendly Clone - Smart Scheduling</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #006bff;
            --primary-dark: #0056cc;
            --bg: #ffffff;
            --text: #1a1a1a;
            --text-light: #666666;
            --accent: #f2f7ff;
            --shadow: 0 10px 30px rgba(0, 107, 255, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Navigation */
        nav {
            padding: 25px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo span {
            color: var(--text);
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-light);
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .btn {
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            display: inline-block;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--accent);
        }

        /* Hero Section */
        .hero {
            padding: 100px 0 150px;
            display: flex;
            align-items: center;
            gap: 50px;
        }

        .hero-content {
            flex: 1;
            animation: fadeIn 1s ease;
        }

        .hero-image {
            flex: 1;
            position: relative;
            animation: slideUp 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .hero h1 {
            font-size: 64px;
            line-height: 1.1;
            margin-bottom: 25px;
            font-weight: 800;
        }

        .hero p {
            font-size: 18px;
            color: var(--text-light);
            margin-bottom: 35px;
            max-width: 500px;
        }

        .hero-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.1);
        }

        .stats {
            display: flex;
            gap: 40px;
            margin-top: 50px;
        }

        .stat-item h3 {
            font-size: 24px;
            color: var(--primary);
        }

        .stat-item p {
            font-size: 14px;
            margin-bottom: 0;
        }

        /* Features */
        .features {
            padding: 100px 0;
            background: var(--accent);
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow);
        }

        .card i {
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 20px;
            display: block;
        }

        .card h3 {
            margin-bottom: 15px;
        }

        /* Footer */
        footer {
            padding: 60px 0;
            text-align: center;
            border-top: 1px solid #eee;
        }

        @media (max-width: 768px) {
            .hero {
                flex-direction: column;
                text-align: center;
                padding: 50px 0;
            }
            .hero h1 {
                font-size: 40px;
            }
            .hero p {
                margin: 0 auto 30px;
            }
            .grid {
                grid-template-columns: 1fr;
            }
            .nav-links {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <nav>
            <a href="index.php" class="logo">Calendly<span>Clone</span></a>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="login.php" class="btn btn-outline">Log In</a>
                <a href="signup.php" class="btn btn-primary">Sign Up Free</a>
            </div>
        </nav>
    </div>

    <section class="hero">
        <div class="container">
            <div style="display: flex; align-items: center; gap: 50px; flex-wrap: wrap;">
                <div class="hero-content">
                    <h1>Scheduling automation platform</h1>
                    <p>Calendly is the modern scheduling platform that makes "finding time" a breeze. When connecting with others is easy, your work can go further.</p>
                    <div style="display: flex; gap: 15px;">
                        <a href="signup.php" class="btn btn-primary">Get Started for Free</a>
                        <a href="#features" class="btn btn-outline">Learn More</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1506784983877-45594efa4cbe?auto=format&fit=crop&w=800&q=80" alt="Scheduling Dashboard">
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2>Why choose Calendly Clone?</h2>
                <p>Simple, easy to use, and powerful features for everyone.</p>
            </div>
            <div class="grid">
                <div class="card">
                    <h3>Create Events</h3>
                    <p>Set up different types of meetings and share your unique booking link with anyone.</p>
                </div>
                <div class="card">
                    <h3>Set Availability</h3>
                    <p>Define your working hours and let others book time slots that work for you.</p>
                </div>
                <div class="card">
                    <h3>Zero Overlap</h3>
                    <p>Automated system ensures no double bookings ever occur. Complete peace of mind.</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Calendly Clone. Built with PhP & Passion.</p>
        </div>
    </footer>
</body>
</html>
