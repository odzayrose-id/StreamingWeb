<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
include '../koneksi.php';
if(!isset($_GET['film_id'])) { header("Location: index.php"); exit; }
$film_id = $_GET['film_id'];
$film = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM films WHERE id=$film_id"));

// Ambil data episode untuk edit jika parameter edit_id tersedia
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = mysqli_query($conn, "SELECT * FROM episodes WHERE id=$edit_id AND film_id=$film_id");
    $edit_data = mysqli_fetch_assoc($edit_query);
}

// Proses Hapus Episode
if(isset($_GET['hapus_eps'])) {
    $id_eps = $_GET['hapus_eps'];
    mysqli_query($conn, "DELETE FROM episodes WHERE id=$id_eps");
    header("Location: kelola_episode.php?film_id=$film_id"); exit;
}

// Proses Tambah / Update Episode
if(isset($_POST['simpan_eps'])) {
    $season = $_POST['season'];
    $eps_ke = $_POST['eps_ke'];
    $judul_eps = mysqli_real_escape_string($conn, $_POST['judul_eps']);
    $deskripsi_eps = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $v360 = $_POST['video_url_360'];
    $v480 = $_POST['video_url_480'];
    $v720 = $_POST['video_url_720'];
    $v1080 = $_POST['video_url_1080'];

    $d360 = $_POST['download_url_360'];
    $d480 = $_POST['download_url_480'];
    $d720 = $_POST['download_url_720'];
    $d1080 = $_POST['download_url_1080'];
    
    $telegram_url = $_POST['telegram_url'];

    if (isset($_POST['id_eps_edit']) && !empty($_POST['id_eps_edit'])) {
        // Mode Update / Edit
        $id_eps_edit = $_POST['id_eps_edit'];
        $sql = "UPDATE episodes SET 
                season='$season', eps_ke='$eps_ke', judul_eps='$judul_eps', deskripsi='$deskripsi_eps',
                video_url_360='$v360', video_url_480='$v480', 
                video_url_720='$v720', video_url_1080='$v1080', 
                download_url_360='$d360', download_url_480='$d480', 
                download_url_720='$d720', download_url_1080='$d1080',
                telegram_url='$telegram_url' 
                WHERE id=$id_eps_edit AND film_id=$film_id";
    } else {
        // Mode Tambah Baru
        $sql = "INSERT INTO episodes (film_id, season, eps_ke, judul_eps, deskripsi, video_url_360, video_url_480, video_url_720, video_url_1080, download_url_360, download_url_480, download_url_720, download_url_1080, telegram_url) 
                VALUES ('$film_id', '$season', '$eps_ke', '$judul_eps', '$deskripsi_eps', '$v360', '$v480', '$v720', '$v1080', '$d360', '$d480', '$d720', '$d1080', '$telegram_url')";
    }
    
    mysqli_query($conn, $sql);
    header("Location: kelola_episode.php?film_id=$film_id"); exit;
}

