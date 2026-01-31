<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Virtual Study Buddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7ff 0%, #f0f0f0 100%);
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .welcome-section h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }

        .welcome-section p {
            color: #666;
            font-size: 16px;
        }

        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 24px;
            color: #764ba2;
            margin-bottom: 10px;
        }

        .stat-card h3 {
            color: #333;
            margin-bottom: 5px;
        }

        .stat-card p {
            color: #666;
            font-size: 14px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card i {
            font-size: 32px;
            color: #764ba2;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .feature-card a {
            color: #764ba2;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .feature-card a:hover {
            color: #667eea;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 20px;
            }

            .welcome-section h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="dashboard-container">
        <section class="welcome-section">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            <p>Track your progress and access all study tools from your personal dashboard.</p>
        </section>

        <section class="quick-stats">
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <h3>Study Time</h3>
                <p>12 hours this week</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-tasks"></i>
                <h3>Tasks</h3>
                <p>8 completed, 3 pending</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-file-alt"></i>
                <h3>Notes</h3>
                <p>15 uploaded</p>
            </div>
        </section>

        <section class="features-grid">
            <div class="feature-card" onclick="window.location.href='study_planner.php'">
                <i class="fas fa-calendar-alt"></i>
                <h3>Study Planner</h3>
                <p>Plan your study sessions and track your progress with our interactive planner.</p>
                <a href="study_planner.php">Open Planner <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="feature-card" onclick="window.location.href='upload_notes.php'">
                <i class="fas fa-upload"></i>
                <h3>Upload Notes</h3>
                <p>Share your study materials and access notes from other students.</p>
                <a href="upload_notes.php">Upload Notes <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="feature-card" onclick="window.location.href='studybuddy.php'">
                <i class="fas fa-robot"></i>
                <h3>StudyBuddy AI</h3>
                <p>Get clear explanations at your level. Ask questions, paste notes to summarize, and learn step by step.</p>
                <a href="studybuddy.php">Chat with StudyBuddy <i class="fas fa-arrow-right"></i></a>
            </div>
        </section>
    </div>

    <script>
        // Add hover effect to feature cards
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                card.style.transform = `
                    translateY(-5px)
                    scale(1.02)
                    perspective(1000px)
                    rotateX(${(y - rect.height/2)/20}deg)
                    rotateY(${(x - rect.width/2)/20}deg)
                `;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(-5px)';
            });
        });
    </script>
</body>
</html> 