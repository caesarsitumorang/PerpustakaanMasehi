<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register - Perpustakaan Digital</title>
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
            <!-- Left Side -->
            <div class="icon-side">
                <i class="fas fa-user-plus main-icon"></i>
                <h2 class="icon-title">Perpustakaan Digital</h2>
                <p class="icon-subtitle">SMP-SMK Swasta Masehi Sibolangit<br>Gabung dan nikmati buku digital</p>
            </div>

            <!-- Right Side - Register Form -->
            <div class="form-side">
                <div class="welcome-text">
                    <h1 class="welcome-title">Buat Akun Baru</h1>
                    <p class="welcome-subtitle">Isi data di bawah untuk mendaftar</p>
                </div>

                <form class="login-form" method="post">
                    <div class="form-group">
                        <input type="text" name="username" class="form-input" placeholder="Username" required>
                        <i class="fas fa-user input-icon"></i>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password" class="form-input" placeholder="Password" required>
                        <i class="fas fa-lock input-icon"></i>
                    </div>

                    <div class="form-group">
                        <input type="text" name="fullname" class="form-input" placeholder="Nama Lengkap" required>
                        <i class="fas fa-id-card input-icon"></i>
                    </div>

                    <button type="submit" name="submit" value="Daftar" class="login-btn loading" id="registerBtn">
                        <span>
                            <i class="fas fa-user-plus" style="margin-right: 0.5rem;"></i>
                            Daftar Sekarang
                        </span>
                    </button>
                </form>

                <div class="divider">
                    <span>atau</span>
                </div>

                <div class="form-links">
                    <a href="login.php">
                        <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i>
                        Sudah punya akun? Masuk di sini
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Input focus effects
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Button loading animation
        document.getElementById('registerBtn').addEventListener('click', function () {
            this.classList.add('active');
            setTimeout(() => {
                this.classList.remove('active');
            }, 3000);
        });
    </script>
</body>
</html>
<?php
session_start();
require_once("config/koneksi.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit']) && $_POST['submit'] == 'Daftar') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $fullname = trim($_POST['fullname']);

        if (empty($username) || empty($password) || empty($fullname)) {
            echo "<script>alert('Semua field wajib diisi!'); window.location='register.php';</script>";
            exit;
        }

        // Cek apakah username sudah digunakan
        $check = mysqli_query($koneksi, "SELECT * FROM pengunjung WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('Username sudah terdaftar!'); window.location='register.php';</script>";
            exit;
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Simpan ke tabel pengunjung
        $insertPengunjung = mysqli_query($koneksi, "INSERT INTO pengunjung (username, password, nama_lengkap) VALUES ('$username', '$hashedPassword', '$fullname')");

        // Simpan juga ke tabel users
        $insertUser = mysqli_query($koneksi, "INSERT INTO users (username, password, nama_lengkap, role) VALUES ('$username', '$hashedPassword', '$fullname', 'pengunjung')");

        if ($insertPengunjung && $insertUser) {
            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal menyimpan data!'); window.location='register.php';</script>";
            exit;
        }
    }
}
?>
