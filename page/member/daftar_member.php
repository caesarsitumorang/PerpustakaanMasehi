<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "config/koneksi.php";

$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.');window.location='login.php';</script>";
    exit;
}

$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'"));
if (!$user) {
    echo "<div class='alert alert-danger'>User tidak ditemukan.</div>";
    exit;
}

$username = $user['username'];
$pengunjung = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pengunjung WHERE username = '$username'"));
if (!$pengunjung) {
    echo "<div class='alert alert-danger'>Data pengunjung tidak ditemukan.</div>";
    exit;
}

$id_pengunjung = $pengunjung['id'];
$cekMember = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM member WHERE id_pengunjung = '$id_pengunjung'"));
$cekPendaftaran = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pendaftaran_member WHERE id_pengunjung = '$id_pengunjung'"));

$status_pendaftaran = $cekPendaftaran['status'] ?? null;

if ($cekMember) {
    $pesan = '
    <div class="alert alert-success">✅ Kamu sudah terdaftar sebagai <b>member resmi</b>. Selamat datang!</div>
    <div class="benefits-container">
      <h3 class="benefits-title">🎁 Keuntungan Menjadi Member</h3>
      <div class="benefit-card">
        <h4>📚 Membaca Buku Digital</h4>
        <p>Kamu bisa membaca buku digital di perpustakaan kami!</p>
      </div>
    </div>';
}

// Jika sudah mendaftar tapi belum disetujui
else if ($cekPendaftaran) {
    $pesan = '<div class="alert alert-warning">⏳ Pendaftaran kamu sedang dalam proses <b>verifikasi admin</b>. Mohon tunggu persetujuan.</div>';
}

// Proses form jika belum mendaftar
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identitas = '';
    if (!empty($_FILES['identitas']['name'])) {
        $target_dir = "upload/";
        $ext = pathinfo($_FILES['identitas']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("id_") . '.' . $ext;
        $target_file = $target_dir . basename($filename);

        if (move_uploaded_file($_FILES['identitas']['tmp_name'], $target_file)) {
            $identitas = $filename;
        }
    }

    $query = "INSERT INTO pendaftaran_member (id_pengunjung, identitas, status)
              VALUES ('$id_pengunjung', '$identitas', 'pending')";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $pesan = '<div class="alert alert-success">✅ Pendaftaran berhasil dikirim. Mohon tunggu verifikasi dari admin.</div>';
        $cekPendaftaran = true; 
    } else {
        $pesan = '<div class="alert alert-danger">❌ Gagal mengirim pendaftaran. Silakan coba lagi.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran Member</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    html, body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      margin: 0;
      padding: 0;
      min-height: 100vh;
      color: #000; /* teks default hitam */
    }

    .form-container {
        max-width: 600px;
        margin: auto;
        background: #ffffff;
        padding: 35px;
        border-radius: 15px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        transition: 0.3s ease;
    }

    .form-container:hover {
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #2c3e50;
    }

    .form-group {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
    }

    label {
        margin-bottom: 8px;
        font-weight: 600;
        color: #34495e;
    }

    input[type="text"],
    input[type="file"] {
        padding: 12px 14px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 15px;
        background-color: #fdfdfd;
        transition: border 0.3s;
    }

    input[type="text"]:focus,
    input[type="file"]:focus {
        border-color: #007bff;
        outline: none;
    }

    input[readonly] {
        background-color: #f1f3f5;
        cursor: not-allowed;
    }

    .btn-submit {
        display: inline-block;
        width: 100%;
        margin-top: 10px;
        padding: 12px 20px;
        background: #007bff;
        color: #fff;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s;
    }

    .btn-submit:hover {
        background: #0056b3;
    }

    .alert {
        padding: 14px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: #e9f9ee;
        color: #1e7e34;
        border: 1px solid #b4f0c2;
    }

    .alert-danger {
        background: #fdecea;
        color: #b32020;
        border: 1px solid #f5bcbc;
    }

    .alert-warning {
        background: #fff8e1;
        color: #8d6e00;
        border: 1px solid #ffe082;
    }

    .alert i {
        font-size: 18px;
    }
</style>

<body>

<div class="form-container">
    <h2>📝 Pendaftaran Member</h2>

    <?php if (isset($pesan)) echo str_replace(
        ['✅', '❌', '⏳'],
        ['<i>✅</i>', '<i>❌</i>', '<i>⏳</i>'],
        $pesan
    ); ?>

    <?php if (!$cekMember && !$cekPendaftaran) : ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($pengunjung['nama_lengkap']) ?>" readonly>
            </div>

            <div class="form-group">
                <label for="identitas">Upload Identitas (KTP, Kartu Pelajar, atau lainnya)</label>
                <input type="file" name="identitas" accept="image/*,application/pdf" required>
            </div>

            <button type="submit" class="btn-submit">📩 Kirim Pendaftaran</button>
        </form>
    <?php endif; ?>
</div>

</body>
