<style>
  .table-kepsek {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1.5rem;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
    min-width: 1000px; /* Supaya scroll muncul jika layar kecil */
  }

  .table-kepsek thead th {
    background: linear-gradient(to right, #667eea, #764ba2);
    color: white;
  }

  .table-kepsek th, .table-kepsek td {
    padding: 12px 16px;
    text-align: left;
    vertical-align: middle;
    color: #000;
    white-space: nowrap;
  }

  .table-kepsek tbody tr:nth-child(even) {
    background: #f9f9f9;
  }

  .table-kepsek img.foto-img {
    width: 60px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
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

  .btn-tambah-kepsek {
    background: linear-gradient(to right, #667eea, #764ba2);
    border: none;
    color: white;
    font-weight: 600;
    padding: 0.5rem 1.2rem;
    border-radius: 10px;
    text-decoration: none;
    transition: 0.3s;
  }

  .btn-tambah-kepsek:hover {
    background: linear-gradient(to right, #43e97b, #38f9d7);
    color: white;
    transform: scale(1.03);
    text-decoration: none;
  }

  .top-action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 2rem;
  }

  .table-wrapper {
    width: 100%;
    overflow-x: auto;
  }
</style>

<div class="container">
  <div class="top-action-bar">
    <div>
      <h3 class="mb-2 font-weight-bold" style="letter-spacing:1px;color:#2575fc;">🎓 Data Kepala Sekolah</h3>
      <p class="text-muted">Lihat dan kelola semua data kepala sekolah di sistem.</p>
    </div>
    <div>
      <a href="index_admin.php?page_admin=kepala_sekolah/tambah_kepsek" class="btn-tambah-kepsek">
        <i class="fas fa-plus-circle"></i> Tambah Kepsek
      </a>
    </div>
  </div>

  <div class="table-wrapper">
    <table class="table-kepsek">
      <thead>
        <tr>
          <th>No</th>
          <th>Foto</th>
          <th>Username</th>
          <th>Nama Lengkap</th>
          <th>NIP</th>
          <th>Jenis Kelamin</th>
          <th>No HP</th>
          <th>Email</th>
          <th>Tanggal Menjabat</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        include "config/koneksi.php";
        $no = 1;
        $query = "SELECT * FROM kepala_sekolah ORDER BY id_kepala_sekolah ASC";
        $sql = mysqli_query($koneksi, $query);

        if ($sql && mysqli_num_rows($sql) > 0) {
          while ($kepsek = mysqli_fetch_assoc($sql)) {
            $foto = !empty($kepsek['foto']) && file_exists("upload/" . $kepsek['foto'])
              ? "upload/" . $kepsek['foto']
              : "img/cover/default.jpg";

            echo "<tr>
              <td>{$no}</td>
              <td><img src='{$foto}' alt='{$kepsek['nama_lengkap']}' class='foto-img'></td>
              <td>{$kepsek['username']}</td>
              <td>{$kepsek['nama_lengkap']}</td>
              <td>{$kepsek['nip']}</td>
              <td>{$kepsek['jenis_kelamin']}</td>
              <td>{$kepsek['no_hp']}</td>
              <td>{$kepsek['email']}</td>
              <td>{$kepsek['tanggal_menjabat']}</td>
              <td>{$kepsek['status']}</td>
              <td class='aksi-btn'>
                <a href='index_admin.php?page_admin=kepala_sekolah/edit_kepsek&id={$kepsek['id_kepala_sekolah']}' class='btn-edit'>Edit</a>
                <a href='index_admin.php?page_admin=kepala_sekolah/hapus_kepsek&id={$kepsek['id_kepala_sekolah']}' class='btn-hapus' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>
              </td>
            </tr>";
            $no++;
          }
        } else {
          echo "<tr><td colspan='11' class='text-center'>Tidak ada data kepala sekolah.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
