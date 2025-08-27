<?php
include "config/koneksi.php";

$query = "
  SELECT p.*, m.id_member 
  FROM pengunjung p
  LEFT JOIN member m ON m.id_pengunjung = p.id
  ORDER BY p.id ASC
";
$sql = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Print Data Pengunjung</title>
  <style>
    body {
      font-family: Arial, sans-serif;
    }

    h2 {
      text-align: center;
      color: #2575fc;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 2rem;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: left;
    }

    th {
      background-color: #2575fc;
      color: white;
    }

    .foto-img {
      width: 60px;
      height: 80px;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    .badge-member {
      background-color: #28a745;
      color: white;
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: bold;
    }

    @media print {
      @page { size: A4 landscape; }
    }
  </style>
</head>
<body onload="window.print()">

  <h2>Data Pengunjung</h2>

  <table>
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
      </tr>
    </thead>
    <tbody>
      <?php
      $no = 1;
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
          </tr>";
          $no++;
        }
      } else {
        echo "<tr><td colspan='9'>Tidak ada data pengunjung.</td></tr>";
      }
      ?>
    </tbody>
  </table>

</body>
</html>
