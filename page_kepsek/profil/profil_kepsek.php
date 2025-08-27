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

// Ambil username dari tabel users
$id_user_safe = mysqli_real_escape_string($koneksi, $id_user);
$result_user = mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = '$id_user_safe'");
$user = mysqli_fetch_assoc($result_user);

$username = $user['username'] ?? null;

if (!$username) {
    echo "<div class='alert alert-danger'>Username tidak ditemukan di tabel users.</div>";
    exit;
}

// Ambil data kepala sekolah berdasarkan username
$username_safe = mysqli_real_escape_string($koneksi, $username);
$result_kepsek = mysqli_query($koneksi, "SELECT * FROM kepala_sekolah WHERE username = '$username_safe'");
$kepala_sekolah = mysqli_fetch_assoc($result_kepsek);

if (!$kepala_sekolah) {
    echo "<div class='alert alert-danger'>Data kepala sekolah tidak ditemukan di tabel kepala_sekolah.</div>";
    exit;
}

// Foto profil
$foto = !empty($kepala_sekolah['foto']) && $kepala_sekolah['foto'] !== '-' ? $kepala_sekolah['foto'] : 'default.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Kepala Sekolah</title>
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
            max-width: 700px;
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
            width: 160px;
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
    </style>
</head>
<body>

<div class="profile-card text-center">
    <a href="index_kepsek.php?page_kepsek=profil/edit_profil_kepsek" class="btn btn-sm btn-primary edit-btn">
        <i class="fas fa-edit"></i> Edit
    </a>

    <img src="upload/<?= htmlspecialchars($foto) ?>" class="profile-photo" alt="Foto Profil Kepala Sekolah">

    <h3 class="title"><i class="fas fa-user-shield"></i> Profil Kepala Sekolah</h3>

    <div class="profile-group">
        <i class="fas fa-id-badge"></i>
        <label>Nama Lengkap</label>
        <p><?= htmlspecialchars($kepala_sekolah['nama_lengkap']) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-barcode"></i>
        <label>NIP</label>
        <p><?= htmlspecialchars($kepala_sekolah['nip']) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-venus-mars"></i>
        <label>Jenis Kelamin</label>
        <p><?= htmlspecialchars($kepala_sekolah['jenis_kelamin']) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-phone"></i>
        <label>No HP</label>
        <p><?= htmlspecialchars($kepala_sekolah['no_hp']) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-envelope"></i>
        <label>Email</label>
        <p><?= htmlspecialchars($kepala_sekolah['email']) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-user"></i>
        <label>Username</label>
        <p><?= htmlspecialchars($kepala_sekolah['username']) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-map-marker-alt"></i>
        <label>Alamat</label>
        <p><?= htmlspecialchars($kepala_sekolah['alamat']) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-calendar-check"></i>
        <label>Tanggal Menjabat</label>
        <p><?= htmlspecialchars(date('d-m-Y', strtotime($kepala_sekolah['tanggal_menjabat']))) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-toggle-on"></i>
        <label>Status</label>
        <p><?= htmlspecialchars(ucfirst($kepala_sekolah['status'])) ?></p>
    </div>
</div>

</body>
</html>
