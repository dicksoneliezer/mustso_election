<?php
session_start();
include "../config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

// Handle candidate deletion
if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $id = $_GET['delete'];
    
    // First, get the photo filename to delete
    $photoQuery = $conn->prepare("SELECT photo FROM candidates WHERE id = ?");
    $photoQuery->bind_param("i", $id);
    $photoQuery->execute();
    $photoResult = $photoQuery->get_result();
    $candidate = $photoResult->fetch_assoc();
    
    // Delete photo file if exists and not default
    if($candidate && !empty($candidate['photo']) && $candidate['photo'] != 'assets/images/user.png'){
        $photoPath = "../" . $candidate['photo'];
        if(file_exists($photoPath)){
            unlink($photoPath);
        }
    }
    
    // Delete candidate from database
    $deleteQuery = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    $deleteQuery->bind_param("i", $id);
    
    if($deleteQuery->execute()){
        $success = "Candidate deleted successfully!";
    } else {
        $error = "Error deleting candidate: " . $conn->error;
    }
}

// Get filter parameters
$filter_position = isset($_GET['position']) ? $_GET['position'] : '';
$filter_college = isset($_GET['college']) ? $_GET['college'] : '';

// Build query with filters
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM votes WHERE candidate_id = c.id) as vote_count 
          FROM candidates c WHERE 1=1";
$params = [];
$types = "";

if(!empty($filter_position)){
    $query .= " AND c.position = ?";
    $params[] = $filter_position;
    $types .= "s";
}

if(!empty($filter_college)){
    $query .= " AND c.college = ?";
    $params[] = $filter_college;
    $types .= "s";
}

$query .= " ORDER BY c.position, c.name";

$stmt = $conn->prepare($query);
if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$candidates = $stmt->get_result();

// Get unique positions and colleges for filters
$positions = $conn->query("SELECT DISTINCT position FROM candidates ORDER BY position");
$colleges = $conn->query("SELECT DISTINCT college FROM candidates WHERE college IS NOT NULL AND college != '' ORDER BY college");

