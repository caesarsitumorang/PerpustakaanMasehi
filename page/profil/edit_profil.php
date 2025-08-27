<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "config/koneksi.php";

// Ambil ID user dari session
$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

// Ambil data user
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'"));
if (!$user) {
    echo "<div class='alert alert-danger'>Data user tidak ditemukan.</div>";
    exit;
}

$username_user = $user['username'];

// Ambil data pengunjung
$pengunjung = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pengunjung WHERE username = '$username_user'"));
if (!$pengunjung) {
    echo "<div class='alert alert-danger'>Data pengunjung tidak ditemukan.</div>";
    exit;
}

$id_pengunjung = $pengunjung['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];
    $password = $_POST['password'] ?? ''; // password optional
    $foto = $pengunjung['foto'];

    if (!empty($_FILES['foto']['name'])) {
        $uploadDir = "upload/";
        $fileName = time() . "_" . basename($_FILES["foto"]["name"]);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFile)) {
            $foto = $fileName;
        }
    }

    // Update tabel pengunjung
   $query_pengunjung = "UPDATE pengunjung SET 
    username='$username',
    nama_lengkap='$nama_lengkap',
    jenis_kelamin='$jenis_kelamin',
    email='$email',
    no_hp='$no_hp',
    alamat='$alamat',
    foto='$foto'
    WHERE id = '$id_pengunjung'";


    // Update tabel users
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query_user = "UPDATE users SET username='$username', password='$password_hash' WHERE id_user = '$id_user'";
    } else {
        $query_user = "UPDATE users SET username='$username' WHERE id_user = '$id_user'";
    }

    // Eksekusi keduanya
    $update1 = mysqli_query($koneksi, $query_pengunjung);
    $update2 = mysqli_query($koneksi, $query_user);

    if ($update1 && $update2) {
        echo "<script>alert('Profil berhasil diperbarui!'); window.location='index.php?page=profil/profil_pengunjung';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menyimpan perubahan.</div>";
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
<style>
  html, body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: 'Poppins', sans-serif;
  }

  .edit-form-container {
    max-width: 850px;
    margin: auto;
    background: #667eea;
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
    color: #444;
  }

  input[type="text"],
  input[type="email"],
  input[type="number"],
  textarea,
  input[type="file"] {
    width: 100%;
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
  <h3>Edit Profil</h3>

  <form method="POST" enctype="multipart/form-data">
    <div class="form-container">
      <div class="form-row">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" value="<?= $pengunjung['username'] ?>" required>
        </div>
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama_lengkap" value="<?= $pengunjung['nama_lengkap'] ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?= $pengunjung['email'] ?>" required>
        </div>
        <div class="form-group">
          <label>No HP</label>
          <input type="text" name="no_hp" value="<?= $pengunjung['no_hp'] ?>" required>
        </div>
      </div>

      <div class="form-row">
  <div class="form-group">
    <label>Jenis Kelamin</label>
    <select name="jenis_kelamin" required>
      <option value="" disabled selected>Pilih Jenis Kelamin</option>
      <option value="Laki-Laki" <?= $pengunjung['jenis_kelamin'] == 'Laki-Laki' ? 'selected' : '' ?>>Laki-Laki</option>
      <option value="Perempuan" <?= $pengunjung['jenis_kelamin'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
    </select>
  </div>
  <div class="form-group">
    <label>Alamat</label>
    <textarea name="alamat"><?= $pengunjung['alamat'] ?></textarea>
  </div>
</div>


      <div class="form-row">
        <div class="form-group">
          <label>Foto Profil</label><br>
          <?php if (!empty($pengunjung['foto'])): ?>
            <img src="upload/<?= $pengunjung['foto'] ?>" class="preview-img" alt="Foto">
          <?php endif; ?>
          <input type="file" name="foto" accept="image/*">
        </div>
      </div>

      <div class="button-group">
        <button type="submit" class="btn-submit">Simpan Perubahan</button>
        <a href="index.php?page=profil/profil_pengunjung" class="btn-back">Kembali</a>
      </div>
    </div>
  </form>
</div>
