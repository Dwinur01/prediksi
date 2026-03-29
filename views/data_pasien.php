<?php
require_once '../config/koneksi.php';
require_once '../includes/auth_check.php';

// Proses Tambah Data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_pasien'])) {
    try {
        $pdo->beginTransaction();
        
        // 1. Insert ke tabel pasien
        $stmt_p = $pdo->prepare("INSERT INTO pasien (nik, nama, jenis_kelamin, tanggal_lahir, alamat, no_hp) VALUES (?,?,?,?,?,?)");
        $stmt_p->execute([
            $_POST['nik'], $_POST['nama'], $_POST['jenis_kelamin'], $_POST['tanggal_lahir'], $_POST['alamat'], $_POST['no_hp']
        ]);
        $id_pasien = $pdo->lastInsertId();
        
        // 2. Insert ke tabel atribut_kesehatan
        $stmt_ak = $pdo->prepare("INSERT INTO atribut_kesehatan 
            (id_pasien, tanggal_pemeriksaan, tekanan_sistolik, tekanan_diastolik, imt, merokok, konsumsi_alkohol, kurang_buah_sayur, diabetes, riwayat_hipertensi) 
            VALUES (?, CURDATE(), ?,?,?,?,?,?,?,?)");
        $stmt_ak->execute([
            $id_pasien, $_POST['tekanan_sistolik'], $_POST['tekanan_diastolik'], $_POST['imt'], 
            $_POST['merokok'], $_POST['konsumsi_alkohol'], $_POST['kurang_buah_sayur'], 
            $_POST['diabetes'], $_POST['riwayat_hipertensi']
        ]);
        
        $pdo->commit();
        header("Location: data_pasien.php?sukses=1");
        exit;
    } catch(Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}

// Ambil data pasien yang atribut kesehatannya belum ada di tabel hasil_prediksi
$query = "SELECT p.*, ak.*, TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) AS umur 
          FROM pasien p 
          JOIN atribut_kesehatan ak ON p.id_pasien = ak.id_pasien 
          LEFT JOIN hasil_prediksi hp ON ak.id_atribut = hp.id_atribut_kesehatan
          WHERE hp.id_prediksi IS NULL ORDER BY ak.id_atribut DESC";
$pasien = $pdo->query($query)->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-users me-2"></i>Data Pasien (Belum Diprediksi)</h2>
    <button class="btn btn-primary bg-medical-blue border-0 shadow" data-bs-toggle="modal" data-bs-target="#tambahModal">
        <i class="fas fa-plus me-2"></i>Pasien Baru
    </button>
</div>

<?php if(isset($_GET['sukses'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    Data pasien dan atribut kesehatan baru berhasil ditambahkan!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablePasien" class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Tgl Periksa</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>L/P</th>
                        <th>Umur</th>
                        <th>Desa (Alamat)</th>
                        <th>Sistolik/Diastolik</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pasien as $p): ?>
                    <tr>
                        <td><?= $p['tanggal_pemeriksaan'] ?></td>
                        <td><?= htmlspecialchars($p['nik']) ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($p['nama']) ?></td>
                        <td><?= $p['jenis_kelamin'] == 'laki-laki' ? 'L' : 'P' ?></td>
                        <td><?= $p['umur'] ?> thn</td>
                        <td><?= htmlspecialchars($p['alamat']) ?></td>
                        <td><?= $p['tekanan_sistolik'] ?>/<?= $p['tekanan_diastolik'] ?></td>
                        <td>
                            <a href="prediksi.php?id_atribut=<?= $p['id_atribut'] ?>" class="btn btn-sm btn-success shadow-sm">
                                <i class="fas fa-magic me-1"></i>Prediksi
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Data Pasien -->
<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content text-dark">
            <form method="POST">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Pendaftaran & Pemeriksaan Pasien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- BAGIAN IDENTITAS PASIEN -->
                        <div class="col-12"><h6 class="fw-bold text-primary border-bottom pb-2">Identitas Pasien</h6></div>
                        
                        <div class="col-md-4">
                            <label class="form-label">NIK</label>
                            <input type="text" name="nik" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="laki-laki">Laki-laki</option><option value="perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Alamat Desa</label>
                            <select name="alamat" class="form-select" required>
                                <option value="Banjarsari">Banjarsari</option><option value="Betiting">Betiting</option><option value="Cagak Agung">Cagak Agung</option><option value="Cerme Kidul">Cerme Kidul</option><option value="Cerme Lor">Cerme Lor</option><option value="Dadapkuning">Dadapkuning</option><option value="Dampaan">Dampaan</option><option value="Dohoagung">Dohoagung</option><option value="Dungus">Dungus</option><option value="Gedangkulut">Gedangkulut</option><option value="Guranganyar">Guranganyar</option><option value="Iker-iker Geger">Iker-iker Geger</option><option value="Jono">Jono</option><option value="Kambingan">Kambingan</option><option value="Kandanyar">Kandanyar</option><option value="Lengkong">Lengkong</option><option value="Morowudi">Morowudi</option><option value="Ngabetan">Ngabetan</option><option value="Ngembung">Ngembung</option><option value="Padeg">Padeg</option><option value="Pandu">Pandu</option><option value="Semampir">Semampir</option><option value="Sukoanyar">Sukoanyar</option><option value="Tambakberas">Tambakberas</option><option value="Wedani">Wedani</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">No HP</label>
                            <input type="text" name="no_hp" class="form-control" required>
                        </div>

                        <!-- BAGIAN ATRIBUT KESEHATAN -->
                        <div class="col-12 mt-4"><h6 class="fw-bold text-success border-bottom pb-2">Atribut Pemeriksaan</h6></div>

                        <div class="col-md-4">
                            <label class="form-label">Tekanan Sistolik (mmHg)</label>
                            <input type="number" min="50" max="300" name="tekanan_sistolik" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tekanan Diastolik (mmHg)</label>
                            <input type="number" min="30" max="200" name="tekanan_diastolik" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">IMT (Indeks Massa Tubuh)</label>
                            <input type="number" step="0.01" min="10" max="60" name="imt" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Riwayat Hipertensi Keluarga</label>
                            <select name="riwayat_hipertensi" class="form-select" required>
                                <option value="Tidak">Tidak</option><option value="Ya">Ya</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Riwayat Diabetes</label>
                            <select name="diabetes" class="form-select" required>
                                <option value="Tidak">Tidak</option><option value="Ya">Ya</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kebiasaan Merokok</label>
                            <select name="merokok" class="form-select" required>
                                <option value="Tidak">Tidak</option><option value="Ya">Ya</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Konsumsi Alkohol</label>
                            <select name="konsumsi_alkohol" class="form-select" required>
                                <option value="Tidak">Tidak</option><option value="Ya">Ya</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kurang Buah & Sayur</label>
                            <select name="kurang_buah_sayur" class="form-select" required>
                                <option value="Tidak">Tidak</option><option value="Ya">Ya</option>
                            </select>
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light mt-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_pasien" class="btn btn-primary bg-medical-blue"><i class="fas fa-save me-2"></i>Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
