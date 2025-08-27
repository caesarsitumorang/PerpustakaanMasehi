<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "config/koneksi.php";

$id_buku = $_GET['id_buku'] ?? null;
if (!$id_buku) {
    echo "<div class='alert alert-danger'>Buku tidak ditemukan.</div>";
    exit;
}

// Ambil data buku
$buku = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM buku_digital WHERE id_buku = '$id_buku'"));
if (!$buku) {
    echo "<div class='alert alert-danger'>Buku tidak ditemukan di database.</div>";
    exit;
}

// Cek akses berdasarkan status member
$role = 'guest';
$id_user = $_SESSION['id_user'] ?? null;

if ($id_user) {
    $queryUser = mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = '$id_user'");
    $dataUser = mysqli_fetch_assoc($queryUser);

    if ($dataUser) {
        $username = $dataUser['username'];
        $queryPengunjung = mysqli_query($koneksi, "SELECT id FROM pengunjung WHERE username = '$username'");
        $dataPengunjung = mysqli_fetch_assoc($queryPengunjung);

        if ($dataPengunjung) {
            $id_pengunjung = $dataPengunjung['id'];
            $queryMember = mysqli_query($koneksi, "SELECT * FROM member WHERE id_pengunjung = '$id_pengunjung' AND LOWER(status) = 'aktif'");
            if (mysqli_num_rows($queryMember) > 0) {
                $role = 'member';
            }
        }
    }
}

$akses_buku = strtolower($buku['akses']);
$boleh_akses = ($akses_buku === 'publik' || ($akses_buku === 'member' && $role === 'member'));

if (!$boleh_akses) {
    echo "<div class='alert alert-warning text-center mt-5'>⛔ Akses ditolak. Buku ini hanya dapat dibaca oleh member aktif.</div>";
    exit;
}

// Siapkan path file
$file = $buku['file_ebook'];
$filepath = "upload/" . $file;

if (!file_exists($filepath)) {
    echo "<div class='alert alert-danger text-center mt-5'>📁 File ebook tidak ditemukan di server.</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Baca Buku - <?= htmlspecialchars($buku['judul']) ?></title>
  <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="vendor/fontawesome-free/css/all.min.css">
  <script>
    document.addEventListener('contextmenu', function(e) {
      e.preventDefault();
    });
  </script>
  <style>
    body {
      background: #f4f6f9;
      font-family: 'Segoe UI', sans-serif;
      user-select: none;
    }

    .reader-container {
      max-width: 1000px;
      margin: 30px auto;
      background: white;
      padding: 25px;
      box-shadow: 0 4px 18px rgba(0,0,0,0.1);
      border-radius: 10px;
    }

    .pdf-frame {
      width: 100%;
      height: 800px;
      border: none;
      border-radius: 8px;
    }

    .btn-kembali {
      margin-top: 20px;
    }
    
  </style>
</head>
<body>

<div class="reader-container">
  <h4 class="mb-3"><?= htmlspecialchars($buku['judul']) ?></h4>
  <p><strong>Pengarang:</strong> <?= htmlspecialchars($buku['pengarang']) ?> |
     <strong>Tahun:</strong> <?= htmlspecialchars($buku['tahun_terbit']) ?></p>

  <!-- Tampilkan PDF -->
  <embed src="<?= $filepath ?>#toolbar=0" type="application/pdf" class="pdf-frame">

  <a href="index.php?page=buku_digital/daftar" class="btn btn-secondary btn-kembali">
    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Buku
  </a>
</div>

</body>
</html>