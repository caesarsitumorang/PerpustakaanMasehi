<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "config/koneksi.php";

$role = 'guest';
$id_user = $_SESSION['id_user'] ?? null;

if ($id_user) {
    $user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'"));
    if ($user) {
        $username = $user['username'];
        $pengunjung = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pengunjung WHERE username = '$username'"));
        if ($pengunjung) {
            $id_pengunjung = $pengunjung['id'];
            $cek_member = mysqli_query($koneksi, "SELECT * FROM member WHERE id_pengunjung = '$id_pengunjung' AND status = 'aktif'");
            if (mysqli_num_rows($cek_member) > 0) {
                $role = 'member';
            }
        }
    }
}

// Query untuk ambil data buku + total baca
$query = "SELECT b.*, 
          COALESCE(COUNT(tbb.id_buku), 0) as total_baca_buku
          FROM buku_digital b 
          LEFT JOIN total_baca_buku tbb ON b.id_buku = tbb.id_buku 
          GROUP BY b.id_buku, b.kategori, b.judul, b.pengarang, b.tahun_terbit, b.cover, b.akses 
          ORDER BY b.kategori, COALESCE(COUNT(tbb.id_buku), 0) DESC, b.judul ASC";
$result = mysqli_query($koneksi, $query);

// Normalisasi kategori key untuk filter
function kategoriKey($kategori) {
    return strtolower(trim(str_replace(' ', '_', $kategori ?: 'umum')));
}

// Kelompokkan buku per kategori
$buku_per_kategori = [];
if (mysqli_num_rows($result) > 0) {
    while ($buku = mysqli_fetch_assoc($result)) {
        $key = kategoriKey($buku['kategori']);
        if (!isset($buku_per_kategori[$key])) {
            $buku_per_kategori[$key] = ['label' => $buku['kategori'], 'list' => []];
        }
        $buku_per_kategori[$key]['list'][] = $buku;
    }
}

