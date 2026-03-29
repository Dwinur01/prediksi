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
        <div class="card shadow-sm border-0 border-start border-primary border-5 mb-3 hover-card">
            <div class="card-body d-flex align-items-center">
                <div>
                    <h6 class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Total Pasien Diagnosa</h6>
                    <h2 class="fw-black mb-0 text-dark" style="font-weight: 900;"><?= $total_pasien ?></h2>
                </div>
                <div class="ms-auto text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #00C6FF, #0072FF); width: 60px; height: 60px; position: relative; overflow: hidden;">
                    <div style="position: absolute; width: 100%; height: 100%; background: rgba(255,255,255,0.1); backdrop-filter: blur(2px);"></div>
                    <i class="fas fa-hospital-user fs-3" style="position: relative; z-index: 1;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 animate-slide-up" style="animation-delay: 0.2s;">
        <div class="card shadow-sm border-0 border-start border-danger border-5 mb-3 hover-card">
            <div class="card-body d-flex align-items-center">
                <div>
                    <h6 class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Risiko Tinggi</h6>
                    <h2 class="fw-black mb-0 text-danger" style="font-weight: 900;"><?= $total_tinggi ?></h2>
                </div>
                <div class="ms-auto text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #FF4B2B, #FF416C); width: 60px; height: 60px; position: relative; overflow: hidden;">
                    <div style="position: absolute; width: 100%; height: 100%; background: rgba(255,255,255,0.1); backdrop-filter: blur(2px);"></div>
                    <i class="fas fa-heart-pulse fs-3" style="position: relative; z-index: 1;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 animate-slide-up" style="animation-delay: 0.3s;">
        <div class="card shadow-sm border-0 border-start border-success border-5 mb-3 hover-card">
            <div class="card-body d-flex align-items-center">
                <div>
                    <h6 class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Risiko Rendah</h6>
                    <h2 class="fw-black mb-0 text-success" style="font-weight: 900;"><?= $total_rendah ?></h2>
                </div>
                <div class="ms-auto text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #11998e, #38ef7d); width: 60px; height: 60px; position: relative; overflow: hidden;">
                    <div style="position: absolute; width: 100%; height: 100%; background: rgba(255,255,255,0.1); backdrop-filter: blur(2px);"></div>
                    <i class="fas fa-shield-heart fs-3" style="position: relative; z-index: 1;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Baris 2: Map Leaflet & Chart Pie -->
