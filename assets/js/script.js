// Sidebar Toggle
document.getElementById('menu-toggle').addEventListener('click', function() {
    document.getElementById('wrapper').classList.toggle('toggled');
});

// Tutup sidebar jika overlay diklik (hanya berlaku di mobile karena di desktop hilang)
const sidebarOverlay = document.getElementById('sidebar-overlay');
if(sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function() {
        document.getElementById('wrapper').classList.remove('toggled');
    });
}

// Inisialisasi DataTables otomatis jika ada (untuk Halaman Data Pasien/Laporan)
$(document).ready(function() {
    if($('#tablePasien').length) {
        $('#tablePasien').DataTable();
    }
    if($('#tableLaporan').length) {
        $('#tableLaporan').DataTable();
    }
});
