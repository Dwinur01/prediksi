<?php
require_once '../config/koneksi.php';
require_once '../includes/auth_check.php';

// Ambil data atribut pasien yang BELUM ada di hasil_prediksi
$query = "SELECT p.nama, p.nik, ak.id_atribut, ak.tanggal_pemeriksaan 
          FROM atribut_kesehatan ak
          JOIN pasien p ON ak.id_pasien = p.id_pasien
          LEFT JOIN hasil_prediksi hp ON ak.id_atribut = hp.id_atribut_kesehatan
          WHERE hp.id_prediksi IS NULL";
$pasien_belum = $pdo->query($query)->fetchAll();

$select_id = isset($_GET['id_atribut']) ? $_GET['id_atribut'] : '';
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-brain text-medical-blue me-2"></i>Prediksi Risiko Hipertensi</h2>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <!-- Panel Pilihan -->
        <div class="card shadow border-0 mb-4 sticky-top" style="top: 20px;">
            <div class="card-header bg-transparent pt-3 pb-0 border-0">
                <h5 class="mb-0 fw-bold">Pilih Data Uji</h5>
            </div>
            <div class="card-body">
                <label class="form-label text-muted">Antrean Pemeriksaan Pasien (Belum Diagnosa)</label>
                <select id="pilihPasien" class="form-select mb-3 shadow-sm border-0 bg-light-theme">
                    <option value="">-- Silakan Pilih --</option>
                    <?php foreach($pasien_belum as $p): ?>
                        <option value="<?= $p['id_atribut'] ?>" <?= $p['id_atribut'] == $select_id ? 'selected' : '' ?>>
                            [<?= $p['tanggal_pemeriksaan'] ?>] <?= htmlspecialchars($p['nama']) ?> (<?= htmlspecialchars($p['nik']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button id="btnHitung" class="btn btn-primary bg-medical-blue w-100 py-2 fw-bold shadow">
                    <i class="fas fa-calculator me-2"></i>Jalankan Algoritma Naïve Bayes
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div id="hasilContainer" style="display: none;">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-transparent pt-3 pb-0 border-0">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-microchip me-2"></i>Terminal Kalkulasi Naïve Bayes</h5>
                </div>
                <div class="card-body">
                    <!-- DOM -->
                    <div id="logikaNB" class="p-4 bg-dark text-light border rounded shadow-inner mb-4" style="max-height: 500px; overflow-y: auto; font-family: monospace;">
                        <!-- Detail JS Masuk Sini -->
                    </div>
                    
                    <div class="alert bg-light border-0 shadow-sm d-flex align-items-center mb-4 p-4">
                        <div class="fs-1 me-4 text-primary animate__animated animate__pulse animate__infinite"><i class="fas fa-stethoscope"></i></div>
                        <div>
                            <p class="mb-1 text-muted fw-bold text-uppercase">Hasil Diagnosa Prediksi Akhir</p>
                            <h2 class="mb-0 fw-bold" id="hasilAkhirTeks" style="letter-spacing: 1px;"></h2>
                        </div>
                    </div>
                    
                    <button id="btnSimpan" class="btn btn-success btn-lg w-100 fw-bold shadow">
                        <i class="fas fa-database me-2"></i>Simpan Diagnosa ke Tabel Prediksi
                    </button>
                </div>
            </div>
        </div>
        
        <div id="placeholderPrediksi" class="text-center p-5 text-muted card shadow-sm border-0 d-flex justify-content-center align-items-center h-100">
            <div>
                <i class="fas fa-robot mb-3 opacity-25 text-primary" style="font-size: 5rem;"></i>
                <h4 class="fw-bold">Sistem Inferensi Siaga</h4>
                <p>Pilih identitas pasien di samping untuk memulai kalkulasi otomatis.</p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
