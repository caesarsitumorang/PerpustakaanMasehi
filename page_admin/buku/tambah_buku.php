<?php
include "config/koneksi.php";

// Buat kode buku otomatis
$result = mysqli_query($koneksi, "SELECT MAX(kode_buku) AS max_kode FROM buku WHERE kode_buku LIKE 'BK%'");
$row = mysqli_fetch_assoc($result);
$lastKode = $row['max_kode'] ?? null;

if ($lastKode) {
  $angka = (int)substr($lastKode, 2) + 1;
} else {
  $angka = 1;
}
$kode_buku_baru = 'BK' . str_pad($angka, 2, '0', STR_PAD_LEFT);

// Proses saat submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $kode_buku = $_POST['kode_buku'];
  $judul = $_POST['judul'];
  $pengarang = $_POST['pengarang'];
  $penerbit = $_POST['penerbit'];
  $tahun_terbit = $_POST['tahun_terbit'];
  $kategori = $_POST['kategori'];
  $jumlah = $_POST['jumlah'];
  $deskripsi = $_POST['deskripsi'];

  $cover = '';
  if (!empty($_FILES['cover']['name'])) {
    $target_dir = "upload/";
    $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
    $filename = uniqid("cover_") . '.' . $ext;
    $target_file = $target_dir . basename($filename);

    if (move_uploaded_file($_FILES['cover']['tmp_name'], $target_file)) {
      $cover = $filename;
    }
  }

  $query = "INSERT INTO buku (kode_buku, judul, pengarang, penerbit, tahun_terbit, kategori, jumlah, deskripsi, cover)
            VALUES ('$kode_buku', '$judul', '$pengarang', '$penerbit', '$tahun_terbit', '$kategori', '$jumlah', '$deskripsi', '$cover')";
  $result = mysqli_query($koneksi, $query);

  if ($result) {
    echo '<div class="alert alert-success">✅ Buku berhasil ditambahkan.</div>';
  } else {
    echo '<div class="alert alert-danger">❌ Gagal menambahkan buku.</div>';
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Buku</title>
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

    .form-container {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: black;
    }

    .form-row {
      display: flex;
      gap: 20px;
      margin-bottom: 15px;
    }

    .form-group {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    label {
      margin-bottom: 6px;
      font-weight: 600;
      color: #000;
    }

    input[type="text"],
    input[type="number"],
    textarea,
    select,
    input[type="file"] {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      color: #333;
    }

    textarea {
      resize: vertical;
      min-height: 80px;
    }
.form-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.btn-back {
  background: #ccc;
  color: black;
  font-weight: bold;
  padding: 10px 20px;
  border-radius: 8px;
  text-decoration: none;
  transition: background 0.3s ease;
}

.btn-back:hover {
  background: #aaa;
}

.btn-submit {
  background: linear-gradient(to right, #4a90e2, #007bff);
  color: white;
  font-weight: bold;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
}

.btn-submit:hover {
  background: linear-gradient(to right, #38f9d7, #43e97b);
}


    .alert {
      margin-bottom: 20px;
      padding: 12px 18px;
      border-radius: 6px;
    }

    .alert-success {
      background: #e6ffed;
      color: #1c7c38;
      border: 1px solid #b6f2cd;
    }

    .alert-danger {
      background: #ffe8e8;
      color: #b32020;
      border: 1px solid #f5bcbc;
    }
  </style>
</head>
<body>

<div class="form-container">
  <h2>📘 Tambah Buku Baru</h2>
  <form action="" method="POST" enctype="multipart/form-data">
    <div class="form-row">
      <div class="form-group">
        <label for="kode_buku">Kode Buku</label>
        <input type="text" name="kode_buku" value="<?= $kode_buku_baru ?>" readonly required>
      </div>
      <div class="form-group">
        <label for="judul">Judul</label>
        <input type="text" name="judul" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="pengarang">Pengarang</label>
        <input type="text" name="pengarang" required>
      </div>
      <div class="form-group">
        <label for="penerbit">Penerbit</label>
        <input type="text" name="penerbit" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="tahun_terbit">Tahun Terbit</label>
        <input type="number" name="tahun_terbit" required>
      </div>
      <div class="form-group">
        <label for="kategori">Kategori</label>
        <select name="kategori" required>
          <option value="" disabled selected>Pilih Kategori</option>
          <option value="fiksi">Fiksi</option>
          <option value="non fiksi">Non Fiksi</option>
          <option value="pelajaran">Pelajaran</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="jumlah">Jumlah</label>
        <input type="number" name="jumlah" required>
      </div>
      <div class="form-group">
        <label for="cover">Cover Buku</label>
        <input type="file" name="cover" accept="image/*">
      </div>
    </div>

    <div class="form-group">
      <label for="deskripsi">Deskripsi</label>
      <textarea name="deskripsi" required></textarea>
    </div>

   <div class="form-buttons">
  <a href="index_admin.php?page_admin=buku/buku_admin" class="btn-back">Kembali</a>
  <button type="submit" class="btn-submit">Simpan Buku</button>
</div>

  </form>
</div>

</body>
</html>