// Urutkan setiap kategori berdasarkan total_baca_buku DESC
foreach ($buku_per_kategori as &$kategoriData) {
    usort($kategoriData['list'], function($a, $b) {
        if ($b['total_baca_buku'] != $a['total_baca_buku']) {
            return $b['total_baca_buku'] - $a['total_baca_buku'];
        }
        return strcmp($a['judul'], $b['judul']);
    });
}
unset($kategoriData);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Digital Library</title>
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="assets/css/library.css" rel="stylesheet"> <!-- CSS dipisah -->
  
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      margin: 0;
      padding: 0;
      min-height: 100vh;
      color: #000;
    }
    .container { max-width: 1200px; }
    h3.title { color: #fff; font-weight: 700; margin-bottom: 2rem; text-align: center; }

    /* Filter + search row */
    .filter-search-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 2rem; }
    .filter-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
    .kategori-btn {
      padding: 10px 20px; border-radius: 25px; border: none; background: rgba(255,255,255,0.9); color: #000; font-weight: 500; cursor: pointer; transition: all 0.3s;
    }
    .kategori-btn.active { background: #ffce00; }
    .search-bar input { padding: 10px 15px; border-radius: 25px; border: 1px solid #ccc; width: 250px; }

    /* Grid buku */
    .card-container { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
    .glass-card {
      background: rgba(255,255,255,0.95); 
      border-radius: 16px; 
      box-shadow: 0 6px 18px rgba(0,0,0,0.1); 
      display: flex; 
      flex-direction: column; 
      overflow: visible; 
      transition: all 0.3s ease-in-out; 
      position: relative;
      margin-top: 20px;
    }
    .glass-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.2); }
    .glass-card img { height: 160px; object-fit: cover; width: 100%; border-radius: 16px 16px 0 0; }
    .glass-card .card-body { padding: 15px; display: flex; flex-direction: column; flex-grow: 1; }
    .glass-card .card-title { font-size: 1rem; font-weight: 700; min-height: 2.5em; }
    .glass-card .meta { font-size: 0.85rem; color: #333; margin-bottom: 10px; }
    .badge-member { background: #dc3545; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; display: inline-flex; align-items: center; margin-bottom: 10px; }
    .btn-baca {
      background: #667eea;
      color: #fff;
      border: none;
      padding: 8px;
      border-radius: 8px;
      font-weight: 600;
      margin-top: auto;
      text-align: center;
      display: block;
      text-decoration: none;
    }

    /* Ribbon favorit yang setengah di atas card dan setengah di dalam */
    .favorite-ribbon {
        position: absolute;
        top: -15px;
        left: 15px;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 700;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 6px;
        z-index: 15;
        color: #fff;
        text-transform: uppercase;
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        transform: rotate(-2deg);
        letter-spacing: 0.5px;
    }
    
    .favorite-ribbon.rank-1 { 
        background: linear-gradient(135deg, #FFD700, #FFA500); 
        color: #000; 
        box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
    }
    .favorite-ribbon.rank-2 { 
        background: linear-gradient(135deg, #C0C0C0, #808080); 
        color: #000; 
        box-shadow: 0 6px 20px rgba(192, 192, 192, 0.4);
    }
    .favorite-ribbon.rank-3 { 
        background: linear-gradient(135deg, #CD7F32, #8B4513); 
        color: #fff; 
        box-shadow: 0 6px 20px rgba(205, 127, 50, 0.4);
    }

    .favorite-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 1.2rem;
        color: red;
        z-index: 10;
    }
    
    /* Styling untuk badge favorit yang lebih menonjol */
    .mega-favorite {
        background: linear-gradient(135deg, rgba(255,215,0,0.15) 0%, rgba(255,255,255,0.95) 100%);
        border: 3px solid gold;
        box-shadow: 0 8px 25px rgba(255,215,0,0.3);
    }
    .super-favorite {
        background: linear-gradient(135deg, rgba(255,165,0,0.15) 0%, rgba(255,255,255,0.95) 100%);
        border: 3px solid orange;
        box-shadow: 0 8px 25px rgba(255,165,0,0.3);
    }
    .favorite {
        background: linear-gradient(135deg, rgba(220,20,60,0.15) 0%, rgba(255,255,255,0.95) 100%);
        border: 3px solid crimson;
        box-shadow: 0 8px 25px rgba(220,20,60,0.3);
    }

    .mega-favorite .favorite-badge { 
        color: gold; 
        font-size: 1.6rem; 
        animation: pulse 2s infinite; 
        text-shadow: 0 0 10px rgba(255,215,0,0.5);
    }
    .super-favorite .favorite-badge { 
        color: orange; 
        font-size: 1.4rem; 
        text-shadow: 0 0 8px rgba(255,165,0,0.5);
    }
    .favorite .favorite-badge { 
        color: crimson; 
        font-size: 1.2rem; 
        text-shadow: 0 0 6px rgba(220,20,60,0.5);
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .btn-baca:hover { background: #764ba2; text-decoration: none; }

    @media (max-width: 1200px) { .card-container { grid-template-columns: repeat(4, 1fr); } }
    @media (max-width: 992px) { .card-container { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) { .card-container { grid-template-columns: repeat(2, 1fr); } .search-bar input { width: 100%; max-width: 300px; } }
    @media (max-width: 576px) { .card-container { grid-template-columns: 1fr; } .filter-buttons { justify-content: center; } .search-bar { justify-content: center; margin-top: 10px; } }
  </style>
</head>
<body>
<div class="title">
  <div class="row mb-3">
    <div class="col-12 title-section">
      <h3 style="color:#fff;">📚 Koleksi Buku Digital</h3>
      <p style="color:#fff;">Temukan dan baca buku favoritmu secara digital!</p>
    </div>
  </div>

  <!-- Filter + Search -->
  <div class="filter-search-row">
    <div class="filter-buttons">
<?php 
$first = true; 
foreach ($buku_per_kategori as $key => $kategoriData): ?>
  <button class="kategori-btn <?= $first ? 'active' : '' ?>" 
          data-kategori="<?= $key ?>">
    <?= htmlspecialchars($kategoriData['label']) ?>
  </button>
<?php 
$first = false; 
endforeach; 
?>
    </div>

    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="🔍 Cari berdasarkan judul...">
    </div>
  </div>

  <div class="card-container" id="bookList">
    <?php
    if (!empty($buku_per_kategori)) {
        foreach ($buku_per_kategori as $key => $kategoriData): 
            $rank = 1; 
            foreach ($kategoriData['list'] as $buku): 
                $isMemberOnly = $buku['akses'] === 'member';
                $canRead = !$isMemberOnly || ($isMemberOnly && $role === 'member');

                // Badge favorit
                $favoriteClass = '';
                $favoriteIcon = '';
                $favoriteTitle = '';
                if ($buku['total_baca_buku'] >= 20) {
                    $favoriteClass = 'mega-favorite';
                    $favoriteIcon = '<i class="fas fa-crown"></i>';
                    $favoriteTitle = 'Buku Legendaris (20+ kali dibaca)';
                } elseif ($buku['total_baca_buku'] >= 10) {
                    $favoriteClass = 'super-favorite';
                    $favoriteIcon = '<i class="fas fa-star"></i>';
                    $favoriteTitle = 'Buku Super Populer (10+ kali dibaca)';
                } elseif ($buku['total_baca_buku'] >= 5) {
                    $favoriteClass = 'favorite';
                    $favoriteIcon = '<i class="fas fa-heart"></i>';
                    $favoriteTitle = 'Buku Populer (5+ kali dibaca)';
                }
        ?>
<div class="glass-card <?= $favoriteClass ?>" 
     data-kategori="<?= $key ?>" 
     data-judul="<?= strtolower(trim($buku['judul'])) ?>">

    <!-- Ribbon top 3 -->
    <?php if ($rank <= 3 && $buku['total_baca_buku'] > 0): ?>
        <div class="favorite-ribbon rank-<?= $rank ?>">
            <?php 
                if ($rank == 1) echo '<i class="fas fa-crown"></i> FAVORIT #1';
                elseif ($rank == 2) echo '<i class="fas fa-star"></i> FAVORIT #2';
                else echo '<i class="fas fa-fire"></i> FAVORIT #3';
            ?>
        </div>
    <?php endif; ?>

    <!-- Badge favorit -->
    <?php if (!empty($favoriteIcon)): ?>
        <div class="favorite-badge" title="<?= $favoriteTitle ?>">
            <?= $favoriteIcon ?>
        </div>
    <?php endif; ?>

    <img src="upload/<?= $buku['cover'] ?: 'default.jpg' ?>" alt="<?= htmlspecialchars($buku['judul']) ?>">
    <div class="card-body d-flex flex-column">
        <h6 class="card-title"><i class="fas fa-book"></i> <?= htmlspecialchars($buku['judul']) ?></h6>
        <div class="meta"><i class="fas fa-user"></i> <?= htmlspecialchars($buku['pengarang']) ?></div>
        <div class="meta"><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($buku['tahun_terbit']) ?></div>
        <div class="meta"><i class="fas fa-chart-line text-success"></i>
            <?= $buku['total_baca_buku'] == 0 ? 'Belum pernah dibaca' :
               ($buku['total_baca_buku'] == 1 ? 'Dibaca 1 kali' : 'Dibaca '.$buku['total_baca_buku'].' kali') ?>
        </div>
        <div class="meta"><i class="fas fa-tag"></i> Kategori: <?= htmlspecialchars($buku['kategori']) ?></div>
        <?php if ($isMemberOnly): ?>
          <span class="badge-member"><i class="fas fa-crown"></i> Khusus Member</span>
        <?php endif; ?>
        <?php if ($canRead): ?>
          <a href="index.php?page=buku_digital/tambah_baca&id_buku=<?= $buku['id_buku'] ?>" class="btn-baca">
            <i class="fas fa-book-open"></i> Baca Buku
          </a>
        <?php else: ?>
          <button class="btn-baca" disabled><i class="fas fa-lock"></i> Hanya untuk Member</button>
        <?php endif; ?>
    </div>
</div>
        <?php $rank++; endforeach; endforeach; ?>
    <?php } else { ?>
      <div class="col-12 text-center" style="color:#fff;padding:2rem;">
        <i class="fas fa-book-open fa-3x"></i>
        <p>Tidak ada buku digital tersedia saat ini.</p>
      </div>
    <?php } ?>
  </div>
</div>

<script>
const kategoriBtns = document.querySelectorAll('.kategori-btn');
const cards = document.querySelectorAll('.glass-card');
const searchInput = document.getElementById("searchInput");

let currentKategori = document.querySelector('.kategori-btn.active').getAttribute('data-kategori');

function normalizeKategori(str) {
    return str.trim().toLowerCase().replace(/\s+/g, '_');
}

function filterBuku() {
    const keyword = searchInput.value.toLowerCase().trim();
    cards.forEach(card => {
        const kategori = normalizeKategori(card.getAttribute("data-kategori"));
        const judul = card.getAttribute("data-judul");
        const matchKategori = kategori === normalizeKategori(currentKategori);
        const matchJudul = keyword === '' || judul.includes(keyword);
        card.style.display = (matchKategori && matchJudul) ? "flex" : "none";
    });
}

kategoriBtns.forEach(btn => {
    btn.addEventListener("click", function () {
        kategoriBtns.forEach(b => b.classList.remove("active"));
        this.classList.add("active");
        currentKategori = this.getAttribute("data-kategori");
        filterBuku();
    });
});

searchInput.addEventListener("input", function() {
    filterBuku();
});

filterBuku();
</script>
</body>
</html>