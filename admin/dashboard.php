<?php
session_start();
include "../config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

// Get admin info
$admin_id = $_SESSION['admin_id'];
$adminQuery = $conn->prepare("SELECT username FROM admin WHERE id = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();
$admin = $adminResult->fetch_assoc();
$adminName = $admin['username'] ?? 'Admin';

// Get comprehensive statistics
$students = $conn->query("SELECT COUNT(*) total FROM students")->fetch_assoc();
$candidates = $conn->query("SELECT COUNT(*) total FROM candidates")->fetch_assoc();

// Get total votes cast
$votes = $conn->query("SELECT COUNT(*) total FROM votes")->fetch_assoc();

// Get positions count
$positions = $conn->query("SELECT DISTINCT position FROM candidates")->num_rows;

// Get recent registrations (last 7 days)
$recentStudents = $conn->query("SELECT COUNT(*) total FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc();

// Get voter turnout percentage
$totalVoters = $students['total'] ?: 1;
$turnout = round(($votes['total'] / $totalVoters) * 100, 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSTSO | Admin Dashboard</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Modern Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(45deg);
            transition: transform 0.3s ease;
        }

        .brand-icon i {
            transform: rotate(-45deg);
            color: #ffd700;
            font-size: 1.5rem;
        }

        .brand-text {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e3c72;
        }

        .brand-text span {
            color: #ffd700;
            font-weight: 700;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f0f4f8;
            padding: 8px 20px;
            border-radius: 50px;
        }

        .user-info i {
            color: #ffd700;
            font-size: 1.2rem;
        }

        .user-name {
            font-weight: 500;
            color: #1e3c72;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5253);
            color: white;
            padding: 10px 22px;
            border-radius: 50px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(238, 82, 83, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(238, 82, 83, 0.4);
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        /* Welcome Section */
        .welcome-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 40px;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInDown 0.8s ease;
        }

        .welcome-section h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .welcome-section h1 i {
            color: #ffd700;
            margin-right: 15px;
        }

        .welcome-text {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .date-badge {
            display: inline-block;
            background: rgba(255, 215, 0, 0.2);
            padding: 8px 20px;
            border-radius: 50px;
            margin-top: 15px;
            border: 1px solid rgba(255, 215, 0, 0.3);
        }

        .date-badge i {
            color: #ffd700;
            margin-right: 8px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 30px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #ffd700, #ffa500);
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .stat-icon i {
            font-size: 2rem;
            color: #ffd700;
        }

        .stat-card h3 {
            color: #1e3c72;
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2a5298;
            line-height: 1.2;
            margin-bottom: 5px;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
            color: #22c55e;
        }

        .stat-trend i {
            font-size: 0.8rem;
        }

        .stat-trend.negative {
            color: #ef4444;
        }

        .stat-sub {
            color: #666;
            font-size: 0.85rem;
        }

        /* Progress Bar for Turnout */
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #f0f0f0;
            border-radius: 10px;
            margin: 15px 0 5px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ffd700, #ffa500);
            border-radius: 10px;
            transition: width 1s ease;
        }

        /* Charts Row */
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .chart-card {
            background: white;
            border-radius: 30px;
            padding: 25px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-header h3 {
            color: #1e3c72;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-header h3 i {
            color: #ffd700;
        }

        .chart-period {
            background: #f0f0f0;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            color: #666;
        }

        /* Action Cards */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .action-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .action-card:hover {
            transform: translateY(-5px);
            border-color: #ffd700;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .action-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .action-icon i {
            font-size: 2rem;
            color: #ffd700;
        }

        .action-card h4 {
            color: #1e3c72;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .action-card p {
            color: #666;
            font-size: 0.85rem;
        }

        /* Recent Activity */
        .recent-activity {
            background: white;
            border-radius: 30px;
            padding: 25px;
            margin-top: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .activity-header h3 {
            color: #1e3c72;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .activity-header h3 i {
            color: #ffd700;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-dot {
            width: 10px;
            height: 10px;
            background: #22c55e;
            border-radius: 50%;
        }

        .activity-dot.warning {
            background: #ffd700;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            color: #1e3c72;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #999;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .brand {
                width: 100%;
                justify-content: center;
            }
            
            .user-menu {
                width: 100%;
                justify-content: center;
            }
            
            .welcome-section h1 {
                font-size: 1.8rem;
            }
            
            .charts-row {
                grid-template-columns: 1fr;
            }
        }

        /* Loading States */
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(5) { animation-delay: 0.5s; }
        
        .action-card:nth-child(1) { animation-delay: 0.6s; }
        .action-card:nth-child(2) { animation-delay: 0.7s; }
        .action-card:nth-child(3) { animation-delay: 0.8s; }
        .action-card:nth-child(4) { animation-delay: 0.9s; }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="brand">
            <div class="brand-icon">
                <i class="fas fa-vote-yea"></i>
            </div>
            <div class="brand-text">
                MUSTSO <span>Admin</span>
            </div>
        </div>
        
        <div class="user-menu">
            <div class="user-info">
                <i class="fas fa-user-shield"></i>
                <span class="user-name"><?= htmlspecialchars($adminName) ?></span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1>
                <i class="fas fa-tachometer-alt"></i>
                Welcome back, <?= htmlspecialchars($adminName) ?>!
            </h1>
            <p class="welcome-text">Here's what's happening with your election today.</p>
            <div class="date-badge">
                <i class="fas fa-calendar"></i>
                <?= date('l, F j, Y') ?>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Total Students</h3>
                <div class="stat-number"><?= number_format($students['total']) ?></div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                    <?= $recentStudents['total'] ?> new this week
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Total Candidates</h3>
                <div class="stat-number"><?= number_format($candidates['total']) ?></div>
                <div class="stat-sub">across <?= $positions ?> positions</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <h3>Votes Cast</h3>
                <div class="stat-number"><?= number_format($votes['total']) ?></div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $turnout ?>%"></div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-percent"></i>
                    <?= $turnout ?>% turnout
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Positions</h3>
                <div class="stat-number"><?= $positions ?></div>
                <div class="stat-sub">active positions</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>
                        <i class="fas fa-chart-pie"></i>
                        Voter Distribution
                    </h3>
                    <span class="chart-period">Current</span>
                </div>
                <canvas id="voterChart" style="max-height: 250px;"></canvas>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3>
                        <i class="fas fa-chart-bar"></i>
                        Voting Activity
                    </h3>
                    <span class="chart-period">Last 7 days</span>
                </div>
                <canvas id="activityChart" style="max-height: 250px;"></canvas>
            </div>
        </div>

        <!-- Quick Actions -->
        <h2 style="color:white; margin: 30px 0 20px;">
            <i class="fas fa-bolt" style="color:#ffd700; margin-right:10px;"></i>
            Quick Actions
        </h2>
        
        <div class="actions-grid">
            <a href="register_candidate.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h4>Register Candidate</h4>
                <p>Add new candidate to election</p>
            </a>

            <a href="manage_candidates.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <h4>Manage Candidates</h4>
                <p>Edit or remove candidates</p>
            </a>

            <a href="../results.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h4>View Results</h4>
                <p>Live election results</p>
            </a>

            <a href="settings.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <h4>Settings</h4>
                <p>Configure system</p>
            </a>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <div class="activity-header">
                <h3>
                    <i class="fas fa-history"></i>
                    Recent Activity
                </h3>
                <a href="#" style="color:#1e3c72; text-decoration:none;">View all →</a>
            </div>
            
            <ul class="activity-list">
                <?php
                // Get recent votes with candidate names
                $recentVotes = $conn->query("
                    SELECT v.voted_at, c.name as candidate_name 
                    FROM votes v 
                    JOIN candidates c ON v.candidate_id = c.id 
                    ORDER BY v.voted_at DESC 
                    LIMIT 5
                ");
                
                if ($recentVotes && $recentVotes->num_rows > 0):
                    while($activity = $recentVotes->fetch_assoc()):
                ?>
                <li class="activity-item">
                    <span class="activity-dot"></span>
                    <div class="activity-content">
                        <span class="activity-title">
                            Vote cast for <?= htmlspecialchars($activity['candidate_name']) ?>
                        </span>
                        <div class="activity-time">
                            <?= date('M j, Y g:i A', strtotime($activity['voted_at'])) ?>
                        </div>
                    </div>
                </li>
                <?php 
                    endwhile;
                else:
                ?>
                <li class="activity-item">
                    <span class="activity-dot warning"></span>
                    <div class="activity-content">
                        <span class="activity-title">No recent voting activity</span>
                        <div class="activity-time">Waiting for votes...</div>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Chart Scripts -->
    <script>
    // Voter Distribution Chart
    new Chart(document.getElementById('voterChart'), {
        type: 'doughnut',
        data: {
            labels: ['Voted', 'Not Voted'],
            datasets: [{
                data: [<?= $votes['total'] ?>, <?= $students['total'] - $votes['total'] ?>],
                backgroundColor: ['#ffd700', '#e0e0e0'],
                borderWidth: 0,
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            family: 'Poppins'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#1e3c72',
                    titleColor: '#ffd700',
                    bodyColor: '#fff'
                }
            }
        }
    });

    // Activity Chart (Last 7 days)
    <?php
    // Get daily vote counts for last 7 days
    $dailyVotes = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $result = $conn->query("SELECT COUNT(*) as count FROM votes WHERE DATE(voted_at) = '$date'");
        $dailyVotes[] = $result->fetch_assoc()['count'];
    }
    ?>

    new Chart(document.getElementById('activityChart'), {
        type: 'line',
        data: {
            labels: [
                '<?= date('D', strtotime('-6 days')) ?>',
                '<?= date('D', strtotime('-5 days')) ?>',
                '<?= date('D', strtotime('-4 days')) ?>',
                '<?= date('D', strtotime('-3 days')) ?>',
                '<?= date('D', strtotime('-2 days')) ?>',
                '<?= date('D', strtotime('-1 day')) ?>',
                'Today'
            ],
            datasets: [{
                label: 'Votes',
                data: [<?= implode(',', $dailyVotes) ?>],
                borderColor: '#ffd700',
                backgroundColor: 'rgba(255, 215, 0, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#1e3c72',
                pointBorderColor: '#ffd700',
                pointRadius: 5,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e3c72',
                    titleColor: '#ffd700'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return value + ' votes';
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>