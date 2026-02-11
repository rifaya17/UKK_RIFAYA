<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "dbukk_rifaya");

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
    exit();
}

if (isset($_POST['id_pelaporan'])) {
    $id_pelaporan = mysqli_real_escape_string($conn, $_POST['id_pelaporan']);
    
    // Ambil feedback lama
    $old_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT feedback FROM aspirasi WHERE id_pelaporan='$id_pelaporan'"));
    $old_feedback = $old_data['feedback'] ?? '';
    
    // Hapus feedback
    $update = mysqli_query($conn, "UPDATE aspirasi SET feedback='' WHERE id_pelaporan='$id_pelaporan'");
    
    if ($update) {
        // Simpan ke histori
        if (!empty($old_feedback)) {
            mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_lama, nilai_baru, waktu_perubahan) 
                               VALUES ('$id_pelaporan', 'feedback', '$old_feedback', NULL, NOW())");
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Feedback berhasil dihapus!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus feedback: ' . mysqli_error($conn)
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID tidak valid'
    ]);
}

mysqli_close($conn);
?>