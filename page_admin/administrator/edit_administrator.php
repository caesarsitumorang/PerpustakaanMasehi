<?php
include "config/koneksi.php";

if (!isset($_GET['username']) || !isset($_GET['role'])) {
    echo "<script>alert('Data tidak ditemukan'); window.location='index_admin.php?page_admin=administrator/data_administrator';</script>";
    exit;
}

$username = mysqli_real_escape_string($koneksi, $_GET['username']);
$role = mysqli_real_escape_string($koneksi, $_GET['role']);

// Ambil data lengkap dari tabel users + tabel sesuai role
if ($role == 'admin') {
    $query = mysqli_query($koneksi, "
        SELECT u.*, a.id AS admin_id, a.jabatan, a.email, a.alamat, a.no_hp
        FROM users u 
        JOIN admin a ON u.username = a.username
        WHERE u.username = '$username'
    ");
} elseif ($role == 'kepala_sekolah') {
    $query = mysqli_query($koneksi, "
        SELECT u.*, k.id_kepala_sekolah, k.nip, k.jenis_kelamin, k.no_hp, k.email, k.alamat, k.tanggal_menjabat, k.status
        FROM users u 
        JOIN kepala_sekolah k ON u.username = k.username
        WHERE u.username = '$username'
    ");
} else {
    echo "<script>alert('Role tidak valid'); window.location='index_admin.php?page_admin=administrator/data_administrator';</script>";
    exit;
}

$data = mysqli_fetch_assoc($query);
if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.location='index_admin.php?page_admin=administrator/data_administrator';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_sql = ", password='$password'";
    } else {
        $password_sql = "";
    }

    // Update tabel users
    $update_users = mysqli_query($koneksi, "
        UPDATE users SET username='$new_username', nama_lengkap='$nama_lengkap' $password_sql 
        WHERE username='{$data['username']}'
    ");

    // Update tabel role
    if ($role == 'admin') {
        $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
        $update_admin = mysqli_query($koneksi, "
            UPDATE admin SET username='$new_username', nama_lengkap='$nama_lengkap', email='$email', alamat='$alamat', no_hp='$no_hp', jabatan='$jabatan' $password_sql
            WHERE id='{$data['admin_id']}'
        ");
    } elseif ($role == 'kepala_sekolah') {
        $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
        $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
        $tanggal_menjabat = mysqli_real_escape_string($koneksi, $_POST['tanggal_menjabat']);
        $status = mysqli_real_escape_string($koneksi, $_POST['status']);

        $update_kepsek = mysqli_query($koneksi, "
            UPDATE kepala_sekolah SET username='$new_username', nama_lengkap='$nama_lengkap', nip='$nip', jenis_kelamin='$jenis_kelamin', no_hp='$no_hp', email='$email', alamat='$alamat', tanggal_menjabat='$tanggal_menjabat', status='$status' $password_sql
            WHERE id_kepala_sekolah='{$data['id_kepala_sekolah']}'
        ");
    }

    if ($update_users && (($role == 'admin' && isset($update_admin) && $update_admin) || ($role == 'kepala_sekolah' && isset($update_kepsek) && $update_kepsek))) {
        echo "<script>alert('Data berhasil diperbarui'); window.location='index_admin.php?page_admin=administrator/data_administrator';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data');</script>";
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
  .form-container {
    max-width: 850px;
    margin: auto;
    background: #ffffff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
  }

  h3 {
    font-size: 24px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 30px;
    color: black;
  }

  .form-group {
    margin-bottom: 1rem;
  }

  label {
    font-weight: 600;
    color: black;
    margin-bottom: 6px;
    display: block;
  }

  input, select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    color: black;
  }

 .form-buttons {
  display: flex;
  gap: 10px;
}

.btn-submit {
  background: #4CAF50;
  color: white;
  font-weight: bold;
  padding: 10px 20px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
}

.btn-submit:hover {
  background: #45a049;
}

.btn-back {
  background: #ccc;
  color: black;
  font-weight: bold;
  padding: 10px 20px;
  border-radius: 10px;
  text-decoration: none;
}

.btn-back:hover {
  background: #aaa;
}

</style>

<div class="form-container">
  <h3>Edit <?= ucfirst(str_replace('_', ' ', $role)) ?></h3>
  <form method="POST">
    <div class="form-group">
      <label>Nama Lengkap</label>
      <input type="text" name="nama_lengkap" value="<?= $data['nama_lengkap'] ?>" required>
    </div>
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" value="<?= $data['username'] ?>" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?= $data['email'] ?>" required>
    </div>
    <div class="form-group">
      <label>Alamat</label>
      <input type="text" name="alamat" value="<?= $data['alamat'] ?>" required>
    </div>
    <div class="form-group">
      <label>No HP</label>
      <input type="text" name="no_hp" value="<?= $data['no_hp'] ?>" required>
    </div>

    <?php if ($role == 'admin'): ?>
      <div class="form-group">
        <label>Jabatan</label>
        <input type="text" name="jabatan" value="<?= $data['jabatan'] ?>" required>
      </div>
    <?php elseif ($role == 'kepala_sekolah'): ?>
      <div class="form-group">
        <label>NIP</label>
        <input type="text" name="nip" value="<?= $data['nip'] ?>" required>
      </div>
      <div class="form-group">
        <label>Jenis Kelamin</label>
        <select name="jenis_kelamin" required>
          <option value="Laki-laki" <?= $data['jenis_kelamin'] == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
          <option value="Perempuan" <?= $data['jenis_kelamin'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
        </select>
      </div>
      <div class="form-group">
        <label>Tanggal Menjabat</label>
        <input type="date" name="tanggal_menjabat" value="<?= $data['tanggal_menjabat'] ?>" required>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" required>
          <option value="Aktif" <?= $data['status'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
          <option value="Nonaktif" <?= $data['status'] == 'Nonaktif' ? 'selected' : '' ?>>Tidak Aktif</option>
        </select>
      </div>
    <?php endif; ?>

    <div class="form-group">
      <label>Password (kosongkan jika tidak ingin diubah)</label>
      <input type="password" name="password">
    </div>

    <div class="form-buttons">
  <button type="submit" class="btn-submit">Simpan Perubahan</button>
  <a href="javascript:history.back()" class="btn-back">Kembali</a>
</div>

  </form>
</div>