// Menampilkan episode diurutkan berdasarkan season lalu episode
$episodes = mysqli_query($conn, "SELECT * FROM episodes WHERE film_id=$film_id ORDER BY season ASC, eps_ke ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="https://apps.odzayrose.my.id/favicon/streaming/favicon-32x32.png">
    <title>Kelola Episode - Streaming</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #141414; color: white; }
        .navbar-admin { background-color: #000; border-bottom: 1px solid #333; }
        .card-dark { background-color: #1f1f1f; border: 1px solid #333; }
        .form-control { background-color: #333; border: 1px solid #444; color: white; }
        .form-control:focus { background-color: #444; border-color: #e50914; box-shadow: none; color: white;}
        .table-dark-custom { --bs-table-bg: transparent; --bs-table-color: white; --bs-table-border-color: #444; }
        .btn-netflix { background-color: #e50914; color: white; border: none; }
        .btn-netflix:hover { background-color: #b20710; color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-admin mb-4 py-3 sticky-top">
        <div class="container-fluid px-md-4">
            <a class="navbar-brand fw-bold text-danger" href="index.php">STREAMING</a>
            <div class="d-flex"><a href="../index.php" class="btn btn-outline-light btn-sm" target="_blank">Lihat Web</a></div>
        </div>
    </nav>

    <div class="container-fluid px-3 px-md-4 mt-4 mb-5">
        <a href="index.php" class="btn btn-outline-light btn-sm mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-4">Kelola Episode: <span class="text-danger"><?= htmlspecialchars($film['judul']) ?></span></h4>

        <div class="row flex-column-reverse flex-lg-row">
            <div class="col-lg-8 mt-4 mt-lg-0">
                <div class="card card-dark shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark-custom mb-0" style="min-width: 450px;">
                                <thead style="background-color: #222;">
                                    <tr>
                                        <th class="ps-3 text-nowrap">S & E</th>
                                        <th>Judul & Sinopsis</th>
                                        <th class="text-end pe-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($eps = mysqli_fetch_assoc($episodes)): ?>
                                    <tr style="<?= ($edit_data && $edit_data['id'] == $eps['id']) ? 'background-color: rgba(229, 9, 20, 0.1);' : '' ?>">
                                        <td class="ps-3 fw-bold fs-6 text-warning align-top pt-3 text-nowrap">
                                            S<?= $eps['season'] ?> E<?= $eps['eps_ke'] ?>
                                        </td>
                                        <td class="align-top pt-3">
                                            <strong class="text-light"><?= htmlspecialchars($eps['judul_eps']) ?: 'Episode '.$eps['eps_ke'] ?></strong>
                                            <?php if(!empty($eps['deskripsi'])): ?>
                                                <p class="text-white-50 small mt-1 mb-0"><?= nl2br(htmlspecialchars(substr($eps['deskripsi'], 0, 100))) ?>...</p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3 align-top pt-3">
                                            <div class="btn-group">
                                                <a href="kelola_episode.php?film_id=<?= $film_id ?>&edit_id=<?= $eps['id'] ?>" class="btn btn-outline-light btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                                <a href="kelola_episode.php?film_id=<?= $film_id ?>&hapus_eps=<?= $eps['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Hapus eps ini?')" title="Hapus"><i class="bi bi-trash"></i></a>
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

            <div class="col-lg-4">
                <div class="card card-dark shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold py-3">
                        <?php if($edit_data): ?>
                            <i class="bi bi-pencil-square text-warning me-1"></i> Edit S<?= $edit_data['season'] ?> E<?= $edit_data['eps_ke'] ?>
                        <?php else: ?>
                            <i class="bi bi-plus-circle text-danger me-1"></i> Tambah Episode
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="id_eps_edit" value="<?= $edit_data['id'] ?? '' ?>">

                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-white-50">Season Ke-</label>
                                    <input type="number" name="season" class="form-control form-control-sm" placeholder="Misal: 1" value="<?= $edit_data['season'] ?? '1' ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-white-50">Episode Ke-</label>
                                    <input type="number" name="eps_ke" class="form-control form-control-sm" placeholder="Misal: 1" value="<?= $edit_data['eps_ke'] ?? '' ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-white-50">Judul Episode</label>
                                <input type="text" name="judul_eps" class="form-control form-control-sm" placeholder="Judul Eps (Opsional)" value="<?= htmlspecialchars($edit_data['judul_eps'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-white-50">Sinopsis Episode</label>
                                <textarea name="deskripsi" class="form-control form-control-sm" rows="3" placeholder="Sinopsis khusus episode ini..."><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="p-3 border border-secondary rounded bg-dark mb-3">
                                <label class="small fw-bold text-white mb-3">Link Video Resolusi</label>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-secondary text-white border-0" style="width: 70px; justify-content: center;">360p</span>
                                    <input type="url" name="video_url_360" class="form-control border-secondary" value="<?= htmlspecialchars($edit_data['video_url_360'] ?? '') ?>">
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-secondary text-white border-0" style="width: 70px; justify-content: center;">480p</span>
                                    <input type="url" name="video_url_480" class="form-control border-secondary" value="<?= htmlspecialchars($edit_data['video_url_480'] ?? '') ?>">
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-secondary text-white border-0" style="width: 70px; justify-content: center;">720p</span>
                                    <input type="url" name="video_url_720" class="form-control border-secondary" value="<?= htmlspecialchars($edit_data['video_url_720'] ?? '') ?>">
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-secondary text-white border-0" style="width: 70px; justify-content: center;">1080p</span>
                                    <input type="url" name="video_url_1080" class="form-control border-secondary" value="<?= htmlspecialchars($edit_data['video_url_1080'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- INPUT LINK DOWNLOAD BARU -->
                            <div class="p-3 border border-secondary rounded bg-dark mb-3">
                                <label class="small fw-bold text-white mb-3">Link Download Resolusi</label>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-danger text-white border-0" style="width: 70px; justify-content: center;">360p</span>
                                    <input type="url" name="download_url_360" class="form-control border-secondary" value="<?= htmlspecialchars($edit_data['download_url_360'] ?? '') ?>">
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-danger text-white border-0" style="width: 70px; justify-content: center;">480p</span>
                                    <input type="url" name="download_url_480" class="form-control border-secondary" value="<?= htmlspecialchars($edit_data['download_url_480'] ?? '') ?>">
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-danger text-white border-0" style="width: 70px; justify-content: center;">720p</span>
                                    <input type="url" name="download_url_720" class="form-control border-secondary" value="<?= htmlspecialchars($edit_data['download_url_720'] ?? '') ?>">
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-danger text-white border-0" style="width: 70px; justify-content: center;">1080p</span>
                                    <input type="url" name="download_url_1080" class="form-control border-secondary" value="<?= htmlspecialchars($edit_data['download_url_1080'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-white-50">Link Play via Telegram</label>
                                <input type="url" name="telegram_url" class="form-control form-control-sm" placeholder="https://t.me/..." value="<?= htmlspecialchars($edit_data['telegram_url'] ?? '') ?>">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" name="simpan_eps" class="btn btn-netflix w-100 fw-bold">
                                    <?= $edit_data ? 'Simpan Perubahan' : 'Simpan Episode' ?>
                                </button>
                                <?php if($edit_data): ?>
                                    <a href="kelola_episode.php?film_id=<?= $film_id ?>" class="btn btn-outline-light fw-bold">Batal</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>