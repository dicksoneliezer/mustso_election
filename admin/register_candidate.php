<?php
session_start();
include "../config.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

// Get admin info for welcome message
$admin_id = $_SESSION['admin_id'];
$adminQuery = $conn->prepare("SELECT username FROM admin WHERE id = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();
$admin = $adminResult->fetch_assoc();
$adminName = $admin['username'] ?? 'Admin';

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate required fields
    $errors = [];
    
    if(empty($_POST['name'])){
        $errors[] = "Candidate name is required";
    }
    
    if(empty($_POST['position'])){
        $errors[] = "Position is required";
    }
    
    // If no errors, proceed with insertion
    if(empty($errors)){
        
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $position = mysqli_real_escape_string($conn, $_POST['position']);
        $college = mysqli_real_escape_string($conn, $_POST['college']);
        $department = mysqli_real_escape_string($conn, $_POST['department']);
        $jimbo = mysqli_real_escape_string($conn, $_POST['jimbo']);
        
        $photo = "";
        
        // Handle photo upload
        if(!empty($_FILES['photo']['name'])){
            $target_dir = "../assets/images/candidates/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $imageFileType = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            
            // Validate file type
            if(in_array($imageFileType, $allowed_types)){
                // Generate unique filename
                $photo = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", $_FILES['photo']['name']);
                $target_file = $target_dir . $photo;
                
                // Check file size (max 5MB)
                if($_FILES['photo']['size'] > 5000000){
                    $errors[] = "File is too large. Maximum size is 5MB.";
                } else {
                    if(move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)){
                        // Success
                    } else {
                        $errors[] = "Error uploading file.";
                        $photo = "";
                    }
                }
            } else {
                $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
        }
        
        // If still no errors, insert into database
        if(empty($errors)){
            // Note: I removed 'votes' column as it doesn't exist in your table
            $sql = "INSERT INTO candidates (name, position, college, department, jimbo, photo) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $name, $position, $college, $department, $jimbo, $photo);
            
            if($stmt->execute()){
                $success = "Candidate added successfully!";
            } else {
                $errors[] = "Database error: " . $conn->error;
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
    <title>MUSTSO | Register Candidate</title>
    
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
            padding: 20px;
        }

        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 60px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
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
        }

        .user-name {
            font-weight: 500;
            color: #1e3c72;
        }

        .back-btn {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 10px 22px;
            border-radius: 50px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(30, 60, 114, 0.3);
        }

        /* Main Container */
        .container {
            max-width: 700px;
            margin: 0 auto;
        }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 40px;
            padding: 50px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.8s ease;
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(90deg, #ffd700, #ffa500, #ffd700);
        }

        /* Header */
        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header h2 {
            color: #1e3c72;
            font-size: 2.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .form-header h2 i {
            color: #ffd700;
            font-size: 2.5rem;
        }

        .form-header p {
            color: #666;
            font-size: 1rem;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
        }

        .alert-error {
            background: linear-gradient(135deg, #ff6b6b, #ee5253);
            color: white;
        }

        .alert i {
            font-size: 1.2rem;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #1e3c72;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group label i {
            color: #ffd700;
            margin-right: 8px;
            width: 20px;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #ffd700;
            background: white;
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.2);
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%231e3c72' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 16px;
        }

        /* File Upload */
        .file-upload {
            border: 2px dashed #e0e0e0;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .file-upload:hover {
            border-color: #ffd700;
            background: rgba(255, 215, 0, 0.05);
        }

        .file-upload i {
            font-size: 3rem;
            color: #ffd700;
            margin-bottom: 10px;
        }

        .file-upload p {
            color: #666;
            margin-bottom: 5px;
        }

        .file-upload small {
            color: #999;
            font-size: 0.8rem;
        }

        .file-upload input {
            display: none;
        }

        .file-info {
            display: none;
            margin-top: 15px;
            padding: 10px;
            background: #e8f0fe;
            border-radius: 12px;
            color: #1e3c72;
            font-size: 0.9rem;
        }

        /* Info Grid for Position Details */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1e3c72;
            font-size: 0.9rem;
        }

        .info-item i {
            color: #ffd700;
            width: 20px;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 30px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(30, 60, 114, 0.4);
        }

        .submit-btn i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .submit-btn:hover i {
            transform: translateX(5px);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Loading Spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Animations */
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

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .form-card {
                padding: 30px 20px;
            }
            
            .form-header h2 {
                font-size: 1.8rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Required Field Indicator */
        .required::after {
            content: '*';
            color: #ff6b6b;
            margin-left: 4px;
        }

        /* Character Counter */
        .char-counter {
            text-align: right;
            font-size: 0.8rem;
            color: #999;
            margin-top: 5px;
        }

        /* Tooltip */
        .tooltip {
            position: relative;
            display: inline-block;
            margin-left: 5px;
            color: #ffd700;
            cursor: help;
        }

        .tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1e3c72;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            white-space: nowrap;
            z-index: 10;
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
        
        <div class="user-menu">
            <div class="user-info">
                <i class="fas fa-user-shield"></i>
                <span class="user-name"><?= htmlspecialchars($adminName) ?></span>
            </div>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Dashboard
            </a>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <div class="form-card">
            <!-- Header -->
            <div class="form-header">
                <h2>
                    <i class="fas fa-user-plus"></i>
                    Register Candidate
                </h2>
                <p>Add a new candidate to the election</p>
            </div>

            <!-- Alerts -->
            <?php if(!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul style="margin-left: 20px;">
                        <?php foreach($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if(isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" enctype="multipart/form-data" id="candidateForm">
                <!-- Candidate Name -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-user"></i>
                        Full Name <span class="required"></span>
                    </label>
                    <input type="text" 
                           name="name" 
                           class="form-control" 
                           placeholder="e.g., John Doe"
                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                           maxlength="100"
                           required>
                    <div class="char-counter">0/100</div>
                </div>

                <!-- Position -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-tag"></i>
                        Position <span class="required"></span>
                        <span class="tooltip" data-tooltip="Select the position the candidate is running for">ⓘ</span>
                    </label>
                    <select name="position" class="form-control" required>
                        <option value="">-- Select Position --</option>
                        <option value="President" <?= (isset($_POST['position']) && $_POST['position'] == 'President') ? 'selected' : '' ?>>President</option>
                        <option value="College MP" <?= (isset($_POST['position']) && $_POST['position'] == 'College MP') ? 'selected' : '' ?>>College MP</option>
                        <option value="Department MP" <?= (isset($_POST['position']) && $_POST['position'] == 'Department MP') ? 'selected' : '' ?>>Department MP</option>
                        <option value="Jimbo MP" <?= (isset($_POST['position']) && $_POST['position'] == 'Jimbo MP') ? 'selected' : '' ?>>Jimbo MP</option>
                    </select>
                </div>

                <!-- Dynamic Info based on position -->
                <div class="info-grid" id="positionInfo">
                    <div class="info-item">
                        <i class="fas fa-info-circle"></i>
                        <span>Select a position to see required fields</span>
                    </div>
                </div>

                <!-- College (shown for College MP) -->
                <div class="form-group position-field" data-positions="College MP">
                    <label>
                        <i class="fas fa-university"></i>
                        College
                    </label>
                    <input type="text" 
                           name="college" 
                           class="form-control" 
                           placeholder="e.g., Engineering"
                           value="<?= isset($_POST['college']) ? htmlspecialchars($_POST['college']) : '' ?>">
                </div>

                <!-- Department (shown for Department MP) -->
                <div class="form-group position-field" data-positions="Department MP">
                    <label>
                        <i class="fas fa-book"></i>
                        Department
                    </label>
                    <input type="text" 
                           name="department" 
                           class="form-control" 
                           placeholder="e.g., Computer Science"
                           value="<?= isset($_POST['department']) ? htmlspecialchars($_POST['department']) : '' ?>">
                </div>

                <!-- Jimbo (shown for Jimbo MP) -->
                <div class="form-group position-field" data-positions="Jimbo MP">
                    <label>
                        <i class="fas fa-map-marker-alt"></i>
                        Jimbo
                    </label>
                    <select name="jimbo" class="form-control">
                        <option value="">-- Select Jimbo --</option>
                        <option value="kati" <?= (isset($_POST['jimbo']) && $_POST['jimbo'] == 'kati') ? 'selected' : '' ?>>Kati</option>
                        <option value="mashariki" <?= (isset($_POST['jimbo']) && $_POST['jimbo'] == 'mashariki') ? 'selected' : '' ?>>Mashariki</option>
                        <option value="magharibi" <?= (isset($_POST['jimbo']) && $_POST['jimbo'] == 'magharibi') ? 'selected' : '' ?>>Magharibi</option>
                    </select>
                </div>

                <!-- Photo Upload -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-camera"></i>
                        Candidate Photo
                    </label>
                    <div class="file-upload" onclick="document.getElementById('photoInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click to upload or drag and drop</p>
                        <small>PNG, JPG, JPEG up to 5MB</small>
                        <input type="file" 
                               id="photoInput" 
                               name="photo" 
                               accept="image/*">
                        <div class="file-info" id="fileInfo">
                            <i class="fas fa-check-circle" style="color:#22c55e;"></i>
                            <span id="fileName"></span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn" id="submitBtn">
                    <span>Register Candidate</span>
                    <i class="fas fa-arrow-right"></i>
                    <span class="spinner" id="spinner"></span>
                </button>
            </form>

            <!-- Footer Links -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="manage_candidates.php" style="color: #1e3c72; text-decoration: none; font-size: 0.9rem;">
                    <i class="fas fa-list"></i> View All Candidates
                </a>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Character Counter
        const nameInput = document.querySelector('input[name="name"]');
        const charCounter = document.querySelector('.char-counter');
        
        nameInput.addEventListener('input', function() {
            const length = this.value.length;
            charCounter.textContent = `${length}/100`;
            charCounter.style.color = length > 90 ? '#ff6b6b' : '#999';
        });

        // Dynamic field visibility based on position
        const positionSelect = document.querySelector('select[name="position"]');
        const positionFields = document.querySelectorAll('.position-field');
        const positionInfo = document.getElementById('positionInfo');

        function updatePositionFields() {
            const selected = positionSelect.value;
            
            // Update info grid
            let infoHtml = '';
            switch(selected) {
                case 'College MP':
                    infoHtml = `
                        <div class="info-item"><i class="fas fa-info-circle"></i> <span>College is required for this position</span></div>
                    `;
                    break;
                case 'Department MP':
                    infoHtml = `
                        <div class="info-item"><i class="fas fa-info-circle"></i> <span>Department is required for this position</span></div>
                    `;
                    break;
                case 'Jimbo MP':
                    infoHtml = `
                        <div class="info-item"><i class="fas fa-info-circle"></i> <span>Jimbo is required for this position</span></div>
                    `;
                    break;
                case 'President':
                    infoHtml = `
                        <div class="info-item"><i class="fas fa-info-circle"></i> <span>No additional fields required</span></div>
                    `;
                    break;
                default:
                    infoHtml = `
                        <div class="info-item"><i class="fas fa-info-circle"></i> <span>Select a position to see required fields</span></div>
                    `;
            }
            positionInfo.innerHTML = infoHtml;

            // Show/hide position-specific fields
            positionFields.forEach(field => {
                const positions = field.dataset.positions.split(',');
                if (positions.includes(selected)) {
                    field.style.display = 'block';
                    // Make required if it's a specific position
                    if (selected === 'College MP' && field.querySelector('input[name="college"]')) {
                        field.querySelector('input').required = true;
                    } else if (selected === 'Department MP' && field.querySelector('input[name="department"]')) {
                        field.querySelector('input').required = true;
                    } else if (selected === 'Jimbo MP' && field.querySelector('select[name="jimbo"]')) {
                        field.querySelector('select').required = true;
                    }
                } else {
                    field.style.display = 'none';
                    // Remove required
                    const input = field.querySelector('input, select');
                    if (input) {
                        input.required = false;
                    }
                }
            });
        }

        positionSelect.addEventListener('change', updatePositionFields);
        updatePositionFields(); // Initial call

        // File upload preview
        const photoInput = document.getElementById('photoInput');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');

        photoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileInfo.style.display = 'block';
                fileName.textContent = this.files[0].name;
            } else {
                fileInfo.style.display = 'none';
            }
        });

        // Form submission animation
        const form = document.getElementById('candidateForm');
        const submitBtn = document.getElementById('submitBtn');
        const spinner = document.getElementById('spinner');
        const btnText = submitBtn.querySelector('span');

        form.addEventListener('submit', function(e) {
            // Validate at least one of college/department/jimbo based on position
            const position = positionSelect.value;
            let isValid = true;

            if (position === 'College MP') {
                const college = document.querySelector('input[name="college"]');
                if (!college.value.trim()) {
                    alert('College is required for College MP position');
                    isValid = false;
                }
            } else if (position === 'Department MP') {
                const dept = document.querySelector('input[name="department"]');
                if (!dept.value.trim()) {
                    alert('Department is required for Department MP position');
                    isValid = false;
                }
            } else if (position === 'Jimbo MP') {
                const jimbo = document.querySelector('select[name="jimbo"]');
                if (!jimbo.value) {
                    alert('Jimbo is required for Jimbo MP position');
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            btnText.textContent = 'Processing...';
            spinner.style.display = 'inline-block';
        });

        // Auto-hide success message
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 300);
            }, 5000);
        }
    </script>
</body>
</html>