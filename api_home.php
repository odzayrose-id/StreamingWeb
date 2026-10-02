<?php
include 'koneksi.php';
header('Content-Type: application/json');

// Pastikan kolom 'kategori' ikut dipanggil
$query_str = "SELECT id, judul, thumbnail_url, tipe, tahun, asal_negara, kategori FROM films ORDER BY id DESC";
$result = mysqli_query($conn, $query_str);

$films_by_category = [];
while($row = mysqli_fetch_assoc($result)) {
    // Pisahkan multi-kategori (misal: "Aksi, Fantasi")
    $kategori_list = explode(',', $row['kategori']);
    
    foreach($kategori_list as $kat) {
        $kat = trim($kat);
        if(!empty($kat)) {
            $films_by_category[$kat][] = $row;
        }
    }
}
ksort($films_by_category); // Urutkan kategori sesuai alfabet (A-Z)

// Susun ulang untuk format JSON Android
$data_array = [];
foreach($films_by_category as $kategori => $films) {
    $data_array[] = [
        "kategori" => $kategori,
        "films" => $films
    ];
}

if(count($data_array) > 0) {
    echo json_encode(["status" => "success", "data" => $data_array]);
} else {
    echo json_encode(["status" => "error", "message" => "Tidak ada data film"]);
}
?>