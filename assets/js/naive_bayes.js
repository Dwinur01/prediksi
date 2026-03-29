document.addEventListener('DOMContentLoaded', function() {
    const btnHitung = document.getElementById('btnHitung');
    const pilihPasien = document.getElementById('pilihPasien');
    const hasilContainer = document.getElementById('hasilContainer');
    const placeholderPrediksi = document.getElementById('placeholderPrediksi');
    const logikaNB = document.getElementById('logikaNB');
    const hasilAkhirTeks = document.getElementById('hasilAkhirTeks');
    const btnSimpan = document.getElementById('btnSimpan');
    
    let currentPasienData = null;
    let finalPrediction = null;

    if(btnHitung) {
        btnHitung.addEventListener('click', async () => {
            const idAtribut = pilihPasien.value; // Kita sekarang mengirim id_atribut_kesehatan
            if(!idAtribut) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Silakan pilih antrean pasien dari list di atas terlebih dahulu.'
                });
                return;
            }

            btnHitung.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghitung Model...`;
            btnHitung.disabled = true;

            try {
                // Fetch data uji berdasarkan id_atribut
                const resPasien = await fetch(`../api/api_data.php?action=get_pasien_belum&id_atribut=${idAtribut}`);
                currentPasienData = await resPasien.json();

                // Fetch keseluruhan data latih Naive Bayes (sudah selesai di-prediksi)
                const resTraining = await fetch(`../api/api_data.php?action=get_training_data`);
                const trainingData = await resTraining.json();

                hitungNaiveBayes(currentPasienData, trainingData);

                placeholderPrediksi.classList.remove('d-flex');
                placeholderPrediksi.style.display = 'none';
                hasilContainer.style.display = 'block';
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi API Gagal',
                    text: "Error saat fetch dataset: " + err
                });
            } finally {
                btnHitung.innerHTML = `<i class="fas fa-calculator me-2"></i>Jalankan Algoritma Naïve Bayes`;
                btnHitung.disabled = false;
            }
        });
    }

    if(btnSimpan) {
        btnSimpan.addEventListener('click', async () => {
            if(!currentPasienData || !finalPrediction) return;
            
            btnSimpan.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan Data...`;
            btnSimpan.disabled = true;

            try {
                const response = await fetch('../api/simpan_prediksi.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_pasien: currentPasienData.id_pasien, // Foreign Key tabel pasien
                        id_atribut: currentPasienData.id_atribut, // Foreign Key tabel atribut_kesehatan
                        hasil_prediksi: finalPrediction // 'Tinggi' atau 'Rendah'
                    })
                });

                const result = await response.json();
                if(result.status === 'success') {
                    // Redirect & toast
                    window.location.href = 'laporan.php?toast=saved'; 
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Database Menolak',
                        text: result.message
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Jaringan',
                    text: err
                });
            } finally {
                btnSimpan.innerHTML = `<i class="fas fa-database me-2"></i>Simpan Diagnosa ke Tabel Prediksi`;
                btnSimpan.disabled = false;
            }
        });
    }

    function hitungNaiveBayes(dataUji, dataLatih) {
        logikaNB.innerHTML = `
            <div class="mb-3">
                <span class="badge bg-primary fs-6 mb-2"><i class="fas fa-search me-1"></i> Fase 1: Identifikasi Data Uji / Query</span>
                <p class="mb-2">Menganalisis profil atribut ID Pasien: <strong>[${dataUji.nik}] - ${dataUji.nama}</strong></p>
                <div class="bg-dark text-warning p-2 rounded small" style="border:1px solid #444;">
                    Desa (Alamat): ${dataUji.desa}<br>
                    Umur: ${dataUji.umur} | J.Kelamin: ${dataUji.jenis_kelamin}<br>
                    Tekanan Darah: ${dataUji.tekanan_sistolik}/${dataUji.tekanan_diastolik} mmHg | IMT: ${dataUji.imt}<br>
                    Riwayat Hipertensi Klg: ${dataUji.riwayat_hipertensi} | Riwayat Diabetes: ${dataUji.diabetes}<br>
                    Gaya Hidup (Rokok, Alkohol, Sy/Bh): ${dataUji.merokok}, ${dataUji.konsumsi_alkohol}, ${dataUji.kurang_buah_sayur}
                </div>
            </div>`;

        const totalData = dataLatih.length;
        if(totalData === 0) {
            logikaNB.innerHTML += `<div class="alert alert-danger"><strong>Dataset Training Kosong!</strong> Model AI tidak dapat menghitung probabilitas prior dan likelihood karena tabel hasil_prediksi masih kosong. Silakan injek data latih ke dalam database.</div>`;
            return;
        }

        const cTinggi = dataLatih.filter(d => d.hasil_prediksi === 'Tinggi').length;
        const cRendah = dataLatih.filter(d => d.hasil_prediksi === 'Rendah').length;

        const pTinggi = cTinggi / totalData;
        const pRendah = cRendah / totalData;

        logikaNB.innerHTML += `
            <div class="mb-3 mt-4">
                <span class="badge bg-secondary fs-6 mb-2"><i class="fas fa-percentage me-1"></i> Fase 2: Probabilitas Prior P(C)</span>
                <p class="mb-2 text-muted small">Menghitung base class count dari ${totalData} total records knowledge-base pelatihan.</p>
                <div class="row">
                    <div class="col-6">
                        <div class="p-2 border border-secondary rounded text-center">
                            <strong class="text-danger d-block mb-1">P(Tinggi)</strong>
                            <code class="text-light">${cTinggi} / ${totalData} = <br><b class="fs-5">${pTinggi.toFixed(4)}</b></code>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border border-secondary rounded text-center">
                            <strong class="text-success d-block mb-1">P(Rendah)</strong>
                            <code class="text-light">${cRendah} / ${totalData} = <br><b class="fs-5">${pRendah.toFixed(4)}</b></code>
                        </div>
                    </div>
                </div>
            </div>`;

        function countKategorik(attr, valObj, kelas) {
            const subset = dataLatih.filter(d => d.hasil_prediksi === kelas);
            const count = subset.filter(d => d[attr] === valObj).length;
            // Laplace Smoothing standar: (X_c + 1) / (N_c + M_unik)
            // Asumsi kasar M_unik atribut klasifikasi 2, kita hardcode denominator +2.
            return (count + 1) / (subset.length + 2); 
        }

        // Gaussian
        function getMeanVar(attr, kelas) {
            const subset = dataLatih.filter(d => d.hasil_prediksi === kelas);
            const values = subset.map(d => parseFloat(d[attr]));
            const mean = values.reduce((a, b) => a + b, 0) / values.length;
            const variance = values.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / values.length;
            return { mean, variance };
        }
        function gaussianLikelihood(x, mean, varc) {
            if(varc === 0) varc = 0.0001; // Cegah div0 error
            const exp = Math.exp(-Math.pow(x - mean, 2) / (2 * varc));
            return (1 / Math.sqrt(2 * Math.PI * varc)) * exp;
        }

        let likeTinggi = 1;
        let likeRendah = 1;

        let tableL = `<div class="mb-3 mt-4">
            <span class="badge bg-info text-dark fs-6 mb-2"><i class="fas fa-table me-1"></i> Fase 3: Likelihood Estimator P(X|C)</span>
            <div class="table-responsive"><table class="table table-dark table-bordered table-sm">
            <thead class="table-secondary text-dark text-center"><tr><th>Variabel Fitur</th><th>Nilai Uji (X)</th><th>P(x|Tinggi)</th><th>P(x|Rendah)</th></tr></thead><tbody class="text-center">`;

        // Atribut Kategorik dari ERD
        const attrsCat = ['jenis_kelamin', 'merokok', 'konsumsi_alkohol', 'kurang_buah_sayur', 'diabetes', 'riwayat_hipertensi', 'desa'];
        attrsCat.forEach(attr => {
            const val = dataUji[attr];
            const pxT = countKategorik(attr, val, 'Tinggi');
            const pxR = countKategorik(attr, val, 'Rendah');
            likeTinggi *= pxT;
            likeRendah *= pxR;
            
            tableL += `<tr><td class="text-start"><small>${attr}</small></td><td><b>${val}</b></td><td class="text-danger">${pxT.toFixed(4)}</td><td class="text-success">${pxR.toFixed(4)}</td></tr>`;
        });

        // Atribut Numerikal Kontinu dari ERD (Dengan Distribusi Normal/Gaussian PDF)
        const attrsNum = ['umur', 'tekanan_sistolik', 'tekanan_diastolik', 'imt'];
        attrsNum.forEach(attr => {
            const val = parseFloat(dataUji[attr]);
            const sT = getMeanVar(attr, 'Tinggi');
            const sR = getMeanVar(attr, 'Rendah');
            
            const pxT = gaussianLikelihood(val, sT.mean, sT.variance);
            const pxR = gaussianLikelihood(val, sR.mean, sR.variance);

            likeTinggi *= pxT;
            likeRendah *= pxR;
            tableL += `<tr><td class="text-start"><small>${attr} <span class="badge bg-primary px-1">Gauss/Num</span></small></td><td><b>${val}</b></td><td class="text-danger">${pxT.toExponential(4)}</td><td class="text-success">${pxR.toExponential(4)}</td></tr>`;
        });

        tableL += `</tbody></table></div></div>`;
        logikaNB.innerHTML += tableL;

        // Fase 4: Posterior (MAP)
        const postTinggi = pTinggi * likeTinggi;
        const postRendah = pRendah * likeRendah;

        logikaNB.innerHTML += `
            <div class="mb-3 mt-4">
                <span class="badge bg-warning text-dark fs-6 mb-2"><i class="fas fa-balance-scale"></i> Fase 4: Maximum A Posteriori (MAP)</span>
                <p class="text-muted small mb-2">Mengkalikan Prior secara independen dengan seluruh agregasi nilai Likelihood.</p>
                <div class="bg-black p-3 rounded" style="border-left:4px solid #ffc107;">
                    <div class="mb-2">
                        <span class="text-danger d-inline-block" style="width:140px;">Posterior(Tinggi)</span> = ${pTinggi.toFixed(4)} × ${likeTinggi.toExponential(4)}<br>
                        => <strong class="fs-4 text-danger">${postTinggi.toExponential(4)}</strong>
                    </div>
                    <div>
                        <span class="text-success d-inline-block" style="width:140px;">Posterior(Rendah)</span> = ${pRendah.toFixed(4)} × ${likeRendah.toExponential(4)}<br>
                        => <strong class="fs-4 text-success">${postRendah.toExponential(4)}</strong>
                    </div>
                </div>
            </div>`;

        // FASE 5: ArgMax Threshold
        if(postTinggi > postRendah) {
            finalPrediction = 'Tinggi';
            hasilAkhirTeks.innerHTML = `<span class="badge bg-danger py-2 px-4 shadow">RISIKO TINGGI</span>`;
        } else {
            finalPrediction = 'Rendah';
            hasilAkhirTeks.innerHTML = `<span class="badge bg-success py-2 px-4 shadow">RISIKO RENDAH</span>`;
        }
    }
});
