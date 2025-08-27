<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f4f6f9;
    margin: 0;
    padding: 20px;
    color: #333;
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
    margin-bottom: 2rem;
  }

  .table-responsive {
    width: 100%;
    overflow-x: auto;
    overflow-y: auto;
  }

  .table-buku {
    min-width: 900px;
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.5rem;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
  }

  .table-buku thead th {
    background: linear-gradient(to right, #667eea, #764ba2);
    color: white;
    font-size: 0.95rem;
  }

  .table-buku th, .table-buku td {
    padding: 12px 16px;
    text-align: left;
    vertical-align: middle;
    color: #000;
    white-space: nowrap;
  }

  .table-buku tbody tr:nth-child(even) {
    background: #f9f9f9;
  }

  .aksi-btn {
    display: flex;
    gap: 8px;
  }

  .btn-edit, .btn-hapus {
    padding: 6px 12px;
    font-size: 0.85rem;
    font-weight: 600;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    color: white;
    display: inline-block;
  }

  .btn-edit {
    background: #28a745;
  }

  .btn-edit:hover {
    background: #218838;
  }

  .btn-hapus {
    background: #dc3545;
  }

  .btn-hapus:hover {
    background: #c82333;
  }

  .btn-tambah-buku {
    background: linear-gradient(to right, #667eea, #764ba2);
    border: none;
    color: white;
    font-weight: 600;
    padding: 0.5rem 1.2rem;
    border-radius: 10px;
    text-decoration: none;
    transition: 0.3s;
  }

  .btn-tambah-buku:hover {
    background: linear-gradient(to right, #43e97b, #38f9d7);
    color: white;
    transform: scale(1.03);
    text-decoration: none;
  }

  @media (max-width: 768px) {
    .top-action-bar {
      flex-direction: column;
      align-items: flex-start;
    }

    .btn-tambah-buku {
      margin-top: 10px;
    }
  }
</style>

<div class="container">
  <div class="top-action-bar">
   <div>
  <h3 class="mb-2 font-weight-bold" style="letter-spacing:1px; color:white;">👤 Data Admin</h3>
  <p style="color:white;">Lihat dan kelola semua admin di sistem.</p>
</div>

    <div>
      <a href="index_admin.php?page_admin=admin/tambah_admin" class="btn-tambah-buku">
        <i class="fas fa-plus-circle"></i> Tambah Admin
      </a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table-buku">
      <thead>
        <tr>
          <th>No</th>
          <th>Username</th>
          <th>Nama Lengkap</th>
          <th>Email</th>
          <th>Alamat</th>
          <th>No HP</th>
          <th>Jabatan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        include "config/koneksi.php";
        $no = 1;
        $query = "SELECT * FROM admin ORDER BY username ASC";
        $sql = mysqli_query($koneksi, $query);

        if ($sql && mysqli_num_rows($sql) > 0) {
          while ($admin = mysqli_fetch_assoc($sql)) {
            echo "<tr>
              <td>{$no}</td>
              <td>{$admin['username']}</td>
              <td>{$admin['nama_lengkap']}</td>
              <td>{$admin['email']}</td>
              <td>{$admin['alamat']}</td>
              <td>{$admin['no_hp']}</td>
              <td>{$admin['jabatan']}</td>
              <td class='aksi-btn'>
                <a href='index_admin.php?page_admin=admin/edit_admin&id={$admin['id']}' class='btn-edit'>Edit</a>
                <a href='index_admin.php?page_admin=admin/hapus_admin&id={$admin['id']}' class='btn-hapus' onclick='return confirm(\"Yakin ingin menghapus admin ini?\")'>Hapus</a>
              </td>
            </tr>";
            $no++;
          }
        } else {
          echo "<tr><td colspan='8' class='text-center'>Tidak ada data admin.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
