<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
include '../koneksi.php';
$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM films WHERE id=$id");
$data = mysqli_fetch_assoc($query);

if(isset($_POST['update'])) {
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
    
    $sql = "UPDATE films SET 
            judul='$judul', deskripsi='$deskripsi', tahun='$tahun', kategori='$kategori', asal_negara='$asal_negara', 
            tipe='$tipe', thumbnail_url='$thumbnail_url', pemeran='$pemeran', 
            video_url_360='$v360', video_url_480='$v480', video_url_720='$v720', video_url_1080='$v1080', 
            download_url_360='$d360', download_url_480='$d480', download_url_720='$d720', download_url_1080='$d1080',
            telegram_url='$telegram_url' 
            WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="https://apps.odzayrose.my.id/favicon/streaming/favicon-32x32.png">
    <title>Edit Film Streaming</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #141414; color: white; }
        .navbar-admin { background-color: #000; border-bottom: 1px solid #333; }
        .card-dark { background-color: #1f1f1f; border: 1px solid #333; }
        .form-control, .form-select { background-color: #333; border: 1px solid #444; color: white; }
        .form-control:focus, .form-select:focus { background-color: #444; color: white; border-color: #e50914; box-shadow: none; }
        .btn-netflix { background-color: #e50914; color: white; border: none; }
        #posterPreview { max-width: 100%; height: auto; border-radius: 5px; margin-top: 10px; max-height: 250px; object-fit: contain;}
        .spin { display: inline-block; animation: spin 1s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-admin mb-4 py-3 sticky-top">
        <div class="container-fluid px-md-4">
            <a class="navbar-brand fw-bold text-danger" href="index.php">STREAMING</a>
            <div class="d-flex gap-2">
                <a href="../index.php" class="btn btn-outline-light btn-sm" target="_blank">Lihat Web</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-3 px-md-4 mt-5 mb-5">
        <div class="card card-dark shadow-lg border-0 mx-auto" style="max-width: 600px;">
            <div class="card-body p-4">
                <h4 class="mb-4 text-center text-white-50">Edit Data Film</h4>
                <form method="POST" action="">

                    <div class="mb-4 p-3 border border-secondary rounded" style="background-color: #1a1a1a;">
                        <label class="form-label small fw-bold text-info">Timpa data dengan Auto Isi TMDB?</label>
                        <div class="input-group">
                            <input type="text" id="judulCari" class="form-control" placeholder="Ketik judul..." value="<?= htmlspecialchars($data['judul']) ?>">
                            <button class="btn btn-info text-dark fw-bold" type="button" id="btnCariApi"><i class="bi bi-search"></i> Cari Info</button>
                        </div>
                        <div id="loadingInfo" class="form-text text-warning d-none mt-2 fw-semibold"><i class="bi bi-arrow-repeat spin"></i> Menghubungi server TMDB...</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-white-50">Judul Film</label>
                        <input type="text" id="judulFilm" name="judul" class="form-control" value="<?= htmlspecialchars($data['judul']) ?>" required>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-white-50">Tahun</label>
                            <input type="number" id="tahunFilm" name="tahun" class="form-control" value="<?= $data['tahun'] ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-white-50">Tipe</label>
                            <select name="tipe" id="tipeFilm" class="form-select">
                                <option value="Movie" <?= $data['tipe'] == 'Movie' ? 'selected' : '' ?>>Movie</option>
                                <option value="Series" <?= $data['tipe'] == 'Series' ? 'selected' : '' ?>>Series</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-white-50">Kategori (Genre)</label>
                            <input type="text" id="kategoriFilm" name="kategori" class="form-control" value="<?= htmlspecialchars($data['kategori']) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-white-50">Asal Negara</label>
                            <input type="text" id="negaraFilm" name="asal_negara" class="form-control" value="<?= htmlspecialchars($data['asal_negara'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-white-50">URL Thumbnail</label>
                        <input type="url" id="posterUrl" name="thumbnail_url" class="form-control" value="<?= htmlspecialchars($data['thumbnail_url']) ?>">
                        <div class="text-center">
                            <img id="posterPreview" src="<?= htmlspecialchars($data['thumbnail_url']) ?>" style="<?= !empty($data['thumbnail_url']) ? 'display: block;' : 'display: none;' ?>" alt="Poster Preview">
                        </div>
                    </div>

                    <div class="mb-3 p-3 border border-secondary rounded bg-dark">
                        <label class="form-label small fw-bold text-white mb-3">Link Video Resolusi</label>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-secondary text-white border-0" style="width: 70px; justify-content: center;">360p</span>
                            <input type="url" name="video_url_360" class="form-control border-secondary" value="<?= htmlspecialchars($data['video_url_360'] ?? '') ?>">
                        </div>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-secondary text-white border-0" style="width: 70px; justify-content: center;">480p</span>
                            <input type="url" name="video_url_480" class="form-control border-secondary" value="<?= htmlspecialchars($data['video_url_480'] ?? '') ?>">
                        </div>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-secondary text-white border-0" style="width: 70px; justify-content: center;">720p</span>
                            <input type="url" name="video_url_720" class="form-control border-secondary" value="<?= htmlspecialchars($data['video_url_720'] ?? '') ?>">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-secondary text-white border-0" style="width: 70px; justify-content: center;">1080p</span>
                            <input type="url" name="video_url_1080" class="form-control border-secondary" value="<?= htmlspecialchars($data['video_url_1080'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- INPUT LINK DOWNLOAD BARU -->
                    <div class="mb-3 p-3 border border-secondary rounded bg-dark">
                        <label class="form-label small fw-bold text-white mb-3">Link Download Resolusi</label>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-danger text-white border-0" style="width: 70px; justify-content: center;">360p</span>
                            <input type="url" name="download_url_360" class="form-control border-secondary" value="<?= htmlspecialchars($data['download_url_360'] ?? '') ?>">
                        </div>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-danger text-white border-0" style="width: 70px; justify-content: center;">480p</span>
                            <input type="url" name="download_url_480" class="form-control border-secondary" value="<?= htmlspecialchars($data['download_url_480'] ?? '') ?>">
                        </div>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-danger text-white border-0" style="width: 70px; justify-content: center;">720p</span>
                            <input type="url" name="download_url_720" class="form-control border-secondary" value="<?= htmlspecialchars($data['download_url_720'] ?? '') ?>">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-danger text-white border-0" style="width: 70px; justify-content: center;">1080p</span>
                            <input type="url" name="download_url_1080" class="form-control border-secondary" value="<?= htmlspecialchars($data['download_url_1080'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-white-50">Link Play via Telegram</label>
                        <input type="url" name="telegram_url" class="form-control" value="<?= htmlspecialchars($data['telegram_url'] ?? '') ?>" placeholder="https://t.me/...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-white-50">Pemeran / Cast</label>
                        <textarea id="pemeranFilm" name="pemeran" class="form-control" rows="2"><?= htmlspecialchars($data['pemeran'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-white-50">Sinopsis</label>
                        <textarea id="sinopsisFilm" name="deskripsi" class="form-control" rows="6"><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="update" class="btn btn-netflix fw-bold flex-grow-1">Simpan Perubahan</button>
                        <a href="index.php" class="btn btn-outline-light fw-bold px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                    if(detail.overview) document.getElementById('sinopsisFilm').value = detail.overview;
                    if (detail.credits && detail.credits.cast && detail.credits.cast.length > 0) {
                        document.getElementById('pemeranFilm').value = detail.credits.cast.slice(0, 8).map(actor => actor.name).join(', ');
                    }
                    if(detail.poster_path) {
                        const posterFullUrl = 'https://image.tmdb.org/t/p/w500' + detail.poster_path;
                        document.getElementById('posterUrl').value = posterFullUrl;
                        document.getElementById('posterPreview').src = posterFullUrl;
                        document.getElementById('posterPreview').style.display = 'block';
                    }
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