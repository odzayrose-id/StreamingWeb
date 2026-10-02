<?php
include 'koneksi.php';

if(isset($_GET['q'])) {
    $q = mysqli_real_escape_string($conn, $_GET['q']);
    // Menambahkan kolom asal_negara dan tipe pada query pencarian
    $query = mysqli_query($conn, "SELECT id, judul, thumbnail_url, tahun, asal_negara, tipe FROM films WHERE judul LIKE '%$q%' LIMIT 5");
    
    if(mysqli_num_rows($query) > 0) {
        while($row = mysqli_fetch_assoc($query)) {
            echo '<a href="play.php?id='.$row['id'].'" class="search-result-item">';
            echo '<img src="'.htmlspecialchars($row['thumbnail_url']).'" alt="">';
            echo '<div>';
            echo '<div class="fw-bold text-white mb-1">'.htmlspecialchars($row['judul']).'</div>';
            
            // Merangkai teks: Tahun • Negara • Series/Movie
            echo '<small class="text-white-50">'.$row['tahun'];
            
            if(!empty($row['asal_negara'])) {
                echo ' &bull; ' . htmlspecialchars($row['asal_negara']);
            }
            if(!empty($row['tipe'])) {
                echo ' &bull; ' . htmlspecialchars($row['tipe']);
            }
            
            echo '</small>';
            echo '</div>';
            echo '</a>';
        }
    } else {
        echo '<div class="p-3 text-white-50 text-center small">Film tidak ditemukan</div>';
    }
}
?>
