<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
include '../koneksi.php';

if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM films WHERE id=$id");
    header("Location: index.php");
    exit;
}

if(isset($_POST['tambah'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $tahun = $_POST['tahun'];
    $kategori = $_POST['kategori'];
    $asal_negara = mysqli_real_escape_string($conn, $_POST['asal_negara']);
    $tipe = $_POST['tipe'];
    $thumbnail_url = $_POST['thumbnail_url'];
    $pemeran = mysqli_real_escape_string($conn, $_POST['pemeran'] ?? '');
    
    $v360 = $_POST['video_url_360'];
    $v480 = $_POST['video_url_480'];
    $v720 = $_POST['video_url_720'];
    $v1080 = $_POST['video_url_1080'];

    $d360 = $_POST['download_url_360'];
    $d480 = $_POST['download_url_480'];
    $d720 = $_POST['download_url_720'];
    $d1080 = $_POST['download_url_1080'];
    
    $telegram_url = $_POST['telegram_url'];
    
    $sql = "INSERT INTO films (judul, deskripsi, tahun, kategori, asal_negara, tipe, video_url_360, video_url_480, video_url_720, video_url_1080, download_url_360, download_url_480, download_url_720, download_url_1080, telegram_url, thumbnail_url, pemeran) 
            VALUES ('$judul', '$deskripsi', '$tahun', '$kategori', '$asal_negara', '$tipe', '$v360', '$v480', '$v720', '$v1080', '$d360', '$d480', '$d720', '$d1080', '$telegram_url', '$thumbnail_url', '$pemeran')";
    mysqli_query($conn, $sql);
    header("Location: index.php");
    exit;
}
$films = mysqli_query($conn, "SELECT * FROM films ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="https://apps.odzayrose.my.id/favicon/streaming/favicon-32x32.png">
    <title>Admin Panel - Streaming</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #141414; color: white; }
        .navbar-admin { background-color: #000; border-bottom: 1px solid #333; }
        .card-dark { background-color: #1f1f1f; border: 1px solid #333; }
        .card-header-dark { background-color: #2b2b2b; border-bottom: 1px solid #333; }
        .form-control, .form-select { background-color: #333; border: 1px solid #444; color: white; }
        .form-control:focus, .form-select:focus { background-color: #444; color: white; border-color: #e50914; box-shadow: none; }
        .table-dark-custom { --bs-table-bg: transparent; --bs-table-color: white; --bs-table-border-color: #444; }
        .btn-netflix { background-color: #e50914; color: white; border: none; }
        .btn-netflix:hover { background-color: #b20710; color: white; }
        .thumb-tabel { width: 45px; height: 65px; object-fit: cover; border-radius: 4px; }
        #posterPreview { max-width: 100%; height: auto; border-radius: 5px; display: none; margin-top: 10px; max-height: 250px; object-fit: contain;}
        .spin { display: inline-block; animation: spin 1s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-admin mb-4 py-3 sticky-top">
        <div class="container-fluid px-md-4">
            <a class="navbar-brand fw-bold text-danger" href="#">STREAMING</a>
            <div class="d-flex gap-2">
                <a href="../index.php" class="btn btn-outline-light btn-sm" target="_blank">Lihat Web</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-md-4 mb-5">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card card-dark shadow-sm">
                    <div class="card-header card-header-dark text-white fw-bold py-3">Tambah Film Baru</div>
                    <div class="card-body">
                        <form method="POST" action="">
                            
                            <div class="mb-3 p-3 border border-secondary rounded" style="background-color: #1a1a1a;">
                                <label class="form-label small fw-bold text-info">Auto Isi dari TMDB (IMDb Alternative)</label>
                                <div class="input-group">
                                    <input type="text" id="judulCari" class="form-control form-control-sm" placeholder="Ketik judul film/series...">
                                    <button class="btn btn-info btn-sm text-dark fw-bold" type="button" id="btnCariApi"><i class="bi bi-search"></i> Cari Info</button>
                                </div>
                                <div id="loadingInfo" class="form-text text-warning d-none mt-2 fw-semibold"><i class="bi bi-arrow-repeat spin"></i> Menghubungi server TMDB...</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-white-50">Judul Film</label>
                                <input type="text" id="judulFilm" name="judul" class="form-control form-control-sm" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-white-50">Tahun</label>
                                    <input type="number" id="tahunFilm" name="tahun" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-white-50">Tipe</label>
                                    <select name="tipe" id="tipeFilm" class="form-select form-select-sm" required>
                                        <option value="Movie">Movie</option>
                                        <option value="Series">Series</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-white-50">Kategori</label>
                                    <input type="text" id="kategoriFilm" name="kategori" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-white-50">Asal Negara</label>
                                    <input type="text" id="negaraFilm" name="asal_negara" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-white-50">URL Thumbnail (Poster)</label>
                                <input type="url" id="posterUrl" name="thumbnail_url" class="form-control form-control-sm">
                                <div class="text-center">
                                    <img id="posterPreview" src="" alt="Poster Preview">
                                </div>
                            </div>

                            <div class="mb-3 p-3 border border-secondary rounded bg-dark">
                                <label class="form-label small fw-bold text-white mb-3">Link Video Resolusi</label>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-secondary text-white border-0" style="width: 70px; justify-content: center;">360p</span>
                                    <input type="url" name="video_url_360" class="form-control border-secondary">
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-secondary text-white border-0" style="width: 70px; justify-content: center;">480p</span>
                                    <input type="url" name="video_url_480" class="form-control border-secondary">
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-secondary text-white border-0" style="width: 70px; justify-content: center;">720p</span>
                                    <input type="url" name="video_url_720" class="form-control border-secondary">
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-secondary text-white border-0" style="width: 70px; justify-content: center;">1080p</span>
                                    <input type="url" name="video_url_1080" class="form-control border-secondary">
                                </div>
                            </div>

                            <!-- INPUT LINK DOWNLOAD BARU -->
                            <div class="mb-3 p-3 border border-secondary rounded bg-dark">
                                <label class="form-label small fw-bold text-white mb-3">Link Download Resolusi</label>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-danger text-white border-0" style="width: 70px; justify-content: center;">360p</span>
                                    <input type="url" name="download_url_360" class="form-control border-secondary">
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-danger text-white border-0" style="width: 70px; justify-content: center;">480p</span>
                                    <input type="url" name="download_url_480" class="form-control border-secondary">
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-danger text-white border-0" style="width: 70px; justify-content: center;">720p</span>
                                    <input type="url" name="download_url_720" class="form-control border-secondary">
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-danger text-white border-0" style="width: 70px; justify-content: center;">1080p</span>
                                    <input type="url" name="download_url_1080" class="form-control border-secondary">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-white-50">Link Play via Telegram</label>
                                <input type="url" name="telegram_url" class="form-control form-control-sm" placeholder="https://t.me/...">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-white-50">Pemeran / Cast</label>
                                <textarea id="pemeranFilm" name="pemeran" class="form-control form-control-sm" rows="2" placeholder="Nama aktor, pisahkan dengan koma..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-white-50">Sinopsis</label>
                                <textarea id="sinopsisFilm" name="deskripsi" class="form-control form-control-sm" rows="5"></textarea>
                            </div>
                            
                            <button type="submit" name="tambah" class="btn btn-netflix w-100 fw-bold">Simpan Film</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card card-dark shadow-sm">
                    <div class="card-header card-header-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-film me-2 text-danger"></i> Daftar Katalog</div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark-custom table-hover align-middle mb-0 m-0" style="min-width: 600px;">
                                <thead style="background-color: #222;">
                                    <tr>
                                        <th class="ps-3 text-white-50">Info Film</th>
                                        <th class="text-white-50 text-center">Tipe</th>
                                        <th class="pe-3 text-white-50 text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($films)): ?>
                                    <tr>
                                        <td class="ps-3 py-3 d-flex align-items-center border-bottom border-secondary">
                                            <img src="<?= htmlspecialchars($row['thumbnail_url']) ?>" class="thumb-tabel me-3">
                                            <div>
                                                <div class="fw-bold fs-6 text-white text-truncate" style="max-width: 250px;"><?= htmlspecialchars($row['judul']) ?></div>
                                                <small class="text-white-50"><?= $row['tahun'] ?> | <?= htmlspecialchars($row['kategori']) ?> | <?= htmlspecialchars($row['asal_negara']) ?></small>
                                            </div>
                                        </td>
                                        <td class="text-center border-bottom border-secondary">
                                            <span class="badge <?= $row['tipe'] == 'Series' ? 'bg-danger' : 'bg-secondary' ?>"><?= $row['tipe'] ?></span>
                                        </td>
                                        <td class="pe-3 text-end border-bottom border-secondary">
                                            <div class="btn-group shadow-sm">
                                                <?php if($row['tipe'] == 'Series'): ?>
                                                    <a href="kelola_episode.php?film_id=<?= $row['id'] ?>" class="btn btn-outline-info btn-sm"><i class="bi bi-list-ol"></i></a>
                                                <?php endif; ?>
                                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-outline-light btn-sm"><i class="bi bi-pencil-square"></i></a>
                                                <a href="index.php?hapus=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Hapus film ini?')"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('btnCariApi').addEventListener('click', function() {
            const judulKetik = document.getElementById('judulCari').value;
            const loading = document.getElementById('loadingInfo');
            const apiKey = '3a9352e90da3de0d31bf12c4c5e6b2e8'; 
            
            if(judulKetik.trim() === '') { alert('Silakan ketik judul film terlebih dahulu.'); return; }
            loading.classList.remove('d-none');
            
            fetch(`https://api.themoviedb.org/3/search/multi?api_key=${apiKey}&language=id-ID&query=${encodeURIComponent(judulKetik)}`)
                .then(response => response.json())
                .then(data => {
                    if(data.results && data.results.length > 0) {
                        const result = data.results[0];
                        const mediaType = result.media_type === 'tv' ? 'tv' : 'movie';
                        return fetch(`https://api.themoviedb.org/3/${mediaType}/${result.id}?api_key=${apiKey}&language=id-ID&append_to_response=credits`);
                    } else { throw new Error('Not Found'); }
                })
                .then(response => response.json())
                .then(detail => {
                    loading.classList.add('d-none');
                    document.getElementById('judulFilm').value = detail.title || detail.name || judulKetik;
                    let date = detail.release_date || detail.first_air_date || '';
                    document.getElementById('tahunFilm').value = date ? date.substring(0,4) : '';
                    document.getElementById('tipeFilm').value = detail.name ? 'Series' : 'Movie';
                    if(detail.genres && detail.genres.length > 0) document.getElementById('kategoriFilm').value = detail.genres.map(g => g.name).join(', ');
                    if(detail.production_countries && detail.production_countries.length > 0) document.getElementById('negaraFilm').value = detail.production_countries[0].name;
                    else if (detail.origin_country && detail.origin_country.length > 0) document.getElementById('negaraFilm').value = detail.origin_country[0];
                    document.getElementById('sinopsisFilm').value = detail.overview || '';
                    if (detail.credits && detail.credits.cast && detail.credits.cast.length > 0) {
                        document.getElementById('pemeranFilm').value = detail.credits.cast.slice(0, 8).map(actor => actor.name).join(', ');
                    } else { document.getElementById('pemeranFilm').value = ''; }
                    if(detail.poster_path) {
                        const posterFullUrl = 'https://image.tmdb.org/t/p/w500' + detail.poster_path;
                        document.getElementById('posterUrl').value = posterFullUrl;
                        document.getElementById('posterPreview').src = posterFullUrl;
                        document.getElementById('posterPreview').style.display = 'block';
                    } else { document.getElementById('posterPreview').style.display = 'none'; }
                })
                .catch(error => {
                    loading.classList.add('d-none');
                    if(error.message === 'Not Found') alert('Data film tidak ditemukan.');
                    else alert('Gagal menghubungi server API.');
                });
        });

        document.getElementById('posterUrl').addEventListener('input', function() {
            const preview = document.getElementById('posterPreview');
            if(this.value.trim() !== '') {
                preview.src = this.value;
                preview.style.display = 'block';
            } else { preview.style.display = 'none'; }
        });
    </script>
</body>
</html>