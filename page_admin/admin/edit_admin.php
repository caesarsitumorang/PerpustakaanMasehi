<?php
include "config/koneksi.php";

$id_admin = $_GET['id'] ?? null;

if (!$id_admin) {
    echo "<div class='alert alert-danger'>ID admin tidak ditemukan.</div>";
    exit;
}

// Ambil data admin
$admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM admin WHERE id = '$id_admin'"));
if (!$admin) {
    echo "<div class='alert alert-danger'>Data admin tidak ditemukan.</div>";
    exit;
}

// Ambil data user berdasarkan username admin
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE username = '{$admin['username']}'"));
$id_user = $user['id_user'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $jabatan = $_POST['jabatan'];
    $password = $_POST['password'];

    // Update admin
    $update_admin = mysqli_query($koneksi, "UPDATE admin SET 
        username = '$username',
        nama_lengkap = '$nama_lengkap',
        email = '$email',
        alamat = '$alamat',
        no_hp = '$no_hp',
        jabatan = '$jabatan'
        WHERE id = '$id_admin'");

    // Update users
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_user = mysqli_query($koneksi, "UPDATE users SET 
            username = '$username',
            password = '$password_hash',
            nama_lengkap = '$nama_lengkap'
            WHERE id_user = '$id_user'");
    } else {
        $update_user = mysqli_query($koneksi, "UPDATE users SET 
            username = '$username',
            nama_lengkap = '$nama_lengkap'
            WHERE id_user = '$id_user'");
    }

    if ($update_admin && $update_user) {
        echo "<script>alert('Data admin berhasil diperbarui.'); window.location='index_admin.php?page_admin=admin/data_admin';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.');</script>";
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

  .form-admin {
    max-width: 850px;
    margin: auto;
    background: #ffffff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
  }

  .form-admin h3 {
    font-size: 24px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 30px;
    color: #2575fc;
  }

  .form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
  }

  .form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
  }

  .form-group label {
    font-weight: 600;
    margin-bottom: 6px;
    color: #444;
  }

  .form-group input {
    padding: 10px 14px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    background-color: #fdfdfd;
    color: #333;
  }

  .form-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.25);
  }

  .form-actions {
    text-align: center;
    margin-top: 2rem;
  }

  .btn-simpan {
    background: linear-gradient(to right, #667eea, #764ba2);
    color: white;
    border: none;
    font-weight: 600;
    padding: 0.6rem 1.5rem;
    border-radius: 10px;
    cursor: pointer;
    transition: 0.3s;
  }

  .btn-simpan:hover {
    background: linear-gradient(to right, #43e97b, #38f9d7);
    transform: scale(1.02);
  }

  @media (max-width: 768px) {
    .form-row {
      flex-direction: column;
    }
  }
</style>

<div class="form-admin">
  <h3>Edit Admin</h3>
  <form method="POST" action="">
    <div class="form-row">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($admin['nama_lengkap']) ?>" required>
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Alamat</label>
        <input type="text" name="alamat" value="<?= htmlspecialchars($admin['alamat']) ?>" required>
      </div>
      <div class="form-group">
        <label>No HP</label>
        <input type="text" name="no_hp" value="<?= htmlspecialchars($admin['no_hp']) ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Jabatan</label>
        <input type="text" name="jabatan" value="<?= htmlspecialchars($admin['jabatan']) ?>" required>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-simpan">Perbarui Admin</button>
    </div>
  </form>
</div>
