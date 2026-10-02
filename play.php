<?php
include 'koneksi.php';
if(!isset($_GET['id'])) { header("Location: index.php"); exit; }

$id = $_GET['id'];
$film = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM films WHERE id=$id"));
if(!$film) { die("<h3 style='color:white; text-align:center; margin-top:50px;'>Film tidak ditemukan!</h3>"); }

// Variabel default dari Movie/Series Utama
$v360 = $film['video_url_360'] ?? '';
$v480 = $film['video_url_480'] ?? '';
$v720 = $film['video_url_720'] ?? '';
$v1080 = $film['video_url_1080'] ?? '';

$d360 = $film['download_url_360'] ?? '';
$d480 = $film['download_url_480'] ?? '';
$d720 = $film['download_url_720'] ?? '';
$d1080 = $film['download_url_1080'] ?? '';

$telegram_url = $film['telegram_url'] ?? ''; 
$asal_negara = $film['asal_negara'] ?? '';
$pemeran = $film['pemeran'] ?? '';
$judul_tampil = $film['judul'];
$deskripsi_tampil = $film['deskripsi'];
$eps_aktif = 0;

// Jika memutar Episode Series
if(isset($_GET['eps'])) {
    $eps_id = $_GET['eps'];
    $eps_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM episodes WHERE id=$eps_id"));
    if($eps_data) {
        $v360 = $eps_data['video_url_360'] ?? '';
        $v480 = $eps_data['video_url_480'] ?? '';
        $v720 = $eps_data['video_url_720'] ?? '';
        $v1080 = $eps_data['video_url_1080'] ?? '';

        $d360 = $eps_data['download_url_360'] ?? '';
        $d480 = $eps_data['download_url_480'] ?? '';
        $d720 = $eps_data['download_url_720'] ?? '';
        $d1080 = $eps_data['download_url_1080'] ?? '';
        
        $telegram_url = !empty($eps_data['telegram_url']) ? $eps_data['telegram_url'] : ($film['telegram_url'] ?? '');
        $deskripsi_tampil = !empty($eps_data['deskripsi']) ? $eps_data['deskripsi'] : $film['deskripsi'];
        
        $judul_tampil = $film['judul'] . " - Season " . $eps_data['season'] . " Episode " . $eps_data['eps_ke'];
        $eps_aktif = $eps_data['id'];
    }
}

// Menyiapkan Array Kualitas untuk ArtPlayer JSON
$art_qualities = [];
$default_label = "";
$kualitas_badge = "";

if (!empty($v1080)) { $kualitas_badge = "HD"; $default_label = "1080p"; } 
elseif (!empty($v720)) { $kualitas_badge = "HD"; $default_label = "720p"; } 
elseif (!empty($v480)) { $kualitas_badge = "SD"; $default_label = "480p"; } 
elseif (!empty($v360)) { $kualitas_badge = "SD"; $default_label = "360p"; }

if (!empty($v1080)) $art_qualities[] = ['html' => '1080p', 'url' => $v1080, 'download' => $d1080];
if (!empty($v720))  $art_qualities[] = ['html' => '720p',  'url' => $v720, 'download' => $d720];
if (!empty($v480))  $art_qualities[] = ['html' => '480p',  'url' => $v480, 'download' => $d480];
if (!empty($v360))  $art_qualities[] = ['html' => '360p',  'url' => $v360, 'download' => $d360];

$default_vid = "";
foreach ($art_qualities as &$q) {
    if ($q['html'] === $default_label) {
        $q['default'] = true;
        $default_vid = $q['url'];
    }
}
$qualities_json = json_encode($art_qualities);

// Kelompokkan episode berdasarkan Season
$episodes_by_season = [];
if($film['tipe'] == 'Series') {
    $episodes_query = mysqli_query($conn, "SELECT * FROM episodes WHERE film_id=$id ORDER BY season ASC, eps_ke ASC");
    while($eps = mysqli_fetch_assoc($episodes_query)) {
        $episodes_by_season[$eps['season']][] = $eps;
    }
}

$trending_query = mysqli_query($conn, "SELECT * FROM films WHERE id != $id ORDER BY id DESC LIMIT 6");

