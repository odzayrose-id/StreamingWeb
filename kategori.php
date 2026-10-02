<?php
include 'koneksi.php';

$tipe = isset($_GET['tipe']) ? $_GET['tipe'] : 'Movie';
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM films WHERE tipe='$tipe'");
$total_data = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_data / $limit);

$query = mysqli_query($conn, "SELECT * FROM films WHERE tipe='$tipe' ORDER BY id DESC LIMIT $start, $limit");
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
    <title>Daftar <?= htmlspecialchars($tipe) ?> - NontonKuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #141414; color: white; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .navbar-netflix { background-color: #000; border-bottom: 1px solid #333; }
        .row-title { font-size: 1.6rem; font-weight: bold; margin-bottom: 20px; color: #ffffff; border-left: 4px solid #e50914; padding-left: 12px; }
        .film-grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .film-card { width: 140px; text-decoration: none; display: flex; flex-direction: column; transition: transform 0.3s ease; }
        .film-card img { width: 100%; height: 200px; object-fit: cover; border-radius: 5px; }
        .film-card:hover { transform: scale(1.05); }
        .film-card:hover img { border: 2px solid white; }
        @media (min-width: 768px) { .film-card { width: 180px; } .film-card img { height: 260px; } }
        .pagination .page-link { background-color: #222; border-color: #444; color: white; }
        .pagination .page-link:hover { background-color: #e50914; border-color: #e50914; }
        .pagination .active .page-link { background-color: #e50914; border-color: #e50914; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-netflix py-3">
        <div class="container-fluid px-md-5">
            <a class="navbar-brand fw-bold fs-3 text-danger" href="index.php">NONTONKUY</a>
            <div class="d-flex"><a href="index.php" class="btn btn-outline-light btn-sm"><i class="bi bi-house"></i> Beranda</a></div>
        </div>
    </nav>

    <div class="container py-5" style="max-width: 1200px;">
        <h2 class="row-title text-uppercase">Semua <?= htmlspecialchars($tipe) ?></h2>
        <?php if(mysqli_num_rows($query) > 0): ?>
            <div class="film-grid mt-4">
                <?php while($film = mysqli_fetch_assoc($query)): ?>
                    <a href="play.php?id=<?= $film['id'] ?>" class="film-card">
                        <img src="<?= htmlspecialchars($film['thumbnail_url']) ?>" alt="<?= htmlspecialchars($film['judul']) ?>" loading="lazy">
                        <div class="mt-2">
                            <div class="text-white fw-bold text-truncate" style="font-size: 0.95rem;"><?= htmlspecialchars($film['judul']) ?></div>
                            <div class="text-white-50 mt-1" style="font-size: 0.85rem;"><?= $film['tahun'] ?> • <?= htmlspecialchars($film['asal_negara'] ?? 'Lokal') ?></div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>

            <?php if($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="kategori.php?tipe=<?= $tipe ?>&page=<?= $page - 1 ?>">Sebelumnya</a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="kategori.php?tipe=<?= $tipe ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="kategori.php?tipe=<?= $tipe ?>&page=<?= $page + 1 ?>">Selanjutnya</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center text-secondary mt-5"><h5>Belum ada data untuk kategori ini.</h5></div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
