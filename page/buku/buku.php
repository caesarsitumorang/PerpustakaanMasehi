<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "config/koneksi.php";

$query = "SELECT b.*, COUNT(p.id_peminjaman) AS total_pinjam
          FROM buku b
          LEFT JOIN peminjaman p ON b.kode_buku = p.kode_buku
          GROUP BY b.kode_buku
          ORDER BY b.kategori ASC, total_pinjam DESC, b.judul ASC";
$sql = mysqli_query($koneksi, $query);

$booksByKategori = [];
if ($sql && mysqli_num_rows($sql) > 0) {
    while ($buku = mysqli_fetch_assoc($sql)) {
        $kategori = strtolower($buku['kategori']);
        $booksByKategori[$kategori][] = $buku;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Koleksi Buku</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <style>
        /* GLOBAL STYLING */
        html, body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            color: #000;
        }

        .title-section h3 {
            font-weight: 700;
            color: #fff;
        }

        .title-section p {
            color: rgba(255,255,255,0.85);
            margin-bottom: 0.75rem;
        }

        /* FILTER + SEARCH */
        .filter-search-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .filter-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-buttons .btn {
            font-weight: 500;
            border-radius: 20px;
            background-color: rgba(255,255,255,0.9);
            color: #000;
            border: none;
            transition: 0.3s;
        }

        .filter-buttons .btn.active {
            background-color: #ffce00;
            color: #000;
        }

        .search-bar input {
            width: 250px;
            height: 40px;
            padding-left: 12px;
            border-radius: 12px;
            border: 1px solid #ccc;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        /* GRID & CARD */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1.5rem;
        }

        .glass-card {
            position: relative;
            background: rgba(255,255,255,0.95);
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            transition: 0.3s ease-in-out;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            color: #000;
        }

        .glass-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
        }

        .glass-card .card-img-top {
            height: 160px;
            object-fit: cover;
            width: 100%;
        }

        .glass-card .card-body {
            padding: 15px 16px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .glass-card .badge-info {
            background: #ffce00;
            font-weight: bold;
            font-size: 0.85em;
            padding: 4px 12px;
            border-radius: 10px;
            color: #000;
            margin-bottom: 10px;
            display: inline-block;
        }

        .glass-card .card-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.5em;
            min-height: 2.5em;
            color: #000;
        }

        .glass-card .meta {
            font-size: 0.87rem;
            color: #333;
            margin-bottom: 10px;
        }

        .glass-card .btn-pinjam {
            background: #667eea;
            color: white;
            border: none;
            font-size: 0.9rem;
            padding: 8px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease-in-out;
            width: 100%;
        }

        .glass-card .btn-pinjam:hover {
            background: #764ba2;
            transform: scale(1.02);
            text-decoration: none;
        }

        .no-book-illustration {
            max-width: 160px;
            opacity: 0.92;
        }

        /* PAGINATION */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 3rem;
            gap: 15px;
        }

        .pagination-btn {
            background: #ffce00;
            color: #000;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .pagination-btn:hover:not(.disabled) {
            background: #ffe880;
            transform: translateY(-2px);
        }

        .pagination-btn.disabled {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .page-info {
            background: rgba(255,255,255,0.8);
            padding: 8px 16px;
            border-radius: 20px;
            color: #000;
            font-size: 0.9rem;
            border: 1px solid #dee2e6;
        }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            .filter-search-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .search-bar input {
                width: 100%;
            }
        }

       /* Ribbon Populer/Favorit Keren */
.popular-ribbon {
    position: absolute;
    top: -12px; /* sedikit keluar card */
    left: 10px;
    padding: 6px 14px;
    border-radius: 12px 12px 0 12px;
    font-weight: 700;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 6px;
    z-index: 10;
    color: #fff;
    text-transform: uppercase;
    box-shadow: 0 4px 10px rgba(0,0,0,0.25);
    transform: rotate(-5deg); /* efek miring */
}

