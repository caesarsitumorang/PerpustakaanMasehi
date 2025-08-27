<?php
include "config/koneksi.php";

if (!isset($_GET['id'])) {
  echo "<div class='alert alert-warning'>ID buku tidak ditemukan.</div>";
  exit;
}

$id = $_GET['id'];
$buku = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku = '$id'"));

if (!$buku) {
  echo "<div class='alert alert-danger'>Buku tidak ditemukan.</div>";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $kode_buku = $_POST['kode_buku']; // tetap dikirim tapi readonly
  $judul = $_POST['judul'];
  $pengarang = $_POST['pengarang'];
  $penerbit = $_POST['penerbit'];
  $tahun_terbit = $_POST['tahun_terbit'];
  $kategori = $_POST['kategori'];
  $jumlah = $_POST['jumlah'];
  $deskripsi = $_POST['deskripsi'];
  $cover = $buku['cover'];

  if ($_FILES['cover']['name']) {
    $uploadDir = "upload/";
    $fileName = time() . "_" . basename($_FILES["cover"]["name"]);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES["cover"]["tmp_name"], $targetFile)) {
      $cover = $fileName;
    }
  }

  $query = "UPDATE buku SET 
            judul='$judul',
            pengarang='$pengarang',
            penerbit='$penerbit',
            tahun_terbit='$tahun_terbit',
            kategori='$kategori',
            jumlah='$jumlah',
            deskripsi='$deskripsi',
            cover='$cover'
            WHERE id_buku = '$id'";

  if (mysqli_query($koneksi, $query)) {
    echo "<script>alert('Buku berhasil diperbarui!'); window.location='index_admin.php?page_admin=buku/buku_admin';</script>";
  } else {
    echo "<div class='alert alert-danger'>Gagal menyimpan perubahan.</div>";
  }
}
?>

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
  .edit-form-container {
    max-width: 850px;
    margin: auto;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  }

  h3 {
    text-align: center;
    color: white;
    margin-bottom: 25px;
    font-size: 24px;
  }

  .form-container {
    background: #fff;
    border-radius: 10px;
    padding: 30px;
  }

  .form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    flex-wrap: wrap;
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
  input[type="file"],
  select {
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

  .btn-submit {
    padding: 10px 20px;
    background: linear-gradient(to right, #4a90e2, #007bff);
    color: white;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s ease;
  }

  .btn-submit:hover {
    background: linear-gradient(to right, #38f9d7, #43e97b);
  }

  .btn-back {
    padding: 10px 20px;
    background-color: #6c757d;
    color: white;
    text-decoration: none;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    display: inline-block;
    font-weight: bold;
    transition: background 0.3s ease;
  }

  .btn-back:hover {
    background-color: #5a6268;
  }

  .preview-img {
    width: 120px;
    height: auto;
    border-radius: 8px;
    margin-bottom: 10px;
  }

  .alert {
    margin-bottom: 20px;
    padding: 12px 18px;
    border-radius: 6px;
  }

  .alert-danger {
    background: #ffe8e8;
    color: #b32020;
    border: 1px solid #f5bcbc;
  }

  .button-group {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 30px;
  }
</style>

<div class="edit-form-container">
  <h3>Edit Buku</h3>

  <form method="POST" enctype="multipart/form-data">
    <div class="form-container">
      <div class="form-row">
        <div class="form-group">
          <label>Kode Buku</label>
          <input type="text" name="kode_buku" value="<?= $buku['kode_buku'] ?>" readonly>
        </div>
        <div class="form-group">
          <label>Judul</label>
          <input type="text" name="judul" value="<?= $buku['judul'] ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Pengarang</label>
          <input type="text" name="pengarang" value="<?= $buku['pengarang'] ?>" required>
        </div>
        <div class="form-group">
          <label>Penerbit</label>
          <input type="text" name="penerbit" value="<?= $buku['penerbit'] ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Tahun Terbit</label>
          <input type="number" name="tahun_terbit" value="<?= $buku['tahun_terbit'] ?>" required>
        </div>
        <div class="form-group">
          <label>Kategori</label>
          <select name="kategori" required>
            <option value="fiksi" <?= $buku['kategori'] == 'fiksi' ? 'selected' : '' ?>>Fiksi</option>
            <option value="non fiksi" <?= $buku['kategori'] == 'non fiksi' ? 'selected' : '' ?>>Non Fiksi</option>
            <option value="pelajaran" <?= $buku['kategori'] == 'pelajaran' ? 'selected' : '' ?>>Pelajaran</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Jumlah</label>
          <input type="number" name="jumlah" value="<?= $buku['jumlah'] ?>" required>
        </div>
        <div class="form-group">
          <label>Deskripsi</label>
          <textarea name="deskripsi" required><?= $buku['deskripsi'] ?></textarea>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Cover (Ganti jika perlu)</label><br>
          <?php if (!empty($buku['cover'])): ?>
            <img src="upload/<?= $buku['cover'] ?>" class="preview-img" alt="Cover Buku">
          <?php endif; ?>
          <input type="file" name="cover" accept="image/*">
        </div>
      </div>

      <div class="button-group">
        <button type="submit" class="btn-submit">Simpan Perubahan</button>
        <a href="index_admin.php?page_admin=buku/buku_admin" class="btn-back">Kembali</a>
      </div>
    </div>
  </form>
</div>
