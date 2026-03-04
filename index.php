<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSTSO | Democratic Election System</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            animation: float 20s infinite;
        }

        .circle1 {
            width: 500px;
            height: 500px;
            top: -250px;
            right: -100px;
            animation-delay: 0s;
        }

        .circle2 {
            width: 300px;
            height: 300px;
            bottom: -150px;
            left: -50px;
            animation-delay: 5s;
        }

        .circle3 {
            width: 200px;
            height: 200px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(50px, 50px) scale(1.1); }
            50% { transform: translate(100px, -50px) scale(0.9); }
            75% { transform: translate(-50px, 100px) scale(1.05); }
        }

        /* Main Container */
        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
            position: relative;
            z-index: 1;
        }

        /* Split Layout */
        .split-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            max-width: 1400px;
            width: 100%;
            align-items: center;
        }

        /* Hero Section */
        .hero-section {
            color: white;
            padding-right: 40px;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(255, 215, 0, 0.2);
            backdrop-filter: blur(10px);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 215, 0, 0.3);
            animation: fadeInUp 0.8s ease;
        }

        .hero-badge i {
            color: #ffd700;
            margin-right: 8px;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
            animation: fadeInUp 0.8s ease 0.1s both;
        }

        .hero-title span {
            color: #ffd700;
            display: block;
            font-size: 3rem;
        }

        .hero-description {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 40px;
            line-height: 1.6;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 40px;
            animation: fadeInUp 0.8s ease 0.3s both;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 15px 20px;
            border-radius: 60px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.2);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ffd700, #ffa500);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature-icon i {
            color: #1e3c72;
            font-size: 1.2rem;
        }

        .feature-text {
            font-weight: 500;
        }

        /* Login Card */
        .login-card {
            background: white;
            border-radius: 50px;
            padding: 60px 50px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            animation: slideInRight 0.8s ease;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(90deg, #ffd700, #ffa500, #ffd700);
        }

        /* Logo */
        .logo-wrapper {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(45deg);
            transition: transform 0.3s ease;
            animation: pulse 2s infinite;
        }

        .logo img {
            width: 60%;
            transform: rotate(-45deg);
            filter: brightness(0) invert(1);
        }

        .logo-wrapper h1 {
            color: #1e3c72;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #666;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .subtitle i {
            color: #ffd700;
        }

        /* Form */
        .login-form {
            margin-top: 40px;
        }

        .input-group {
            margin-bottom: 25px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #1e3c72;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .input-group label i {
            color: #ffd700;
            margin-right: 8px;
            width: 20px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            width: 100%;
            padding: 18px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #ffd700;
            background: white;
            box-shadow: 0 5px 20px rgba(255, 215, 0, 0.2);
        }

        .input-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #ffd700;
        }

        /* Login Button */
        .login-btn {
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
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .login-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(30, 60, 114, 0.4);
        }

        .login-btn i {
            transition: transform 0.3s ease;
        }

        .login-btn:hover i {
            transform: translateX(5px);
        }

        /* Register Link */
        .register-section {
            text-align: center;
            margin-top: 25px;
            padding: 20px 0 0;
            border-top: 2px solid #f0f0f0;
        }

        .register-text {
            color: #666;
            margin-bottom: 10px;
        }

        .register-link {
            display: inline-block;
            background: linear-gradient(135deg, #ffd700, #ffa500);
            color: #1e3c72;
            padding: 12px 35px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }

        .register-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.4);
        }

        .register-link i {
            margin-right: 8px;
        }

        /* Admin Login */
        .admin-section {
            text-align: center;
            margin-top: 25px;
        }

        .admin-link {
            color: #1e3c72;
            text-decoration: none;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 30px;
            background: #f0f0f0;
            transition: all 0.3s ease;
        }

        .admin-link:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        .admin-link i {
            color: #ff6b6b;
        }

        /* Stats Counter */
        .stats-counter {
            display: flex;
            justify-content: space-around;
            margin-top: 40px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 60px;
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #ffd700;
        }

        .stat-label {
            font-size: 0.8rem;
            opacity: 0.9;
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

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% { transform: rotate(45deg) scale(1); }
            50% { transform: rotate(45deg) scale(1.05); }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .hero-title span {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 768px) {
            .split-layout {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .hero-section {
                padding-right: 0;
                text-align: center;
            }
            
            .features-grid {
                max-width: 400px;
                margin-left: auto;
                margin-right: auto;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .login-card {
                padding: 40px 30px;
            }
            
            .stats-counter {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .stat-item {
                flex: 1 1 40%;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="bg-circle circle1"></div>
        <div class="bg-circle circle2"></div>
        <div class="bg-circle circle3"></div>
    </div>

    <div class="container">
        <div class="split-layout">
            <!-- Left Side - Hero Section -->
            <div class="hero-section">
                <div class="hero-badge">
                    <i class="fas fa-check-circle"></i>
                    Secure • Transparent • Democratic
                </div>
                
                <h1 class="hero-title">
                    Your Voice,
                    <span>Your Vote,</span>
                    Your Future
                </h1>
                
                <p class="hero-description">
                    Welcome to the Mbeya University of Science and Technology 
                    Student Organization (MUSTSO) electronic voting system. 
                    Experience a seamless, secure, and transparent electoral process.
                </p>

                <!-- Features Grid -->
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <span class="feature-text">Secure Voting</span>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="feature-text">Live Results</span>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <span class="feature-text">Mobile Friendly</span>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <span class="feature-text">Easy Access</span>
                    </div>
                </div>

                <!-- Stats Counter -->
                <?php
                // Include database connection
                include "config.php";

                // Initialize variables with default values
                $totalStudents = 1250;  // Default sample data
                $totalCandidates = 24;
                $totalVotes = 890;

                // Try to get real data from database
                if(isset($conn) && $conn && !$conn->connect_error){
                    
                    // Get total students
                    $studentsResult = $conn->query("SELECT COUNT(*) as total FROM students");
                    if($studentsResult && $studentsResult->num_rows > 0){
                        $row = $studentsResult->fetch_assoc();
                        $totalStudents = $row['total'] ?: $totalStudents;
                    }
                    
                    // Get total candidates
                    $candidatesResult = $conn->query("SELECT COUNT(*) as total FROM candidates");
                    if($candidatesResult && $candidatesResult->num_rows > 0){
                        $row = $candidatesResult->fetch_assoc();
                        $totalCandidates = $row['total'] ?: $totalCandidates;
                    }
                    
                    // Get total votes cast
                    $votesResult = $conn->query("SELECT COUNT(*) as total FROM votes");
                    if($votesResult && $votesResult->num_rows > 0){
                        $row = $votesResult->fetch_assoc();
                        $totalVotes = $row['total'] ?: $totalVotes;
                    }
                }
                ?>
                
                <div class="stats-counter">
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($totalStudents) ?></div>
                        <div class="stat-label">Registered Voters</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($totalCandidates) ?></div>
                        <div class="stat-label">Candidates</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($totalVotes) ?></div>
                        <div class="stat-label">Votes Cast</div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Card -->
            <div class="login-card">
                <!-- Logo -->
                <div class="logo-wrapper">
                    <div class="logo">
                        <img src="assets/images/must_logo.png" alt="MUST Logo" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\' viewBox=\'0 0 24 24\' fill=\'%23ffd700\'%3E%3Cpath d=\'M12 2L2 7v10l10 5 10-5V7l-10-5z\'/%3E%3C/svg%3E'">
                    </div>
                    <h1>MUSTSO Election</h1>
                    <p class="subtitle">
                        <i class="fas fa-graduation-cap"></i>
                        Student Login Portal
                    </p>
                </div>

                <!-- Login Form -->
                <form action="login.php" method="POST" class="login-form" id="loginForm">
                    <div class="input-group">
                        <label>
                            <i class="fas fa-id-card"></i>
                            Registration Number
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   name="reg_no" 
                                   placeholder="e.g., 2024/CS/001" 
                                   required
                                   pattern="[A-Za-z0-9/-]+"
                                   title="Enter your valid registration number">
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="input-wrapper">
                            <input type="password" 
                                   name="password" 
                                   placeholder="••••••••" 
                                   required
                                   minlength="6">
                            <i class="fas fa-eye input-icon" 
                               id="togglePassword"
                               style="cursor: pointer;"
                               onclick="togglePasswordVisibility()"></i>
                        </div>
                    </div>

                    <button type="submit" class="login-btn" id="loginBtn">
                        <span>Access Voting Portal</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <!-- Register Section -->
                <div class="register-section">
                    <p class="register-text">New to MUSTSO Election System?</p>
                    <a href="register.html" class="register-link">
                        <i class="fas fa-user-plus"></i>
                        Create Your Account
                    </a>
                </div>

                <!-- Admin Login -->
                <div class="admin-section">
                    <a href="admin/login.php" class="admin-link">
                        <i class="fas fa-user-shield"></i>
                        Administrator Login
                    </a>
                </div>

                <!-- System Info -->
                <div style="text-align: center; margin-top: 25px; font-size: 0.8rem; color: #999;">
                    <i class="fas fa-clock"></i>
                    Election Period: <?= date('F j, Y') ?> - Ongoing
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Password visibility toggle
        function togglePasswordVisibility() {
            const passwordInput = document.querySelector('input[name="password"]');
            const toggleIcon = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form submission animation
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        
        loginForm.addEventListener('submit', function(e) {
            const regNo = document.querySelector('input[name="reg_no"]').value.trim();
            const password = document.querySelector('input[name="password"]').value.trim();
            
            if (!regNo || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return;
            }
            
            // Show loading state
            loginBtn.innerHTML = '<span>Processing...</span> <i class="fas fa-spinner fa-spin"></i>';
            loginBtn.disabled = true;
        });

        // Add animation to feature items on scroll
        const observerOptions = {
            threshold: 0.2,
            rootMargin: '0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-item').forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            item.style.transition = 'all 0.5s ease';
            observer.observe(item);
        });
    </script>
</body>
</html>