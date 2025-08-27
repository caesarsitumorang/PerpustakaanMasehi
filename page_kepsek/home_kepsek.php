<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include "config/koneksi.php";

$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
  echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
  exit;
}

$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'"));
$nama_user = $user['username'] ?? 'Pengguna';

// Data statistik
$totalPengunjung = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pengunjung"));
$totalBuku = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM buku"));
$totalBukuDigital = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM buku_digital"));
$totalDipinjam = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE status='dipinjam'"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Perpustakaan</title>
  <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
  
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
<style>
  html, body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: 'Poppins', sans-serif;
  }


    .dashboard-container {
      display: flex;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 40px;
    }

    .dashboard-box {
      flex: 1;
      min-width: 200px;
      padding: 25px 20px;
      border-radius: 12px;
      color: white;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
    }

    .dashboard-box h2 {
      font-size: 2.5rem;
      margin-bottom: 10px;
      font-weight: bold;
    }

    .dashboard-box p {
      margin: 0;
      font-size: 1rem;
      font-weight: 500;
      opacity: 0.95;
    }

    .bg-pengunjung { background: linear-gradient(135deg, #007bff, #0056b3); }
    .bg-digital { background: linear-gradient(135deg, #6f42c1, #563d7c); }
    .bg-buku { background: linear-gradient(135deg, #17a2b8, #138496); }
    .bg-dipinjam { background: linear-gradient(135deg, #ffc107, #e0a800); }

    .info-section {
      margin-top: 60px;
      background: white;
      padding: 30px 20px;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      text-align: center;
    }

    .info-section h1 {
      font-size: 1.8rem;
      font-weight: bold;
      color: #333;
    }

    .info-section p {
      font-size: 1.05rem;
      color: #555;
      margin-top: 6px;
    }

    @media (max-width: 768px) {
      .dashboard-container {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="dashboard-container">
    <div class="dashboard-box bg-pengunjung">
      <h2><?= $totalPengunjung ?></h2>
      <p>Total Pengunjung</p>
    </div>

    <div class="dashboard-box bg-digital">
      <h2><?= $totalBukuDigital ?></h2>
      <p>Buku Digital</p>
    </div>

    <div class="dashboard-box bg-buku">
      <h2><?= $totalBuku ?></h2>
      <p>Total Buku</p>
    </div>

    <div class="dashboard-box bg-dipinjam">
      <h2><?= $totalDipinjam ?></h2>
      <p>Sedang Dipinjam</p>
    </div>
  </div>

  <div class="info-section">
    <h1>Perpustakaan SMP - SMK Swasta Masehi Sibolangit</h1>
    <p>Alamat: Jln. Jamin Ginting, Sibolangit, Kec. Sibolangit, Kab. Deli Serdang, Sumatera Utara</p>
  </div>
</div>

</body>
</html>
