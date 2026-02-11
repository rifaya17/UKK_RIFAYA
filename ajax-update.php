<?php
session_start();
header('Content-Type: application/json');

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

if (isset($_POST['id_pelaporan']) && isset($_POST['feedback_status'])) {
    $id_pelaporan = mysqli_real_escape_string($conn, $_POST['id_pelaporan']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    $status = mysqli_real_escape_string($conn, $_POST['feedback_status']);
    
    // Ambil data dari input_aspirasi
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM input_aspirasi WHERE id_pelaporan='$id_pelaporan'"));
    
    if ($data) {
        // Cek apakah sudah ada di tabel aspirasi
        $cek = mysqli_query($conn, "SELECT * FROM aspirasi WHERE id_pelaporan='$id_pelaporan'");
        
        if (mysqli_num_rows($cek) > 0) {
            // Ambil data lama
            $old_data = mysqli_fetch_assoc($cek);
            $old_status = $old_data['status'];
            $old_feedback = $old_data['feedback'];
            
            // Update
            $update = mysqli_query($conn, "UPDATE aspirasi SET feedback='$feedback', status='$status' WHERE id_pelaporan='$id_pelaporan'");
            
            if ($update) {
                // Simpan histori status jika berubah
                if ($old_status != $status) {
                    mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_lama, nilai_baru, waktu_perubahan) 
                                       VALUES ('$id_pelaporan', 'status', '$old_status', '$status', NOW())");
                }
                
                // Simpan histori feedback jika berubah
                if ($old_feedback != $feedback) {
                    mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_lama, nilai_baru, waktu_perubahan) 
                                       VALUES ('$id_pelaporan', 'feedback', " . 
                                       ($old_feedback ? "'$old_feedback'" : "NULL") . ", " . 
                                       ($feedback ? "'$feedback'" : "NULL") . ", NOW())");
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Status dan feedback berhasil diupdate!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal update: ' . mysqli_error($conn)
                ]);
            }
        } else {
            // Insert baru
            $insert = mysqli_query($conn, "INSERT INTO aspirasi (id_pelaporan, id_kategori, status, feedback) 
                                          VALUES ('$id_pelaporan', '{$data['id_kategori']}', '$status', '$feedback')");
            
            if ($insert) {
                // Histori masuk
                mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_baru, waktu_perubahan) 
                                   VALUES ('$id_pelaporan', 'masuk', 'Aspirasi masuk ke sistem', '{$data['tanggal']}')");
                
                // Histori status
                mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_baru, waktu_perubahan) 
                                   VALUES ('$id_pelaporan', 'status', '$status', NOW())");
                
                // Histori feedback (jika ada)
                if (!empty($feedback)) {
                    mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_baru, waktu_perubahan) 
                                       VALUES ('$id_pelaporan', 'feedback', '$feedback', NOW())");
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Data berhasil disimpan!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal insert: ' . mysqli_error($conn)
                ]);
            }
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Data aspirasi tidak ditemukan'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap'
    ]);
}

mysqli_close($conn);
?>