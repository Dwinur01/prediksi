<?php
require_once '../config/koneksi.php';
require_once '../includes/auth_check.php';
include '../includes/header.php';

// Fetch ringkasan data dari tabel pasien dan hasil_prediksi
$total_pasien = $pdo->query("SELECT COUNT(*) FROM pasien p JOIN hasil_prediksi hp ON p.id_pasien = hp.id_pasien")->fetchColumn();
$total_tinggi = $pdo->query("SELECT COUNT(*) FROM hasil_prediksi WHERE hasil_prediksi = 'Tinggi'")->fetchColumn();
$total_rendah = $pdo->query("SELECT COUNT(*) FROM hasil_prediksi WHERE hasil_prediksi = 'Rendah'")->fetchColumn();

// Fetch 5 prediksi terbaru
$query = "SELECT p.nama, TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) as umur, p.alamat, 
          ak.tekanan_sistolik, ak.tekanan_diastolik, hp.hasil_prediksi
          FROM hasil_prediksi hp
          JOIN pasien p ON hp.id_pasien = p.id_pasien
          JOIN atribut_kesehatan ak ON hp.id_atribut_kesehatan = ak.id_atribut
          ORDER BY hp.id_prediksi DESC LIMIT 5";
$latest = $pdo->query($query)->fetchAll();
?>

<!-- Baris 1: Card Widget -->
<div class="row mb-4">
    <div class="col-md-4 animate-slide-up" style="animation-delay: 0.1s;">
        <div class="card glass-card hover-card border-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div>
                    <h6 class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.8px;">Total Diagnosa</h6>
                    <h2 class="fw-black mb-0" id="count-total" style="font-weight: 800; color: var(--medical-blue); font-size: 2.5rem;"><?= $total_pasien ?></h2>
                </div>
                <div class="ms-auto text-white rounded-4 d-flex align-items-center justify-content-center shadow-lg" style="background: linear-gradient(135deg, var(--medical-blue), var(--medical-cyan)); width: 64px; height: 64px;">
                    <i class="fas fa-users-viewfinder fs-3"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3">
                <small class="text-success fw-bold"><i class="fas fa-arrow-up me-1"></i>Sistem Aktif</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 animate-slide-up" style="animation-delay: 0.2s;">
        <div class="card glass-card hover-card border-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div>
                    <h6 class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.8px;">Risiko Tinggi</h6>
                    <h2 class="fw-black mb-0" id="count-tinggi" style="font-weight: 800; color: var(--danger-red); font-size: 2.5rem;"><?= $total_tinggi ?></h2>
                </div>
                <div class="ms-auto text-white rounded-4 d-flex align-items-center justify-content-center shadow-lg" style="background: linear-gradient(135deg, #ef4444, #f87171); width: 64px; height: 64px;">
                    <i class="fas fa-heart-pulse fs-3"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3">
                <small class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i>Perlu Tindakan</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 animate-slide-up" style="animation-delay: 0.3s;">
        <div class="card glass-card hover-card border-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div>
                    <h6 class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.8px;">Risiko Rendah</h6>
                    <h2 class="fw-black mb-0" id="count-rendah" style="font-weight: 800; color: var(--success-green); font-size: 2.5rem;"><?= $total_rendah ?></h2>
                </div>
                <div class="ms-auto text-white rounded-4 d-flex align-items-center justify-content-center shadow-lg" style="background: linear-gradient(135deg, #10b981, #34d399); width: 64px; height: 64px;">
                    <i class="fas fa-shield-heart fs-3"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3">
                <small class="text-secondary fw-bold"><i class="fas fa-check-circle me-1"></i>Kondisi Aman</small>
            </div>
        </div>
    </div>
</div>

