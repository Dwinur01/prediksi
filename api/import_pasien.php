<?php
require_once '../config/koneksi.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt_p = $pdo->prepare("INSERT INTO pasien (nik, nama, jenis_kelamin, tanggal_lahir, alamat, no_hp) VALUES (?,?,?,?,?,?)");
    $stmt_ak = $pdo->prepare("INSERT INTO atribut_kesehatan 
        (id_pasien, tanggal_pemeriksaan, tekanan_sistolik, tekanan_diastolik, imt, merokok, konsumsi_alkohol, kurang_buah_sayur, diabetes, riwayat_hipertensi) 
        VALUES (?, CURDATE(), ?,?,?,?,?,?,?,?)");

    $success_count = 0;
    $errors = [];

    foreach ($data as $index => $row) {
        try {
            // Basic validation
            if (empty($row['nik']) || empty($row['nama'])) {
                continue;
            }

            // Insert Pasien
            $stmt_p->execute([
                $row['nik'], 
                $row['nama'], 
                strtolower($row['jenis_kelamin']), 
                $row['tanggal_lahir'], 
                $row['alamat'], 
                $row['no_hp']
            ]);
            $id_pasien = $pdo->lastInsertId();

            // Insert Atribut Kesehatan
            $stmt_ak->execute([
                $id_pasien, 
                $row['tekanan_sistolik'], 
                $row['tekanan_diastolik'], 
                $row['imt'], 
                $row['merokok'] ?: 'Tidak', 
                $row['konsumsi_alkohol'] ?: 'Tidak', 
                $row['kurang_buah_sayur'] ?: 'Tidak', 
                $row['diabetes'] ?: 'Tidak', 
                $row['riwayat_hipertensi'] ?: 'Tidak'
            ]);

            $success_count++;
        } catch (Exception $e) {
            $errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
        }
    }

    if (empty($errors)) {
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "$success_count data berhasil diimport"]);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Gagal mengimport data', 'errors' => $errors]);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
