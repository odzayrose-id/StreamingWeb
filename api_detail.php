<?php
include 'koneksi.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if(empty($id)) {
    echo json_encode(["status" => "error", "message" => "ID Film tidak ditemukan"]);
    exit;
}

$film_query = mysqli_query($conn, "SELECT * FROM films WHERE id = '$id'");
$film = mysqli_fetch_assoc($film_query);

if(!$film) {
    echo json_encode(["status" => "error", "message" => "Film tidak ditemukan"]);
    exit;
}

// Ambil data episode jika tipe film adalah Series
$episodes = [];
if($film['tipe'] == 'Series') {
    $eps_query = mysqli_query($conn, "SELECT * FROM episodes WHERE film_id = '$id' ORDER BY season ASC, eps_ke ASC");
    while($eps = mysqli_fetch_assoc($eps_query)) {
        $episodes[] = $eps;
    }
}

echo json_encode([
    "status" => "success",
    "film" => $film,
    "episodes" => $episodes
]);
?>