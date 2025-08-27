<?php
include "config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $nip = $_POST['nip'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_menjabat = $_POST['tanggal_menjabat'];
    $status = $_POST['status'];
    $password_plain = $_POST['password'];
    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    // Upload foto jika ada
    $foto = '';
    if ($_FILES['foto']['name'] != '') {
        $foto = time() . '_' . $_FILES['foto']['name'];
        move_uploaded_file($_FILES['foto']['tmp_name'], 'upload/' . $foto);
    }

    $query1 = "INSERT INTO kepala_sekolah 
        (username, password, nama_lengkap, nip, jenis_kelamin, no_hp, email, alamat, foto, tanggal_menjabat, status)
        VALUES 
        ('$username', '$password', '$nama_lengkap', '$nip', '$jenis_kelamin', '$no_hp', '$email', '$alamat', '$foto', '$tanggal_menjabat', '$status')";

    $insertKepsek = mysqli_query($koneksi, $query1);

    $query2 = "INSERT INTO users (username, password, nama_lengkap, role) 
               VALUES ('$username', '$password', '$nama_lengkap', 'kepala_sekolah')";
    $insertUser = mysqli_query($koneksi, $query2);

    if ($insertKepsek && $insertUser) {
        echo "<script>alert('Kepala Sekolah berhasil ditambahkan.'); window.location='index_admin.php?page_admin=kepala_sekolah/data_kepsek';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan Kepala Sekolah.'); window.location='index_admin.php?page_admin=kepala_sekolah/tambah_kepsek';</script>";
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

  .form-group input,
  .form-group select {
    padding: 10px 14px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    background-color: #fdfdfd;
    color: #333;
  }

  .form-group input:focus,
  .form-group select:focus {
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
  <h3>Tambah Kepala Sekolah</h3>
  <form method="POST" enctype="multipart/form-data">
    <div class="form-row">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="nama_lengkap" required>
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>NIP</label>
        <input type="text" name="nip">
      </div>
      <div class="form-group">
        <label>Jenis Kelamin</label>
        <select name="jenis_kelamin" required>
          <option value="">-- Pilih --</option>
          <option value="Laki-laki">Laki-laki</option>
          <option value="Perempuan">Perempuan</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>No HP</label>
        <input type="text" name="no_hp">
      </div>
      <div class="form-group">
        <label>Alamat</label>
        <input type="text" name="alamat">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Foto</label>
        <input type="file" name="foto">
      </div>
      <div class="form-group">
        <label>Tanggal Menjabat</label>
        <input type="date" name="tanggal_menjabat" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="Aktif">Aktif</option>
          <option value="Nonaktif">Nonaktif</option>
        </select>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-simpan">Simpan Kepala Sekolah</button>
    </div>
  </form>
</div>