/* Warna dan ikon berbeda sesuai rank */
.popular-ribbon.rank-1 {
    background: #FFD700;
    color: #000;
}
.popular-ribbon.rank-2 {
    background: #C0C0C0;
    color: #000;
}
.popular-ribbon.rank-3 {
    background: #CD7F32;
    color: #fff;
}

/* Ribbon tidak menutupi cover */
.stat-card {
    position: relative;
    overflow: visible; /* biar ribbon bisa keluar sedikit */
}

    </style>
</head>
<body>

<div class="container mt-5">
    <!-- TITLE -->
    <div class="row mb-3">
        <div class="col-12 title-section">
            <h3>📚 Koleksi Buku</h3>
            <p>Temukan dan pinjam buku favoritmu secara digital!</p>
        </div>
    </div>

    <!-- FILTER & SEARCH -->
    <div class="filter-search-row">
        <div class="filter-buttons">
            <button class="btn kategori-btn active" data-kategori="fiksi">Fiksi</button>
            <button class="btn kategori-btn" data-kategori="non fiksi">Non Fiksi</button>
            <button class="btn kategori-btn" data-kategori="pelajaran">Pelajaran</button>
        </div>
        <div class="search-bar">
            <input type="text" class="form-control" id="searchJudul" placeholder="Cari berdasarkan judul...">
        </div>
    </div>

    <!-- LIST BUKU -->
    <div class="stats-grid" id="bukuContainer">
        <?php
        if (!empty($booksByKategori)):
            foreach ($booksByKategori as $kategori => $books):
                $rank = 1;
                foreach ($books as $buku):

                    // hitung jumlah peminjaman aktif (status dipinjam) untuk buku ini
                    $qryPinjam = $koneksi->prepare("
                        SELECT COUNT(*) AS jumlah_dipinjam 
                        FROM peminjaman 
                        WHERE kode_buku = ? AND status = 'dipinjam'
                    ");
                    $qryPinjam->bind_param("s", $buku['kode_buku']);
                    $qryPinjam->execute();
                    $result = $qryPinjam->get_result()->fetch_assoc();
                    $jumlahDipinjam = $result['jumlah_dipinjam'];

                    $favoriteClass = '';
                    $favoriteIcon = '';
                    $favoriteTitle = '';

                    if ($buku['total_pinjam'] >= 15) {
                        $favoriteClass = 'mega-favorite';
                        $favoriteIcon = '<i class="fas fa-crown"></i>';
                        $favoriteTitle = 'Buku Legendaris';
                    } elseif ($buku['total_pinjam'] >= 8) {
                        $favoriteClass = 'super-favorite';
                        $favoriteIcon = '<i class="fas fa-star"></i>';
                        $favoriteTitle = 'Buku Super Populer';
                    } elseif ($buku['total_pinjam'] >= 3) {
                        $favoriteClass = 'favorite';
                        $favoriteIcon = '<i class="fas fa-heart"></i>';
                        $favoriteTitle = 'Buku Populer';
                    }
        ?>
        <div class="stat-card glass-card <?= $favoriteClass ?>" 
             data-kategori="<?= strtolower($buku['kategori']) ?>" 
             data-judul="<?= strtolower($buku['judul']) ?>">

            <?php if ($rank <= 3 && $buku['total_pinjam'] > 0): ?>
                <div class="popular-ribbon rank-<?= $rank; ?>">
                    <?php
                        if ($rank == 1) echo '<i class="fas fa-crown"></i> POPULER #1';
                        elseif ($rank == 2) echo '<i class="fas fa-star"></i> POPULER #2';
                        else echo '<i class="fas fa-fire"></i> POPULER #3';
                    ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($favoriteIcon)): ?>
                <div class="favorite-badge <?= $favoriteClass ?>" title="<?= $favoriteTitle ?>">
                    <?= $favoriteIcon ?>
                </div>
            <?php endif; ?>

            <img src="<?= !empty($buku['cover']) ? 'upload/'.$buku['cover'] : 'img/cover/default.jpg' ?>" 
                 class="card-img-top" alt="<?= $buku['judul'] ?>">

            <div class="card-body d-flex flex-column">
                <span class="badge badge-info">
                    <i class="fas fa-book"></i> <?= strtoupper($buku['kode_buku']) ?>
                </span>

                <h6 class="card-title"><?= $buku['judul'] ?></h6>

                <div class="meta">
                    <i class="fas fa-user"></i> <?= $buku['pengarang'] ?>
                    <span class="mx-1">|</span>
                    <i class="fas fa-calendar-alt"></i> <?= $buku['tahun_terbit'] ?>
                    <br>
                    <i class="fas fa-chart-line text-success"></i> 
                    <span class="popularity-stats">
                        <?= $jumlahDipinjam == 0 ? 'Belum dipinjam' :
                           ($jumlahDipinjam == 1 ? '1 kali dipinjam' : $jumlahDipinjam.' kali dipinjam') ?>
                    </span>
                </div>

                <div class="mt-auto">
                    <a href="index.php?page=buku/pinjam_buku&id=<?= $buku['kode_buku'] ?>" 
                       class="btn btn-sm btn-pinjam">
                        <i class="fas fa-book-reader"></i> Pinjam Sekarang
                    </a>
                </div>
            </div>
        </div>
        <?php
                $rank++;
                endforeach;
            endforeach;
        else:
        ?>
        <div class='col-12 text-center py-5'>
            <img src='img/undraw_posting_photo.svg' class='no-book-illustration' alt='No Book'>
            <h5 class='mt-3 text-light'>Tidak ada buku yang tersedia saat ini.<br>
                <span style='font-size:0.95em;'>Yuk, tambah koleksi baru!</span></h5>
        </div>
        <?php endif; ?>
    </div>

    <!-- PAGINATION -->
    <div class="pagination-container" id="paginationContainer" style="display: none;">
        <button class="pagination-btn" id="prevBtn">
            <i class="fas fa-chevron-left"></i> Previous
        </button>
        <div class="page-info" id="pageInfo">Halaman 1</div>
        <button class="pagination-btn" id="nextBtn">
            Next <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<script>
    const kategoriBtns = document.querySelectorAll('.kategori-btn');
    const bukuCards = document.querySelectorAll('.stat-card');
    const searchInput = document.getElementById('searchJudul');
    const paginationContainer = document.getElementById('paginationContainer');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const pageInfo = document.getElementById('pageInfo');

    let currentKategori = 'fiksi';
    let currentPage = 1;
    let booksPerPage = 5;
    let filteredBooks = [];

    function getFilteredBooks() {
        const keyword = searchInput.value.toLowerCase();
        filteredBooks = Array.from(bukuCards).filter(card => {
            const kategori = card.getAttribute('data-kategori');
            const judul = card.getAttribute('data-judul');
            return kategori === currentKategori && judul.includes(keyword);
        });
        return filteredBooks;
    }

    function showPage(page) {
        const books = getFilteredBooks();
        const totalPages = Math.ceil(books.length / booksPerPage);

        bukuCards.forEach(card => card.style.display = 'none');

        const startIndex = (page - 1) * booksPerPage;
        const endIndex = startIndex + booksPerPage;
        const booksToShow = books.slice(startIndex, endIndex);

        booksToShow.forEach(card => card.style.display = 'flex');

        if (books.length > booksPerPage) {
            paginationContainer.style.display = 'flex';
            pageInfo.textContent = `Halaman ${page} dari ${totalPages}`;
            prevBtn.classList.toggle('disabled', page === 1);
            nextBtn.classList.toggle('disabled', page === totalPages);
        } else {
            paginationContainer.style.display = 'none';
        }
    }

    function filterBuku() {
        currentPage = 1;
        showPage(currentPage);
    }

    kategoriBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            kategoriBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentKategori = this.getAttribute('data-kategori');
            filterBuku();
        });
    });

    searchInput.addEventListener('keyup', filterBuku);
    filterBuku();

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            showPage(currentPage);
        }
    });

    nextBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredBooks.length / booksPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            showPage(currentPage);
        }
    });
</script>

</body>
</html>