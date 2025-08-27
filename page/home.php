<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

include "config/koneksi.php";

// Cek sesi login
$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
  echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Informasi Sekolah</title>
  <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">


<style>
    html, body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin: 0;
    padding: 0;
    min-height: 100vh;
  }
    .info-section {
      max-width: 800px;
      margin: auto;
      background: #ffffff;
      padding: 40px 30px;
      border-radius: 16px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
      text-align: center;
    }

    .info-section h1 {
      font-size: 1.9rem;
      font-weight: bold;
      color: #333;
      margin-bottom: 15px;
    }

    .info-section p {
      font-size: 1.1rem;
      color: #555;
      margin: 0;
    }
  </style>
</head>
<body>

<div class="info-section">
  <h1>Perpustakaan SMP - SMK Swasta Masehi Sibolangit</h1>
  <p>Alamat: Jln. Jamin Ginting, Sibolangit, Kec. Sibolangit, Kab. Deli Serdang, Sumatera Utara</p>
</div>

</body>
</html>
