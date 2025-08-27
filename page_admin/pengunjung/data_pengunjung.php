<?php
include "config/koneksi.php";

// Pagination
$batas = 5;
$halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$halaman_awal = ($halaman - 1) * $batas;

// Ambil total data
$jumlah_data = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pengunjung"));
$total_halaman = ceil($jumlah_data / $batas);

// Query dengan pagination dan LEFT JOIN ke member
$query = "
  SELECT p.*, m.id_member 
  FROM pengunjung p
  LEFT JOIN member m ON m.id_pengunjung = p.id
  ORDER BY p.id ASC
  LIMIT $halaman_awal, $batas
";
$sql = mysqli_query($koneksi, $query);
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

  .container {
    padding: 25px;
    color: #fff;
  }

  .top-action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1rem;
  }

  .top-action-bar h3 {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
  }

  .top-action-bar p {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.8);
  }

  .btn-action {
    background: linear-gradient(to right, #021f9f, #00365c);
    border: none;
    color: white;
    font-weight: 500;
    padding: 0.55rem 1.2rem;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
  }

  .btn-action:hover {
    background: linear-gradient(to right, #5a67d8, #6b46c1);
    transform: translateY(-1px);
  }

  .table-wrapper {
    overflow-x: auto;
    margin-top: 1rem;
  }

  .table-pengunjung {
    width: 100%;
    border-collapse: collapse;
    background-color: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    min-width: 1000px;
  }

  .table-pengunjung thead th {
    background: linear-gradient(to right, #000b3a, #00365c);
    color: white;
    text-align: left;
    padding: 14px 18px;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .table-pengunjung th, .table-pengunjung td {
    padding: 14px 18px;
    vertical-align: middle;
    font-size: 0.95rem;
    color: #333;
    white-space: nowrap;
    border-bottom: 1px solid #f0f0f0;
  }

  .table-pengunjung tbody tr:nth-child(even) {
    background-color: #f9f9f9;
  }

  .table-pengunjung tbody tr:hover {
    background: #f2f6ff;
    transition: background 0.2s ease;
  }

  .foto-img {
    width: 60px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #ddd;
  }

  .badge-member {
    background: #28a745;
    color: white;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
  }

  .btn-hapus {
    background: #dc3545;
    color: white;
    font-size: 0.8rem;
    font-weight: 500;
    padding: 6px 10px;
    border-radius: 6px;
    text-decoration: none;
    transition: 0.2s ease;
  }

  .btn-hapus:hover {
    background: #c82333;
  }

  /* Search bar */
  .search-bar {
    margin-bottom: 1rem;
  }
  .search-bar input {
    padding: 0.5rem 1rem;
    border-radius: 12px;
    border: 1px solid #ccc;
    width: 250px;
    font-size: 0.9rem;
  }

  /* Pagination */
  .pagination {
    margin-top: 1.5rem;
    text-align: center;
  }
  .pagination a {
    display: inline-block;
    padding: 6px 12px;
    margin: 0 4px;
    border-radius: 6px;
    background: #eee;
    text-decoration: none;
    color: #333;
    font-weight: 600;
  }
  .pagination a.active {
    background: #2575fc;
    color: white;
  }
  .pagination a:hover {
    background: #38f9d7;
    color: white;
  }
</style>

<div class="container">
  <div class="top-action-bar">
    <div>
      <h3>👥 Data Pengunjung</h3>
      <p>Lihat dan kelola semua data pengunjung.</p>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="index_admin.php?page_admin=pengunjung/tambah_pengunjung" class="btn-action">
        <i class="fas fa-plus-circle"></i> Tambah Pengunjung
      </a>
      <a href="page_admin/pengunjung/cetak_pengunjung.php" target="_blank" class="btn-action" style="background: linear-gradient(to right, #000b3aff, #337ccfff);">
        <i class="fas fa-print"></i> Cetak PDF
      </a>
    </div>
  </div>

  <!-- Search -->
  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="Cari username atau nama lengkap...">
  </div>

  <div class="table-wrapper">
    <table class="table-pengunjung" id="pengunjungTable">
      <thead>
        <tr>
          <th>No</th>
          <th>Foto</th>
          <th>Username</th>
          <th>Nama Lengkap</th>
          <th>Email</th>
          <th>No HP</th>
          <th>Alamat</th>
          <th>Tanggal Daftar</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php
        $no = $halaman_awal + 1;
        if ($sql && mysqli_num_rows($sql) > 0) {
          while ($data = mysqli_fetch_assoc($sql)) {
            $foto = (!empty($data['foto']) && file_exists("upload/" . $data['foto']))
              ? "upload/" . $data['foto']
              : "img/cover/default.jpg";
            $status = !empty($data['id_member']) ? "<span class='badge-member'>Member</span>" : "Non Member";

            echo "<tr>
              <td>{$no}</td>
              <td><img src='{$foto}' class='foto-img'></td>
              <td>{$data['username']}</td>
              <td>{$data['nama_lengkap']}</td>
              <td>{$data['email']}</td>
              <td>{$data['no_hp']}</td>
              <td>{$data['alamat']}</td>
              <td>{$data['tanggal_daftar']}</td>
              <td>{$status}</td>
              <td>
                <a href='index_admin.php?page_admin=pengunjung/hapus_pengunjung&id={$data['id']}' class='btn-hapus' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>
              </td>
            </tr>";
            $no++;
          }
        } else {
          echo "<tr><td colspan='10' class='text-center'>Tidak ada data pengunjung.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
      <a href="?page_admin=pengunjung/data_pengunjung&hal=<?= $i ?>" class="<?= ($i == $halaman) ? 'active' : '' ?>">
        <?= $i ?>
      </a>
    <?php endfor; ?>
  </div>
</div>

<script>
  const searchInput = document.getElementById('searchInput');
  const table = document.getElementById('pengunjungTable');
  const tbody = table.querySelector('tbody');

  searchInput.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = tbody.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
      const username = rows[i].cells[2].textContent.toLowerCase();
      const nama = rows[i].cells[3].textContent.toLowerCase();
      rows[i].style.display = (username.includes(filter) || nama.includes(filter)) ? '' : 'none';
    }
  });
</script>