// Siapkan Array Pemeran untuk JS
$aktor_array = [];
if(!empty($pemeran)) {
    $aktor_list = explode(',', $pemeran);
    foreach($aktor_list as $aktor) {
        $aktor_trim = trim($aktor);
        if(!empty($aktor_trim)) $aktor_array[] = $aktor_trim;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/png" sizes="32x32" href="https://apps.odzayrose.my.id/favicon/streaming/favicon-32x32.png">
    <title>Play - <?= htmlspecialchars($film['judul']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- Library ArtPlayer -->
    <script src="https://cdn.jsdelivr.net/npm/artplayer/dist/artplayer.js"></script>

    <style>
        body { background-color: #141414; color: white; overflow-x: hidden; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
               -webkit-user-select: none; -khtml-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
        input, textarea { -webkit-user-select: text; -khtml-user-select: text; -moz-user-select: text; -ms-user-select: text; user-select: text; }
        img { -webkit-user-drag: none; }
        
        .navbar-netflix { background-color: #000; border-bottom: 1px solid #333; position: relative; z-index: 1000; }
        .nav-top-bar { height: 60px; display: flex; align-items: center; justify-content: space-between; width: 100%; position: relative;}
        .brand-center { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); margin: 0; transition: opacity 0.3s ease; }
        
        .search-container { position: relative; width: 220px; z-index: 100; }
        .search-box { background: rgba(0,0,0,0.7); border: 1px solid white; color: white !important; height: 35px; }
        .search-box:focus { background: rgba(0,0,0,0.9); box-shadow: none; border-color: white;}
        .search-box::placeholder { color: #ffffff !important; opacity: 1; }
        .search-results { position: absolute; top: 100%; left: 0; right: 0; background-color: #1a1a1a; border: 1px solid #333; z-index: 9999; display: none; max-height: 350px; overflow-y: auto; box-shadow: 0 5px 15px rgba(0,0,0,0.8); }
        .search-result-item { display: flex; align-items: center; padding: 10px; text-decoration: none; border-bottom: 1px solid #333; color: white; text-align: left; }
        .search-result-item:hover { background-color: #333; color: white; }
        .search-result-item img { width: 35px; height: 50px; object-fit: cover; margin-right: 12px; border-radius: 3px; }
        
        .video-wrapper { background: #000; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.8); border: 1px solid #333; width: 100%; max-width: 1000px; margin: 0 auto; aspect-ratio: 16/9; display: flex; justify-content: center; align-items: center; }
        .artplayer-app { width: 100%; height: 100%; }
        
        .artwork-wrapper { width: 100%; max-width: 350px; margin: 0 auto; border-radius: 10px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.9); }
        .artwork-wrapper img.artwork { width: 100%; height: auto; display: block; border-radius: 10px; }

        .age-rating { border: 1px solid rgba(255,255,255,0.4); padding: 2px 6px; font-size: 0.75rem; border-radius: 3px; letter-spacing: 0.5px; }

        .season-title { font-size: 1.2rem; font-weight: bold; color: #fff; margin-top: 30px; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 5px;}
        .eps-card { background-color: #2b2b2b; border-radius: 5px; text-decoration: none; color: white; display: flex; align-items: center; padding: 15px; margin-bottom: 10px; }
        .eps-card:hover { background-color: #333; }
        .eps-card.active { background-color: #404040; border-left: 4px solid #e50914; }
        .eps-number { font-size: 1.5rem; font-weight: bold; color: #888; min-width: 40px; text-align: center; }

        .row-title { font-size: 1.4rem; font-weight: bold; margin-bottom: 15px; color: #ffffff; border-left: 4px solid #e50914; padding-left: 12px; line-height: 1.2; }
        
        /* ======================================= */
        /* CSS WADAH & TOMBOL SCROLL LINGKARAN     */
        /* ======================================= */
        .row-container { position: relative; display: block; width: 100%; }

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

        .left-btn { left: -10px; }
        .right-btn { right: -10px; }
        /* ======================================= */

        .row-scroll { 
            display: flex; 
            flex-wrap: nowrap; 
            overflow-x: auto; 
            overflow-y: hidden; 
            padding-top: 15px; 
            padding-bottom: 20px; 
            margin-top: -15px; 
            scroll-behavior: smooth; 
        }
        .row-scroll::-webkit-scrollbar { display: none; }
        .film-card { flex: 0 0 auto; width: 140px; margin-right: 15px; border-radius: 5px; overflow: hidden; text-decoration: none; display: flex; flex-direction: column; }
        .film-card img { width: 100%; height: 200px; object-fit: cover; border-radius: 5px; transition: border 0.3s; }
        .film-card:hover { transform: scale(1.05); z-index: 10; }
        .film-card:hover img { border: 2px solid white; }
        
        .btn-telegram-play { background-color: #0088cc; color: white; border: none; }
        .btn-telegram-play:hover { background-color: #006699; color: white; }
        
        .btn-download-vid { background-color: #e50914; color: white; border: none; }
        .btn-download-vid:hover { background-color: #b20710; color: white; }

        .cast-scroll { display: flex; overflow-x: auto; gap: 15px; padding-bottom: 15px; scrollbar-width: none; scroll-behavior: smooth; }
        .cast-scroll::-webkit-scrollbar { display: none; }
        .cast-card { flex: 0 0 auto; width: 110px; text-align: center; cursor: pointer; transition: transform 0.2s; }
        .cast-card:hover { transform: scale(1.05); }
        .cast-img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid #333; box-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        .cast-name { font-size: 0.85rem; font-weight: bold; margin-top: 8px; color: #fff; line-height: 1.2; }

        .modal-content-dark { background-color: #1a1a1a; border: 1px solid #444; color: white; box-shadow: 0 10px 30px rgba(0,0,0,0.9); }
        .modal-header-dark { border-bottom: 1px solid #333; }
        .actor-photo { width: 100%; max-width: 220px; border-radius: 8px; object-fit: cover; box-shadow: 0 5px 15px rgba(0,0,0,0.8); }

        /* PERBAIKAN CSS SEARCH UNTUK VERSI MOBILE */
        @media (max-width: 768px) { 
            .search-container { display: none; } 
            .search-container.mobile-active {
                display: block !important; position: absolute; left: 45px; right: 40px; width: auto; top: 50%; transform: translateY(-50%); z-index: 1050;
            }
            .search-container.mobile-active .search-box { width: 100%; background: rgba(0,0,0,0.95); }
            .scroll-btn { display: none !important; } /* Hilangkan tombol scroll di HP */
        }
        @media (min-width: 768px) { .film-card { width: 180px; } .film-card img { height: 260px; } }
        @media (max-width: 400px) { .brand-center { font-size: 1.2rem !important; } }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-netflix py-0">
        <div class="container-fluid px-md-5 d-block position-relative">
            <div class="nav-top-bar d-flex justify-content-between align-items-center">
                <a href="index.php" class="text-white text-decoration-none fs-4" style="z-index: 10;"><i class="bi bi-arrow-left"></i></a>
                <a class="navbar-brand brand-center fw-bold fs-3 text-danger m-0" href="index.php" style="z-index: 5;">STREAMING</a>
                
                <div class="search-container">
                    <form method="GET" action="index.php" class="m-0">
                        <input type="text" name="search" id="searchInput" class="form-control search-box rounded-0" placeholder="Cari Judul..." autocomplete="off">
                    </form>
                    <div id="searchResults" class="search-results"></div>
                </div>
                
                <a href="javascript:void(0);" id="mobileSearchBtn" class="text-white text-decoration-none fs-5 d-md-none" style="z-index: 10;"><i class="bi bi-search"></i></a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5" style="max-width: 1100px;">
        
        <?php if (!empty($default_vid)): ?>
            <!-- Wadah ArtPlayer -->
            <div class="video-wrapper mb-4">
                <div class="artplayer-app" id="player"></div>
            </div>
        <?php else: ?>
            <div class="artwork-wrapper mb-5 mt-2">
                <img src="<?= htmlspecialchars($film['thumbnail_url']) ?>" alt="<?= htmlspecialchars($judul_tampil) ?>" class="artwork" loading="lazy" draggable="false">
            </div>
        <?php endif; ?>
        
        <div class="row mb-0">
            <div class="col-lg-12">
                <h2 class="fw-bold mb-2"><?= htmlspecialchars($judul_tampil) ?></h2>
                <div class="d-flex align-items-center mb-3">
                    <span style="color: #46d369; font-weight: bold;">Baru</span>
                    <span class="ms-3 text-white-50 fw-semibold">
                        <?= $film['tahun'] ?><?php if(!empty($asal_negara)): ?> &bull; <?= htmlspecialchars($asal_negara) ?><?php endif; ?>
                    </span>
                    <span class="badge bg-secondary text-light ms-3"><?= htmlspecialchars($film['kategori']) ?></span>
                    
                    <?php if(!empty($kualitas_badge)): ?>
                        <span class="age-rating text-white-50 fw-bold ms-2"><?= $kualitas_badge ?></span>
                    <?php endif; ?>
                </div>
                
                <p class="fs-6 text-light mb-2" style="line-height: 1.6;"><?= nl2br(htmlspecialchars($deskripsi_tampil)) ?></p>
                
                <!-- BUNGKUSAN ROW-CONTAINER UNTUK PEMERAN -->
                <?php if(count($aktor_array) > 0): ?>
                    <h5 class="fw-bold text-white mt-4 mb-3">Pemeran :</h5>
                    <div class="row-container">
                        <button class="scroll-btn left-btn"><i class="bi bi-chevron-left"></i></button>
                        
                        <div class="cast-scroll mb-2" id="castContainer">
                            <!-- Kartu aktor akan dimuat ke sini oleh JS -->
                        </div>
                        
                        <button class="scroll-btn right-btn"><i class="bi bi-chevron-right"></i></button>
                    </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2 mt-4 pt-1 mb-2">
                    <?php if(!empty($telegram_url)): ?>
                        <a href="<?= htmlspecialchars($telegram_url) ?>" class="btn btn-telegram-play fw-bold px-4 py-2" target="_blank">
                            <i class="bi bi-telegram me-2"></i> Play Via Telegram
                        </a>
                    <?php endif; ?>
                    
                    <a href="#" id="btnDownload" class="btn btn-download-vid fw-bold px-4 py-2" target="_blank" style="display: none;">
                        <i class="bi bi-cloud-arrow-down-fill me-2"></i> Download Video
                    </a>
                </div>
            </div>
        </div>

        <?php if($film['tipe'] == 'Series' && !empty($episodes_by_season)): ?>
            <div class="mt-4 mb-5">
                <h4 class="row-title border-0 ps-0 mb-0"><i class="bi bi-list-ol text-danger me-2"></i> Daftar Episode</h4>
                
                <div class="row">
                    <div class="col-12">
                        <?php 
                        $is_first_eps = true;
                        foreach($episodes_by_season as $season_num => $eps_list): 
                        ?>
                            <h5 class="season-title text-danger">Season <?= $season_num ?></h5>
                            
                            <?php foreach($eps_list as $eps): 
                                $is_active = ($eps_aktif == $eps['id']) || ($eps_aktif == 0 && $is_first_eps);
                                $is_first_eps = false;
                            ?>
                                <a href="play.php?id=<?= $id ?>&eps=<?= $eps['id'] ?>" class="eps-card <?= $is_active ? 'active' : '' ?>">
                                    <div class="eps-number"><?= $eps['eps_ke'] ?></div>
                                    <div class="ms-3">
                                        <h6 class="mb-0 fw-bold text-white">Episode <?= $eps['eps_ke'] ?> <?= $eps['judul_eps'] ? '- '.htmlspecialchars($eps['judul_eps']) : '' ?></h6>
                                    </div>
                                    <div class="ms-auto pe-3 text-white-50">
                                        <i class="bi <?= $is_active ? 'bi-play-circle-fill text-danger' : 'bi-play-circle' ?> fs-3"></i>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-5 pt-4 border-top border-secondary">
            <h4 class="row-title">Sedang Trend (Terbaru)</h4>
            <?php if(mysqli_num_rows($trending_query) > 0): ?>
                
                <!-- BUNGKUSAN ROW-CONTAINER UNTUK SEDANG TREND -->
                <div class="row-container">
                    <button class="scroll-btn left-btn"><i class="bi bi-chevron-left"></i></button>
                    
                    <div class="row-scroll mt-4">
                        <?php while($trend = mysqli_fetch_assoc($trending_query)): ?>
                            <a href="play.php?id=<?= $trend['id'] ?>" class="film-card" title="<?= htmlspecialchars($trend['judul']) ?>">
                                <img src="<?= htmlspecialchars($trend['thumbnail_url']) ?>" alt="<?= htmlspecialchars($trend['judul']) ?>" loading="lazy" draggable="false">
                                <div class="mt-2 text-start">
                                    <div class="text-white fw-bold text-truncate" style="font-size: 0.95rem; line-height: 1.2;"><?= htmlspecialchars($trend['judul']) ?></div>
                                    <div class="text-white-50 mt-1" style="font-size: 0.85rem;">
                                        <?= $trend['tahun'] ?><?php if(!empty($trend['asal_negara'])): ?> &bull; <?= htmlspecialchars($trend['asal_negara']) ?><?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                    
                    <button class="scroll-btn right-btn"><i class="bi bi-chevron-right"></i></button>
                </div>
            <?php else: ?>
                <p class="text-white-50 mt-3 ms-2">Belum ada film terbaru lainnya yang tersedia.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL POPUP INFO PEMERAN -->
    <div class="modal fade" id="actorModal" tabindex="-1" aria-labelledby="actorModalLabel" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-dark">
                <div class="modal-header modal-header-dark py-3">
                    <h5 class="modal-title fw-bold text-danger" id="actorModalLabel">Info Pemeran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4" id="actorModalBody">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT WAJIB BOOTSTRAP -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const tmdbApiKey = '3a9352e90da3de0d31bf12c4c5e6b2e8'; // API Key TMDB

        document.addEventListener('DOMContentLoaded', function() {
            
            // FUNGSI TOGGLE SEARCH BAR MOBILE
            const mobileSearchBtn = document.getElementById('mobileSearchBtn');
            if(mobileSearchBtn) {
                mobileSearchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const searchContainer = document.querySelector('.search-container');
                    const brandCenter = document.querySelector('.brand-center');
                    
                    searchContainer.classList.toggle('mobile-active');
                    if(searchContainer.classList.contains('mobile-active')) {
                        brandCenter.classList.add('d-none');
                        document.getElementById('searchInput').focus();
                        this.innerHTML = '<i class="bi bi-x-lg"></i>'; 
                    } else {
                        brandCenter.classList.remove('d-none');
                        this.innerHTML = '<i class="bi bi-search"></i>'; 
                    }
                });
            }

            // GENERATE KARTU PEMERAN
            const actors = <?= json_encode($aktor_array) ?>;
            const castContainer = document.getElementById('castContainer');
            
            if(castContainer && actors.length > 0) {
                actors.forEach(actorName => {
                    fetch(`https://api.themoviedb.org/3/search/person?api_key=${tmdbApiKey}&query=${encodeURIComponent(actorName)}`)
                    .then(res => res.json())
                    .then(data => {
                        let fallbackImg = `https://ui-avatars.com/api/?name=${encodeURIComponent(actorName)}&background=2b2b2b&color=ffffff&size=225`;
                        let imgSrc = fallbackImg;
                        let personId = null;

                        if(data.results && data.results.length > 0) {
                            const person = data.results[0];
                            personId = person.id;
                            if(person.profile_path) {
                                imgSrc = `https://image.tmdb.org/t/p/w185${person.profile_path}`;
                            }
                        }

                        const card = document.createElement('div');
                        card.className = 'cast-card';
                        card.innerHTML = `
                            <img src="${imgSrc}" class="cast-img" alt="${actorName}" loading="lazy" draggable="false">
                            <div class="cast-name">${actorName}</div>
                        `;

                        card.addEventListener('click', function() {
                            openActorModal(actorName, personId, fallbackImg); 
                        });

                        castContainer.appendChild(card);
                    })
                    .catch(err => console.error('Gagal mengambil data aktor:', actorName));
                });
            }

            // FUNGSI GOOGLE TRANSLATE
            async function translateTextToId(text) {
                if(!text) return "";
                try {
                    const url = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl=id&dt=t&q=${encodeURIComponent(text)}`;
                    const res = await fetch(url);
                    const json = await res.json();
                    let translated = "";
                    json[0].forEach(chunk => {
                        if(chunk[0]) translated += chunk[0];
                    });
                    return translated;
                } catch (e) {
                    return text; 
                }
            }

            // FUNGSI MEMBUKA MODAL INFO PEMERAN
            function openActorModal(actorName, personId, fallbackImg) {
                const actorModalLabel = document.getElementById('actorModalLabel');
                const actorModalBody = document.getElementById('actorModalBody');
                
                actorModalLabel.innerText = actorName;
                actorModalBody.innerHTML = '<div class="spinner-border text-danger mb-3" role="status"></div><p class="text-white-50">Menyiapkan profil & Menerjemahkan biografi...</p>';
                
                const modalInstance = new bootstrap.Modal(document.getElementById('actorModal'));
                modalInstance.show();

                if(!personId) {
                    actorModalBody.innerHTML = `
                        <img src="${fallbackImg}" class="actor-photo mb-3 shadow-lg" alt="${actorName}">
                        <h4 class="fw-bold text-white mb-0">${actorName}</h4>
                        <p class="mt-4 text-white-50 small">Pemeran ini tidak ditemukan di database TMDB.</p>
                    `;
                    return;
                }

                fetch(`https://api.themoviedb.org/3/person/${personId}?api_key=${tmdbApiKey}&language=id-ID`)
                .then(res => res.json())
                .then(person => {
                    if(!person.biography || person.biography.trim() === '') {
                        return fetch(`https://api.themoviedb.org/3/person/${personId}?api_key=${tmdbApiKey}`)
                               .then(r => r.json());
                    }
                    return person;
                })
                .then(async (person) => {
                    let imgSrc = person.profile_path ? `https://image.tmdb.org/t/p/w300${person.profile_path}` : fallbackImg;
                    let bio = person.biography || '';
                    let placeOfBirth = person.place_of_birth || '-';
                    let birthday = person.birthday || '-';

                    if(bio.trim() !== '') {
                        bio = await translateTextToId(bio);
                    } else {
                        bio = 'Biografi pemeran belum tersedia di TMDB.';
                    }

                    actorModalBody.innerHTML = `
                        <img src="${imgSrc}" class="actor-photo mb-3 shadow-lg" alt="${person.name}">
                        <h4 class="fw-bold text-white mb-0">${person.name}</h4>
                        <div class="text-start mt-4 border-top border-secondary pt-3">
                            <p class="mb-1 text-white-50 small"><strong class="text-white">Lahir:</strong> ${birthday}</p>
                            <p class="mb-3 text-white-50 small"><strong class="text-white">Tempat Lahir:</strong> ${placeOfBirth}</p>
                            <p class="small text-light" style="line-height: 1.7; text-align: justify;">${bio}</p>
                        </div>
                    `;
                })
                .catch(err => {
                    actorModalBody.innerHTML = `<p class="mt-4 text-white-50 fw-bold">Gagal mengambil informasi profil.</p>`;
                });
            }

            // =========================================================================
            // FUNGSI KLIK TOMBOL GESER (KIRI DAN KANAN)
            // =========================================================================
            const rowContainers = document.querySelectorAll('.row-container');
            rowContainers.forEach(container => {
                // Deteksi apakah bungkusannya adalah area pemeran (cast-scroll) atau film (row-scroll)
                const scrollArea = container.querySelector('.row-scroll') || container.querySelector('.cast-scroll');
                const leftBtn = container.querySelector('.left-btn');
                const rightBtn = container.querySelector('.right-btn');

                if(scrollArea && leftBtn && rightBtn) {
                    leftBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        scrollArea.scrollBy({ left: -(scrollArea.clientWidth * 0.75), behavior: 'smooth' });
                    });

                    rightBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        scrollArea.scrollBy({ left: (scrollArea.clientWidth * 0.75), behavior: 'smooth' });
                    });
                }
            });

            // =========================================================================
            // SISTEM UPDATE DOWNLOAD LINK TAHAN BANTING (Anti-Gagal)
            // =========================================================================
            function getSafelinkUrl(originalUrl) {
                const domains = ["drive.odzayrose.my.id"];
                const needsSafelink = domains.some(domain => originalUrl.includes(domain));
                if(needsSafelink) {
                    return 'https://sfl.gl/st?api=91506c20055849a0d938a26cd9330c4afff233ca&url=' + encodeURIComponent(originalUrl);
                }
                return originalUrl;
            }

            function updateDownloadLink(currentUrl) {
                const btn = document.getElementById('btnDownload');
                if(!btn || !currentUrl) return;
                
                const qualities = <?= $qualities_json ?>;
                const activeQuality = qualities.find(q => currentUrl === q.url || currentUrl.includes(q.url) || q.url.includes(currentUrl));
                
                if(activeQuality && activeQuality.download && activeQuality.download.trim() !== '') {
                    btn.href = getSafelinkUrl(activeQuality.download);
                    btn.innerHTML = `<i class="bi bi-cloud-arrow-down-fill me-2"></i> Download Video (${activeQuality.html})`;
                    btn.style.display = 'inline-block';
                } else {
                    btn.style.display = 'none';
                }
            }

            // INIT ARTPLAYER
            <?php if (!empty($default_vid)): ?>
            var art = new Artplayer({
                container: '.artplayer-app',
                url: '<?= htmlspecialchars($default_vid) ?>',
                poster: '<?= htmlspecialchars($film['thumbnail_url']) ?>',
                title: '<?= htmlspecialchars($judul_tampil) ?>',
                volume: 1,
                isLive: false,
                muted: false,
                autoplay: false,
                pip: true,               
                autoSize: true,          
                autoMini: true,
                screenshot: false,
                setting: true,           
                loop: false,
                flip: true,
                playbackRate: true,      
                aspectRatio: true,       
                fullscreen: true,
                fullscreenWeb: true,
                miniProgressBar: true,   
                mutex: true,
                backdrop: true,
                playsInline: true,
                autoPlayback: true,      
                airplay: true,
                theme: '#e50914',        
                quality: <?= $qualities_json ?>, 
                lang: navigator.language.toLowerCase(),
                icons: {
                    state: '<i class="bi bi-play-circle-fill" style="font-size: 4rem; color: rgba(255,255,255,0.8);"></i>',
                },
            });

            // 1. Cek dari event built-in ArtPlayer
            art.on('ready', () => { updateDownloadLink(art.url); });
            art.on('restart', () => { updateDownloadLink(art.url); });
            art.on('video:src', (url) => { updateDownloadLink(url); });

            // 2. SISTEM POLLING (Garansi 100% mendeteksi perubahan resolusi)
            let lastKnownUrl = art.url;
            setInterval(() => {
                if(art && art.url !== lastKnownUrl) {
                    lastKnownUrl = art.url;
                    updateDownloadLink(art.url);
                }
            }, 500);

            <?php endif; ?>

            // LIVE SEARCH
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');
            if(searchInput) {
                searchInput.addEventListener('keyup', function() {
                    let query = this.value.trim();
                    if(query.length > 0) {
                        fetch('ajax_search.php?q=' + encodeURIComponent(query))
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
                    if(!searchInput.contains(e.target) && !searchResults.contains(e.target) && e.target !== mobileSearchBtn && !mobileSearchBtn.contains(e.target)) {
                        searchResults.style.display = 'none';
                    }
                });
            }
        });

        // PENCEGAHAN KLIK KANAN & DRAG
        document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
        document.addEventListener('dragstart', function(e) { if(e.target.tagName === 'IMG') e.preventDefault(); });
        document.addEventListener('selectstart', function(e) { 
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') { e.preventDefault(); }
        });
    </script>

    <!-- SCRIPT SAFELINKU INTEGRATION -->
    <script type="text/javascript">
        var go_url = 'https://sfl.gl/';
        var api = '91506c20055849a0d938a26cd9330c4afff233ca';
        var shorten_includ = ["drive.odzayrose.my.id"];
    </script>
    <script src="//safelinku.com/js/web-script.js"></script>
</body>
</html>