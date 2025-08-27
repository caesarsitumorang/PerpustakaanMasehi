<?php
session_start();
require_once("config/koneksi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo "<script>alert('Username atau password tidak boleh kosong!'); window.location='login.php';</script>";
        exit;
    }

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if ($data = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $data['password'])) {
            $_SESSION['username'] = $data['username'];
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['role'] = $data['role'];

            switch ($data['role']) {
                case 'admin':
                    header("Location: index_admin.php");
                    exit;
                case 'kepala_sekolah':
                    header("Location: index_kepsek.php");
                    exit;
                case 'pengunjung':
                default:
                    header("Location: index.php");
                    exit;
            }
        } else {
            echo "<script>alert('Password salah!'); window.location='login.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Username tidak ditemukan!'); window.location='login.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - Perpustakaan Digital</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="css/css_login.css" rel="stylesheet">
</head>
<body>
    <div class="animated-bg"></div>

    <div class="login-container">
        <div class="login-card">
            <!-- Left Side - Icon & Branding -->
            <div class="icon-side">
                <i class="fas fa-book-reader main-icon"></i>
                <h2 class="icon-title">Perpustakaan Digital</h2>
                <p class="icon-subtitle">SMP-SMK Swasta Masehi Sibolangit<br>Portal pembelajaran masa depan</p>
            </div>

            <!-- Right Side - Login Form -->
            <div class="form-side">
                <div class="welcome-text">
                    <h1 class="welcome-title">Selamat Datang!</h1>
                    <p class="welcome-subtitle">Masuk ke akun perpustakaan digital Anda</p>
                </div>

                <form class="login-form" method="post" id="loginForm">
                    <div class="form-group">
                        <input type="text" name="username" class="form-input" placeholder="Username" required>
                        <i class="fas fa-user input-icon"></i>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password" class="form-input" placeholder="Password" required id="passwordInput">
                        <i class="fas fa-eye input-icon" id="togglePassword" style="cursor: pointer;"></i>
                    </div>

                    <button type="submit" name="submit" class="login-btn loading" id="loginBtn">
                        <span>
                            <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i>
                            Masuk ke Perpustakaan
                        </span>
                    </button>
                </form>

                <div class="divider">
                    <span>atau</span>
                </div>

                <div class="form-links">
                    <a href="register.php">
                        <i class="fas fa-user-plus" style="margin-right: 0.5rem;"></i>
                        Belum punya akun? Daftar di sini
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('passwordInput');
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
        });

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.classList.add('active');
            
            // Remove loading state after 3 seconds (fallback)
            setTimeout(() => {
                loginBtn.classList.remove('active');
            }, 3000);
        });

        // Input focus effects
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Typing effect for welcome text
        function typeWriter(element, text, speed = 100) {
            let i = 0;
            element.innerHTML = '';
            
            function type() {
                if (i < text.length) {
                    element.innerHTML += text.charAt(i);
                    i++;
                    setTimeout(type, speed);
                }
            }
            type();
        }

        // Initialize typing effect
        window.addEventListener('load', () => {
            const welcomeTitle = document.querySelector('.welcome-title');
            const originalText = welcomeTitle.textContent;
            typeWriter(welcomeTitle, originalText, 80);
        });
    </script>
</body>
</html>