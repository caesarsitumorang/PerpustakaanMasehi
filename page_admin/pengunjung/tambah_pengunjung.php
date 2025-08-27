<?php
include "config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $tanggal_daftar = date("Y-m-d");
    $password_plain = $_POST['password'];
    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    // Cek apakah username sudah digunakan di tabel users
    $cekUsername = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cekUsername) > 0) {
        echo "<script>alert('Username sudah digunakan. Silakan pilih username lain.'); window.location='index_admin.php?page_admin=pengunjung/tambah_pengunjung';</script>";
        exit;
    }

    // Upload foto jika ada
    $foto = '';
    if ($_FILES['foto']['name'] != '') {
        $foto = time() . '_' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], 'upload/' . $foto);
    }

    // Insert ke tabel pengunjung
    $query1 = "INSERT INTO pengunjung 
        (username, password, nama_lengkap, email, no_hp, alamat, tanggal_daftar, foto)
        VALUES 
        ('$username', '$password', '$nama_lengkap', '$email', '$no_hp', '$alamat', '$tanggal_daftar', '$foto')";

    $insertPengunjung = mysqli_query($koneksi, $query1);

    // Insert ke tabel users
    $query2 = "INSERT INTO users (username, password, nama_lengkap, role) 
               VALUES ('$username', '$password', '$nama_lengkap', 'pengunjung')";
    $insertUser = mysqli_query($koneksi, $query2);

    if ($insertPengunjung && $insertUser) {
        echo "<script>alert('Pengunjung berhasil ditambahkan.'); window.location='index_admin.php?page_admin=pengunjung/data_pengunjung';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan Pengunjung.'); window.location='index_admin.php?page_admin=pengunjung/tambah_pengunjung';</script>";
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
  .btn-secondary {
  background: linear-gradient(to right, #6c757d, #495057);
  border: none;
  font-weight: bold;
  color: white;
  padding: 10px 20px;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: linear-gradient(to right, #868e96, #343a40);
  transform: scale(1.05);
  text-decoration: none;
}

</style>

<div class="form-admin">
  <h3>Tambah Pengunjung</h3>
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
    </div>

     <div class="btn-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        <a href="index_admin.php?page_admin=pengunjung/data_pengunjung  " class="btn btn-secondary">Kembali</a>
      </div>
  </form>
</div>
