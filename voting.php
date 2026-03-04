<?php
session_start();
include "config.php";

/* Login Protection */
if(!isset($_SESSION['student_id'])){
    header("Location:index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$student_name = explode(' ', $student['name'])[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSTSO | Voting Portal</title>
    
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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

        /* Enhanced Navigation */
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 1rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid #ffd700;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-logo {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo i {
            font-size: 1.8rem;
            color: #ffd700;
            animation: pulse 2s infinite;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-main {
            font-size: 1.3rem;
            font-weight: 600;
            color: white;
            line-height: 1.2;
        }

        .brand-sub {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 40px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .nav-link i {
            color: #ffd700;
            font-size: 1rem;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .nav-link.active {
            background: rgba(255, 215, 0, 0.2);
            border: 1px solid rgba(255, 215, 0, 0.3);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 5px 5px 15px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-greeting {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ffd700, #ffa500);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e3c72;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5253);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            margin-left: 5px;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(238, 82, 83, 0.4);
        }

        /* Breadcrumb */
        .breadcrumb {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 12px 30px;
            margin: 20px auto;
            max-width: 1400px;
            border-radius: 60px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }

        .breadcrumb a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }

        .breadcrumb a:hover {
            opacity: 1;
            color: #ffd700;
        }

        .breadcrumb i {
            color: #ffd700;
            font-size: 0.8rem;
        }

        .breadcrumb span {
            color: #ffd700;
        }

        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
        }

        .page-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .page-title i {
            color: #ffd700;
        }

        .student-badge {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .badge-item {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .badge-item i {
            color: #ffd700;
        }

        .badge-item.off-campus {
            background: linear-gradient(135deg, #ffd700, #ffa500);
            color: #1e3c72;
        }

        .badge-item.off-campus i {
            color: #1e3c72;
        }

        .position-card {
            background: white;
            border-radius: 30px;
            margin-bottom: 40px;
            padding: 30px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .position-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            flex-wrap: wrap;
            gap: 15px;
        }

        .position-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .position-title i {
            font-size: 2rem;
            color: #1e3c72;
            background: linear-gradient(135deg, #ffd700, #ffa500);
            padding: 15px;
            border-radius: 15px;
        }

        .position-title h2 {
            color: #1e3c72;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .vote-count-badge {
            background: #f0f0f0;
            padding: 8px 16px;
            border-radius: 50px;
            color: #1e3c72;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .candidate-card {
            background: #f8f9fa;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .candidate-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .card-image {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
        }

        .card-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .candidate-card:hover .card-image img {
            transform: scale(1.1);
        }

        .card-body {
            padding: 20px;
            text-align: center;
        }

        .candidate-name {
            font-size: 1.2rem;
            color: #1e3c72;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .candidate-college,
        .candidate-jimbo {
            font-size: 0.9rem;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .candidate-college i,
        .candidate-jimbo i {
            color: #ffd700;
        }

        .card-footer {
            padding: 20px;
            background: white;
            border-top: 1px solid #f0f0f0;
        }

        .vote-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .vote-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(30, 60, 114, 0.3);
        }

        .no-candidates {
            text-align: center;
            padding: 40px;
            color: #666;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .no-candidates i {
            font-size: 3rem;
            color: #ffd700;
            margin-bottom: 15px;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                justify-content: center;
            }
            
            .user-menu {
                width: 100%;
                justify-content: center;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .position-header {
                flex-direction: column;
                text-align: center;
            }
            
            .position-title {
                flex-direction: column;
            }
            
            .candidates-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <div class="brand-logo">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-main">MUSTSO</span>
                    <span class="brand-sub">Election System</span>
                </div>
            </div>

            <div class="nav-links">
                <a href="voting_page.php" class="nav-link active">
                    <i class="fas fa-vote-yea"></i>
                    Vote
                </a>
                <a href="results.php" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    Results
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i>
                    Profile
                </a>
                <a href="help.php" class="nav-link">
                    <i class="fas fa-question-circle"></i>
                    Help
                </a>
            </div>

            <div class="user-menu">
                <div class="user-info">
                    <span class="user-greeting">Welcome back,</span>
                    <span class="user-name"><?php echo htmlspecialchars($student_name); ?></span>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($student_name, 0, 1)); ?>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.html"><i class="fas fa-home"></i> Home</a>
        <i class="fas fa-chevron-right"></i>
        <span>Voting Portal</span>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-check-circle"></i>
                Cast Your Vote
            </h1>

            <div class="student-badge">
                <span class="badge-item">
                    <i class="fas fa-graduation-cap"></i>
                    <?php echo htmlspecialchars($student['college']); ?>
                </span>
                <span class="badge-item">
                    <i class="fas fa-book"></i>
                    <?php echo htmlspecialchars($student['department']); ?>
                </span>
                <?php if($student['study_mode'] == "off_campus"): ?>
                <span class="badge-item off-campus">
                    <i class="fas fa-home"></i>
                    <?php echo ucfirst($student['jimbo']); ?>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <?php
        $positions = ["President", "College MP", "Department MP", "Jimbo MP"];
        $positionIcons = [
            "President" => "fa-crown",
            "College MP" => "fa-university",
            "Department MP" => "fa-users",
            "Jimbo MP" => "fa-map-marker-alt"
        ];

        foreach($positions as $pos):
            $query = "SELECT * FROM candidates WHERE position=?";
            $params = [$pos];
            $types = "s";

            if($pos == "College MP"){
                $query .= " AND LOWER(college)=?";
                $params[] = strtolower($student['college']);
                $types .= "s";
            }

            if($pos == "Department MP"){
                $query .= " AND LOWER(department)=?";
                $params[] = strtolower($student['department']);
                $types .= "s";
            }

            if($pos == "Jimbo MP"){
                if($student['study_mode'] == "off_campus"){
                    $query .= " AND LOWER(jimbo)=?";
                    $params[] = strtolower($student['jimbo']);
                    $types .= "s";
                } else {
                    continue;
                }
            }

            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $candidates = $stmt->get_result();
        ?>

        <div class="position-card">
            <div class="position-header">
                <div class="position-title">
                    <i class="fas <?php echo $positionIcons[$pos] ?? 'fa-user-tie'; ?>"></i>
                    <h2><?php echo $pos; ?></h2>
                </div>
                <span class="vote-count-badge">
                    <i class="fas fa-users"></i>
                    <?php echo $candidates->num_rows; ?> Candidate(s)
                </span>
            </div>

            <?php if($candidates->num_rows > 0): ?>
                <div class="candidates-grid">
                    <?php while($row = $candidates->fetch_assoc()):
                        $photo = !empty($row['photo']) ? $row['photo'] : "assets/images/user.png";
                    ?>
                        <div class="candidate-card">
                            <div class="card-image">
                                <img src="<?php echo $photo; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" onerror="this.src='assets/images/user.png'">
                            </div>
                            <div class="card-body">
                                <h3 class="candidate-name">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </h3>
                                <?php if($pos == "College MP"): ?>
                                <p class="candidate-college">
                                    <i class="fas fa-university"></i>
                                    <?php echo htmlspecialchars($row['college']); ?>
                                </p>
                                <?php endif; ?>
                                <?php if($pos == "Jimbo MP"): ?>
                                <p class="candidate-jimbo">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($row['jimbo']); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <form method="POST" action="vote.php" onsubmit="return confirm('Vote for <?php echo addslashes($row['name']); ?>?');">
                                    <input type="hidden" name="candidate_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="position" value="<?php echo $pos; ?>">
                                    <button type="submit" class="vote-btn">
                                        <i class="fas fa-check-circle"></i>
                                        Vote Now
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-candidates">
                    <i class="fas fa-info-circle"></i>
                    <p>No candidates available for this position</p>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </main>
</body>
</html>