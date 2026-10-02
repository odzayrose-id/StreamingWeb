<?php
include 'koneksi.php';

// Menangkap parameter pencarian dan tipe (Series/Movie)
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$type = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';

// 1. QUERY UNTUK HERO IMAGE (BANNER ATAS)
$hero_query_str = "SELECT * FROM films ";
if ($type) {
    $hero_query_str .= "WHERE tipe = '$type' ";
}
$hero_query_str .= "ORDER BY id DESC LIMIT 1";
$hero_query = mysqli_query($conn, $hero_query_str);
$hero = mysqli_fetch_assoc($hero_query);

// 2. QUERY UNTUK KONTEN UTAMA (Menyesuaikan dengan pencarian dan filter tipe)
$query_str = "SELECT * FROM films ";
$conditions = [];

if ($search) {
    $conditions[] = "judul LIKE '%$search%'";
}
if ($type) {
    $conditions[] = "tipe = '$type'";
}

// Menggabungkan kondisi jika ada
if (count($conditions) > 0) {
    $query_str .= "WHERE " . implode(' AND ', $conditions) . " ";
}
$query_str .= "ORDER BY id DESC";
$result = mysqli_query($conn, $query_str);

// 3. PENGELOMPOKKAN KATEGORI MURNI
$films_by_category = [];
while($row = mysqli_fetch_assoc($result)) {
    $kategori_list = explode(',', $row['kategori']);
    
    foreach($kategori_list as $kat) {
        $kat = trim($kat);
        if(!empty($kat)) {
            $films_by_category[$kat][] = $row;
        }
    }
}
ksort($films_by_category); // Urutkan kategori secara alfabetis
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/png" sizes="32x32" href="https://apps.odzayrose.my.id/favicon/streaming/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://apps.odzayrose.my.id/favicon/streaming/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="https://apps.odzayrose.my.id/favicon/streaming/apple-touch-icon.png">
    <link rel="icon" href="https://apps.odzayrose.my.id/favicon/streaming/favicon.ico">
    <link rel="manifest" href="https://apps.odzayrose.my.id/favicon/streaming/site.webmanifest">
    <title>Streaming - Film & Series</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #141414; color: white; overflow-x: hidden; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .navbar-netflix { background: linear-gradient(to bottom, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0) 100%); transition: background-color 0.4s; }
        
        .hero { height: 75vh; background-size: cover; background-position: center top; position: relative; }
        .hero-vignette { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to right, rgba(20,20,20,0.8) 0%, rgba(20,20,20,0) 60%), linear-gradient(to top, #141414 0%, rgba(20,20,20,0) 100%); }
        .hero-content { position: absolute; bottom: 15%; left: 5%; max-width: 600px; }
        
        .btn-play { background-color: white; color: black; font-weight: bold; padding: 8px 24px; }
        .btn-play:hover { background-color: rgba(255,255,255,0.7); color: black; }
        
        .row-title { 
            font-size: 1.4rem; font-weight: bold; margin-left: 5%; margin-bottom: 15px; 
            color: #ffffff; border-left: 4px solid #e50914; padding-left: 12px; line-height: 1.2;
        }
        
        /* WADAH BARIS UNTUK POSISI TOMBOL */
        .row-container { position: relative; display: block; width: 100%; }

        .row-scroll { display: flex; flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; padding-top: 15px; margin-top: -15px; padding-left: 5%; padding-right: 5%; padding-bottom: 20px; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; }
        .row-scroll::-webkit-scrollbar { display: none; }
        .row-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        
        .film-card { flex: 0 0 auto; width: 140px; margin-right: 15px; transition: transform 0.3s ease; text-decoration: none; display: flex; flex-direction: column; }
        .film-card img { width: 100%; height: 200px; object-fit: cover; border-radius: 5px; transition: border 0.3s ease; }
        .film-card:hover { transform: scale(1.05); z-index: 10; }
        .film-card:hover img { border: 2px solid white; }
        
        /* TOMBOL SCROLL LINGKARAN */
        .scroll-btn {
            position: absolute;
            top: 45%;
            transform: translateY(-50%);
            z-index: 15;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0; 
            transition: all 0.3s ease;
            font-size: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.8);
        }

        .row-container:hover .scroll-btn { opacity: 1; }
        
        .scroll-btn:hover {
            background-color: #e50914;
            border-color: #e50914;
            transform: translateY(-50%) scale(1.15);
        }

        .left-btn { left: 1%; }
        .right-btn { right: 1%; }

        .search-container { position: relative; }
        .search-box { background: rgba(0,0,0,0.7); border: 1px solid white; color: white !important; }
        .search-box:focus { background: rgba(0,0,0,0.9); box-shadow: none; border-color: white;}
        .search-box::placeholder { color: #ffffff !important; opacity: 1; }
        
        .search-results { position: absolute; top: 100%; left: 0; right: 0; background-color: #1a1a1a; border: 1px solid #333; z-index: 9999; display: none; max-height: 350px; overflow-y: auto; box-shadow: 0 5px 15px rgba(0,0,0,0.8); }
        .search-result-item { display: flex; align-items: center; padding: 10px; text-decoration: none; border-bottom: 1px solid #333; color: white; }
        .search-result-item:hover { background-color: #333; color: white; }
        .search-result-item img { width: 35px; height: 50px; object-fit: cover; margin-right: 12px; border-radius: 3px; }

        @media (max-width: 768px) {
            .scroll-btn { display: none !important; } /* Hilangkan tombol di HP karena bisa geser pakai jari */
            .left-btn, .right-btn { display: none !important; }
        }

        @media (min-width: 768px) { .film-card { width: 180px; } .film-card img { height: 260px; } }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-netflix py-3">
        <div class="container-fluid px-md-5">
            <a class="navbar-brand fw-bold fs-3 text-danger" href="index.php">STREAMING</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-semibold">
                    <li class="nav-item">
                        <a class="nav-link <?= (empty($type) && empty($search)) ? 'active fw-bold' : '' ?>" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($type == 'Series') ? 'active fw-bold' : '' ?>" href="index.php?type=Series">Series</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($type == 'Movie') ? 'active fw-bold' : '' ?>" href="index.php?type=Movie">Film</a>
                    </li>
                </ul>
                <div class="search-container d-flex me-3 mt-3 mt-lg-0">
                    <form method="GET" action="index.php" class="w-100 m-0 d-flex gap-2">
                        <?php if($type): ?>
                            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                        <?php endif; ?>
                        
                        <input type="text" name="search" id="searchInput" class="form-control search-box rounded-0" placeholder="Cari Judul..." autocomplete="off" value="<?= htmlspecialchars($search) ?>">
                    </form>
                    <div id="searchResults" class="search-results"></div>
                </div>
            </div>
        </div>
    </nav>

    <?php if(empty($search) && $hero): ?>
    <div class="hero" style="background-image: url('<?= htmlspecialchars($hero['thumbnail_url']) ?>');">
        <div class="hero-vignette"></div>
        <div class="hero-content">
            <h1 class="fw-bold text-uppercase mb-3 shadow-sm"><?= htmlspecialchars($hero['judul']) ?></h1>
            <p class="lead mb-4 text-truncate" style="max-width: 500px;"><?= htmlspecialchars($hero['deskripsi']) ?></p>
            <div class="d-flex gap-2">
                <a href="play.php?id=<?= $hero['id'] ?>" class="btn btn-play rounded-1 fs-5"><i class="bi bi-play-fill"></i> Putar</a>
                <span class="btn btn-secondary rounded-1 fs-5 bg-secondary bg-opacity-75 text-white border-0"><i class="bi bi-info-circle"></i> Info</span>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div style="height: 100px;"></div>
    <?php endif; ?>

    <div class="container-fluid p-0 <?= empty($search) ? 'mt-n5 position-relative' : 'mt-4' ?>" style="z-index: 5;">
        
        <?php if(!empty($search)): ?>
            <h4 class="row-title mt-4">Hasil Pencarian: "<?= htmlspecialchars($search) ?>" <?= $type ? 'di ' . ($type == 'Movie' ? 'Film' : 'Series') : '' ?></h4>
        <?php elseif(!empty($type)): ?>
            <h4 class="row-title mt-4">Menampilkan: <?= $type == 'Movie' ? 'Film (Movies)' : 'Series (TV Shows)' ?></h4>
        <?php endif; ?>

        <?php if(!empty($films_by_category)): ?>
            <?php foreach($films_by_category as $kategori => $films): ?>
                <h4 class="row-title mt-4 pt-2"><?= htmlspecialchars($kategori) ?></h4>
                
                <!-- BUNGKUSAN ROW-CONTAINER DAN TOMBOL SCROLL -->
                <div class="row-container">
                    <button class="scroll-btn left-btn"><i class="bi bi-chevron-left"></i></button>
                    
                    <div class="row-scroll">
                        <?php foreach($films as $film): ?>
                            <a href="play.php?id=<?= $film['id'] ?>" class="film-card text-decoration-none">
                                <img src="<?= htmlspecialchars($film['thumbnail_url']) ?>" alt="<?= htmlspecialchars($film['judul']) ?>" loading="lazy">
                                <div class="mt-2 text-start">
                                    <div class="text-white fw-bold text-truncate" style="font-size: 0.95rem; line-height: 1.2;"><?= htmlspecialchars($film['judul']) ?></div>
                                    <div class="text-white-50 mt-1" style="font-size: 0.85rem;">
                                        <?= $film['tahun'] ?><?php if(!empty($film['asal_negara'])): ?> &bull; <?= htmlspecialchars($film['asal_negara']) ?><?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <button class="scroll-btn right-btn"><i class="bi bi-chevron-right"></i></button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center mt-5 text-secondary">
                <i class="bi bi-film fs-1"></i>
                <h5 class="mt-2">Tidak ada konten yang ditemukan.</h5>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Efek transisi warna background navbar saat di scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-netflix');
            if (window.scrollY > 50) { 
                navbar.style.backgroundColor = '#141414'; 
            } else { 
                navbar.style.background = 'linear-gradient(to bottom, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0) 100%)'; 
            }
        });

        // Script Pencarian Ajax Dropdown
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                let query = this.value.trim();
                
                if(query.length > 0) {
                    let typeParam = '<?= $type ? "&type=".urlencode($type) : "" ?>';
                    
                    fetch('ajax_search.php?q=' + encodeURIComponent(query) + typeParam)
                    .then(response => response.text())
                    .then(data => {
                        searchResults.innerHTML = data;
                        searchResults.style.display = 'block';
                    });
                } else {
                    searchResults.style.display = 'none';
                }
            });
            
            document.addEventListener('click', function(e) {
                if(!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
        }

        // ==========================================
        // SCRIPT FUNGSI TOMBOL GESER KIRI/KANAN
        // ==========================================
        document.addEventListener('DOMContentLoaded', function() {
            const rowContainers = document.querySelectorAll('.row-container');
            
            rowContainers.forEach(container => {
                const rowScroll = container.querySelector('.row-scroll');
                const leftBtn = container.querySelector('.left-btn');
                const rightBtn = container.querySelector('.right-btn');

                if(rowScroll && leftBtn && rightBtn) {
                    leftBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        rowScroll.scrollBy({ left: -(rowScroll.clientWidth * 0.75), behavior: 'smooth' });
                    });

                    rightBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        rowScroll.scrollBy({ left: (rowScroll.clientWidth * 0.75), behavior: 'smooth' });
                    });
                }
            });
        });

        // ==========================================
        // SCRIPT ANTI KLIK KANAN (DISABLE RIGHT CLICK)
        // ==========================================
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>