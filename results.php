<?php
session_start();
include "config.php";

// Check if user is logged in (either student or admin)
if(!isset($_SESSION['student_id']) && !isset($_SESSION['admin_id'])){
    header("Location: index.html");
    exit();
}

// Determine user type for potential role-based display
$isAdmin = isset($_SESSION['admin_id']);
$userId = $isAdmin ? $_SESSION['admin_id'] : $_SESSION['student_id'];
$userType = $isAdmin ? 'Admin' : 'Student';

// Get statistics
$totalVoters = 0;
$votersResult = $conn->query("SELECT COUNT(*) as total FROM students");
if ($votersResult) {
    $totalVoters = $votersResult->fetch_assoc()['total'];
}

$totalVotesCast = 0;
$votesResult = $conn->query("SELECT COUNT(*) as total FROM votes");
if ($votesResult) {
    $totalVotesCast = $votesResult->fetch_assoc()['total'];
}

$voterTurnout = $totalVoters > 0 ? round(($totalVotesCast / $totalVoters) * 100, 2) : 0;

$totalCandidates = 0;
$candidatesResult = $conn->query("SELECT COUNT(*) as total FROM candidates");
if ($candidatesResult) {
    $totalCandidates = $candidatesResult->fetch_assoc()['total'];
}

// Get admin name if admin is logged in
$adminName = '';
if ($isAdmin) {
    $adminQuery = $conn->prepare("SELECT username FROM admin WHERE id = ?");
    $adminQuery->bind_param("i", $userId);
    $adminQuery->execute();
    $adminResult = $adminQuery->get_result();
    if ($adminRow = $adminResult->fetch_assoc()) {
        $adminName = $adminRow['username'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>MUSTSO | Live Voting Results</title>
    
    <meta http-equiv="refresh" content="15">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Your existing style.css -->
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* Additional styles for results page */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 20px;
        }

        .results-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Navigation Bar */
        .nav-bar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 60px;
            padding: 15px 30px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInDown 0.8s ease;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .nav-brand i {
            color: #ffd700;
            font-size: 1.8rem;
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-badge {
            background: rgba(255, 215, 0, 0.2);
            padding: 8px 20px;
            border-radius: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(255, 215, 0, 0.3);
        }

        .user-badge i {
            color: #ffd700;
        }

        .user-badge.admin i {
            color: #ff6b6b;
        }

        .user-type {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .user-name {
            font-weight: 600;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 40px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .nav-link i {
            font-size: 1rem;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5253);
            color: white;
            padding: 8px 20px;
            border-radius: 40px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(238, 82, 83, 0.4);
        }

        /* Header Section */
        .results-header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
            animation: fadeInDown 0.8s ease;
        }

        .results-header h1 {
            font-size: 2.8rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .results-header h1 i {
            color: #ffd700;
            font-size: 3rem;
            animation: pulse 2s infinite;
        }

        .last-updated {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            display: inline-block;
            padding: 10px 25px;
            border-radius: 50px;
            font-size: 0.95rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .last-updated i {
            color: #ffd700;
            margin-right: 8px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .stat-icon {
            font-size: 2.5rem;
            color: #ffd700;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .turnout-progress {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            margin: 15px 0 5px;
            overflow: hidden;
        }

        .turnout-bar {
            height: 100%;
            background: linear-gradient(90deg, #ffd700, #ffa500);
            border-radius: 10px;
            transition: width 1s ease;
        }

        /* Position Cards */
        .results-card {
            background: white;
            border-radius: 30px;
            margin-bottom: 40px;
            padding: 30px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.5s ease;
            transition: all 0.3s ease;
        }

        .results-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
        }

        .results-card h2 {
            color: #1e3c72;
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .results-card h2 i {
            color: #ffd700;
            font-size: 2rem;
        }

        /* Table Styles */
        .table-wrapper {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 5px;
            overflow-x: auto;
            margin-bottom: 25px;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .results-table th {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            font-weight: 500;
            padding: 15px;
            text-align: left;
        }

        .results-table th:first-child {
            border-radius: 15px 0 0 0;
        }

        .results-table th:last-child {
            border-radius: 0 15px 0 0;
        }

        .results-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .results-table tr:last-child td {
            border-bottom: none;
        }

        .results-table tr:hover td {
            background: rgba(30, 60, 114, 0.05);
        }

        .candidate-name {
            font-weight: 600;
            color: #1e3c72;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .candidate-name i {
            color: #ffd700;
        }

        .winner-badge {
            background: linear-gradient(135deg, #ffd700, #ffa500);
            color: #1e3c72;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 10px;
        }

        .votes-count {
            font-weight: 600;
            color: #1e3c72;
        }

        .percentage {
            background: #e8f0fe;
            padding: 5px 10px;
            border-radius: 50px;
            font-weight: 500;
            color: #1e3c72;
            display: inline-block;
        }

        /* Chart Container */
        .chart-container {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 20px;
            margin-top: 20px;
        }

        canvas {
            max-height: 250px;
            width: 100% !important;
        }

        /* Admin Controls */
        .admin-controls {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-controls h3 {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-controls h3 i {
            color: #ffd700;
        }

        .export-btn {
            background: white;
            color: #1e3c72;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,255,255,0.3);
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

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .nav-bar {
                flex-direction: column;
                gap: 15px;
                border-radius: 30px;
            }
            
            .nav-user {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .results-header h1 {
                font-size: 2rem;
                flex-direction: column;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .results-card {
                padding: 20px;
            }
            
            .results-card h2 {
                font-size: 1.5rem;
            }
            
            .admin-controls {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }

        /* Loading animation for stat cards */
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>

<body>

<div class="results-container">
    
    <!-- Navigation Bar -->
    <div class="nav-bar">
        <div class="nav-brand">
            <i class="fas fa-vote-yea"></i>
            <span>MUSTSO Election</span>
        </div>
        
        <div class="nav-user">
            <div class="user-badge <?= $isAdmin ? 'admin' : '' ?>">
                <i class="fas <?= $isAdmin ? 'fa-user-shield' : 'fa-user-graduate' ?>"></i>
                <div>
                    <div class="user-type"><?= $userType ?></div>
                    <div class="user-name"><?= $isAdmin ? htmlspecialchars($adminName) : 'Voter' ?></div>
                </div>
            </div>
            
            <div class="nav-links">
                <?php if($isAdmin): ?>
                    <a href="admin/dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                <?php else: ?>
                    <a href="voting_page.php" class="nav-link">
                        <i class="fas fa-vote-yea"></i>
                        <span>Vote</span>
                    </a>
                <?php endif; ?>
                
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Admin Controls (only visible to admin) -->
    <?php if($isAdmin): ?>
    <div class="admin-controls">
        <h3>
            <i class="fas fa-cog"></i>
            Admin Controls
        </h3>
        <div>
            <button class="export-btn" onclick="exportResults()">
                <i class="fas fa-download"></i>
                Export Results
            </button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Header with animation -->
    <div class="results-header">
        <h1>
            <i class="fas fa-chart-line"></i>
            Live Election Results
            <i class="fas fa-chart-pie"></i>
        </h1>
        <div class="last-updated">
            <i class="fas fa-sync-alt"></i>
            Last updated: <?= date('h:i:s A') ?> | Auto-refreshes every 15s
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value"><?= number_format($totalVoters) ?></div>
            <div class="stat-label">Total Voters</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-vote-yea"></i>
            </div>
            <div class="stat-value"><?= number_format($totalVotesCast) ?></div>
            <div class="stat-label">Votes Cast</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-percent"></i>
            </div>
            <div class="stat-value"><?= $voterTurnout ?>%</div>
            <div class="turnout-progress">
                <div class="turnout-bar" style="width: <?= $voterTurnout ?>%"></div>
            </div>
            <div class="stat-label">Voter Turnout</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-value"><?= number_format($totalCandidates) ?></div>
            <div class="stat-label">Candidates</div>
        </div>
    </div>

    <?php
    // Fetch results
    $sql = "SELECT c.id, c.name, c.position,
            COUNT(v.id) as votes
            FROM candidates c
            LEFT JOIN votes v ON c.id = v.candidate_id
            GROUP BY c.id, c.name, c.position
            ORDER BY c.position, votes DESC";
    
    $result = $conn->query($sql);
    $grouped = [];
    
    while($row = $result->fetch_assoc()){
        $grouped[$row['position']][] = $row;
    }
    
    // Position icons mapping
    $positionIcons = [
        'President' => 'fa-crown',
        'College MP' => 'fa-university',
        'Department MP' => 'fa-users',
        'Jimbo MP' => 'fa-map-marker-alt'
    ];
    
    foreach($grouped as $position => $list):
        $totalVotes = array_sum(array_column($list,'votes')) ?: 1;
        $key = md5($position);
    ?>
    
    <!-- Position Card -->
    <div class="results-card">
        <h2>
            <i class="fas <?= $positionIcons[$position] ?? 'fa-user-tie' ?>"></i>
            <?= htmlspecialchars($position) ?>
        </h2>
        
        <!-- Table -->
        <div class="table-wrapper">
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Votes</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($list as $index => $c): 
                        $percent = round(($c['votes']/$totalVotes)*100,2);
                        $isWinner = ($index === 0 && $c['votes'] > 0);
                    ?>
                    <tr>
                        <td>
                            <div class="candidate-name">
                                <i class="fas fa-user-circle"></i>
                                <?= htmlspecialchars($c['name']) ?>
                                <?php if($isWinner): ?>
                                    <span class="winner-badge">WINNER</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="votes-count"><?= number_format($c['votes']) ?></span>
                        </td>
                        <td>
                            <span class="percentage" style="background: <?= $isWinner ? 'rgba(255,215,0,0.2)' : '#e8f0fe' ?>">
                                <?= $percent ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Chart -->
        <div class="chart-container">
            <canvas id="chart_<?= $key ?>"></canvas>
        </div>
    </div>
    
    <?php endforeach; ?>

</div>

<!-- Chart Scripts -->
<script>
<?php 
// Vibrant colors for charts
$chartColors = [
    'rgba(255, 99, 132, 0.8)',
    'rgba(54, 162, 235, 0.8)',
    'rgba(255, 206, 86, 0.8)',
    'rgba(75, 192, 192, 0.8)',
    'rgba(153, 102, 255, 0.8)',
    'rgba(255, 159, 64, 0.8)',
    'rgba(199, 199, 199, 0.8)',
    'rgba(83, 102, 255, 0.8)',
    'rgba(255, 99, 255, 0.8)',
    'rgba(99, 255, 132, 0.8)'
];

foreach($grouped as $position => $list):
    $names = json_encode(array_column($list,'name'));
    $votes = json_encode(array_column($list,'votes'));
    $key = md5($position);
    $totalVotes = array_sum(array_column($list,'votes')) ?: 1;
    
    // Create color array for this chart
    $colorArray = [];
    for($i = 0; $i < count($list); $i++) {
        $colorArray[] = $chartColors[$i % count($chartColors)];
    }
?>

// Chart for <?= $position ?>
new Chart(
    document.getElementById("chart_<?= $key ?>"),
    {
        type: 'bar',
        data: {
            labels: <?= $names ?>,
            datasets: [{
                label: 'Votes',
                data: <?= $votes ?>,
                backgroundColor: <?= json_encode($colorArray) ?>,
                borderRadius: 8,
                barPercentage: 0.7,
                categoryPercentage: 0.8
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
                    titleColor: '#ffd700',
                    bodyColor: '#fff',
                    borderColor: '#ffd700',
                    borderWidth: 2,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            let value = context.raw || 0;
                            let total = <?= $totalVotes ?>;
                            let percentage = ((value / total) * 100).toFixed(2);
                            return `Votes: ${value} (${percentage}%)`;
                        }
                    }
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
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    }
);

<?php endforeach; ?>

// Export function for admin
function exportResults() {
    // Create CSV content
    let csv = "Position,Candidate,Votes,Percentage\n";
    
    <?php foreach($grouped as $position => $list): 
        $totalVotes = array_sum(array_column($list,'votes')) ?: 1;
        foreach($list as $c):
            $percent = round(($c['votes']/$totalVotes)*100,2);
    ?>
    csv += "<?= $position ?>,<?= $c['name'] ?>,<?= $c['votes'] ?>,<?= $percent ?>%\n";
    <?php 
        endforeach;
    endforeach; 
    ?>
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'election_results_<?= date('Y-m-d_H-i-s') ?>.csv';
    a.click();
}

// Add animation delays to stat cards
document.addEventListener('DOMContentLoaded', function() {
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
});
</script>

</body>
</html>