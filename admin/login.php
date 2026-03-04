<?php
session_start();
include "../config.php";

$error = "";

/* ===============================
   Process Login Only After Submit
================================*/
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if(empty($username) || empty($password)){
        $error = "Please enter username and password";
    }
    else{

        $stmt = $conn->prepare("
            SELECT * FROM admin
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->bind_param("s",$username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){

            $admin = $result->fetch_assoc();

            if(password_verify($password,$admin['password'])){

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['username'];

                header("Location: dashboard.php");
                exit();
            }
            else{
                $error = "Incorrect password";
            }

        } else {
            $error = "Admin account not found";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSTSO | Admin Login</title>
    
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

        /* Login Card */
        .login-card {
            background: white;
            border-radius: 50px;
            padding: 60px 50px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.8s ease;
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
            background: linear-gradient(90deg, #ff6b6b, #ee5253, #ff6b6b);
        }

        /* Admin Badge */
        .admin-badge {
            text-align: center;
            margin-bottom: 30px;
        }

        .badge-icon {
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

        .badge-icon i {
            transform: rotate(-45deg);
            color: #ffd700;
            font-size: 3rem;
        }

        .admin-badge h1 {
            color: #1e3c72;
            font-size: 2rem;
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
            color: #ff6b6b;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        .alert-error {
            background: linear-gradient(135deg, #ff6b6b, #ee5253);
            color: white;
        }

        .alert i {
            font-size: 1.2rem;
        }

        /* Form */
        .login-form {
            margin-top: 30px;
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
            color: #ff6b6b;
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
            border-color: #ff6b6b;
            background: white;
            box-shadow: 0 5px 20px rgba(255, 107, 107, 0.2);
        }

        .input-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #ff6b6b;
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

        /* Back Link */
        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
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

        .back-link a:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        .back-link a i {
            color: #ff6b6b;
        }

        /* Security Notice */
        .security-notice {
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            color: #666;
        }

        .security-notice i {
            color: #ff6b6b;
            font-size: 1.2rem;
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

        @keyframes pulse {
            0%, 100% { transform: rotate(45deg) scale(1); }
            50% { transform: rotate(45deg) scale(1.05); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                padding: 40px 30px;
            }
            
            .admin-badge h1 {
                font-size: 1.8rem;
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
        <div class="login-card">
            <!-- Admin Badge -->
            <div class="admin-badge">
                <div class="badge-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1>Admin Login</h1>
                <p class="subtitle">
                    <i class="fas fa-lock"></i>
                    Election Management Panel
                </p>
            </div>

            <!-- Error Message -->
            <?php if(!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" class="login-form" id="loginForm">
                <div class="input-group">
                    <label>
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <div class="input-wrapper">
                        <input type="text" 
                               name="username" 
                               placeholder="Enter admin username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               required
                               autofocus>
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

                <!-- Remember Me & Forgot Password (Optional) -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; color: #666; font-size: 0.9rem;">
                        <input type="checkbox" name="remember" style="width: auto;">
                        <span>Remember me</span>
                    </label>
                    <a href="#" style="color: #ff6b6b; text-decoration: none; font-size: 0.9rem;">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <span>Access Dashboard</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- Back to Student Login -->
            <div class="back-link">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i>
                    Back to Student Login
                </a>
            </div>

            <!-- Security Notice -->
            <div class="security-notice">
                <i class="fas fa-shield-alt"></i>
                <span>This area is restricted to authorized election administrators only. All access attempts are logged.</span>
            </div>

            <!-- System Info -->
            <div style="text-align: center; margin-top: 20px; font-size: 0.8rem; color: #999;">
                <i class="fas fa-clock"></i>
                <?= date('l, F j, Y') ?>
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
            const username = document.querySelector('input[name="username"]').value.trim();
            const password = document.querySelector('input[name="password"]').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return;
            }
            
            // Show loading state
            loginBtn.innerHTML = '<span>Authenticating...</span> <i class="fas fa-spinner fa-spin"></i>';
            loginBtn.disabled = true;
        });

        // Add focus effect to inputs
        const inputs = document.querySelectorAll('.input-wrapper input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Auto-hide error message after 5 seconds
        const errorAlert = document.querySelector('.alert-error');
        if (errorAlert) {
            setTimeout(() => {
                errorAlert.style.opacity = '0';
                setTimeout(() => errorAlert.remove(), 300);
            }, 5000);
        }
    </script>
</body>
</html>