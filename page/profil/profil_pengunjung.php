<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "config/koneksi.php";

// Ambil ID user dari session
$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.');window.location='login.php';</script>";
    exit;
}

// Ambil data dari tabel users
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'"));

if (!$user) {
    echo "<div class='alert alert-danger'>User tidak ditemukan.</div>";
    exit;
}

// Ambil data pengunjung berdasarkan username dari tabel users
$username = $user['username'];
$pengunjung = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pengunjung WHERE username = '$username'"));

if (!$pengunjung) {
    echo "<div class='alert alert-danger'>Data pengunjung tidak ditemukan.</div>";
    exit;
}

// Ambil ID pengunjung untuk pengecekan member
$id_pengunjung = $pengunjung['id'];

// Cek akses member
$cek_member = mysqli_query($koneksi, "SELECT * FROM member WHERE id_pengunjung = '$id_pengunjung'");
$is_member = mysqli_num_rows($cek_member) > 0;
$status_member = '-';

if ($is_member) {
    $member = mysqli_fetch_assoc($cek_member);
    $akses = 'Member';
    $status_member = $member['status'] ?? '-';
} else {
    $akses = 'Publik';
}

// Foto profil
$foto = !empty($pengunjung['foto']) && $pengunjung['foto'] !== '-' ? $pengunjung['foto'] : 'default.png';
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Pengunjung</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
  html, body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: 'Poppins', sans-serif;
  }

        .profile-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            max-width: 600px;
            margin: 50px auto;
            position: relative;
        }

        .profile-photo {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
            margin-top: -100px;
            z-index: 1;
            position: relative;
        }

        .edit-btn {
            position: absolute;
            right: 20px;
            top: 20px;
        }

        h3.title {
            color: #007bff;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .profile-group {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .profile-group i {
            width: 30px;
            color: #007bff;
        }

        .profile-group label {
            font-weight: 600;
            margin-bottom: 0;
            width: 140px;
            color: #555;
        }

      .profile-group p {
    margin-bottom: 0;
    background: #f1f3f6;
    border-radius: 6px;
    padding: 8px 12px;
    flex: 1;
    color: #000; /* teks hitam */
}


        .badge-akses {
            font-weight: bold;
            color: white;
            padding: 5px 12px;
            border-radius: 10px;
        }

        .badge-member {
            background: #28a745;
        }

        .badge-umum {
            background: #6c757d;
        }
    </style>
</head>
<body>

<div class="profile-card text-center">
    <a href="index.php?page=profil/edit_profil"  class="btn btn-sm btn-primary edit-btn">
        <i class="fas fa-edit"></i> Edit
    </a>

    <img src="upload/<?= htmlspecialchars($foto) ?>" class="profile-photo" alt="Foto Profil">

    <h3 class="title"><i class="fas fa-user-circle"></i> Profil Pengunjung</h3>

    <div class="profile-group">
        <i class="fas fa-id-badge"></i>
        <label>Nama Lengkap</label>
        <p><?= !empty($pengunjung['nama_lengkap']) ? htmlspecialchars($pengunjung['nama_lengkap']) : '-' ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-user"></i>
        <label>Username</label>
        <p><?= !empty($pengunjung['username']) ? htmlspecialchars($pengunjung['username']) : '-' ?></p>
    </div>

     <div class="profile-group">
        <i class="fas fa-user"></i>
        <label>Jenis Kelamin</label>
        <p><?= !empty($pengunjung['jenis_kelamin']) ? htmlspecialchars($pengunjung['jenis_kelamin']) : '-' ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-envelope"></i>
        <label>Email</label>
        <p><?= !empty($pengunjung['email']) ? htmlspecialchars($pengunjung['email']) : '-' ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-phone"></i>
        <label>No HP</label>
        <p><?= !empty($pengunjung['no_hp']) ? htmlspecialchars($pengunjung['no_hp']) : '-' ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-map-marker-alt"></i>
        <label>Alamat</label>
        <p><?= !empty($pengunjung['alamat']) ? htmlspecialchars($pengunjung['alamat']) : '-' ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-key"></i>
        <label>Akses</label>
        <p><span class="badge-akses <?= $is_member ? 'badge-member' : 'badge-umum' ?>">
            <?= $akses ?>
        </span></p>
    </div>

    <?php if ($is_member): ?>
    <div class="profile-group">
        <i class="fas fa-info-circle"></i>
        <label>Status Member</label>
        <p><?= $status_member ?></p>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
