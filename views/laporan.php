<?php
require_once '../config/koneksi.php';
require_once '../includes/auth_check.php';

$query = "SELECT hp.id_prediksi, hp.tanggal_prediksi, hp.hasil_prediksi,
          p.id_pasien, p.nik, p.nama, TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) as umur, p.tanggal_lahir, p.jenis_kelamin, p.alamat, p.no_hp,
          ak.id_atribut, ak.tekanan_sistolik, ak.tekanan_diastolik, ak.imt, ak.merokok, ak.konsumsi_alkohol, ak.kurang_buah_sayur, ak.diabetes, ak.riwayat_hipertensi
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
                        <th width="15%">Prediksi</th>
                        <th width="5%" class="no-print">Aksi</th>
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
                        <td class="text-center no-print">
                            <button class="btn btn-sm btn-outline-primary btn-edit" 
                                data-id_prediksi="<?= $p['id_prediksi'] ?>"
                                data-id_pasien="<?= $p['id_pasien'] ?>"
                                data-id_atribut="<?= $p['id_atribut'] ?>"
                                data-nik="<?= $p['nik'] ?>"
                                data-nama="<?= $p['nama'] ?>"
                                data-tgl_lahir="<?= $p['tanggal_lahir'] ?>"
                                data-gender="<?= $p['jenis_kelamin'] ?>"
                                data-alamat="<?= $p['alamat'] ?>"
                                data-hp="<?= $p['no_hp'] ?>"
                                data-sistolik="<?= $p['tekanan_sistolik'] ?>"
                                data-diastolik="<?= $p['tekanan_diastolik'] ?>"
                                data-imt="<?= $p['imt'] ?>"
                                data-merokok="<?= $p['merokok'] ?>"
                                data-alkohol="<?= $p['konsumsi_alkohol'] ?>"
                                data-sayur="<?= $p['kurang_buah_sayur'] ?>"
                                data-diabetes="<?= $p['diabetes'] ?>"
                                data-riwayat="<?= $p['riwayat_hipertensi'] ?>"
                                data-hasil_prediksi="<?= $p['hasil_prediksi'] ?>"
                                title="Edit Laporan / Data">
                                <i class="fas fa-edit"></i>
                            </button>
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

<!-- Modal Edit Laporan -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-medical-blue text-white p-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Data & Laporan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditLaporan">
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" id="edit_id_prediksi" name="id_prediksi">
                    <input type="hidden" id="edit_id_pasien" name="id_pasien">
                    <input type="hidden" id="edit_id_atribut" name="id_atribut">
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">NIK Pasien</label>
                            <input type="text" id="edit_nik" name="nik" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                            <input type="text" id="edit_nama" name="nama" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Tgl Lahir</label>
                            <input type="date" id="edit_tgl_lahir" name="tanggal_lahir" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Jenis Kelamin</label>
                            <select id="edit_gender" name="jenis_kelamin" class="form-select">
                                <option value="laki-laki">Laki-laki</option>
                                <option value="perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Desa / Alamat</label>
                            <input type="text" id="edit_alamat" name="alamat" class="form-control">
                        </div>
                        
                        <div class="col-12 border-top pt-4 mt-5"><h6 class="fw-bold text-primary"><i class="fas fa-stethoscope me-2"></i>Atribut Pemeriksaan</h6></div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Sistolik (mmHg)</label>
                            <input type="number" id="edit_sistolik" name="tekanan_sistolik" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Diastolik (mmHg)</label>
                            <input type="number" id="edit_diastolik" name="tekanan_diastolik" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">IMT</label>
                            <input type="number" step="0.01" id="edit_imt" name="imt" class="form-control">
                        </div>
                        
                        <div class="col-md-12">
                            <div class="p-3 bg-white rounded-4 border">
                                <label class="form-label small fw-bold text-muted">Hasil Kesimpulan Prediksi (Override)</label>
                                <select id="edit_hasil_prediksi" name="hasil_prediksi" class="form-select fw-bold border-2">
                                    <option value="Rendah" class="text-success">RENDAH</option>
                                    <option value="Tinggi" class="text-danger">TINGGI</option>
                                </select>
                                <small class="text-muted mt-2 d-block">Gunakan fitur ini hanya jika hasil klasifikasi otomatis Naive Bayes dirasa kurang tepat terhadap kondisi riil pasien.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0 p-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-save me-2"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));

    // Handle Edit Button Click
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            document.getElementById('edit_id_prediksi').value = d.id_prediksi;
            document.getElementById('edit_id_pasien').value = d.id_pasien;
            document.getElementById('edit_id_atribut').value = d.id_atribut;
            document.getElementById('edit_nik').value = d.nik;
            document.getElementById('edit_nama').value = d.nama;
            document.getElementById('edit_tgl_lahir').value = d.tgl_lahir;
            document.getElementById('edit_gender').value = d.gender;
            document.getElementById('edit_alamat').value = d.alamat;
            document.getElementById('edit_sistolik').value = d.sistolik;
            document.getElementById('edit_diastolik').value = d.diastolik;
            document.getElementById('edit_imt').value = d.imt;
            document.getElementById('edit_hasil_prediksi').value = d.hasil_prediksi;
            
            editModal.show();
        });
    });

    // Handle Form Submit
    document.getElementById('formEditLaporan').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        Swal.fire({
            title: 'Simpan Perubahan?',
            text: "Data laporan yang sudah dicetak akan diperbarui.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0072FF',
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('../api/edit_laporan.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire('Berhasil!', res.message, 'success')
                        .then(() => location.reload());
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                });
            }
        });
    });

    // PDF Export Logic
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
