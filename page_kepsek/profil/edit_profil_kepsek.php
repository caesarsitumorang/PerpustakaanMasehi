<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "config/koneksi.php";

$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'"));
if (!$user) {
    echo "<div class='alert alert-danger'>Data user tidak ditemukan.</div>";
    exit;
}

$username_user = $user['username'];
$kepsek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM kepala_sekolah WHERE username = '$username_user'"));
if (!$kepsek) {
    echo "<div class='alert alert-danger'>Data kepala sekolah tidak ditemukan.</div>";
    exit;
}

$id_kepsek = $kepsek['id_kepala_sekolah'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username         = $_POST['username'];
    $nama_lengkap     = $_POST['nama_lengkap'];
    $nip              = $_POST['nip'];
    $jenis_kelamin    = $_POST['jenis_kelamin'];
    $no_hp            = $_POST['no_hp'];
    $email            = $_POST['email'];
    $alamat           = $_POST['alamat'];
    $tanggal_menjabat = $_POST['tanggal_menjabat'];
    $status           = $_POST['status'];
    $password         = $_POST['password'] ?? '';
    $foto             = $kepsek['foto'];

    if (!empty($_FILES['foto']['name'])) {
        $uploadDir = "upload/";
        $fileName = time() . "_" . basename($_FILES["foto"]["name"]);
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFile)) {
            $foto = $fileName;
        }
    }

    $query_kepsek = "UPDATE kepala_sekolah SET 
        username = '$username',
        nama_lengkap = '$nama_lengkap',
        nip = '$nip',
        jenis_kelamin = '$jenis_kelamin',
        no_hp = '$no_hp',
        email = '$email',
        alamat = '$alamat',
        foto = '$foto',
        tanggal_menjabat = '$tanggal_menjabat',
        status = '$status',
        updated_at = NOW()
        WHERE id_kepala_sekolah = '$id_kepsek'";

    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query_user = "UPDATE users SET username = '$username', password = '$password_hash' WHERE id_user = '$id_user'";
    } else {
        $query_user = "UPDATE users SET username = '$username' WHERE id_user = '$id_user'";
    }

    $update1 = mysqli_query($koneksi, $query_kepsek);
    $update2 = mysqli_query($koneksi, $query_user);

    if ($update1 && $update2) {
        echo "<script>alert('Profil kepala sekolah berhasil diperbarui!'); window.location='index_kepsek.php?page_kepsek=profil/profil_kepsek';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menyimpan perubahan.</div>";
    }
}
?>
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

  input, select, textarea {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
    width: 100%;
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
  }

  .btn-back {
    padding: 10px 20px;
    background-color: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 16px;
    display: inline-block;
    font-weight: bold;
  }

  .preview-img {
    width: 120px;
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
  <h3>Edit Profil Kepala Sekolah</h3>

  <form method="POST" enctype="multipart/form-data">
    <div class="form-container">

      <div class="form-row">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" value="<?= htmlspecialchars($kepsek['username']) ?>" required>
        </div>
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($kepsek['nama_lengkap']) ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>NIP</label>
          <input type="text" name="nip" value="<?= htmlspecialchars($kepsek['nip']) ?>">
        </div>
        <div class="form-group">
          <label>Jenis Kelamin</label>
          <select name="jenis_kelamin" required>
            <option value="">-- Pilih --</option>
            <option value="Laki-laki" <?= $kepsek['jenis_kelamin'] === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
            <option value="Perempuan" <?= $kepsek['jenis_kelamin'] === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($kepsek['email']) ?>" required>
        </div>
        <div class="form-group">
          <label>No HP</label>
          <input type="text" name="no_hp" value="<?= htmlspecialchars($kepsek['no_hp']) ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Alamat</label>
          <textarea name="alamat"><?= htmlspecialchars($kepsek['alamat']) ?></textarea>
        </div>
        <div class="form-group">
          <label>Tanggal Menjabat</label>
          <input type="date" name="tanggal_menjabat" value="<?= htmlspecialchars($kepsek['tanggal_menjabat']) ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Status</label>
          <select name="status" required>
            <option value="Aktif" <?= $kepsek['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Nonaktif" <?= $kepsek['status'] === 'Nonaktif' ? 'selected' : '' ?>>Tidak Aktif</option>
          </select>
        </div>
        <div class="form-group">
          <label>Password Baru (Opsional)</label>
          <input type="password" name="password" placeholder="Kosongkan jika tidak diubah">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Foto</label><br>
          <?php if (!empty($kepsek['foto'])): ?>
            <img src="upload/<?= htmlspecialchars($kepsek['foto']) ?>" class="preview-img" alt="Foto Profil">
          <?php endif; ?>
          <input type="file" name="foto" accept="image/*">
        </div>
      </div>

      <div class="button-group">
        <button type="submit" class="btn-submit">Simpan Perubahan</button>
        <a href="index_kepsek.php?page_kepsek=profil/profil_kepsek " class="btn-back">Kembali</a>
      </div>

    </div>
  </form>
</div>
