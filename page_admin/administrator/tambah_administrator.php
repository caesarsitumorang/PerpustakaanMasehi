<?php
include "config/koneksi.php";

if (isset($_POST['submit'])) {
  $role = $_POST['role'];

  if ($role == 'admin') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $jabatan = $_POST['jabatan'];
    $created_at = $updated_at = date('Y-m-d H:i:s');

    $foto = '';
    if ($_FILES['foto']['name'] != '') {
      $foto = time() . '_' . $_FILES['foto']['name'];
      move_uploaded_file($_FILES['foto']['tmp_name'], 'upload/' . $foto);
    }

    // insert ke users
    mysqli_query($koneksi, "INSERT INTO users (username, password, nama_lengkap, role) 
                            VALUES ('$username', '$password', '$nama_lengkap', 'admin')");

    // insert ke admin
    mysqli_query($koneksi, "INSERT INTO admin (username, password, nama_lengkap, email, alamat, no_hp, jabatan, created_at, updated_at, foto)
                            VALUES ('$username', '$password', '$nama_lengkap', '$email', '$alamat', '$no_hp', '$jabatan', '$created_at', '$updated_at', '$foto')");

    echo "<script>alert('Admin berhasil ditambahkan'); location.href='index_admin.php?page_admin=administrator/data_administrator';</script>";
  }

  else if ($role == 'kepala_sekolah') {
    $username = $_POST['username_kepsek'];
    $password = password_hash($_POST['password_kepsek'], PASSWORD_DEFAULT);
    $nama_lengkap = $_POST['nama_lengkap_kepsek'];
    $nip = $_POST['nip'];
    $email = $_POST['email_kepsek'];
    $alamat = $_POST['alamat_kepsek'];
    $no_hp = $_POST['no_hp_kepsek'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_menjabat = $_POST['tanggal_menjabat'];
    $status = $_POST['status'];
    $created_at = $updated_at = date('Y-m-d H:i:s');

    $foto = '';
    if ($_FILES['foto_kepsek']['name'] != '') {
      $foto = time() . '_' . $_FILES['foto_kepsek']['name'];
      move_uploaded_file($_FILES['foto_kepsek']['tmp_name'], 'upload/' . $foto);
    }

    // insert ke users
    mysqli_query($koneksi, "INSERT INTO users (username, password, nama_lengkap, role) 
                            VALUES ('$username', '$password', '$nama_lengkap', 'kepala_sekolah')");

    // insert ke kepala_sekolah
    mysqli_query($koneksi, "INSERT INTO kepala_sekolah 
        (username, password, nama_lengkap, nip, jenis_kelamin, no_hp, email, alamat, foto, tanggal_menjabat, status, created_at, updated_at)
        VALUES 
        ('$username', '$password', '$nama_lengkap', '$nip', '$jenis_kelamin', '$no_hp', '$email', '$alamat', '$foto', '$tanggal_menjabat', '$status', '$created_at', '$updated_at')");

    echo "<script>alert('Kepala Sekolah berhasil ditambahkan'); location.href='index_admin.php?page_admin=administrator/data_administrator';</script>";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Tambah Administrator</title>
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
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
    }

    h3 {
      text-align: center;
      color: black;
      margin-bottom: 20px;
    }

    label {
      font-weight: 600;
      color: black;
      display: block;
      margin-bottom: 5px;
    }

    input, select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      color: black;
    }

    .form-buttons {
      display: flex;
      gap: 10px;
      justify-content: flex-start;
      margin-top: 20px;
    }

    .btn-submit {
      background: linear-gradient(to right, #667eea, #764ba2);
      color: white;
      font-weight: bold;
      padding: 10px 20px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .btn-submit:hover {
      background: linear-gradient(to right, #43e97b, #38f9d7);
    }

    .btn-back {
      background: #ccc;
      color: black;
      font-weight: bold;
      padding: 10px 20px;
      border-radius: 10px;
      text-decoration: none;
      display: inline-block;
      transition: background 0.3s ease;
    }

    .btn-back:hover {
      background: #aaa;
    }

    .form-section {
      display: none;
    }
  </style>
  <script>
    function showForm() {
      const role = document.getElementById('role').value;
      document.getElementById('form-admin').style.display = 'none';
      document.getElementById('form-kepsek').style.display = 'none';
      if (role === 'admin') {
        document.getElementById('form-admin').style.display = 'block';
      } else if (role === 'kepala_sekolah') {
        document.getElementById('form-kepsek').style.display = 'block';
      }
    }
  </script>
</head>
<body>

<div class="form-container">
  <h3>Tambah Administrator</h3>

  <!-- Pilihan Role -->
  <form method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label>Pilih Role</label>
      <select name="role" id="role" onchange="showForm()" required>
        <option value="">-- Pilih Role --</option>
        <option value="admin">Admin</option>
        <option value="kepala_sekolah">Kepala Sekolah</option>
      </select>
    </div>

    <!-- FORM ADMIN -->
    <div id="form-admin" class="form-section">
      <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap"></div>
      <div class="form-group"><label>Username</label><input type="text" name="username"></div>
      <div class="form-group"><label>Password</label><input type="password" name="password"></div>
      <div class="form-group"><label>Email</label><input type="email" name="email"></div>
      <div class="form-group"><label>Alamat</label><input type="text" name="alamat"></div>
      <div class="form-group"><label>No HP</label><input type="text" name="no_hp"></div>
      <div class="form-group"><label>Jabatan</label><input type="text" name="jabatan"></div>
      <div class="form-group"><label>Foto</label><input type="file" name="foto"></div>
    </div>

    <!-- FORM KEPALA SEKOLAH -->
    <div id="form-kepsek" class="form-section">
      <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap_kepsek"></div>
      <div class="form-group"><label>NIP</label><input type="text" name="nip"></div>
      <div class="form-group"><label>Username</label><input type="text" name="username_kepsek"></div>
      <div class="form-group"><label>Password</label><input type="password" name="password_kepsek"></div>
      <div class="form-group"><label>Email</label><input type="email" name="email_kepsek"></div>
      <div class="form-group"><label>No HP</label><input type="text" name="no_hp_kepsek"></div>
      <div class="form-group"><label>Jenis Kelamin</label>
        <select name="jenis_kelamin">
          <option value="Laki-laki">Laki-laki</option>
          <option value="Perempuan">Perempuan</option>
        </select>
      </div>
      <div class="form-group"><label>Alamat</label><input type="text" name="alamat_kepsek"></div>
      <div class="form-group"><label>Tanggal Menjabat</label><input type="date" name="tanggal_menjabat"></div>
      <div class="form-group"><label>Status</label><input type="text" name="status"></div>
      <div class="form-group"><label>Foto</label><input type="file" name="foto_kepsek"></div>
    </div>

   <div class="form-buttons">
  <button type="submit" class="btn-submit" name="submit">Simpan Administrator</button>
  <a href="javascript:history.back()" class="btn-back">Kembali</a>
</div>

  </form>
</div>
