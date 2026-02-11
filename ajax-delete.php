<?php
session_start();

// Cek login admin
if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Koneksi Database
$conn = mysqli_connect("localhost", "root", "", "dbukk_rifaya");

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
    exit();
}

// Proses hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pelaporan'])) {
    $id_pelaporan = mysqli_real_escape_string($conn, $_POST['id_pelaporan']);
    
    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // Hapus dari tabel aspirasi terlebih dahulu (foreign key)
        $delete_aspirasi = mysqli_query($conn, "DELETE FROM aspirasi WHERE id_pelaporan='$id_pelaporan'");
        
        // Hapus dari tabel input_aspirasi
        $delete_input = mysqli_query($conn, "DELETE FROM input_aspirasi WHERE id_pelaporan='$id_pelaporan'");
        
        if ($delete_input) {
            // Commit transaksi
            mysqli_commit($conn);
            echo json_encode([
                'success' => true, 
                'message' => 'Aspirasi berhasil dihapus!'
            ]);
        } else {
            // Rollback jika gagal
            mysqli_rollback($conn);
            echo json_encode([
                'success' => false, 
                'message' => 'Gagal menghapus aspirasi: ' . mysqli_error($conn)
            ]);
        }
    } catch (Exception $e) {
        // Rollback jika terjadi error
        mysqli_rollback($conn);
        echo json_encode([
            'success' => false, 
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

mysqli_close($conn);
?>