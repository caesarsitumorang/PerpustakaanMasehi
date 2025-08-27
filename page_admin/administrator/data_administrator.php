<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

  html, body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin: 0;
    padding: 0;
    min-height: 100vh;
  }

  .container {
    max-width: 100%;
    margin: auto;
  }

  .top-action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
  }

  .top-action-bar h3 {
    margin: 0;
    font-size: 1.4rem;
    color: white;
  }

  .top-action-bar p {
    margin: 0;
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.9rem;
  }

  .btn-tambah {
    background: linear-gradient(to right, #021f9fff, #00365cff);
    border: none;
    color: white;
    font-weight: 600;
    padding: 0.5rem 1.2rem;
    border-radius: 8px;
    text-decoration: none;
    transition: 0.3s;
    font-size: 0.9rem;
  }

  .btn-tambah:hover {
    background: linear-gradient(to right, #5a67d8, #6b46c1);
    text-decoration: none;
  }

  .table-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  }

  .table-admin {
    width: 100%;
    border-collapse: collapse;
    border-radius: 12px;
    overflow: hidden;
  }

  .table-admin thead th {
    background: linear-gradient(to right, #000b3aff, #00365cff);
    color: white;
    font-weight: 600;
    text-align: left;
    padding: 14px 16px;
  }

  .table-admin th,
  .table-admin td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.95rem;
    color: #333;
    white-space: nowrap;
  }

  .table-admin tbody tr:hover {
    background: #f7f9fc;
    transition: 0.2s;
  }

  .table-admin tbody tr:nth-child(even) {
    background: #fafafa;
  }

  .aksi-btn {
    display: flex;
    gap: 6px;
  }

  .btn-edit, .btn-hapus {
    padding: 6px 10px;
    font-size: 0.85rem;
    font-weight: 500;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s;
    color: white;
  }

  .btn-edit {
    background: #4CAF50;
  }

  .btn-edit:hover {
    background: #43a047;
  }

  .btn-hapus {
    background: #e53935;
  }

  .btn-hapus:hover {
    background: #d32f2f;
  }
</style>

<?php
include "config/koneksi.php";

// Pagination
$batas = 10;
$halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$halaman_awal = ($halaman - 1) * $batas;

// Ambil total data
$jumlah_data = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM users WHERE role IN ('admin', 'kepala_sekolah')"));
$total_halaman = ceil($jumlah_data / $batas);

// Query dengan pagination
$query = "SELECT * FROM users WHERE role IN ('admin', 'kepala_sekolah') ORDER BY role, username ASC LIMIT $halaman_awal, $batas";
$sql = mysqli_query($koneksi, $query);
?>

<div class="container">
  <div class="top-action-bar">
    <div>
      <h3>👤 Data Admin & Kepala Sekolah</h3>
      <p>Lihat dan kelola semua akun pengguna khusus (admin & kepala sekolah).</p>
    </div>
    <div>
      <a href="index_admin.php?page_admin=administrator/tambah_administrator" class="btn-tambah">
        <i class="fas fa-plus-circle"></i> Tambah Pengguna
      </a>
    </div>
  </div>

  <!-- Search -->
  <div class="search-bar" style="margin-bottom:1rem;">
    <input type="text" id="searchInput" placeholder="Cari username atau nama lengkap..." style="padding:0.5rem 1rem; border-radius:12px; border:1px solid #ccc; font-size:0.9rem; width:250px;">
  </div>

  <div class="table-container">
    <div class="table-responsive">
      <table class="table-admin" id="adminTable">
        <thead>
          <tr>
            <th>No</th>
            <th>Username</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>Alamat</th>
            <th>No HP</th>
            <th>Jabatan</th>
            <th>Role</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = $halaman_awal + 1;
          if ($sql && mysqli_num_rows($sql) > 0) {
            while ($user = mysqli_fetch_assoc($sql)) {
              $username = $user['username'];
              $nama_lengkap = $user['nama_lengkap'];
              $role = $user['role'];

              if ($role === 'admin') {
                $qDetail = mysqli_query($koneksi, "SELECT * FROM admin WHERE username = '$username'");
              } else {
                $qDetail = mysqli_query($koneksi, "SELECT * FROM kepala_sekolah WHERE username = '$username'");
              }

              $detail = mysqli_fetch_assoc($qDetail);

              $email = $detail['email'] ?? '-';
              $alamat = $detail['alamat'] ?? '-';
              $no_hp = $detail['no_hp'] ?? '-';
              $jabatan = $detail['jabatan'] ?? '-';

              echo "<tr>
                <td>{$no}</td>
                <td>{$username}</td>
                <td>{$nama_lengkap}</td>
                <td>{$email}</td>
                <td>{$alamat}</td>
                <td>{$no_hp}</td>
                <td>{$jabatan}</td>
                <td style='text-transform:capitalize;'>{$role}</td>
                <td class='aksi-btn'>
                  <a href='index_admin.php?page_admin=administrator/edit_administrator&username={$username}&role={$role}' class='btn-edit'>Edit</a>
                  <a href='index_admin.php?page_admin=administrator/hapus_administrator&username={$username}&role={$role}' class='btn-hapus' onclick='return confirm(\"Yakin ingin menghapus pengguna ini?\")'>Hapus</a>
                </td>
              </tr>";
              $no++;
            }
          } else {
            echo "<tr><td colspan='9' style='text-align:center; color:#666;'>Tidak ada data pengguna.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pagination -->
  <div class="pagination" style="margin-top:1.5rem; text-align:center;">
    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
      <a href="?page_admin=administrator/data_administrator&hal=<?= $i ?>" class="<?= ($i == $halaman) ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
</div>

<script>
  const searchInput = document.getElementById('searchInput');
  const table = document.getElementById('adminTable');
  const tbody = table.querySelector('tbody');

  searchInput.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = tbody.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
      const username = rows[i].cells[1].textContent.toLowerCase();
      const nama = rows[i].cells[2].textContent.toLowerCase();
      rows[i].style.display = (username.includes(filter) || nama.includes(filter)) ? '' : 'none';
    }
  });
</script>

