<?php
require_once '../config/koneksi.php';
require_once '../includes/auth_check.php';

$query = "SELECT hp.id_prediksi, hp.tanggal_prediksi, hp.hasil_prediksi,
          p.nik, p.nama, TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) as umur, p.jenis_kelamin, p.alamat,
          ak.tekanan_sistolik, ak.tekanan_diastolik
          FROM hasil_prediksi hp
          JOIN pasien p ON hp.id_pasien = p.id_pasien
          JOIN atribut_kesehatan ak ON hp.id_atribut_kesehatan = ak.id_atribut
          ORDER BY hp.id_prediksi DESC";
$pasien_sudah = $pdo->query($query)->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0 fw-bold"><i class="fas fa-file-invoice-dollar text-success me-2"></i>Laporan Cetak</h2>
    <div>
        <button onclick="window.print()" class="btn btn-secondary shadow me-2">
            <i class="fas fa-print me-2"></i>Print / Save
        </button>
        <button id="btnPdf" class="btn btn-danger shadow">
            <i class="fas fa-file-pdf me-2"></i>Export PDF
        </button>
    </div>
</div>

<?php if(isset($_GET['toast']) && $_GET['toast'] == 'saved'): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
    <i class="fas fa-check-circle me-2"></i>Data prediksi diagnostik sukses disimpan ke master database!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card shadow border-0" id="areaLaporanPDF">
    <div class="card-body p-5">
        
        <div class="d-flex border-bottom border-dark border-3 pb-3 mb-4 justify-content-center align-items-center">
            <div class="text-center">
                <h3 class="fw-bold mb-1 letter-spacing">PEMERINTAH KABUPATEN GRESIK</h3>
                <h4 class="fw-bold mb-1">DINAS KESEHATAN</h4>
                <h5 class="fw-bold mb-1 text-uppercase">UPT PUSKESMAS CERME</h5>
                <p class="mb-0 small">Jl. Raya Cerme Kidul No. XX, Gresik, Jawa Timur</p>
                <p class="mb-0 small fw-bold mt-2">LAPORAN PEMETAAN DAN KLASIFIKASI RISIKO HIPERTENSI (NAÏVE BAYES)</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered border-dark table-sm align-middle" id="tableLaporan">
                <thead class="table-light text-center">
                    <tr>
                        <th width="3%">No</th>
                        <th width="12%">ID Prediksi</th>
                        <th width="10%">Tgl Diagnosa</th>
                        <th width="15%">Nama Pasien</th>
                        <th width="10%">L/P (Umur)</th>
                        <th width="15%">Desa Wilayah</th>
                        <th width="15%">Tekanan (S/D)</th>
                        <th width="20%">Kesimpulan Prediksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; foreach($pasien_sudah as $p): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="fw-bold text-center">PRD-<?= sprintf('%04d', $p['id_prediksi']) ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($p['tanggal_prediksi'])) ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($p['nama']) ?></td>
                        <td class="text-center"><?= $p['jenis_kelamin'] == 'laki-laki' ? 'L' : 'P' ?> (<?= $p['umur'] ?>)</td>
                        <td><?= htmlspecialchars($p['alamat']) ?></td>
                        <td class="text-center"><?= $p['tekanan_sistolik'] ?>/<?= $p['tekanan_diastolik'] ?></td>
                        <td class="text-center <?= $p['hasil_prediksi'] == 'Tinggi' ? 'text-danger fw-bold' : 'text-success fw-bold' ?>">
                            RISIKO <?= strtoupper($p['hasil_prediksi']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5 d-flex justify-content-end">
            <div class="text-center" style="width: 250px;">
                <p>Cerme, <?= date('d F Y') ?><br>Mengetahui,</p>
                <p class="mb-5">Kepala Poli PTM</p>
                <p class="mb-0 text-decoration-underline fw-bold">dr. Admin Cerme</p>
                <p>NIP. 19800101 201001 2 001</p>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnPdf').addEventListener('click', function() {
        this.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Generating...`;
        this.disabled = true;

        const el = document.getElementById('areaLaporanPDF');
        let opt = {
            margin:       0.4,
            filename:     'Laporan_Hipertensi_Cerme.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'legal', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(el).save().then(() => {
            this.innerHTML = `<i class="fas fa-file-pdf me-2"></i>Export PDF`;
            this.disabled = false;
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
