<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "config/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='login.php';</script>";
    exit;
}

$id_user = $_SESSION['id_user'];
$getUser = mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = '$id_user'");
$dataUser = mysqli_fetch_assoc($getUser);
$username = $dataUser['username'] ?? null;

$getPengunjung = mysqli_query($koneksi, "SELECT id FROM pengunjung WHERE username = '$username'");
$dataPengunjung = mysqli_fetch_assoc($getPengunjung);
$id_pengunjung = $dataPengunjung['id'] ?? null;

if (!$id_pengunjung) {
    echo "<script>alert('Data pengunjung tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

$kode_buku = $_GET['id'] ?? null;

$sql = mysqli_query($koneksi, "SELECT * FROM buku WHERE kode_buku = '$kode_buku'");
$buku = mysqli_fetch_assoc($sql);

if (!$buku) {
    echo "<script>alert('Buku tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// Cek apakah sudah pernah mengajukan peminjaman buku yang sama dan belum diproses/masih dipinjam
$cekPengajuan = mysqli_query($koneksi, "SELECT * FROM peminjaman 
                                        WHERE id_pengunjung = '$id_pengunjung' 
                                        AND kode_buku = '$kode_buku' 
                                        AND status IN ('Diajukan', 'Dipinjam')");
                                        
if (mysqli_num_rows($cekPengajuan) > 0) {
    $dataPengajuan = mysqli_fetch_assoc($cekPengajuan);
    if ($dataPengajuan['status'] == 'Diajukan') {
        echo "<script>alert('Kamu sudah mengajukan peminjaman buku ini dan menunggu persetujuan admin.'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Kamu sudah meminjam buku ini dan belum mengembalikannya.'); window.location='index.php';</script>";
    }
    exit;
}

// Cek jumlah buku yang sedang dipinjam (status Dipinjam saja, bukan Diajukan)
$cekJumlah = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM peminjaman 
                                     WHERE id_pengunjung = '$id_pengunjung' 
                                     AND status = 'Dipinjam'");
$dataJumlah = mysqli_fetch_assoc($cekJumlah);
if ($dataJumlah['total'] >= 2) {
    echo "<script>alert('Kamu sudah meminjam 2 buku. Harap kembalikan salah satu terlebih dahulu.'); window.location='index.php';</script>";
    exit;
}

// Cek jumlah pengajuan yang sedang menunggu persetujuan
$cekPengajuanMenunggu = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM peminjaman 
                                                WHERE id_pengunjung = '$id_pengunjung' 
                                                AND status = 'Diajukan'");
$dataPengajuanMenunggu = mysqli_fetch_assoc($cekPengajuanMenunggu);
if ($dataPengajuanMenunggu['total'] >= 3) {
    echo "<script>alert('Kamu sudah memiliki 3 pengajuan peminjaman yang menunggu persetujuan. Harap tunggu admin memproses pengajuan sebelumnya.'); window.location='index.php';</script>";
    exit;
}

if (isset($_POST['submit'])) {
    $tanggal_pinjam = date('Y-m-d H:i:s');

    if ($buku['jumlah'] <= 0) {
        echo "<script>alert('Stok buku habis!'); window.location='index.php';</script>";
        exit;
    }

    $query = mysqli_query($koneksi, "INSERT INTO peminjaman (kode_buku, id_pengunjung, tanggal_pinjam, status) 
                                     VALUES ('$kode_buku', '$id_pengunjung', '$tanggal_pinjam', 'Diajukan')");


    if ($query) {
        echo "<script>alert('Pengajuan peminjaman berhasil diajukan! Silakan tunggu persetujuan dari admin.'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Gagal mengajukan peminjaman buku!');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Peminjaman Buku</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .card-custom {
            border: none;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            background: #fff;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .card-header-custom h2 {
            margin: 0;
            font-weight: 700;
            font-size: 2rem;
        }

        .book-content-wrapper {
            display: flex;
            min-height: 500px;
        }

        .book-cover-section {
            flex: 0 0 40%;
            padding: 40px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .book-cover-container {
            position: relative;
            max-width: 280px;
            width: 100%;
        }

        .book-cover-image {
            width: 100%;
            height: 350px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .book-cover-image:hover {
            transform: translateY(-5px);
        }

        .stock-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #10b981;
            color: white;
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        }

        .book-details-section {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .book-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 30px;
            line-height: 1.2;
        }

        .detail-row {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .detail-value {
            font-weight: 500;
            color: #1f2937;
            font-size: 16px;
        }

        .category-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 14px;
            display: inline-block;
        }

        .action-section {
            background: #f8fafc;
            padding: 30px 40px;
            border-top: 1px solid #e2e8f0;
        }

        .info-section {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .info-section h5 {
            color: #92400e;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .info-section ul {
            color: #78350f;
            margin-bottom: 0;
            padding-left: 20px;
        }

        .info-section li {
            margin-bottom: 8px;
        }

        .btn-group-custom {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn-custom {
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 180px;
            justify-content: center;
            text-decoration: none;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(245, 158, 11, 0.4);
            color: white;
        }

        .btn-secondary-custom {
            background: #6b7280;
            color: white;
            box-shadow: 0 8px 20px rgba(107, 114, 128, 0.3);
        }

        .btn-secondary-custom:hover {
            background: #4b5563;
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(75, 85, 99, 0.4);
            color: white;
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .book-content-wrapper {
                flex-direction: column;
            }

            .book-cover-section {
                flex: none;
                padding: 30px 20px;
            }

            .book-details-section {
                padding: 30px 20px;
            }

            .action-section {
                padding: 25px 20px;
            }

            .btn-group-custom {
                flex-direction: column;
            }

            .book-title {
                font-size: 1.5rem;
                text-align: center;
            }

            .card-header-custom h2 {
                font-size: 1.5rem;
            }

            .book-cover-image {
                height: 300px;
            }
        }

        @media (max-width: 576px) {
            .detail-row {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .detail-icon {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header-custom">
                        <h2><i class="fas fa-file-signature me-2"></i>Ajukan Peminjaman Buku</h2>
                    </div>
                    
                    <div class="book-content-wrapper">
                        <div class="book-cover-section">
                            <div class="book-cover-container">
                                <?php if (!empty($buku['cover'])): ?>
                                    <img src="upload/<?= $buku['cover'] ?>" class="book-cover-image" alt="<?= $buku['judul'] ?>">
                                <?php else: ?>
                                    <img src="img/cover/default.jpg" class="book-cover-image" alt="No Cover">
                                <?php endif; ?>
                                <div class="stock-badge">
                                    <i class="fas fa-layer-group me-1"></i><?= htmlspecialchars($buku['jumlah']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="book-details-section">
                            <h3 class="book-title"><?= htmlspecialchars($buku['judul']) ?></h3>
                            
                            <div class="detail-row">
                                <div class="detail-icon">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Pengarang</div>
                                    <div class="detail-value"><?= htmlspecialchars($buku['pengarang']) ?></div>
                                </div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Penerbit</div>
                                    <div class="detail-value"><?= htmlspecialchars($buku['penerbit']) ?></div>
                                </div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Tahun Terbit</div>
                                    <div class="detail-value"><?= htmlspecialchars($buku['tahun_terbit']) ?></div>
                                </div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-icon">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Kategori</div>
                                    <div class="detail-value">
                                        <span class="category-badge"><?= htmlspecialchars($buku['kategori']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="action-section">
                        <div class="info-section">
                            <h5><i class="fas fa-info-circle me-2"></i>Informasi Pengajuan Peminjaman</h5>
                            <ul>
                                <li>Pengajuan akan dikirim ke admin untuk diproses</li>
                                <li>Tunggu persetujuan dari admin sebelum buku dapat dipinjam</li>
                                <li>Maksimal 2 buku dipinjam bersamaan</li>
                                <li>Tidak dapat mengajukan buku yang sama jika masih dalam proses atau dipinjam</li>
                                <li>Maksimal 3 pengajuan menunggu persetujuan</li>
                            </ul>
                        </div>
                        
                        <form method="post">
                            <div class="btn-group-custom">
                                <button type="submit" name="submit" class="btn-custom btn-primary-custom">
                                    <i class="fas fa-paper-plane"></i> Ajukan Peminjaman
                                </button>
                                <a href="index.php?page=buku/buku" class="btn-custom btn-secondary-custom">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>