// Get statistics
$totalCandidates = $candidates->num_rows;
$totalVotes = 0;
$votesResult = $conn->query("SELECT SUM(vote_count) as total FROM (SELECT COUNT(*) as vote_count FROM votes GROUP BY candidate_id) as v");
if ($votesResult) {
    $totalVotes = $votesResult->fetch_assoc()['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSTSO | Manage Candidates</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
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

        /* Navigation */
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
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-link {
            color: #1e3c72;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 40px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            background: rgba(30, 60, 114, 0.1);
        }

        .nav-link i {
            color: #ffd700;
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
            transition: all 0.3s ease;
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            color: white;
            animation: fadeInDown 0.8s ease;
        }

        .page-header h1 {
            font-size: 2.2rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-header h1 i {
            color: #ffd700;
        }

        .add-btn {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
        }

        /* Stats Summary */
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .stat-box:nth-child(1) { animation-delay: 0.1s; }
        .stat-box:nth-child(2) { animation-delay: 0.2s; }
        .stat-box:nth-child(3) { animation-delay: 0.3s; }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
        }

        /* Filters */
        .filters-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.5s ease forwards;
            animation-delay: 0.3s;
            opacity: 0;
        }

        .filters-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1e3c72;
            margin-bottom: 20px;
        }

        .filters-title i {
            color: #ffd700;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-size: 0.9rem;
            color: #1e3c72;
            font-weight: 500;
        }

        .filter-group select {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .filter-group select:focus {
            border-color: #ffd700;
            outline: none;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .apply-btn,
        .reset-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .apply-btn {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
        }

        .reset-btn {
            background: #f0f0f0;
            color: #666;
            text-decoration: none;
        }

        /* Candidates Grid */
        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .candidate-card {
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease;
        }

        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            position: relative;
            height: 120px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            display: flex;
            justify-content: center;
        }

        .candidate-avatar {
            position: absolute;
            bottom: -40px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 5px solid white;
            overflow: hidden;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .candidate-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-body {
            padding: 50px 20px 20px;
            text-align: center;
        }

        .candidate-name {
            font-size: 1.3rem;
            color: #1e3c72;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .candidate-position {
            color: #ffd700;
            font-weight: 500;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .candidate-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #666;
        }

        .detail-item i {
            color: #ffd700;
            width: 20px;
        }

        .vote-count {
            background: linear-gradient(135deg, #ffd700, #ffa500);
            color: #1e3c72;
            padding: 8px 15px;
            border-radius: 50px;
            display: inline-block;
            font-weight: 600;
            margin: 10px 0;
            font-size: 0.9rem;
        }

        .card-footer {
            display: flex;
            gap: 10px;
            padding: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .action-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .edit-btn {
            background: #1e3c72;
            color: white;
        }

        .delete-btn {
            background: #ff6b6b;
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        /* No Results */
        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 30px;
        }

        .no-results i {
            font-size: 4rem;
            color: #ffd700;
            margin-bottom: 20px;
        }

        .no-results h3 {
            color: #1e3c72;
            margin-bottom: 10px;
        }

        .no-results p {
            color: #666;
            margin-bottom: 20px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: #22c55e;
            color: white;
        }

        .alert-error {
            background: #ef4444;
            color: white;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .close-btn {
            margin-left: auto;
            cursor: pointer;
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .modal-content {
            background: white;
            border-radius: 30px;
            padding: 40px;
            max-width: 400px;
            text-align: center;
            animation: slideUp 0.3s ease;
        }

        .modal-icon {
            width: 80px;
            height: 80px;
            background: #ff6b6b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .modal-icon i {
            font-size: 3rem;
            color: white;
        }

        .modal h3 {
            color: #1e3c72;
            margin-bottom: 10px;
        }

        .modal p {
            color: #666;
            margin-bottom: 25px;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
        }

        .modal-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .confirm-btn {
            background: #ff6b6b;
            color: white;
        }

        .cancel-btn {
            background: #f0f0f0;
            color: #666;
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
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
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
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .candidates-grid {
                grid-template-columns: 1fr;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-actions {
                flex-direction: column;
            }
            
            .card-footer {
                flex-direction: column;
            }
        }
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
        
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="register_candidate.php" class="nav-link">
                <i class="fas fa-user-plus"></i>
                Add Candidate
            </a>
            <a href="../results.php" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                Results
            </a>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Alerts -->
        <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $success ?>
                <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
                <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-tasks"></i>
                Manage Candidates
            </h1>
            <a href="register_candidate.php" class="add-btn">
                <i class="fas fa-plus"></i>
                Register New Candidate
            </a>
        </div>

        <!-- Stats Summary -->
        <div class="stats-summary">
            <div class="stat-box">
                <div class="stat-label">Total Candidates</div>
                <div class="stat-number"><?= $totalCandidates ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Total Votes Received</div>
                <div class="stat-number"><?= number_format($totalVotes) ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Avg Votes per Candidate</div>
                <div class="stat-number"><?= $totalCandidates > 0 ? round($totalVotes / $totalCandidates, 1) : 0 ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <div class="filters-title">
                <i class="fas fa-filter"></i>
                <h3>Filter Candidates</h3>
            </div>
            
            <form method="GET" class="filters-grid">
                <div class="filter-group">
                    <label>Position</label>
                    <select name="position">
                        <option value="">All Positions</option>
                        <?php if($positions && $positions->num_rows > 0): ?>
                            <?php while($pos = $positions->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($pos['position']) ?>" <?= $filter_position == $pos['position'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pos['position']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>College</label>
                    <select name="college">
                        <option value="">All Colleges</option>
                        <?php if($colleges && $colleges->num_rows > 0): ?>
                            <?php while($col = $colleges->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($col['college']) ?>" <?= $filter_college == $col['college'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($col['college']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="apply-btn">
                        <i class="fas fa-search"></i>
                        Apply Filters
                    </button>
                    <a href="manage_candidates.php" class="reset-btn">
                        <i class="fas fa-times"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Candidates Grid -->
        <?php if($candidates && $candidates->num_rows > 0): ?>
            <div class="candidates-grid">
                <?php while($candidate = $candidates->fetch_assoc()): 
                    $photo = !empty($candidate['photo']) ? "../" . $candidate['photo'] : "../assets/images/user.png";
                ?>
                    <div class="candidate-card">
                        <div class="card-header">
                            <div class="candidate-avatar">
                                <img src="<?= $photo ?>" alt="<?= htmlspecialchars($candidate['name']) ?>" onerror="this.src='../assets/images/user.png'">
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <h3 class="candidate-name"><?= htmlspecialchars($candidate['name']) ?></h3>
                            <div class="candidate-position">
                                <i class="fas fa-tag"></i>
                                <?= htmlspecialchars($candidate['position']) ?>
                            </div>
                            
                            <div class="candidate-details">
                                <?php if(!empty($candidate['college'])): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-university"></i>
                                        <span><?= htmlspecialchars($candidate['college']) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if(!empty($candidate['department'])): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-book"></i>
                                        <span><?= htmlspecialchars($candidate['department']) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if(!empty($candidate['jimbo'])): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= htmlspecialchars($candidate['jimbo']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="vote-count">
                                <i class="fas fa-vote-yea"></i>
                                <?= $candidate['vote_count'] ?> Vote<?= $candidate['vote_count'] != 1 ? 's' : '' ?>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <a href="edit_candidate.php?id=<?= $candidate['id'] ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit"></i>
                                Edit
                            </a>
                            
                            <button class="action-btn delete-btn" onclick="showDeleteModal(<?= $candidate['id'] ?>, '<?= htmlspecialchars(addslashes($candidate['name'])) ?>')">
                                <i class="fas fa-trash"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-users-slash"></i>
                <h3>No Candidates Found</h3>
                <p>Try adjusting your filters or register a new candidate.</p>
                <a href="register_candidate.php" class="add-btn" style="display: inline-block; margin-top: 20px;">
                    <i class="fas fa-plus"></i>
                    Register Candidate
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Delete Candidate</h3>
            <p id="deleteMessage">Are you sure you want to delete <span id="candidateName"></span>? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="modal-btn cancel-btn" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <a href="#" id="confirmDeleteBtn" class="modal-btn confirm-btn">
                    <i class="fas fa-trash"></i>
                    Delete
                </a>
            </div>
        </div>
    </div>

    <script>
        function showDeleteModal(id, name) {
            document.getElementById('deleteModal').style.display = 'flex';
            document.getElementById('candidateName').textContent = name;
            document.getElementById('confirmDeleteBtn').href = '?delete=' + id + '<?= !empty($_SERVER['QUERY_STRING']) ? '&' . $_SERVER['QUERY_STRING'] : '' ?>';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>