<?php
include "config/koneksi.php";

$kode_buku = $_GET['id'] ?? null;
if (!$kode_buku) {
  echo "<div class='alert alert-danger'>ID buku tidak ditemukan.</div>";
  exit;
}

$query = mysqli_query($koneksi, "SELECT * FROM buku WHERE kode_buku = '$kode_buku'");
$buku = mysqli_fetch_assoc($query);

if (!$buku) {
  echo "<div class='alert alert-warning'>Data buku tidak ditemukan.</div>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Buku - <?= $buku['judul'] ?></title>
  <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
  <style>
    body {
      background-color: #f7f9fc;
      font-family: 'Segoe UI', sans-serif;
    }

    .detail-container {
      max-width: 800px;
      margin: 40px auto;
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.08);
      overflow: hidden;
    }

    .detail-cover {
      width: 100%;
      height: 300px;
      object-fit: cover;
      border-bottom: 1px solid #eee;
    }

    .detail-body {
      padding: 24px;
    }

    .detail-body h3 {
      font-weight: bold;
      color: #007bff;
    }

    .detail-info {
      margin-top: 16px;
    }

    .detail-info p {
      margin-bottom: 10px;
      font-size: 1rem;
      color: #333;
    }

    .label {
      font-weight: bold;
      color: #555;
    }

    .btn-back {
      margin-top: 20px;
    }
  </style>
</head>
<body>

<div class="detail-container">
  <?php if (!empty($buku['cover'])): ?>
    <img src="upload/<?= $buku['cover'] ?>" class="detail-cover" alt="<?= $buku['judul'] ?>">
  <?php else: ?>
    <img src="img/default.jpg" class="detail-cover" alt="No Cover">
  <?php endif; ?>

  <div class="detail-body">
    <h3><?= $buku['judul'] ?></h3>

    <div class="detail-info">
      <p><span class="label">Kode Buku:</span> <?= $buku['kode_buku'] ?></p>
      <p><span class="label">Pengarang:</span> <?= $buku['pengarang'] ?></p>
      <p><span class="label">Penerbit:</span> <?= $buku['penerbit'] ?></p>
      <p><span class="label">Tahun Terbit:</span> <?= $buku['tahun_terbit'] ?></p>
      <p><span class="label">Kategori:</span> <?= $buku['kategori'] ?></p>
      <p><span class="label">Jumlah:</span> <?= $buku['jumlah'] ?></p>
      <p><span class="label">Deskripsi:</span><br><?= nl2br($buku['deskripsi']) ?></p>
    </div>

    <a href="index.php?page=buku/koleksi" class="btn btn-secondary btn-back">← Kembali ke Koleksi</a>
  </div>
</div>

</body>
</html>