<div class="row mb-4">
    <div class="col-lg-8 animate-slide-up" style="animation-delay: 0.4s;">
        <div class="card shadow border-0 mb-3 h-100 hover-card">
            <div class="card-header bg-transparent pt-3 pb-0 border-0">
                <h5 class="mb-0 fw-bold"><i class="fas fa-map-marked-alt text-primary me-2"></i>Peta Zonasi (Cerme)</h5>
            </div>
            <div class="card-body p-0 position-relative">
                <div id="map" class="shadow-sm" style="height: 500px; border-bottom-left-radius: 6px; border-bottom-right-radius: 6px; z-index: 1;"></div>
                <!-- Legend Overlay -->
                <div class="position-absolute bottom-0 start-0 m-3 p-3 bg-white bg-opacity-75 rounded shadow-sm border border-light" style="z-index: 1000; backdrop-filter: blur(10px); min-width: 200px;">
                    <h6 class="text-muted fw-bold mb-3 border-bottom pb-2" style="font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">Status Klasifikasi</h6>
                    <div class="d-flex align-items-center mb-2">
                        <div style="width: 14px; height: 14px; border-radius: 4px; background: #dc3545; box-shadow: 0 2px 4px rgba(220,53,69,0.3);" class="me-2"></div>
                        <span style="font-size: 12px; font-weight: bold;" class="text-dark">Risiko Tinggi (>50%)</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div style="width: 14px; height: 14px; border-radius: 4px; background: #198754; box-shadow: 0 2px 4px rgba(25,135,84,0.3);" class="me-2"></div>
                        <span style="font-size: 12px; font-weight: bold;" class="text-dark">Risiko Rendah (&le;50%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 animate-slide-up" style="animation-delay: 0.5s;">
        <div class="card shadow border-0 mb-3 h-100 hover-card">
            <div class="card-header bg-transparent pt-3 pb-0 border-0">
                <h5 class="mb-0 fw-bold"><i class="fas fa-chart-pie text-secondary me-2"></i>Proporsi Diagnosa</h5>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="riskPieChart" style="max-height: 250px; width: 100%;"></canvas>
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
    .custom-popup .leaflet-popup-content-wrapper { border-radius: 16px; padding: 0; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
    .custom-popup .leaflet-popup-content { margin: 0; width: 320px !important; }
    .village-label {
        background: transparent;
        border: none;
        box-shadow: none;
        font-size: 10px;
        font-weight: 800;
        color: #1e293b;
        text-shadow: 0px 0px 4px rgba(255,255,255,1), 0px 0px 4px rgba(255,255,255,1);
        text-align: center;
        white-space: nowrap;
        pointer-events: none;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
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
        if (prevalence > 50) return { label: 'Tinggi', color: '#dc3545' };
        return { label: 'Rendah', color: '#198754' };
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
                v.prevalence = 0;
                v.sysTotal = 0;
                v.sysTinggi = 0;
                v.sysRendah = 0;
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
                    style: {
                        color: '#ffffff',
                        fillColor: risk.color,
                        fillOpacity: 0.4,
                        weight: 2,
                        dashArray: '3'
                    }
                }).addTo(map);

                geojsonLayer.on('mouseover', function(e) {
                    e.layer.setStyle({ weight: 3, fillOpacity: 0.6, color: '#1e293b', dashArray: '' });
                    e.layer.bringToFront();
                });
                geojsonLayer.on('mouseout', function(e) {
                    geojsonLayer.resetStyle(e.layer);
                });

                const popup = `
                    <div class="custom-popup shadow">
                        <div class="p-3 text-white d-flex align-items-center justify-content-between" style="background-color: ${risk.color};">
                            <h5 class="fw-bold mb-0" style="font-size:16px; letter-spacing: 0.5px;"><i class="fas fa-map-marker-alt me-2"></i>Desa ${v.name}</h5>
                        </div>
                        <div class="p-3 bg-white text-dark">
                            <div class="d-flex justify-content-between gap-2 mb-3">
                                <div class="flex-fill p-2 rounded border text-center shadow-sm" style="background: #fafafa; min-width: 0;">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 9px; font-weight: 700; letter-spacing: 0.5px;">Risiko Wilayah</span>
                                    <strong class="d-block mt-1" style="color: ${risk.color}; font-size: 14px;">${risk.label}</strong>
                                </div>
                                <div class="flex-fill p-2 rounded border text-center shadow-sm" style="background: #fafafa; min-width: 0;">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 9px; font-weight: 700; letter-spacing: 0.5px;">Diperiksa</span>
                                    <strong class="d-block mt-1" style="color: #333; font-size: 14px;">${v.sysTotal} org</strong>
                                </div>
                            </div>
                            
                            <div class="mb-3 p-2 rounded border bg-light text-center shadow-sm">
                                <span class="text-muted text-uppercase d-block mb-2" style="font-size: 9px; font-weight: 700; letter-spacing: 0.5px;">Distribusi Sistem Naive Bayes</span>
                                <div class="d-flex justify-content-center gap-2">
                                    <span class="badge bg-danger shadow-sm py-2 px-2" style="font-size:10px;"><i class="fas fa-arrow-up me-1"></i> ${v.sysTinggi} Tinggi</span>
                                    <span class="badge bg-success shadow-sm py-2 px-2" style="font-size:10px;"><i class="fas fa-arrow-down me-1"></i> ${v.sysRendah} Rendah</span>
                                </div>
                            </div>

                            <a href="${v.link}" target="_blank" class="btn btn-primary w-100 fw-bold shadow-sm" style="border-radius: 8px; font-size: 12px; border:none;">
                                <i class="fas fa-map me-1"></i> Rute Maps Desa
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
            type: 'pie',
            data: {
                labels: ['Risiko Tinggi', 'Risiko Rendah'],
                datasets: [{
                    data: [<?= $total_tinggi ?>, <?= $total_rendah ?>],
                    backgroundColor: ['#dc3545', '#198754'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                let dataset = context.chart.data.datasets[context.datasetIndex];
                                let total = dataset.data.reduce((a, b) => a + b, 0);
                                let currentValue = dataset.data[context.dataIndex];
                                let percentage = total > 0 ? ((currentValue / total) * 100).toFixed(1) : 0;
                                return label + currentValue + ' Pasien (' + percentage + '%)';
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