<!-- Baris 2: Map Leaflet & Chart Pie -->
<div class="row mb-4">
    <div class="col-lg-8 animate-slide-up" style="animation-delay: 0.4s;">
        <div class="card glass-card hover-card border-0 h-100 overflow-hidden">
            <div class="card-header bg-transparent pt-4 px-4 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-map-location-dot text-primary me-2"></i>Peta Zonasi Risiko</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill" style="font-size: 10px;">WILAYAH CERME</span>
            </div>
            <div class="card-body p-0 position-relative">
                <div id="map" style="height: 520px; z-index: 1;"></div>
                <!-- Legend Overlay -->
                <div class="position-absolute bottom-0 start-0 m-4 p-4 glass-card border-0 rounded-4 shadow-lg text-dark" style="z-index: 1000; min-width: 240px;">
                    <h6 class="text-muted fw-bold mb-3 border-bottom pb-2" style="font-size: 10px; letter-spacing: 1px; text-transform: uppercase;">Zonasi Berdasarkan Diagnosa</h6>
                    <div class="d-flex align-items-center mb-3">
                        <div style="width: 14px; height: 14px; border-radius: 4px; background: var(--danger-red); box-shadow: 0 4px 10px rgba(239,68,68,0.3);" class="me-3"></div>
                        <span style="font-size: 12px; font-weight: 700;">Risiko Tinggi (>50%)</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div style="width: 14px; height: 14px; border-radius: 4px; background: var(--success-green); box-shadow: 0 4px 10px rgba(16,185,129,0.3);" class="me-3"></div>
                        <span style="font-size: 12px; font-weight: 700;">Risiko Rendah (&le;50%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 animate-slide-up" style="animation-delay: 0.5s;">
        <div class="card glass-card hover-card border-0 h-100 overflow-hidden">
            <div class="card-header bg-transparent pt-4 px-4 border-0">
                <h5 class="mb-0 fw-bold"><i class="fas fa-chart-line text-secondary me-2"></i>Statistik Diagnosa</h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
                <canvas id="riskPieChart" style="max-height: 280px; width: 100%;"></canvas>
                <div class="mt-4 p-3 rounded-4 bg-light w-100 border border-white">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small fw-bold">Keakuratan Model</span>
                        <span class="text-primary small fw-bold">98.5%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" style="width: 98.5%; background: linear-gradient(to right, var(--medical-blue), var(--medical-cyan))"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Baris 3: Tabel 5 Pasien Terbaru -->
<div class="card shadow border-0 hover-card animate-slide-up" style="animation-delay: 0.6s;">
    <div class="card-header bg-white border-bottom-0 pt-4 pb-2 d-flex align-items-center">
        <div class="bg-info bg-opacity-10 p-2 text-info rounded-3 me-3">
            <i class="fas fa-history fs-5"></i>
        </div>
        <h5 class="mb-0 fw-bold" style="font-size: 16px; letter-spacing: 0.3px;">5 Diagnosa Prediksi Terbaru</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 border-top">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 px-4 py-3 text-muted text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Nama Pasien</th>
                        <th class="border-0 py-3 text-muted text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Umur</th>
                        <th class="border-0 py-3 text-muted text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Alamat (Desa)</th>
                        <th class="border-0 py-3 text-muted text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Tekanan Darah</th>
                        <th class="border-0 pe-4 py-3 text-muted text-uppercase text-end" style="font-size: 10px; letter-spacing: 1px;">Status Diagnosa</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if(count($latest) == 0): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data diagnosa hasil prediksi.</td></tr>
                    <?php endif; ?>
                    <?php foreach($latest as $p): ?>
                    <tr>
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width:36px; height:36px; font-size:14px;">
                                    <?= strtoupper(substr($p['nama'],0,1)) ?>
                                </div>
                                <span class="fw-bold text-dark" style="font-size: 14px;"><?= htmlspecialchars($p['nama']) ?></span>
                            </div>
                        </td>
                        <td class="py-3 text-muted" style="font-size: 13px;"><?= htmlspecialchars($p['umur']) ?> th</td>
                        <td class="py-3" style="font-size: 13px;">
                            <span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-map-marker-alt text-muted me-1"></i><?= htmlspecialchars($p['alamat']) ?></span>
                        </td>
                        <td class="py-3 fw-bold" style="font-size: 13px; color:#475569;"><?= htmlspecialchars($p['tekanan_sistolik']) ?> / <?= htmlspecialchars($p['tekanan_diastolik']) ?> <small class="text-muted fw-normal">mmHg</small></td>
                        <td class="pe-4 py-3 text-end">
                            <span class="badge rounded-pill px-3 py-2 <?= $p['hasil_prediksi'] == 'Tinggi' ? 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' : 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' ?>" style="font-size: 11px;">
                                <?php if($p['hasil_prediksi'] == 'Tinggi') echo '<i class="fas fa-chart-line me-1"></i>'; else echo '<i class="fas fa-shield-alt me-1"></i>'; ?>
                                Risiko <?= $p['hasil_prediksi'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .custom-popup .leaflet-popup-content-wrapper { border-radius: 20px; padding: 0; overflow: hidden; box-shadow: var(--shadow-hover); border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px); }
    .custom-popup .leaflet-popup-content { margin: 0; width: 320px !important; }
    .village-label {
        background: rgba(255,255,255,0.8);
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: 800;
        color: #1e293b;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        text-align: center;
        white-space: nowrap;
        pointer-events: none;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initial CountUp Animations
    const options = { duration: 2, useEasing: true, useGrouping: true };
    new countUp.CountUp('count-total', <?= $total_pasien ?>, options).start();
    new countUp.CountUp('count-tinggi', <?= $total_tinggi ?>, options).start();
    new countUp.CountUp('count-rendah', <?= $total_rendah ?>, options).start();

    // 2. Map Initialization
    var map = L.map('map', { zoomSnap: 0.1, attributionControl: false }).setView([-7.240, 112.550], 12.8);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(map);

    const villagesData = [
        { id: 1, name: "Dadapkuning", prevalence: 12, pop: 3200, link: "https://maps.app.goo.gl/4rD4WF7dYADqaCSv8", lng: 112.520, lat: -7.245 },
        { id: 2, name: "Lengkong", prevalence: 8, pop: 2800, link: "https://maps.app.goo.gl/av9fYSogdmwjLEcY9", lng: 112.535, lat: -7.240 },
        { id: 3, name: "Dooro", prevalence: 26, pop: 4100, link: "https://maps.app.goo.gl/wkfjCRV7CNnX7xBt8", lng: 112.530, lat: -7.275 },
        { id: 4, name: "Dampaan", prevalence: 15, pop: 3500, link: "https://maps.app.goo.gl/KrxQozrxj17tb11C7", lng: 112.545, lat: -7.280 },
        { id: 5, name: "Ngembung", prevalence: 30, pop: 2900, link: "https://maps.app.goo.gl/rw9c6gB2JNM2BNhM6", lng: 112.530, lat: -7.250 },
        { id: 6, name: "Guranganyar", prevalence: 5, pop: 3700, link: "https://maps.app.goo.gl/FBnQiefucsEfF87s7", lng: 112.545, lat: -7.265 },
        { id: 7, name: "Sukoanyar", prevalence: 18, pop: 4500, link: "https://maps.app.goo.gl/s2x43V9VaG3kX94T7", lng: 112.540, lat: -7.255 },
        { id: 8, name: "Morowudi", prevalence: 22, pop: 5200, link: "https://maps.app.goo.gl/bSwKqZ4jZtM68xCo6", lng: 112.575, lat: -7.230 },
        { id: 9, name: "Iker-iker Geger", prevalence: 10, pop: 2400, link: "https://maps.app.goo.gl/fiPMC3CPcFM18gob7", lng: 112.580, lat: -7.240 },
        { id: 10, name: "Betiting", prevalence: 27, pop: 3300, link: "https://maps.app.goo.gl/geEFAsGwWU3rYKLd8", lng: 112.550, lat: -7.225 },
        { id: 11, name: "Cerme Kidul", prevalence: 35, pop: 6100, link: "https://maps.app.goo.gl/bTRQCUZJXvfiskBKA", lng: 112.560, lat: -7.235 },
        { id: 12, name: "Cerme Lor", prevalence: 33, pop: 5800, link: "https://maps.app.goo.gl/euEQAUxZEGq6qipX7", lng: 112.560, lat: -7.225 },
        { id: 13, name: "Cagak Agung", prevalence: 7, pop: 3100, link: "https://maps.app.goo.gl/FiTC22ZjB6b3saoc7", lng: 112.550, lat: -7.240 },
        { id: 14, name: "Ngabetan", prevalence: 14, pop: 3900, link: "https://maps.app.goo.gl/M6xRJJMz4ZbRMDnz8", lng: 112.565, lat: -7.245 },
        { id: 15, name: "Kambingan", prevalence: 19, pop: 4200, link: "https://maps.app.goo.gl/ynig5ffQSTMvY69KA", lng: 112.575, lat: -7.255 },
        { id: 16, name: "Wedani", prevalence: 21, pop: 3600, link: "https://maps.app.goo.gl/pJBMNigKRpezoGAm8", lng: 112.560, lat: -7.265 },
        { id: 17, name: "Dungus", prevalence: 9, pop: 2700, link: "https://maps.app.goo.gl/SCUuKV2N1pmNUfv29", lng: 112.555, lat: -7.280 },
        { id: 18, name: "Kandangan", prevalence: 11, pop: 3200, link: "https://maps.app.goo.gl/ExugH9DZdB8SfMGW6", lng: 112.540, lat: -7.295 },
        { id: 19, name: "Gedangkulut", prevalence: 28, pop: 3400, link: "https://maps.app.goo.gl/DebAhiXkJ5PpSDgg6", lng: 112.530, lat: -7.290 },
        { id: 20, name: "Semampir", prevalence: 6, pop: 2100, link: "https://maps.app.goo.gl/MeNpaRyw1A4uDaVf9", lng: 112.540, lat: -7.220 },
        { id: 21, name: "Pandu", prevalence: 13, pop: 2900, link: "https://maps.app.goo.gl/TmNaLHnGU6kAVPnu6", lng: 112.555, lat: -7.198 },
        { id: 22, name: "Jono", prevalence: 17, pop: 3300, link: "https://maps.app.goo.gl/SqAXVmSdb9mWeDSq7", lng: 112.565, lat: -7.205 },
        { id: 23, name: "Tambakberas", prevalence: 24, pop: 3100, link: "https://maps.app.goo.gl/xKifToYJLHSoyydMA", lng: 112.570, lat: -7.215 },
        { id: 24, name: "Padeg", prevalence: 4, pop: 2500, link: "https://maps.app.goo.gl/sg5mkGfmaw3hoSZg8", lng: 112.550, lat: -7.210 },
        { id: 25, name: "Banjarsari", prevalence: 16, pop: 3800, link: "https://maps.app.goo.gl/Cq42sqNpKgvbyU7fA", lng: 112.545, lat: -7.195 }
    ];

    const cermeBoundary = turf.polygon([[
        [112.535, -7.185], [112.550, -7.190], [112.565, -7.185], [112.570, -7.200],
        [112.585, -7.220], [112.580, -7.235], [112.590, -7.250], [112.580, -7.265],
        [112.575, -7.275], [112.560, -7.290], [112.550, -7.305], [112.535, -7.300],
        [112.525, -7.305], [112.520, -7.285], [112.510, -7.270], [112.515, -7.255],
        [112.510, -7.240], [112.520, -7.220], [112.530, -7.200], [112.535, -7.185]
    ]]);

    function getRisk(prevalence) {
        if (prevalence > 50) return { label: 'Tinggi', color: '#ef4444' };
        return { label: 'Rendah', color: '#10b981' };
    }

    fetch('../api/api_data.php?action=get_map_data')
    .then(res => res.json())
    .then(data => {
        let stats = {};
        data.forEach(d => {
            let n = d.desa.toLowerCase().trim();
            if (n === 'dohoagung') n = 'dooro';
            if (n === 'kandanyar') n = 'kandangan';

            stats[n] = {
                tinggi: parseInt(d.tinggi),
                rendah: parseInt(d.rendah),
                total: parseInt(d.total)
            };
        });

        villagesData.forEach(v => {
            let key = v.name.toLowerCase().trim();
            if(stats[key] && stats[key].total > 0) {
                let p = (stats[key].tinggi / stats[key].total) * 100;
                v.prevalence = Math.round(p);
                v.sysTotal = stats[key].total;
                v.sysTinggi = stats[key].tinggi;
                v.sysRendah = stats[key].rendah;
            } else {
                v.prevalence = 0; v.sysTotal = 0; v.sysTinggi = 0; v.sysRendah = 0;
            }
        });

        const points = turf.featureCollection(villagesData.map(v => turf.point([v.lng, v.lat])));
        const bbox = [112.50, -7.31, 112.60, -7.18]; 
        const voronoiPolys = turf.voronoi(points, { bbox: bbox });

        villagesData.forEach((v, index) => {
            const vPoly = voronoiPolys.features[index];
            if (vPoly) {
                v.geoJSON = turf.intersect(vPoly, cermeBoundary);
            }
        });

        villagesData.forEach(v => {
            const risk = getRisk(v.prevalence);
            if (v.geoJSON) {
                const geojsonLayer = L.geoJSON(v.geoJSON, {
                    style: { color: '#ffffff', fillColor: risk.color, fillOpacity: 0.4, weight: 2, dashArray: '3' }
                }).addTo(map);

                geojsonLayer.on('mouseover', function(e) { e.layer.setStyle({ weight: 3, fillOpacity: 0.6, color: '#1e293b', dashArray: '' }); e.layer.bringToFront(); });
                geojsonLayer.on('mouseout', function(e) { geojsonLayer.resetStyle(e.layer); });

                const popup = `
                    <div class="custom-popup">
                        <div class="p-4 text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, ${risk.color}, #333);">
                            <h6 class="fw-bold mb-0"><i class="fas fa-hospital-user me-2"></i>Desa ${v.name}</h6>
                        </div>
                        <div class="p-4 bg-white">
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="p-3 rounded-4 border bg-light text-center">
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 8px;">Kategori</small>
                                        <span class="fw-bold" style="color: ${risk.color}; font-size: 14px;">${risk.label}</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 rounded-4 border bg-light text-center">
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 8px;">Total Data</small>
                                        <span class="fw-bold text-dark" style="font-size: 14px;">${v.sysTotal}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 d-flex justify-content-between">
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2">Tinggi: ${v.sysTinggi}</span>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">Rendah: ${v.sysRendah}</span>
                            </div>
                            <a href="${v.link}" target="_blank" class="btn btn-primary w-100 py-2 shadow-sm" style="border-radius: 12px; font-size: 12px;">
                                <i class="fas fa-location-arrow me-2"></i>Navigasi ke Lokasi
                            </a>
                        </div>
                    </div>
                `;
                geojsonLayer.bindPopup(popup, { closeButton: false });

                L.marker([v.lat, v.lng], {
                    icon: L.divIcon({
                        className: 'village-label-container',
                        html: '<div class="village-label">'+v.name+'</div>',
                        iconSize: [0, 0]
                    })
                }).addTo(map);
            }
        });

        const ctx = document.getElementById('riskPieChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Risiko Tinggi', 'Risiko Rendah'],
                datasets: [{
                    data: [<?= $total_tinggi ?>, <?= $total_rendah ?>],
                    backgroundColor: ['#ef4444', '#10b981'],
                    hoverOffset: 20,
                    borderRadius: 10,
                    spacing: 12
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { family: 'Plus Jakarta Sans', weight: 'bold' } } },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1e293b',
                        bodyColor: '#1e293b',
                        cornerRadius: 12,
                        padding: 15,
                        displayColors: true,
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let currentValue = context.raw;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = ((currentValue / total) * 100).toFixed(1);
                                return ` ${label}: ${currentValue} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
