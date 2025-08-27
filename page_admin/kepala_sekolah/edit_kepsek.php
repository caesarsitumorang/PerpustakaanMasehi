<?php
include "config/koneksi.php";

if (!isset($_GET['id'])) {
  echo "<div class='alert alert-warning'>ID kepala sekolah tidak ditemukan.</div>";
  exit;
}

$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM kepala_sekolah WHERE id_kepala_sekolah = '$id'"));

if (!$data) {
  echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama_lengkap = $_POST['nama_lengkap'];
  $nip = $_POST['nip'];
  $jenis_kelamin = $_POST['jenis_kelamin'];
  $no_hp = $_POST['no_hp'];
  $email = $_POST['email'];
  $alamat = $_POST['alamat'];
  $tanggal_menjabat = $_POST['tanggal_menjabat'];
  $status = $_POST['status'];
  $username = $_POST['username'];

  // Jika password baru diisi, update. Jika kosong, biarkan.
  if (!empty($_POST['password'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $updatePassword = ", password='$password'";
  } else {
    $updatePassword = "";
  }

  // Upload foto jika diganti
  $foto = $data['foto'];
  if ($_FILES['foto']['name']) {
    $uploadDir = "upload/";
    $fileName = time() . "_" . basename($_FILES["foto"]["name"]);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFile)) {
      $foto = $fileName;
    }
  }

  $query = "UPDATE kepala_sekolah SET 
            nama_lengkap='$nama_lengkap',
            nip='$nip',
            jenis_kelamin='$jenis_kelamin',
            no_hp='$no_hp',
            email='$email',
            username='$username'
            $updatePassword,
            alamat='$alamat',
            tanggal_menjabat='$tanggal_menjabat',
            status='$status',
            foto='$foto'
            WHERE id_kepala_sekolah = '$id'";

  if (mysqli_query($koneksi, $query)) {
    echo "<script>alert('Data berhasil diperbarui.'); window.location='index_admin.php?page_admin=kepala_sekolah/data_kepsek';</script>";
  } else {
    echo "<div class='alert alert-danger'>Gagal menyimpan perubahan: " . mysqli_error($koneksi) . "</div>";
  }
}
?>

<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f4f6f9;
    padding: 20px;
    color: #333;
  }

  .edit-form-container {
    max-width: 850px;
    margin: auto;
    background: #007bff;
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

  input, select, textarea {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
    color: #333;
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

  .button-group {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 30px;
  }
</style>

<div class="edit-form-container">
  <h3>Edit Kepala Sekolah</h3>

  <form method="POST" enctype="multipart/form-data">
    <div class="form-container">

      <div class="form-row">
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama_lengkap" value="<?= $data['nama_lengkap'] ?>" required>
        </div>
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" value="<?= $data['username'] ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Password (Kosongkan jika tidak diubah)</label>
          <input type="password" name="password">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?= $data['email'] ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>NIP</label>
          <input type="text" name="nip" value="<?= $data['nip'] ?>">
        </div>
        <div class="form-group">
          <label>Jenis Kelamin</label>
          <select name="jenis_kelamin" required>
            <option value="Laki-laki" <?= $data['jenis_kelamin'] == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
            <option value="Perempuan" <?= $data['jenis_kelamin'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>No HP</label>
          <input type="text" name="no_hp" value="<?= $data['no_hp'] ?>">
        </div>
        <div class="form-group">
          <label>Alamat</label>
          <input type="text" name="alamat" value="<?= $data['alamat'] ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Foto</label><br>
          <?php if (!empty($data['foto'])): ?>
            <img src="upload/<?= $data['foto'] ?>" class="preview-img" alt="Foto">
          <?php endif; ?>
          <input type="file" name="foto" accept="image/*">
        </div>
        <div class="form-group">
          <label>Tanggal Menjabat</label>
          <input type="date" name="tanggal_menjabat" value="<?= $data['tanggal_menjabat'] ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <option value="Aktif" <?= $data['status'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Nonaktif" <?= $data['status'] == 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
          </select>
        </div>
      </div>

      <div class="button-group">
        <button type="submit" class="btn-submit">Simpan Perubahan</button>
        <a href="index_admin.php?page_admin=kepala_sekolah/data_kepsek" class="btn-back">Kembali</a>
      </div>
    </div>
  </form>
</div>
