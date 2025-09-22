<?php 
    session_start();
    require_once __DIR__ . '/../water/connection.php';

    $error = "";
    $success = false;
    $full_name = "";
    // Handle POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_success'] = true;
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid email or password.";
            header("Location: login.php");
            exit();
        }
    }

    // Handle GET (after redirect)
    if (isset($_SESSION['login_error'])) {
        $error = $_SESSION['login_error'];
        unset($_SESSION['login_error']);
    }
    if (isset($_SESSION['login_success'])) {
        $success = true;
        $full_name = $_SESSION['full_name'] ?? '';
        unset($_SESSION['login_success']);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP AquaMonitor - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ...existing CSS from your prompt... */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #8B0000 0%, #B22222 25%, #DC143C 50%, #B22222 75%, #8B0000 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .bg-pattern { position: absolute; width: 100%; height: 100%; opacity: 0.1; z-index: 0; }
        .bg-pattern::before { content: ''; position: absolute; width: 200%; height: 200%; background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 2px, transparent 2px), radial-gradient(circle at 75% 75%, rgba(255,255,255,0.05) 1px, transparent 1px); background-size: 60px 60px, 40px 40px; animation: patternMove 20s linear infinite; }
        @keyframes patternMove { 0% { transform: translate(0, 0); } 100% { transform: translate(60px, 60px); } }
        .main-container { position: relative; z-index: 1; display: flex; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border-radius: 24px; box-shadow: 0 32px 64px rgba(0, 0, 0, 0.25), 0 16px 32px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.1); overflow: hidden; max-width: 900px; width: 90%; min-height: 600px; animation: containerSlide 1s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
        @keyframes containerSlide { from { opacity: 0; transform: translateY(40px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .left-panel { flex: 1; background: linear-gradient(135deg, #8B0000 0%, #DC143C 100%); padding: 3rem; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; position: relative; overflow: hidden; }
        .left-panel::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 30px 30px; animation: dotPattern 15s linear infinite; }
        @keyframes dotPattern { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .logo-section { position: relative; z-index: 2; animation: logoFloat 1.5s ease-out 0.5s both; }
        @keyframes logoFloat { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        /* .logo-icon { width: 120px; height: 120px; background: rgba(255, 255, 255, 0.95); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1); } */
        .pup-logo { width: 80px; height: 80px; background-size: contain; background-repeat: no-repeat; background-position: center; }
        .logo-title { color: white; font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); letter-spacing: -0.5px; }
        .logo-subtitle { color: rgba(255, 255, 255, 0.9); font-size: 1.1rem; font-weight: 400; line-height: 1.6; max-width: 300px; }
        .right-panel { flex: 1; padding: 3rem; display: flex; flex-direction: column; justify-content: center; background: linear-gradient(135deg, #fafafa 0%, #ffffff 100%); }
        .login-header { text-align: center; margin-bottom: 2.5rem; animation: headerSlide 1s ease-out 0.7s both; }
        @keyframes headerSlide { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
        .login-title { font-size: 2rem; font-weight: 600; color: #1a1a1a; margin-bottom: 0.5rem; letter-spacing: -0.5px; }
        .login-subtitle { color: #666; font-size: 1rem; font-weight: 400; }
        .login-form { animation: formSlide 1s ease-out 0.9s both; }
        @keyframes formSlide { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
        .form-group { margin-bottom: 1.5rem; position: relative; }
        .form-label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem; transition: color 0.3s ease; }
        .input-wrapper { position: relative; }
        .form-input { width: 100%; padding: 1rem 3rem 1rem 1rem; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1rem; font-weight: 400; background: #ffffff; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); color: #1f2937; }
        .form-input:focus { outline: none; border-color: #DC143C; box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1); transform: translateY(-1px); }
        .form-input:focus + .input-icon { color: #DC143C; transform: translateY(-50%) scale(1.1); }
        .input-icon { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); font-size: 1.25rem; color: #9ca3af; cursor: pointer; transition: all 0.3s ease; user-select: none; }
        .input-icon:hover { color: #DC143C; transform: translateY(-50%) scale(1.1); }
        .login-button { width: 100%; padding: 1rem; background: linear-gradient(135deg, #8B0000 0%, #DC143C 100%); color: white; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; margin-top: 1rem; letter-spacing: 0.5px; text-transform: uppercase; }
        .login-button::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent); transition: left 0.6s ease; }
        .login-button:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(220, 20, 60, 0.4); }
        .login-button:hover::before { left: 100%; }
        .login-button:active { transform: translateY(0); }
        .login-button:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        .button-content { display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .spinner { width: 20px; height: 20px; border: 2px solid rgba(255, 255, 255, 0.3); border-top: 2px solid white; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .divider { margin: 2rem 0; display: flex; align-items: center; gap: 1rem; color: #9ca3af; font-size: 0.875rem; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }
        .footer-links { text-align: center; margin-top: 2rem; }
        .footer-link { color: #DC143C; text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: color 0.3s ease; }
        .footer-link:hover { color: #8B0000; text-decoration: underline; }
        @media (max-width: 768px) { .main-container { flex-direction: column; max-width: 400px; margin: 1rem; } .left-panel { padding: 2rem; min-height: 300px; } .right-panel { padding: 2rem; } .logo-icon { width: 80px; height: 80px; font-size: 2.5rem; } .logo-title { font-size: 1.8rem; } .login-title { font-size: 1.5rem; } }
        .form-input.error { border-color: #ef4444; animation: shake 0.5s ease-in-out; }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
        .form-input.success { border-color: #10b981; }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="main-container">
        <div class="left-panel">
            <div class="logo-section">
                <div class="logo-icon">
                    <img class="pup-logo" src="pup_logo-removebg-preview.png" alt=""> 
                </div>
                <h1 class="logo-title">PUP AquaMonitor</h1>
                <p class="logo-subtitle">Polytechnic University of the Philippines<br>IoT Water Level Monitoring System</p>
            </div>
        </div>
        <div class="right-panel">
            <div class="login-header">
                <h2 class="login-title">Welcome Back</h2>
                <p class="login-subtitle">Sign in to access your dashboard</p>
            </div>
            <form class="login-form" id="loginForm" method="POST" autocomplete="off">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email address" required>
                        <span class="input-icon">📧</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                        <span class="input-icon" onclick="togglePassword()" id="passwordToggle">👁️</span>
                    </div>
                </div>
                <button type="submit" class="login-button" id="loginBtn">
                    <div class="button-content">
                        <span id="buttonText">Login</span>
                        <div id="buttonSpinner" class="spinner" style="display: none;"></div>
                    </div>
                </button>
                <div class="divider">
                    <span>Polytechnic University of the Philippines</span>
                </div>
                <div class="divider">
                   <span>DIT - 3 (2024 - 2025)</span>
                </div>
                <!-- <div class="footer-links">
                    <a href="#" class="footer-link">Forgot your password?</a>
                </div> -->
            </form>
        </div>
    </div>
    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="errorModalLabel">Login Error</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <?= htmlspecialchars($error) ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="successModalLabel">Login Successful</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Welcome, <?= htmlspecialchars($full_name) ?>! Redirecting...
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggle');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = '👁️';
            }
        }
        // Real-time input validation
        document.getElementById('email').addEventListener('input', function() {
            if (this.value.includes('@') && this.value.length > 3) {
                this.classList.remove('error');
                this.classList.add('success');
            } else {
                this.classList.remove('success');
            }
        });
        document.getElementById('password').addEventListener('input', function() {
            if (this.value.length >= 6) {
                this.classList.remove('error');
                this.classList.add('success');
            } else {
                this.classList.remove('success');
            }
        });
        // Enhanced focus effects
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
        // Bootstrap modal logic for PHP feedback
        document.addEventListener("DOMContentLoaded", function() {
            <?php if ($error): ?>
                var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            <?php endif; ?>
            <?php if ($success): ?>
                var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
                setTimeout(function() {
                    window.location.href = "dashboard.php";
                }, 1000);
            <?php endif; ?>
        });
    </script>
</body>
</html>
