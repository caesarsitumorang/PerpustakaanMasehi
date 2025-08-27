<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "config/koneksi.php";

// Generate kode otomatis
function generateKodeBuku($koneksi) {
    $query = mysqli_query($koneksi, "SELECT MAX(kode_buku) as kodeTerbesar FROM buku_digital WHERE kode_buku LIKE 'BG%'");
    $data = mysqli_fetch_assoc($query);
    $kodeTerakhir = $data['kodeTerbesar'];

    if ($kodeTerakhir) {
        $angka = (int)substr($kodeTerakhir, 2);
        $angka++;
        return "BG" . str_pad($angka, 2, "0", STR_PAD_LEFT);
    } else {
        return "BG01";
    }
}

$kode_buku = generateKodeBuku($koneksi);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $pengarang = $_POST['pengarang'];
    $penerbit = $_POST['penerbit'];
    $tahun = $_POST['tahun_terbit'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $akses = $_POST['akses'];
    $kode_buku = $_POST['kode_buku']; // tetap gunakan hasil generate

    // Upload cover
    $cover_name = '';
    if ($_FILES['cover']['name']) {
        $cover_name = uniqid() . '_' . $_FILES['cover']['name'];
        move_uploaded_file($_FILES['cover']['tmp_name'], 'upload/' . $cover_name);
    }

    // Upload file ebook
    $file_ebook = '';
    if ($_FILES['file_ebook']['name']) {
        $file_ebook = uniqid() . '_' . $_FILES['file_ebook']['name'];
        move_uploaded_file($_FILES['file_ebook']['tmp_name'], 'upload/' . $file_ebook);
    }

    $query = "INSERT INTO buku_digital 
              (kode_buku, judul, pengarang, penerbit, tahun_terbit, kategori, deskripsi, cover, file_ebook, akses)
              VALUES 
              ('$kode_buku', '$judul', '$pengarang', '$penerbit', '$tahun', '$kategori', '$deskripsi', '$cover_name', '$file_ebook', '$akses')";
    
    $insert = mysqli_query($koneksi, $query);

    if ($insert) {
        echo "<script>alert('Buku berhasil ditambahkan!'); window.location='index_admin.php?page_admin=buku_digital/buku_digital_admin';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan buku!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Buku Digital</title>
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
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
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    }

    h4 {
      text-align: center;
      color: #2575fc;
      font-weight: bold;
      margin-bottom: 25px;
    }

    .form-wrapper {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    label {
      font-weight: 600;
      margin-bottom: 6px;
      color: #444;
    }

    input[type="text"],
    input[type="number"],
    textarea,
    input[type="file"],
    select {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      color: #333;
    }

    input[readonly] {
      background-color: #eee;
      font-weight: bold;
    }

    textarea {
      resize: vertical;
      min-height: 80px;
    }

    .btn-group {
      margin-top: 30px;
      display: flex;
      justify-content: space-between;
      gap: 10px;
    }

    .btn-primary {
      background: linear-gradient(to right, #4a90e2, #007bff);
      font-weight: bold;
      border: none;
    }

    .btn-primary:hover {
      background: linear-gradient(to right, #43e97b, #38f9d7);
    }

    .btn-secondary {
      background: #6c757d;
      border: none;
      font-weight: bold;
    }

    .btn-secondary:hover {
      background: #5a6268;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h4>📘 Tambah Buku Digital</h4>
    <form method="post" enctype="multipart/form-data">
      <div class="form-wrapper">
        <div class="form-group">
          <label>Kode Buku</label>
          <input type="text" name="kode_buku" value="<?= $kode_buku ?>" readonly>
        </div>
        <div class="form-group">
          <label>Judul</label>
          <input type="text" name="judul" required>
        </div>
        <div class="form-group">
          <label>Pengarang</label>
          <input type="text" name="pengarang">
        </div>
        <div class="form-group">
          <label>Penerbit</label>
          <input type="text" name="penerbit">
        </div>
        <div class="form-group">
          <label>Tahun Terbit</label>
          <input type="number" name="tahun_terbit" min="1900" max="2099">
        </div>
        <div class="form-group">
          <label>Kategori</label>
          <select name="kategori" required>
            <option value="" disabled selected>Pilih Kategori</option>
            <option value="fiksi">Fiksi</option>
            <option value="non fiksi">Non Fiksi</option>
            <option value="pelajaran">Pelajaran</option>
          </select>
        </div>
        <div class="form-group" style="grid-column: span 2;">
          <label>Deskripsi</label>
          <textarea name="deskripsi" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label>Cover (JPG/PNG)</label>
          <input type="file" name="cover" accept="image/*">
        </div>
        <div class="form-group">
          <label>File Ebook (PDF)</label>
          <input type="file" name="file_ebook" accept=".pdf">
        </div>
        <div class="form-group">
          <label>Akses</label>
          <select name="akses" required>
            <option value="publik">Publik</option>
            <option value="member">Khusus Member</option>
          </select>
        </div>
      </div>

      <div class="btn-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        <a href="index_admin.php?page_admin=buku_digital/buku_digital_admin" class="btn btn-secondary">Kembali</a>
      </div>
    </form>
  </div>
</body>
</html>
