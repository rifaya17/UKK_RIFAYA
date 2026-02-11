<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();

header('Content-Type: application/json; charset=utf-8');

// Cek login admin HANYA untuk halaman admin
// Untuk halaman siswa, skip pengecekan ini
$is_admin_page = isset($_POST['admin_access']) && $_POST['admin_access'] === 'true';

if ($is_admin_page && !isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Koneksi Database
$conn = mysqli_connect("localhost", "root", "", "dbukk_rifaya");

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
    exit();
}

mysqli_set_charset($conn, 'utf8');

// Ambil id_pelaporan dari POST
$id_pelaporan = isset($_POST['id_pelaporan']) ? mysqli_real_escape_string($conn, $_POST['id_pelaporan']) : '';

if (empty($id_pelaporan)) {
    echo json_encode(['success' => false, 'message' => 'ID Pelaporan tidak valid']);
    exit();
}

// Query dengan nama kolom yang benar: id_histori
$query = "SELECT id_histori, id_pelaporan, jenis_perubahan, nilai_lama, nilai_baru, waktu_perubahan 
          FROM histori_aspirasi 
          WHERE id_pelaporan = '$id_pelaporan' 
          ORDER BY waktu_perubahan DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query gagal: ' . mysqli_error($conn)]);
    exit();
}

$histori = [];
while ($row = mysqli_fetch_assoc($result)) {
    $histori[] = [
        'id' => $row['id_histori'],
        'id_pelaporan' => $row['id_pelaporan'],
        'jenis_perubahan' => $row['jenis_perubahan'],
        'nilai_lama' => $row['nilai_lama'],
        'nilai_baru' => $row['nilai_baru'],
        'waktu_perubahan' => $row['waktu_perubahan']
    ];
}

if (count($histori) > 0) {
    echo json_encode(['success' => true, 'histori' => $histori]);
} else {
    echo json_encode(['success' => false, 'message' => 'Belum ada histori', 'histori' => []]);
}

mysqli_close($conn);
?>